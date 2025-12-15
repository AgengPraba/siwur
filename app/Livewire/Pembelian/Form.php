<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PembayaranPembelian;
use App\Models\TransaksiGudangStock;
use App\Models\GudangStock;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\Supplier;
use Carbon\Carbon;

#[Title('Form Pembelian')]
class Form extends Component
{
    use Toast;

    public $breadcrumbs;
    public $type = 'create';
    public $pembelian_ID;
    public $pembelian;
    // Header Pembelian
    public $nomor_pembelian;
    public $tanggal_pembelian;
    public $supplier_id;
    public $user_id;
    public $keterangan;
    public $total_harga = 0;
    public $status = 'belum_bayar';

    // Detail Items
    public $details = [];
    public $detail_barang_id;
    public $detail_satuan_id;
    public $detail_gudang_id;
    public $detail_harga_satuan = 0;
    public $detail_jumlah = 1;
    public $detail_rencana_harga_jual = 0;
    public $detail_diskon = 0;
    public $detail_biaya_lain = 0;
    public $detail_diskon_tipe = [
        ['label' => '%', 'value' => 'persen'],
        ['label' => 'Rp', 'value' => 'nominal'],
    ];
    public $diskon_tipe = 'nominal';


    // Barcode Scanner
    public $barcode_input = '';
    public $barcode_message = '';
    public $barcode_message_type = 'info'; // info, success, error

    // Pembayaran
    public $payments = [];
    public $payment_jenis = 'cash';
    public $payment_jumlah = 0;
    public $payment_keterangan;
    public $payment_kembalian = 0; // Add kembalian property

    // Data untuk dropdown
    public $supplier_data = [];
    public $barang_data = [];
    public $barang_searchable = []; // For searchable barang
    public $satuan_data = [];
    public $gudang_data = [];
    public $barang_satuan_data = [];

    // Computed Properties
    public $subtotal_sebelum_diskon = 0;
    public $total_payment = 0;
    public $sisa_pembayaran = 0;
    public $total_kembalian = 0; // Add total kembalian property

    public function mount($id = null)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        $this->tanggal_pembelian = Carbon::now()->format('Y-m-d H:i');
        $this->user_id = Auth::id();
        $this->loadDropdownData();
        $this->generateNomorPembelian();

        if ($id) {
            $this->loadEditData($id);
        } else {
            // Get default supplier for the toko
            $defaultSupplier = DB::table('supplier')->where('toko_id', $user->akses->toko_id)->first();
            if ($defaultSupplier) {
                $this->supplier_id = $defaultSupplier->id;
            }
            // Load data from session if available (for create mode)
            $this->loadFromSession();
        }

        $this->user_id = Auth::id();

        $akses = $user->akses ?? null;
        $namaTokoLabel = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => "Riwayat Pembelian", 'link' => route('pembelian.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }

    private function loadDropdownData()
    {
        // Get current user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses) {
            $this->error('Anda tidak memiliki akses ke toko manapun.');
            return;
        }
        
        $tokoId = $user->akses->toko_id;

        // Supplier data - using Eloquent with HasTenancy
        $this->supplier_data = DB::table('supplier')->where('toko_id', $tokoId)->where('is_opname', false)
            ->select('id', 'nama_supplier as nama')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

        // Barang data with jenis - using Eloquent with HasTenancy
        $this->barang_data = DB::table('barang')->where('barang.toko_id', $tokoId)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('jenis_barang.toko_id', $tokoId)
            ->select('barang.id', DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as nama"))
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();

            

        // Initialize searchable barang with first 10 items
        $this->searchBarang();

        // Gudang data - using Eloquent with HasTenancy
        $this->gudang_data = DB::table('gudang')->where('toko_id', $tokoId)
            ->select('id', 'nama_gudang as nama')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    /**
     * Search method for Mary UI Choices component
     * Called automatically when user types in the searchable choices component
     */
    public function search(string $value = '')
    {
        $this->searchBarang($value);
    }

    /**
     * Search barang with server-side filtering
     * Called automatically when user types in the searchable choices component
     */
    public function searchBarang(string $value = '')
    {
        // Get current user's toko_id
        $user = Auth::user();
        if (!$user || !$user->akses) {
            $this->barang_searchable = [];
            return;
        }
        
        $tokoId = $user->akses->toko_id;

        // Get currently selected barang if exists
        $selectedOption = collect();
        if ($this->detail_barang_id) {
            $selectedOption = DB::table('barang')->where('barang.toko_id', $tokoId)
                ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->where('jenis_barang.toko_id', $tokoId)
                ->where('barang.id', $this->detail_barang_id)
                ->select('barang.id', DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as nama"))
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                });
        }

        // Search barang based on nama_barang or kode_barang - dengan filtering toko_id
        $searchResults = DB::table('barang')->where('barang.toko_id', $tokoId)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('jenis_barang.toko_id', $tokoId)
            ->select('barang.id', DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as nama"))
            ->where(function ($query) use ($value) {
                if (!empty($value)) {
                    $query->where('barang.nama_barang', 'like', "%{$value}%")
                        ->orWhere('barang.kode_barang', 'like', "%{$value}%");
                }
            })
            ->orderBy('barang.nama_barang')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            });

        // Merge selected option with search results to ensure selected item is always available
        $this->barang_searchable = $searchResults->merge($selectedOption)->unique('id')->values()->toArray();
    }

    private function generateNomorPembelian()
    {
        // Get current user's toko_id
        $user = Auth::user();
        $tokoId = $user->akses->toko_id;
        
        $today = Carbon::now()->format('Ymd');
        $count = Pembelian::whereDate('created_at', Carbon::today())->count() + 1;
        $this->nomor_pembelian = 'PB-' . $tokoId . '-' . $today . '-' . sprintf('%03d', $count);
    }

    private function loadEditData($id)
    {
        // Pastikan data pembelian yang dimuat sesuai dengan toko user
        $pembelian = Pembelian::forToko()->with(['pembelianDetails', 'pembayaranPembelian'])->findOrFail($id);
        $this->type = 'edit';
        $this->pembelian_ID = $pembelian->id;
        $this->nomor_pembelian = $pembelian->nomor_pembelian;
        $this->tanggal_pembelian = $pembelian->tanggal_pembelian;
        $this->supplier_id = $pembelian->supplier_id;
        $this->user_id = $pembelian->user_id;
        $this->keterangan = $pembelian->keterangan;
        $this->total_harga = $pembelian->total_harga;
        $this->status = $pembelian->status;

        // Load details
        $this->details = $pembelian->pembelianDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'barang_nama' => $detail->barang->nama_barang,
                'satuan_id' => $detail->satuan_id,
                'satuan_nama' => $detail->satuan->nama_satuan,
                'gudang_id' => $detail->gudang_id,
                'gudang_nama' => $detail->gudang->nama_gudang,
                'harga_satuan' => $detail->harga_satuan,
                'jumlah' => $detail->jumlah,
                'konversi_satuan_terkecil' => $detail->konversi_satuan_terkecil,
                'diskon' => $detail->diskon,
                'biaya_lain' => $detail->biaya_lain,
                'subtotal' => $detail->subtotal,
                'total_harga' => $detail->subtotal - $detail->diskon * $detail->jumlah,
                'rencana_harga_jual' => $detail->rencana_harga_jual,
            ];
        })->toArray();

        // Load payments
        $this->payments = $pembelian->pembayaranPembelian->map(function ($payment) {
            return [
                'id' => $payment->id,
                'jenis_pembayaran' => $payment->jenis_pembayaran,
                'jumlah' => $payment->jumlah,
                'kembalian' => $payment->kembalian ?? 0,
                'keterangan' => $payment->keterangan,
            ];
        })->toArray();

        $this->calculateTotals();
        // Update barang_searchable after loading edit data
        $this->searchBarang();
    }

    private function saveToSession()
    {
        $sessionKey = 'pembelian_temp_' . $this->user_id;

        session()->put($sessionKey, [
            'nomor_pembelian' => $this->nomor_pembelian,
            'tanggal_pembelian' => $this->tanggal_pembelian,
            'supplier_id' => $this->supplier_id,
            'keterangan' => $this->keterangan,
            'details' => $this->details,
            'payments' => $this->payments,
            'total_harga' => $this->total_harga,
            'status' => $this->status,
            'payment_kembalian' => $this->payment_kembalian,
            'barcode_message' => $this->barcode_message,
            'barcode_message_type' => $this->barcode_message_type,
        ]);
    }

    private function loadFromSession()
    {
        $sessionKey = 'pembelian_temp_' . $this->user_id;
        $sessionData = session()->get($sessionKey);

        if ($sessionData) {
            // Check if the saved nomor_pembelian is still valid (not exists in DB)
            $savedNomor = $sessionData['nomor_pembelian'] ?? null;
            $user = Auth::user();
            $tokoId = $user->akses->toko_id;
            
            if ($savedNomor && Pembelian::where('toko_id', $tokoId)->where('nomor_pembelian', $savedNomor)->exists()) {
                // If nomor already exists, generate new one
                $this->generateNomorPembelian();
            } else {
                $this->nomor_pembelian = $savedNomor ?? $this->nomor_pembelian;
            }
            
            $this->tanggal_pembelian = $sessionData['tanggal_pembelian'] ?? $this->tanggal_pembelian;
            $this->supplier_id = $sessionData['supplier_id'] ?? null;
            $this->keterangan = $sessionData['keterangan'] ?? '';
            $this->details = $sessionData['details'] ?? [];
            $this->payments = $sessionData['payments'] ?? [];
            $this->total_harga = $sessionData['total_harga'] ?? 0;
            $this->status = $sessionData['status'] ?? 'belum_bayar';
            $this->payment_kembalian = $sessionData['payment_kembalian'] ?? 0;
            $this->barcode_message = $sessionData['barcode_message'] ?? '';
            $this->barcode_message_type = $sessionData['barcode_message_type'] ?? 'info';

            $this->calculateTotals();
            // Update barang_searchable after loading from session
            $this->searchBarang();
        }
    }

    private function clearSession()
    {
        $sessionKey = 'pembelian_temp_' . $this->user_id;
        session()->forget($sessionKey);
    }

    public function updatedDetailBarangId()
    {
        // Reset satuan selection when barang changes
        $this->detail_satuan_id = null;

        // Update barang_searchable to include selected item
        $this->searchBarang();

        if ($this->detail_barang_id) {
            // Get current user's toko_id
            $user = Auth::user();
            if (!$user || !$user->akses) {
                $this->error('Anda tidak memiliki akses ke toko manapun.');
                return;
            }
            
            $tokoId = $user->akses->toko_id;

            $data_satuan_terkecil = DB::table('barang_satuan')->join('satuan', 'barang_satuan.satuan_id', '=', 'satuan.id')
                ->join('barang', 'barang_satuan.barang_id', '=', 'barang.id')
                ->where('barang_satuan.barang_id', $this->detail_barang_id)
                ->where('barang.toko_id', $tokoId)
                ->select('satuan.id', 'satuan.nama_satuan as nama', 'barang_satuan.konversi_satuan_terkecil')
                ->where('barang_satuan.konversi_satuan_terkecil', 1)
                ->first();
                
            if ($data_satuan_terkecil) {
                $this->detail_satuan_id = $data_satuan_terkecil->id;
            }
            
            // Get first gudang for this toko
            $firstGudang = DB::table('gudang')->where('toko_id', $tokoId)->first();
            if ($firstGudang) {
                $this->detail_gudang_id = $firstGudang->id;
            }
            
            $this->satuan_data = DB::table('barang_satuan')->join('satuan', 'barang_satuan.satuan_id', '=', 'satuan.id')
                ->join('barang', 'barang_satuan.barang_id', '=', 'barang.id')
                ->where('barang_satuan.barang_id', $this->detail_barang_id)
                ->where('barang.toko_id', $tokoId)
                ->select('satuan.id', 'satuan.nama_satuan as nama', 'barang_satuan.konversi_satuan_terkecil')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();
        } else {
            $this->satuan_data = [];
        }
    }

    public function updatedDetailSatuanId()
    {

        // Reset harga when satuan changes
        $this->detail_harga_satuan = 0;
        $this->detail_rencana_harga_jual = 0;

        // Set default gudang if not selected
        if (!$this->detail_gudang_id && count($this->gudang_data) > 0) {
            $this->detail_gudang_id = $this->gudang_data[0]['id'];
        }

        // Save to session after satuan change
        $this->saveToSession();
    }

    public function updatedBarcodeInput()
    {
        if (!empty($this->barcode_input)) {
            $this->scanBarcode();
        }
    }

    public function scanBarcode()
    {
        // Trim dan clean input
        $this->barcode_input = trim($this->barcode_input);

        if (empty($this->barcode_input)) {
            $this->barcode_message = 'Silakan masukkan kode barcode';
            $this->barcode_message_type = 'error';
            return;
        }

        // Cari barang berdasarkan kode_barang (case-insensitive) - otomatis terfilter berdasarkan toko_id
        $barang = Barang::whereRaw('LOWER(kode_barang) = LOWER(?)', [$this->barcode_input])->first();

        if (!$barang) {
            $this->barcode_message = 'Barang dengan kode "' . $this->barcode_input . '" tidak ditemukan';
            $this->barcode_message_type = 'error';
            return;
        }

        // Set barang yang ditemukan
        $this->detail_barang_id = $barang->id;
        $this->barcode_message = 'Barang "' . $barang->nama_barang . '" berhasil ditemukan';
        $this->barcode_message_type = 'success';

        // Update barang_searchable to include selected item
        $this->searchBarang();

        // Load satuan untuk barang ini
        $this->updatedDetailBarangId();

        // Auto-select satuan terkecil jika ada
        if (!empty($this->satuan_data)) {
            $satuanTerkecil = collect($this->satuan_data)->first();
            $this->detail_satuan_id = $satuanTerkecil['id'];
        }

        // Clear barcode input untuk scan berikutnya
        $this->barcode_input = '';

        // Focus ke field jumlah
        $this->dispatch('focus-jumlah', ['id' => 'jumlah-input']);

        // Dispatch event untuk JavaScript
        $this->dispatch('barcode-scanned');
    }

    public function clearBarcodeMessage()
    {
        $this->barcode_message = '';
        $this->barcode_message_type = 'info';
    }

    public function resetDetailForm()
    {
        $this->detail_barang_id = null;
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_jumlah = 1;
        $this->detail_rencana_harga_jual = 0;
        $this->detail_diskon = 0;
        $this->detail_biaya_lain = 0;
        $this->satuan_data = [];
        $this->clearBarcodeMessage();
        // Reset barang_searchable to initial state
        $this->searchBarang();
    }

    public function addDetail()
    {
        $this->validate([
            'detail_barang_id' => 'required',
            'detail_satuan_id' => 'required',
            'detail_gudang_id' => 'required',
            'detail_harga_satuan' => 'required|numeric|min:0',
            'detail_jumlah' => 'required|numeric|min:1',
            'detail_rencana_harga_jual' => 'required|numeric|min:0',
            'detail_diskon' => 'nullable|numeric|min:0',
            'detail_biaya_lain' => 'nullable|numeric|min:0',
        ]);

        // Get user toko_id for validation
        $user = Auth::user();
        $tokoId = $user->akses->toko_id;

        // Get barang and satuan info with toko_id validation
        $barang = Barang::where('id', $this->detail_barang_id)
                        ->where('toko_id', $tokoId)
                        ->first();
        $satuan = collect($this->satuan_data)->firstWhere('id', $this->detail_satuan_id);
        $gudang = collect($this->gudang_data)->firstWhere('id', $this->detail_gudang_id);

        if (!$barang || !$satuan || !$gudang) {
            $this->error('Error', 'Data barang, satuan, atau gudang tidak valid untuk toko ini.');
            return;
        }

        // Calculate subtotal with discount and additional costs
        if ($this->diskon_tipe === 'persen') {
            $subtotal = $this->detail_harga_satuan * $this->detail_jumlah;
            $this->detail_diskon = (float)($this->detail_diskon / 100) * $this->detail_harga_satuan;
            $total_harga = $subtotal - (float)$this->detail_diskon * $this->detail_jumlah + (float)$this->detail_biaya_lain;
        } elseif ($this->diskon_tipe === 'nominal') {
            $subtotal= $this->detail_harga_satuan * $this->detail_jumlah;
            $total_harga = $subtotal - (float)$this->detail_diskon * $this->detail_jumlah + (float)$this->detail_biaya_lain;
        } else {
            $this->error('Error', 'Tipe diskon tidak valid.');
            return;
        }

        $this->details[] = [
            'id' => null,
            'barang_id' => $this->detail_barang_id,
            'barang_nama' => $barang->nama_barang,
            'satuan_id' => $this->detail_satuan_id,
            'satuan_nama' => $satuan['nama'],
            'gudang_id' => $this->detail_gudang_id,
            'gudang_nama' => $gudang['nama'],
            'harga_satuan' => $this->detail_harga_satuan,
            'jumlah' => $this->detail_jumlah,
            'konversi_satuan_terkecil' => $satuan['konversi_satuan_terkecil'],
            'diskon' => $this->detail_diskon,
            'biaya_lain' => $this->detail_biaya_lain,
            'subtotal' => $subtotal,
            'total_harga' => $total_harga,
            'rencana_harga_jual' => $this->detail_rencana_harga_jual,
        ];

        

        $this->resetDetailFormPrivate();
        $this->calculateTotals();
        $this->saveToSession(); // Save to session after adding detail
        $this->success('Berhasil', 'Item berhasil ditambahkan.');

        // Clear any validation errors
        $this->resetValidation();
    }

    public function removeDetail($index)
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->calculateTotals();
        $this->saveToSession(); // Save to session after removing detail
        $this->success('Berhasil', 'Item berhasil dihapus.');
    }

    public function editDetail($index)
    {
        if (isset($this->details[$index])) {
            $detail = $this->details[$index];

            // Load data ke form untuk edit
            $this->detail_barang_id = $detail['barang_id'];
            $this->detail_satuan_id = $detail['satuan_id'];
            $this->detail_gudang_id = $detail['gudang_id'];
            $this->detail_harga_satuan = $detail['harga_satuan'];
            $this->detail_jumlah = $detail['jumlah'];
            $this->detail_rencana_harga_jual = $detail['rencana_harga_jual'];
            $this->detail_diskon = $detail['diskon'];
            $this->detail_biaya_lain = $detail['biaya_lain'];

            // Load satuan data untuk barang yang dipilih
            $this->updatedDetailBarangId();

            // Remove item dari list sementara
            $this->removeDetail($index);

            $this->info('Info', 'Item berhasil dimuat untuk diedit. Silakan ubah data dan klik "Tambah Item".');
        }
    }

    public function addPayment()
    {
        $this->validate([
            'payment_jenis' => 'required',
            'payment_jumlah' => 'required|numeric|min:1',
            'payment_keterangan' => 'required',
        ]);

        // Calculate kembalian if payment exceeds remaining amount
        $this->calculateKembalian();

        $this->payments[] = [
            'id' => null,
            'jenis_pembayaran' => $this->payment_jenis,
            'jumlah' => $this->payment_jumlah,
            'kembalian' => $this->payment_kembalian,
            'keterangan' => $this->payment_keterangan,
        ];

        $this->resetPaymentForm();
        $this->calculateTotals();
        $this->saveToSession(); // Save to session after adding payment

        if ($this->status === 'lunas') {
            if ($this->payment_kembalian > 0) {
                $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Pembelian LUNAS dengan kembalian: Rp ' . number_format($this->payment_kembalian, 0, ',', '.'));
            } else {
                $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Pembelian telah LUNAS!');
            }
        } else {
            $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Total: ' . count($this->payments) . ' pembayaran.');
        }

        // Clear validation errors
        $this->resetValidation();
    }

    public function removePayment($index)
    {
        unset($this->payments[$index]);
        $this->payments = array_values($this->payments);
        $this->calculateTotals();
        $this->saveToSession(); // Save to session after removing payment
        $this->success('Berhasil', 'Pembayaran berhasil dihapus.');
    }

    private function resetDetailFormPrivate()
    {
        $this->detail_barang_id = null;
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_jumlah = 1;
        $this->detail_rencana_harga_jual = 0;
        $this->detail_diskon = 0;
        $this->detail_biaya_lain = 0;
        $this->satuan_data = [];
        $this->clearBarcodeMessage();
        // Reset barang_searchable to initial state
        $this->searchBarang();
    }

    private function resetPaymentForm()
    {
        $this->payment_jenis = 'cash';
        $this->payment_jumlah = 0;
        $this->payment_kembalian = 0;
        $this->payment_keterangan = '';
    }

    public function calculateTotals()
    {
        $this->total_harga = collect($this->details)->sum('total_harga');

        // Calculate total payment (payment amount minus kembalian)
        $this->total_payment = collect($this->payments)->sum(function ($payment) {
            return $payment['jumlah'] - ($payment['kembalian'] ?? 0);
        });

        // Calculate total kembalian
        $this->total_kembalian = collect($this->payments)->sum(function ($payment) {
            return $payment['kembalian'] ?? 0;
        });

        $this->sisa_pembayaran = (float)$this->total_harga - (float)$this->total_payment;

        // Auto-calculate status based on payment
        $this->updatePaymentStatus();
    }

    // Helper method to get total kembalian
    public function getTotalKembalianAttribute()
    {
        return collect($this->payments)->sum(function ($payment) {
            return $payment['kembalian'] ?? 0;
        });
    }

    private function updatePaymentStatus()
    {
        if ($this->total_payment == 0) {
            $this->status = 'belum_bayar';
        } elseif ($this->total_payment < $this->total_harga) {
            $this->status = 'belum_lunas';
        } else {
            $this->status = 'lunas';
        }
    }

    public function updated($field)
    {
        if (in_array($field, ['detail_harga_satuan', 'detail_jumlah', 'detail_rencana_harga_jual', 'detail_diskon', 'detail_biaya_lain'])) {
            $this->calculateTotals();
            $this->saveToSession(); // Save to session after field update
        }

        // Calculate kembalian when payment amount changes
        if ($field === 'payment_jumlah') {
            $this->calculateKembalian();
        }

        // Save to session for other important fields
        if (in_array($field, ['supplier_id', 'tanggal_pembelian', 'keterangan'])) {
            $this->saveToSession();
        }
    }

    private function calculateKembalian()
    {
        if ($this->payment_jumlah > $this->sisa_pembayaran && $this->sisa_pembayaran > 0) {
            $this->payment_kembalian = $this->payment_jumlah - $this->sisa_pembayaran;
        } else {
            $this->payment_kembalian = 0;
        }
    }

    public function store()
    {
        $this->validate([
            'nomor_pembelian' => 'required|unique:pembelian,nomor_pembelian',
            // 'tanggal_pembelian' => 'required|date',
            'supplier_id' => 'required|exists:supplier,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
            'payments' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            // Create Pembelian
            $pembelian = Pembelian::create([
                'nomor_pembelian' => $this->nomor_pembelian,
                'tanggal_pembelian' => date('Y-m-d H:i:s', strtotime('Asia/Jakarta')),
                'supplier_id' => $this->supplier_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => $this->total_harga,
                'status' => $this->status,
                'kembalian' => $this->total_kembalian,
                // toko_id akan otomatis diset oleh HasTenancy trait
            ]);

        // Create Details
            foreach ($this->details as $detail) {
                $pembelianDetail = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'diskon' => $detail['diskon'],
                    'biaya_lain' => $detail['biaya_lain'],
                    'subtotal' => $detail['subtotal'],
                    'rencana_harga_jual' => $detail['rencana_harga_jual'],
                ]);

                // Update/Create Gudang Stock
                $gudangStock = GudangStock::firstOrCreate(
                    [
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ],
                    ['jumlah' => 0]
                );

                // Calculate quantity in smallest unit
                $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                // Update stock
                $gudangStock->increment('jumlah', $jumlahTerkecil);

                // Create stock transaction
                TransaksiGudangStock::create([
                    'gudang_stock_id' => $gudangStock->id,
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'tipe' => 'masuk',
                ]);
            }

            // Create Payments
            foreach ($this->payments as $payment) {
                PembayaranPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'user_id' => $this->user_id,
                    'jenis_pembayaran' => $payment['jenis_pembayaran'],
                    'jumlah' => $payment['jumlah'],
                    'kembalian' => $payment['kembalian'] ?? 0,
                    'keterangan' => $payment['keterangan'],
                ]);
            }
        });

        $this->clearSession(); // Clear session after successful save
        $this->success('Notifikasi', 'Berhasil menyimpan transaksi pembelian.');
        return $this->redirectRoute('pembelian.index', navigate: true);
    }

    public function update()
    {
        $this->validate([
            'nomor_pembelian' => 'required|unique:pembelian,nomor_pembelian,' . $this->pembelian_ID,
            // 'tanggal_pembelian' => 'required|date',
            'supplier_id' => 'required|exists:supplier,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
            'payments' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            $pembelian = Pembelian::findOrFail($this->pembelian_ID);

            // Update Pembelian
            $pembelian->update([
                'nomor_pembelian' => $this->nomor_pembelian,
                // 'tanggal_pembelian' => $this->tanggal_pembelian,
                'supplier_id' => $this->supplier_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => $this->total_harga,
                'status' => $this->status,
            ]);

            // Handle stock reversal for old details before deletion
            foreach ($pembelian->pembelianDetails as $oldDetail) {
                // Find related stock transaction
                $stockTransaction = TransaksiGudangStock::where('pembelian_detail_id', $oldDetail->id)->first();

                if ($stockTransaction) {
                    // Find the related gudang stock
                    $gudangStock = GudangStock::find($stockTransaction->gudang_stock_id);

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit to reverse
                        $jumlahTerkecil = $oldDetail->jumlah * $oldDetail->konversi_satuan_terkecil;

                        // Reverse the stock (subtract the old quantity)
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);

                        // If stock becomes zero or negative, you might want to handle this
                        if ($gudangStock->jumlah < 0) {
                            $gudangStock->update(['jumlah' => 0]);
                        }
                    }

                    // Delete the old stock transaction
                    $stockTransaction->delete();
                }
            }

            // Delete old details and payments
            $pembelian->pembelianDetails()->delete();
            $pembelian->pembayaranPembelian()->delete();

            // Recreate details with new stock transactions
            foreach ($this->details as $detail) {
                $pembelianDetail = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'diskon' => $detail['diskon'],
                    'biaya_lain' => $detail['biaya_lain'],
                    'subtotal' => $detail['subtotal'],
                    'rencana_harga_jual' => $detail['rencana_harga_jual'],
                ]);

                // Update/Create Gudang Stock
                $gudangStock = GudangStock::firstOrCreate(
                    [
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ],
                    ['jumlah' => 0]
                );

                // Calculate quantity in smallest unit
                $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                // Update stock
                $gudangStock->increment('jumlah', $jumlahTerkecil);

                // Create new stock transaction
                TransaksiGudangStock::create([
                    'gudang_stock_id' => $gudangStock->id,
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'penjualan_detail_id' => null, // Explicitly set to null for pembelian
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'tipe' => 'masuk',
                ]);
            }

            foreach ($this->payments as $payment) {
                PembayaranPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'user_id' => $this->user_id,
                    'jenis_pembayaran' => $payment['jenis_pembayaran'],
                    'jumlah' => $payment['jumlah'],
                    'kembalian' => $payment['kembalian'] ?? 0,
                    'keterangan' => $payment['keterangan'],
                ]);
            }
        });

        $this->clearSession(); // Clear session after successful update
        $this->success('Notifikasi', 'Berhasil mengupdate transaksi pembelian.');
        return $this->redirectRoute('pembelian.index', navigate: true);
    }

    public function quickFillPayment()
    {
        if ($this->sisa_pembayaran > 0) {
            $this->payment_jumlah = $this->sisa_pembayaran;
            $this->payment_kembalian = 0; // Reset kembalian since we're filling exact amount
            $this->payment_keterangan = 'Pelunasan pembelian ' . $this->nomor_pembelian;
            $this->saveToSession(); // Save to session after quick fill
            $this->success('Info', 'Jumlah pembayaran telah diisi untuk melunasi sisa pembayaran.');
        } else {
            $this->warning('Peringatan', 'Tidak ada sisa pembayaran yang perlu dilunasi.');
        }
    }

    public function clearTempData()
    {
        $this->clearSession();
        $this->details = [];
        $this->payments = [];
        $this->keterangan = '';
        $this->supplier_id = null;
        $this->payment_kembalian = 0;
        $this->calculateTotals();
        $this->success('Berhasil', 'Data sementara telah dihapus.');
    }

    public function addPaymentAndSave($saveOnly = false)
    {
        // Validate payment data
        if ($saveOnly == false) {
            // If only saving payment, skip the transaction validation
            $this->validate([
                'payment_jenis' => 'required',
                'payment_jumlah' => 'required|numeric|min:1',
                'payment_keterangan' => 'required',
            ]);
        }


        // Validate transaction data
        $user = Auth::user();
        $tokoId = $user->akses->toko_id;
        
        $this->validate([
            'nomor_pembelian' => [
                'required',
                'unique:pembelian,nomor_pembelian,NULL,id,toko_id,' . $tokoId
            ],
            'tanggal_pembelian' => 'required|date',
            'supplier_id' => [
                'required',
                'exists:supplier,id,toko_id,' . $tokoId
            ],
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);



        // Add payment to array
        if ($saveOnly == false) {
            // Calculate kembalian if payment exceeds remaining amount
            $this->calculateKembalian();

            $this->payments[] = [
                'id' => null,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'kembalian' => $this->payment_kembalian,
                'keterangan' => $this->payment_keterangan,
            ];
        }

        $this->calculateTotals();
        $this->tanggal_pembelian = Carbon::now()->format('Y-m-d H:i');
        
        // Get toko_id for current user
        $user = Auth::user();
        $tokoId = $user->akses->toko_id;
        
        // Save the transaction
        DB::transaction(function () use ($tokoId) {
            // Create Pembelian
            $pembelian = Pembelian::create([
                'nomor_pembelian' => $this->nomor_pembelian,
                'tanggal_pembelian' => $this->tanggal_pembelian,
                'supplier_id' => $this->supplier_id,
                'user_id' => $this->user_id,
                'toko_id' => $tokoId,
                'keterangan' => $this->keterangan,
                'total_harga' => $this->total_harga,
                'status' => $this->status,
            ]);
            $informasi_tambahan = null;
            // Create Details
            foreach ($this->details as $detail) {
                $gudangStock = GudangStock::firstOrCreate(
                    [
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ],
                    ['jumlah' => 0]
                );

                if ($gudangStock->jumlah  - ($detail['jumlah'] * $detail['konversi_satuan_terkecil'])  > 2) {
                    $informasi_tambahan .= "Barang {$detail['barang_nama']} dengan jumlah pembelian {$detail['jumlah']} di gudang {$detail['gudang_nama']} selisihnya dengan stock yang ada lebih dari 2. Stock sebelum penambahan adalah {$gudangStock->jumlah}, jadi stock gudang lebih banyak dari stock pembelian. \n";
                }
                $pembelianDetail = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'rencana_harga_jual' => $detail['rencana_harga_jual'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'diskon' => $detail['diskon'],
                    'biaya_lain' => $detail['biaya_lain'],
                    'subtotal' => $detail['subtotal'],
                ]);
                // Update/Create Gudang Stock
                // Calculate quantity in smallest unit
                $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                // Update stock
                $gudangStock->increment('jumlah', $jumlahTerkecil);

                // Create stock transaction
                TransaksiGudangStock::create([
                    'gudang_stock_id' => $gudangStock->id,
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'penjualan_detail_id' => null, // Explicitly set to null for pembelian
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'tipe' => 'masuk',
                ]);
            }

            // Update informasi_tambahan if it exists
            if (!empty($informasi_tambahan)) {
                $pembelian->update([
                    'informasi_tambahan' => $informasi_tambahan
                ]);
            }


            // Create Payments
            foreach ($this->payments as $payment) {
                PembayaranPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'user_id' => $this->user_id,
                    'jenis_pembayaran' => $payment['jenis_pembayaran'],
                    'jumlah' => $payment['jumlah'],
                    'kembalian' => $payment['kembalian'] ?? 0,
                    'keterangan' => $payment['keterangan'],
                ]);
            }
        });

        $this->clearSession(); // Clear session after successful save

        $kembalian_message = '';
        if ($this->payment_kembalian > 0) {
            $kembalian_message = ' dengan kembalian Rp ' . number_format($this->payment_kembalian, 0, ',', '.');
        }

        if ($this->status === 'lunas') {
            $this->success('Berhasil!', 'Transaksi pembelian berhasil disimpan dengan status LUNAS!' . $kembalian_message);
        } else {
            $this->success('Berhasil!', 'Transaksi pembelian berhasil disimpan dengan status ' . strtoupper(str_replace('_', ' ', $this->status)) . '.' . $kembalian_message);
        }

        return $this->redirectRoute('pembelian.index', navigate: true);
    }

    public function addPaymentAndUpdate($saveOnly = false)
    {
        if ($saveOnly == false) {
            // If only saving payment, skip the transaction validation
            $this->validate([
                'payment_jenis' => 'required',
                'payment_jumlah' => 'required|numeric|min:1',
                'payment_keterangan' => 'required',
            ]);
        }

        // Validate transaction data
        $user = Auth::user();
        $tokoId = $user->akses->toko_id;
        
        $this->validate([
            'nomor_pembelian' => [
                'required',
                'unique:pembelian,nomor_pembelian,' . $this->pembelian_ID . ',id,toko_id,' . $tokoId
            ],
            'tanggal_pembelian' => 'required|date',
            'supplier_id' => [
                'required',
                'exists:supplier,id,toko_id,' . $tokoId
            ],
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        if ($saveOnly == false) {
            // Calculate kembalian if payment exceeds remaining amount
            $this->calculateKembalian();

            // Add payment to array
            $this->payments[] = [
                'id' => null,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'kembalian' => $this->payment_kembalian,
                'keterangan' => $this->payment_keterangan,
            ];
        }


        $this->calculateTotals();

        DB::transaction(function () {
            $pembelian = Pembelian::findOrFail($this->pembelian_ID);

            // Update Pembelian
            $pembelian->update([
                'nomor_pembelian' => $this->nomor_pembelian,
                // 'tanggal_pembelian' => $this->tanggal_pembelian,
                'supplier_id' => $this->supplier_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => $this->total_harga,
                'status' => $this->status,
            ]);

            // Handle stock reversal for old details before deletion
            foreach ($pembelian->pembelianDetails as $oldDetail) {
                // Find related stock transaction
                $stockTransaction = TransaksiGudangStock::where('pembelian_detail_id', $oldDetail->id)->first();

                if ($stockTransaction) {
                    // Find the related gudang stock
                    $gudangStock = GudangStock::find($stockTransaction->gudang_stock_id);

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit to reverse
                        $jumlahTerkecil = $oldDetail->jumlah * $oldDetail->konversi_satuan_terkecil;

                        // Reverse the stock (subtract the old quantity)
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);

                        // If stock becomes zero or negative, you might want to handle this
                        if ($gudangStock->jumlah < 0) {
                            $gudangStock->update(['jumlah' => 0]);
                        }
                    }

                    // Delete the old stock transaction
                    $stockTransaction->delete();
                }
            }

            // Delete old details and payments
            try {
                $pembelian->pembelianDetails()->delete();
                $pembelian->pembayaranPembelian()->delete();
            } catch (\Illuminate\Database\QueryException $e) {
                // Check if error is due to foreign key constraint
                if (str_contains($e->getMessage(), 'foreign key constraint')) {
                    // Only delete payments if details can't be deleted due to sales reference
                    $pembelian->pembayaranPembelian()->delete();
                    $this->error('Warning', 'Beberapa detail pembelian tidak dapat dihapus karena sudah terkait dengan penjualan.');
                    return;
                }
                throw $e;
            }

            $informasi_tambahan = null;
            // Recreate details with new stock transactions
            foreach ($this->details as $detail) {
                // Update/Create Gudang Stock
                $gudangStock = GudangStock::firstOrCreate(
                    [
                        'gudang_id' => $detail['gudang_id'],
                        'barang_id' => $detail['barang_id']
                    ],
                    ['jumlah' => 0]
                );
                if ($gudangStock->jumlah  - ($detail['jumlah'] * $detail['konversi_satuan_terkecil'])  > 2) {
                    $informasi_tambahan .= "Barang {$detail['barang_nama']} dengan jumlah pembelian {$detail['jumlah']} di gudang {$detail['gudang_nama']} selisihnya dengan stock yang ada lebih dari 2. Stock sebelum penambahan adalah {$gudangStock->jumlah}, jadi stock gudang lebih banyak dari stock pembelian. \n";
                }
                $pembelianDetail = PembelianDetail::create([
                    'pembelian_id' => $pembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'rencana_harga_jual' => $detail['rencana_harga_jual'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'diskon' => $detail['diskon'],
                    'biaya_lain' => $detail['biaya_lain'],
                    'subtotal' => $detail['subtotal'],
                ]);




                // Calculate quantity in smallest unit
                $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                // Update stock
                $gudangStock->increment('jumlah', $jumlahTerkecil);

                // Create new stock transaction
                TransaksiGudangStock::create([
                    'gudang_stock_id' => $gudangStock->id,
                    'pembelian_detail_id' => $pembelianDetail->id,
                    'penjualan_detail_id' => null, // This field is for sales transactions, null for purchase transactions
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'tipe' => 'masuk',
                ]);
            }

            if ($informasi_tambahan) {
                $pembelian->update([
                    'informasi_tambahan' => $informasi_tambahan
                ]);
            }

            foreach ($this->payments as $payment) {
                PembayaranPembelian::create([
                    'pembelian_id' => $pembelian->id,
                    'user_id' => $this->user_id,
                    'jenis_pembayaran' => $payment['jenis_pembayaran'],
                    'jumlah' => $payment['jumlah'],
                    'kembalian' => $payment['kembalian'] ?? 0,
                    'keterangan' => $payment['keterangan'],
                ]);
            }
        });

        $this->clearSession(); // Clear session after successful update

        $kembalian_message = '';
        if ($this->payment_kembalian > 0) {
            $kembalian_message = ' dengan kembalian Rp ' . number_format($this->payment_kembalian, 0, ',', '.');
        }

        if ($this->status === 'lunas') {
            $this->success('Berhasil!', 'Transaksi pembelian berhasil diupdate dengan status LUNAS!' . $kembalian_message);
        } else {
            $this->success('Berhasil!', 'Transaksi pembelian berhasil diupdate dengan status ' . strtoupper(str_replace('_', ' ', $this->status)) . '.' . $kembalian_message);
        }

        return $this->redirectRoute('pembelian.show', $this->pembelian_ID, navigate: true);
    }


    public function render()
    {
        return view('livewire.pembelian.form');
    }
}

/* End of file Form.php */
/* Location: ./app/Livewire/Pembelian/Form.php */