<?php

namespace App\Livewire\Penjualan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\PembayaranPenjualan;
use App\Models\TransaksiGudangStock;
use App\Models\GudangStock;
use App\Models\Barang;
use App\Models\BarangSatuan;
use App\Models\PembelianDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

#[Title('Form Penjualan')]
class Form extends Component
{
    use Toast;

    public $breadcrumbs;
    // Hydration optimization methods
    public function hydrate()
    {
        try {
            // Ensure arrays are properly initialized with safe defaults
            $this->details = is_array($this->details) ? $this->details : [];
            $this->payments = is_array($this->payments) ? $this->payments : [];
            $this->satuan_data = is_array($this->satuan_data) ? $this->satuan_data : [];
            $this->pembelian_detail_data = is_array($this->pembelian_detail_data) ? $this->pembelian_detail_data : [];

            // Sanitize numeric values with better validation
            $this->total_harga = $this->sanitizeNumeric($this->total_harga, 0);
            $this->detail_harga_satuan = $this->sanitizeNumeric($this->detail_harga_satuan, 0);
            $this->detail_harga_beli = $this->sanitizeNumeric($this->detail_harga_beli, 0);
            $this->detail_jumlah = $this->sanitizeNumeric($this->detail_jumlah, 1, 0.01);
            $this->detail_jumlah_tersedia = $this->sanitizeNumeric($this->detail_jumlah_tersedia, 0);
            $this->detail_diskon = $this->sanitizeNumeric($this->detail_diskon, 0);
            $this->detail_biaya_lain = $this->sanitizeNumeric($this->detail_biaya_lain, 0);
            $this->payment_jumlah = $this->sanitizeNumeric($this->payment_jumlah, 0);

            // Validate entire component state after hydration
            $this->validateComponentState();
        } catch (\Exception $e) {
            Log::error('Hydration error: ' . $e->getMessage());
            $this->resetToSafeDefaults();
        }
    }

    /**
     * Sanitize numeric values with proper validation
     */
    private function sanitizeNumeric($value, $default = 0, $min = null)
    {
        if (is_null($value) || $value === '' || $value === false) {
            return $default;
        }

        if (is_string($value)) {
            $value = str_replace([',', ' '], '', $value);
        }

        if (!is_numeric($value)) {
            return $default;
        }

        $numericValue = (float) $value;

        if ($min !== null && $numericValue < $min) {
            return $min;
        }

        return $numericValue;
    }

    /**
     * Reset component to safe defaults in case of corruption
     */
    private function resetToSafeDefaults()
    {
        $this->details = [];
        $this->payments = [];
        $this->satuan_data = [];
        $this->pembelian_detail_data = [];
        $this->total_harga = 0;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah = 1;
        $this->detail_jumlah_tersedia = 0;
        $this->payment_jumlah = 0;
        $this->status = 'belum_bayar';
        $this->barcode_search = '';
    }

    /**
     * Prevent rapid updates that can cause hydration issues
     */
    private function isRapidUpdate($field)
    {
        $now = microtime(true);
        $key = 'last_update_' . $field;

        if (!isset($this->lastUpdateTimes)) {
            $this->lastUpdateTimes = [];
        }

        if (isset($this->lastUpdateTimes[$key])) {
            $timeDiff = $now - $this->lastUpdateTimes[$key];
            // Prevent updates faster than 100ms (0.1 seconds)
            if ($timeDiff < 0.1) {
                return true;
            }
        }

        $this->lastUpdateTimes[$key] = $now;
        return false;
    }

    public function dehydrate()
    {

        // Clean up and optimize data before serialization
        // Remove any null or invalid entries from arrays
        $this->details = array_filter($this->details ?? [], function ($detail) {
            return is_array($detail) && isset($detail['barang_id']);
        });

        $this->payments = array_filter($this->payments ?? [], function ($payment) {
            return is_array($payment) && isset($payment['jenis_pembayaran']);
        });

        // Reset array indexes
        $this->details = array_values($this->details);
        $this->payments = array_values($this->payments);
    }

    // Method to validate and sanitize component state
    private function validateComponentState()
    {
        try {
            // Ensure user_id is set
            if (!$this->user_id) {
                $this->user_id = Auth::id();
            }

            // Validate and sanitize basic properties
            $this->nomor_penjualan = $this->nomor_penjualan ?? '';
            $this->tanggal_penjualan = $this->tanggal_penjualan ?? Carbon::now()->format('Y-m-d H:i:s');
            $this->customer_id = is_numeric($this->customer_id) ? (int) $this->customer_id : null;

            $this->keterangan = $this->keterangan ?? '';
            $this->status = in_array($this->status, ['belum_bayar', 'belum_lunas', 'lunas']) ? $this->status : 'belum_bayar';

            // Validate arrays
            if (!is_array($this->details)) {
                $this->details = [];
            }
            if (!is_array($this->payments)) {
                $this->payments = [];
            }
            if (!is_array($this->satuan_data)) {
                $this->satuan_data = [];
            }
            if (!is_array($this->pembelian_detail_data)) {
                $this->pembelian_detail_data = [];
            }

            // Validate detail form properties
            $this->detail_barang_id = is_numeric($this->detail_barang_id) ? (int) $this->detail_barang_id : null;
            $this->detail_satuan_id = is_numeric($this->detail_satuan_id) ? (int) $this->detail_satuan_id : null;
            $this->detail_gudang_id = is_numeric($this->detail_gudang_id) ? (int) $this->detail_gudang_id : null;
            $this->detail_pembelian_detail_id = is_numeric($this->detail_pembelian_detail_id) ? (int) $this->detail_pembelian_detail_id : null;
            $this->detail_harga_satuan = is_numeric($this->detail_harga_satuan) ? (float) $this->detail_harga_satuan : 0;
            $this->detail_harga_beli = is_numeric($this->detail_harga_beli) ? (float) $this->detail_harga_beli : 0;
            $this->detail_jumlah = is_numeric($this->detail_jumlah) && $this->detail_jumlah > 0 ? (float) $this->detail_jumlah : 1;
            $this->detail_jumlah_tersedia = is_numeric($this->detail_jumlah_tersedia) ? (float) $this->detail_jumlah_tersedia : 0;

            // Validate payment form properties
            $this->payment_jenis = in_array($this->payment_jenis, ['cash', 'transfer', 'check', 'other']) ? $this->payment_jenis : 'cash';
            $this->payment_jumlah = is_numeric($this->payment_jumlah) ? (float) $this->payment_jumlah : 0;
            $this->payment_keterangan = $this->payment_keterangan ?? '';

            // Validate barcode search
            $this->barcode_search = $this->barcode_search ?? '';

            return true;
        } catch (\Exception $e) {
            Log::error('Component state validation failed: ' . $e->getMessage());
            return false;
        }
    }
    public $type = 'create';
    public $penjualan_ID;
    public $penjualan;
    public $nama_satuan;
    // Header Penjualan
    public $nomor_penjualan;
    public $tanggal_penjualan;
    public $customer_id;
    public $user_id;
    public $keterangan;
    public $total_harga = 0;
    public $status = 'belum_bayar';
    // Options list
    public $getBarangSearch;
    // Barcode scanning
    public $barcode_search = '';
    // Detail Items
    public $details = [];
    public $detail_barang_id;
    public $detail_satuan_id;
    public $detail_gudang_id;
    public $detail_pembelian_detail_id;
    public $detail_harga_satuan = 0;
    public $detail_harga_beli = 0;
    public $detail_jumlah = 1;
    public $detail_jumlah_tersedia = 0;
    public $detail_diskon = 0;
    public $detail_biaya_lain = 0;

    // Pembayaran
    public $payments = [];
    public $payment_jenis = 'cash';
    public $payment_jumlah = 0;
    public $payment_keterangan;

    // Data untuk dropdown - converted to computed properties to reduce state size
    public $satuan_data = [];
    public $pembelian_detail_data = [];

    // Computed Properties
    public $subtotal_sebelum_diskon = 0;
    public $total_payment = 0;
    public $sisa_pembayaran = 0;
    public $total_kembalian = 0;

    public $showExtraColumns = false;

    // For preventing rapid updates
    private $lastUpdateTimes = [];

    // Computed properties to reduce state size
    public function getCustomerDataProperty()
    {
        return DB::table('customer')
            ->select('id', 'nama_customer as name')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function getBarangDataProperty()
    {
        return DB::table('barang')
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->select('barang.id', DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as name"))
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function getGudangDataProperty()
    {
        return DB::table('gudang')
            ->select('id', 'nama_gudang as name')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    public function mount($id = null)
    {
        $this->tanggal_penjualan = Carbon::now()->format('Y-m-d H:i:s');
        $this->user_id = Auth::id();
        $this->generateNomorPenjualan();
        $this->search();
        if ($id) {
            $this->type = 'edit';
            $this->loadEditData($id);
        } else {
            $this->type = 'create';
            // Load data from session if available (for create mode)
            $this->loadFromSession();

            // Log mount process untuk debugging
            // Log::info('Component mounted in create mode', [
            //     'user_id' => $this->user_id,
            //     'details_loaded' => count($this->details ?? []),
            //     'payments_loaded' => count($this->payments ?? [])
            // ]);
        }

        // Validate component state after mounting
        $this->validateComponentState();

        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Penjualan', 'href' => route('penjualan.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }



    private function generateNomorPenjualan()
    {
        $today = Carbon::now()->format('Ymd');
        $count = Penjualan::whereDate('created_at', Carbon::today())->count() + 1;
        $this->nomor_penjualan = 'PJ' . $today . sprintf('%03d', $count);
    }

    private function generateNomorPenjualan_final()
    {
        $today = Carbon::now()->format('Ymd');
        $count = Penjualan::whereDate('created_at', Carbon::today())->count() + 1;
        $nomor_penjualan = 'PJ' . $today . sprintf('%03d', $count);
        return $nomor_penjualan;
    }

    private function loadEditData($id)
    {
        $penjualan = Penjualan::with(['penjualanDetails', 'pembayaranPenjualan'])->findOrFail($id);
        $this->type = 'edit';
        $this->penjualan_ID = $penjualan->id;
        $this->nomor_penjualan = $penjualan->nomor_penjualan;
        $this->tanggal_penjualan = $penjualan->tanggal_penjualan;
        $this->customer_id = $penjualan->customer_id;
        $this->user_id = Auth::id();
        $this->keterangan = $penjualan->keterangan;
        $this->total_harga = $penjualan->total_harga;
        $this->status = $penjualan->status;


        // Load details
        $this->details = $penjualan->penjualanDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'barang_id' => $detail->barang_id,
                'barang_nama' => $detail->barang->nama_barang,
                'satuan_id' => $detail->satuan_id,
                'satuan_nama' => $detail->satuan->nama_satuan,
                'gudang_id' => $detail->gudang_id,
                'gudang_nama' => $detail->gudang->nama_gudang,
                'pembelian_detail_id' => $detail->pembelian_detail_id,
                'harga_satuan' => $detail->harga_satuan,
                'harga_beli' => $detail->pembelianDetail->harga_satuan,
                'jumlah' => $detail->jumlah,
                'nomor_pembelian'   => $detail->pembelianDetail->pembelian->nomor_pembelian,
                'konversi_satuan_terkecil' => $detail->konversi_satuan_terkecil,
                'subtotal' => $detail->subtotal,
                'profit' => $detail->profit,
                'diskon'    => $detail->diskon,
                'biaya_lain'    => $detail->biaya_lain
            ];
        })->toArray();

        // Load payments
        $this->payments = $penjualan->pembayaranPenjualan->map(function ($payment) {
            return [
                'id' => $payment->id,
                'jenis_pembayaran' => $payment->jenis_pembayaran,
                'jumlah' => $payment->jumlah,
                'keterangan' => $payment->keterangan,
            ];
        })->toArray();

        $this->calculateTotals();
    }

    private function saveToSession()
    {
        try {
            $sessionKey = 'penjualan_temp_' . $this->user_id;

            // Sanitize and optimize data before saving to session
            $sessionData = [
                'nomor_penjualan' => $this->nomor_penjualan ?? '',
                'tanggal_penjualan' => $this->tanggal_penjualan ?? Carbon::now()->format('Y-m-d H:i:s'),
                'customer_id' => $this->customer_id,
                'keterangan' => $this->keterangan ?? '',
                'details' => array_values(array_filter($this->details ?? [], function ($detail) {
                    return is_array($detail) && isset($detail['barang_id']);
                })),
                'payments' => array_values(array_filter($this->payments ?? [], function ($payment) {
                    return is_array($payment) && isset($payment['jenis_pembayaran']);
                })),
                'total_harga' => is_numeric($this->total_harga) ? (float) $this->total_harga : 0,
                'status' => $this->status ?? 'belum_bayar',
                'subtotal_sebelum_diskon' => is_numeric($this->subtotal_sebelum_diskon) ? (float) $this->subtotal_sebelum_diskon : 0,
                'total_payment' => is_numeric($this->total_payment) ? (float) $this->total_payment : 0,
                'sisa_pembayaran' => is_numeric($this->sisa_pembayaran) ? (float) $this->sisa_pembayaran : 0,
                'total_kembalian' => is_numeric($this->total_kembalian) ? (float) $this->total_kembalian : 0,
            ];

            session()->put($sessionKey, $sessionData);

            // Debug log untuk memastikan data tersimpan
            // Log::info('Session saved successfully', [
            //     'user_id' => $this->user_id,
            //     'details_count' => count($this->details ?? []),
            //     'payments_count' => count($this->payments ?? []),
            //     'session_key' => $sessionKey
            // ]);
        } catch (\Exception $e) {
            // If session save fails, log error but don't break the application
            Log::error('Failed to save penjualan session data: ' . $e->getMessage());
        }
    }

    private function loadFromSession()
    {
        try {
            $sessionKey = 'penjualan_temp_' . $this->user_id;
            $sessionData = session()->get($sessionKey);

            if ($sessionData && is_array($sessionData)) {
                $this->nomor_penjualan = $sessionData['nomor_penjualan'] ?? $this->nomor_penjualan;
                $this->tanggal_penjualan = $sessionData['tanggal_penjualan'] ?? $this->tanggal_penjualan;
                $this->customer_id = $sessionData['customer_id'] ?? DB::table('customer')->select('id')->first()->id;

                $this->keterangan = $sessionData['keterangan'] ?? '';

                // Safely load arrays with validation
                $this->details = is_array($sessionData['details'] ?? []) ?
                    array_values(array_filter($sessionData['details'], function ($detail) {
                        return is_array($detail) && isset($detail['barang_id']);
                    })) : [];

                $this->payments = is_array($sessionData['payments'] ?? []) ?
                    array_values(array_filter($sessionData['payments'], function ($payment) {
                        return is_array($payment) && isset($payment['jenis_pembayaran']);
                    })) : [];

                $this->total_harga = is_numeric($sessionData['total_harga'] ?? 0) ? (float) $sessionData['total_harga'] : 0;
                $this->status = in_array($sessionData['status'] ?? 'belum_bayar', ['belum_bayar', 'belum_lunas', 'lunas']) ?
                    $sessionData['status'] : 'belum_bayar';

                // Load computed properties
                $this->subtotal_sebelum_diskon = is_numeric($sessionData['subtotal_sebelum_diskon'] ?? 0) ? (float) $sessionData['subtotal_sebelum_diskon'] : 0;
                $this->total_payment = is_numeric($sessionData['total_payment'] ?? 0) ? (float) $sessionData['total_payment'] : 0;
                $this->sisa_pembayaran = is_numeric($sessionData['sisa_pembayaran'] ?? 0) ? (float) $sessionData['sisa_pembayaran'] : 0;
                $this->total_kembalian = is_numeric($sessionData['total_kembalian'] ?? 0) ? (float) $sessionData['total_kembalian'] : 0;

                // Debug log untuk memastikan data dimuat
                // Log::info('Session loaded successfully', [
                //     'user_id' => $this->user_id,
                //     'details_count' => count($this->details),
                //     'payments_count' => count($this->payments),
                //     'session_key' => $sessionKey
                // ]);

                $this->calculateTotals();
            } else {
                $this->customer_id = DB::table('customer')->select('id')->first()->id;
                //Log::info('No session data found, using defaults', ['user_id' => $this->user_id]);
            }
        } catch (\Exception $e) {
            // If session loading fails, clear the corrupted session and continue with defaults
            Log::error('Failed to load penjualan session data: ' . $e->getMessage());
            $this->clearSession();
        }
    }

    private function clearSession()
    {
        try {
            $sessionKey = 'penjualan_temp_' . $this->user_id;
            session()->forget($sessionKey);
            //Log::info('Session cleared', ['user_id' => $this->user_id, 'session_key' => $sessionKey]);
        } catch (\Exception $e) {
            Log::error('Failed to clear penjualan session data: ' . $e->getMessage());
        }
    }

    // Method untuk debugging session - bisa dipanggil dari browser console atau testing
    public function debugSession()
    {
        $sessionKey = 'penjualan_temp_' . $this->user_id;
        $sessionData = session()->get($sessionKey);

        // Log::info('Debug Session Data', [
        //     'user_id' => $this->user_id,
        //     'session_key' => $sessionKey,
        //     'session_exists' => !is_null($sessionData),
        //     'session_data' => $sessionData,
        //     'current_details_count' => count($this->details ?? []),
        //     'current_payments_count' => count($this->payments ?? [])
        // ]);

        // $this->info('Debug', 'Session data telah ditulis ke log. Details: ' . count($this->details ?? []) . ', Payments: ' . count($this->payments ?? []));
    }

    // Method untuk test session secara manual
    public function testSession()
    {
        // Simpan data test ke session
        $this->details[] = [
            'id' => null,
            'barang_id' => 999,
            'barang_nama' => 'Test Item',
            'satuan_id' => 1,
            'satuan_nama' => 'PCS',
            'gudang_id' => 1,
            'gudang_nama' => 'Test Gudang',
            'pembelian_detail_id' => 1,
            'nomor_pembelian' => 'TEST001',
            'harga_satuan' => 10000,
            'harga_beli' => 8000,
            'jumlah' => 1,
            'konversi_satuan_terkecil' => 1,
            'subtotal' => 10000,
            'profit' => 2000,
            'diskon' => 0,
            'biaya_lain' => 0,
        ];

        $this->calculateTotals();
        $this->saveToSession();

        $this->success('Test', 'Data test telah ditambahkan dan disimpan ke session. Total items: ' . count($this->details));
    }

    public function updatedDetailBarangId()
    {
        // Reset satuan selection when barang changes
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = DB::table('gudang')->select('id')->first()->id;
        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah_tersedia = 0;

        if ($this->detail_barang_id) {
            $get_satuan_terkecil  = DB::table('barang')->select('satuan_terkecil_id')->where('id', $this->detail_barang_id)->first();
            $this->detail_satuan_id = $get_satuan_terkecil->satuan_terkecil_id;
            $this->satuan_data = DB::table('barang_satuan')
                ->join('satuan', 'barang_satuan.satuan_id', '=', 'satuan.id')
                ->where('barang_satuan.barang_id', $this->detail_barang_id)
                ->select('satuan.id', 'satuan.nama_satuan as name', 'barang_satuan.konversi_satuan_terkecil')
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                })
                ->toArray();

            if ($this->detail_barang_id && $this->detail_satuan_id && $this->detail_gudang_id) {

                $this->cekStockGudang();
            }
        } else {
            $this->satuan_data = [];
            $this->detail_satuan_id = NULL;
        }

        // Simpan perubahan ke session
        $this->saveToSession();
    }



    public function updatedDetailSatuanId()
    {
        $this->detail_gudang_id = DB::table('gudang')->select('id')->first()->id;

        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah_tersedia = 0;
        $this->cekStockGudang();

        // Simpan perubahan ke session
        $this->saveToSession();
    }

    private function cekStockGudang()
    {
        // Load available purchase details for this barang, satuan, and gudang
        $barang = DB::table('barang')->join('satuan', 'satuan.id', 'barang.satuan_terkecil_id')->select('barang.nama_barang', 'satuan.nama_satuan')->where('barang.id', $this->detail_barang_id)->first();
        $this->nama_satuan = $barang->nama_satuan;
        $batch_awal_stock_tersedia = DB::table('pembelian_detail as b')
            ->select(
                'b.id',
                'b.harga_satuan',
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0) as stok_tersedia'),
                'b.rencana_harga_jual',
            )
            ->where('b.barang_id', $this->detail_barang_id)
            //->where('b.satuan_id', $this->detail_satuan_id)
            ->where('b.gudang_id', $this->detail_gudang_id)
            ->havingRaw('stok_tersedia > 0')
            ->orderBy('b.created_at', 'asc')
            ->first();

        $get_all_batch = DB::table('pembelian_detail as b')
            ->select(
                'b.id',
                'b.harga_satuan',
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                DB::raw('ROUND((b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0), 2) as stok_tersedia'),
                'b.rencana_harga_jual'
            )
            ->where('b.barang_id', $this->detail_barang_id)
            //->where('b.satuan_id', $this->detail_satuan_id)
            ->where('b.gudang_id', $this->detail_gudang_id)
            ->havingRaw('stok_tersedia > 0')
            ->orderBy('b.created_at', 'asc')
            ->get();
        // Hitung stock yang sudah digunakan dalam session details
        $stockTerpakai = 0;
        if (is_array($this->details) && count($this->details) > 0) {
            foreach ($this->details as $detail) {
                if (
                    isset($detail['barang_id']) && isset($detail['gudang_id']) &&
                    $detail['barang_id'] == $this->detail_barang_id &&
                    $detail['gudang_id'] == $this->detail_gudang_id
                ) {
                    // Konversi ke satuan terkecil untuk perhitungan yang akurat
                    $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                    $stockTerpakai += $detail['jumlah'] * $konversi;
                }
            }
        }

        // Kurangi stock tersedia dengan stock yang sudah digunakan dalam session
        $stockTersedia = collect($get_all_batch)->sum('stok_tersedia') - $stockTerpakai;

        if ($batch_awal_stock_tersedia && $this->detail_satuan_id && $this->detail_barang_id) {
            // Convert stdClass to array and cast to object for consistent access
            $pembelianDetail = (object)$batch_awal_stock_tersedia;
            $detail_satuan_id = DB::table('barang_satuan')
                ->where('barang_id', $this->detail_barang_id)
                ->where('satuan_id', $this->detail_satuan_id)
                ->first();

            $konversi_satuan_terkecil = $detail_satuan_id->konversi_satuan_terkecil ?? 1;
            // Safely access properties with null coalescing
            $this->detail_harga_beli = round($pembelianDetail->harga_satuan ?? 0, 2);

            // Pastikan stock tersedia tidak negatif
            $stockTersedia = max(0, $stockTersedia);

            if ($konversi_satuan_terkecil == 1) {
                $this->detail_jumlah_tersedia = $stockTersedia * $konversi_satuan_terkecil;
            } else {
                $this->detail_jumlah_tersedia = $stockTersedia / $konversi_satuan_terkecil;
            }

            $rencana_harga_jual = round($pembelianDetail->rencana_harga_jual ?? 0, 2);
            $this->detail_harga_satuan = $rencana_harga_jual;

            // Debug log untuk memantau perhitungan stock
            // Log::info('Stock calculation in cekStockGudang', [
            //     'barang_id' => $this->detail_barang_id,
            //     'gudang_id' => $this->detail_gudang_id,
            //     'stock_database' => collect($get_all_batch)->sum('stok_tersedia'),
            //     'stock_terpakai_session' => $stockTerpakai,
            //     'stock_tersedia_final' => $stockTersedia,
            //     'detail_jumlah_tersedia' => $this->detail_jumlah_tersedia
            // ]);
        }
    }

    public function updatedDetailGudangId()
    {

        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah_tersedia = 0;

        if ($this->detail_barang_id && $this->detail_satuan_id && $this->detail_gudang_id) {
            // Load available purchase details for this barang, satuan, and gudang
            $barang = DB::table('barang')->join('satuan', 'satuan.id', 'barang.satuan_terkecil_id')->select('barang.nama_barang', 'satuan.nama_satuan')->where('barang.id', $this->detail_barang_id)->first();
            $nama_satuan = $barang->nama_satuan;
            $pembelianDetailRaw = DB::table('pembelian_detail as b')
                ->select(
                    'b.id',
                    'b.harga_satuan',
                    DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                    DB::raw('(b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0) as stok_tersedia'),
                    'b.rencana_harga_jual'
                )
                ->where('b.barang_id', $this->detail_barang_id)
                //->where('b.satuan_id', $this->detail_satuan_id)
                ->where('b.gudang_id', $this->detail_gudang_id)
                ->havingRaw('stok_tersedia > 0')
                ->get();

            // Adjust stock tersedia by subtracting stock already used in session
            $this->pembelian_detail_data = $pembelianDetailRaw->map(function ($item) use ($nama_satuan) {
                $stockTerpakai = 0;
                if (is_array($this->details) && count($this->details) > 0) {
                    foreach ($this->details as $detail) {
                        if (
                            isset($detail['pembelian_detail_id']) &&
                            $detail['pembelian_detail_id'] == $item->id
                        ) {
                            // Konversi ke satuan terkecil untuk perhitungan yang akurat
                            $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                            $stockTerpakai += $detail['jumlah'] * $konversi;
                        }
                    }
                }

                // Adjust stock tersedia
                $adjustedStock = max(0, $item->stok_tersedia - $stockTerpakai);

                return [
                    'id' => $item->id,
                    'harga_satuan' => $item->harga_satuan,
                    'jumlah_beli' => $item->jumlah_beli,
                    'stok_tersedia' => $adjustedStock,
                    'rencana_harga_jual' => $item->rencana_harga_jual,
                    'name' => "Rp " . number_format($item->harga_satuan, 0, ',', '.') . " - Stok $nama_satuan: " . number_format($adjustedStock, 2, ',', '.')
                ];
            })->filter(function ($item) {
                // Only keep items with available stock
                return $item['stok_tersedia'] > 0;
            })->values()->toArray();
        }
    }

    public function search(string $value = '')
    {
        // Get selected barang if exists
        $selectedBarang = Barang::where('barang.id', $this->detail_barang_id)
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->select(
                'barang.id',
                DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as name"),
                'barang.kode_barang'
            )
            ->get()
            ->map(function ($item) {
                return (array) $item;
            });

        // Search barang based on name, kode_barang, or jenis_barang and merge with selected
        $searchResults = DB::table('barang')
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where(function ($query) use ($value) {
                $query->where('barang.kode_barang', 'like', "%$value%")
                    ->orWhere('barang.nama_barang', 'like', "%$value%")
                    ->orWhere('jenis_barang.nama_jenis_barang', 'like', "%$value%");
            })
            ->select(
                'barang.id',
                DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as name"),
                'barang.kode_barang'
            )
            ->take(10) // Increased from 5 to 10 for better search results
            ->orderBy('barang.nama_barang')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->merge($selectedBarang);

        $this->getBarangSearch = $searchResults->toArray();
    }

    /**
     * Handle barcode search and auto-add item
     */
    public function updatedBarcodeSearch()
    {
        if (empty($this->barcode_search)) {
            return;
        }

        // Search for barang by kode_barang (barcode) - exact match
        $barang = DB::table('barang')
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('barang.kode_barang', trim($this->barcode_search))
            ->select(
                'barang.id',
                'barang.nama_barang',
                'barang.kode_barang',
                'barang.satuan_terkecil_id',
                'jenis_barang.nama_jenis_barang'
            )
            ->first();

        // If exact match not found, try case-insensitive search
        if (!$barang) {
            $barang = DB::table('barang')
                ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->whereRaw('LOWER(barang.kode_barang) = LOWER(?)', [trim($this->barcode_search)])
                ->select(
                    'barang.id',
                    'barang.nama_barang',
                    'barang.kode_barang',
                    'barang.satuan_terkecil_id',
                    'jenis_barang.nama_jenis_barang'
                )
                ->first();
        }

        if (!$barang) {
            $this->warning('Peringatan', 'Barang dengan kode barcode "' . $this->barcode_search . '" tidak ditemukan.');
            $this->barcode_search = '';
            return;
        }

        // Auto-fill form with found item
        $this->detail_barang_id = $barang->id;
        $this->updatedDetailBarangId();

        // Set default satuan to satuan terkecil
        if ($barang->satuan_terkecil_id && !empty($this->satuan_data)) {
            $satuanTerkecil = collect($this->satuan_data)->firstWhere('id', $barang->satuan_terkecil_id);
            if ($satuanTerkecil) {
                $this->detail_satuan_id = $barang->satuan_terkecil_id;
                $this->updatedDetailSatuanId();
            }
        }

        $rencana_harga_jual = $this->detail_harga_satuan;
        // Set default gudang if only one available
        if (count($this->gudang_data) === 1) {
            $this->detail_gudang_id = $this->gudang_data[0]['id'];
            $this->updatedDetailGudangId();
        }

        if ($this->detail_harga_satuan == 0) {
            $this->updatedDetailBarangId();
            $this->detail_harga_satuan = $rencana_harga_jual;
        }

        // Set default quantity to 1
        $this->detail_jumlah = 1;

        // Clear barcode search
        $this->barcode_search = '';

        // Show success message
        $this->success('Berhasil', 'Barang "' . $barang->nama_barang . '" berhasil ditemukan dan ditambahkan ke form.');

        // Auto-add if all required fields are filled
        if ($this->detail_barang_id && $this->detail_satuan_id && $this->detail_gudang_id && $this->detail_harga_satuan > 0) {
            try {
                $this->addDetail();
                // Show success message with item name
                $this->success('Berhasil', 'Item "' . $barang->nama_barang . '" berhasil ditambahkan ke penjualan!');
                // Dispatch event to refocus barcode input
                $this->dispatch('refocus-barcode');
            } catch (\Exception $e) {
                // If auto-add fails, show error message
                $this->error('Error', 'Gagal menambahkan item: ' . $e->getMessage());
            }
        } else {
            // If not all fields are filled, show info about what's missing
            $missing = [];
            if (!$this->detail_satuan_id) $missing[] = 'satuan';
            if (!$this->detail_gudang_id) $missing[] = 'gudang';
            if (!($this->detail_harga_satuan > 0)) $missing[] = 'harga';

            if (!empty($missing)) {
                $this->info('Info', 'Item ditemukan! Silakan lengkapi: ' . implode(', ', $missing) . ' kemudian tekan F3 untuk menambah.');
            }
        }
    }

    /**
     * Quick add item by barcode (alternative method for direct scanning)
     */
    public function quickAddByBarcode($barcode)
    {
        $this->barcode_search = $barcode;
        $this->updatedBarcodeSearch();
    }

    /**
     * Clear barcode input and reset detail form
     */
    public function clearBarcodeForm()
    {
        $this->barcode_search = '';
        $this->detail_barang_id = null;
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = null;
        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah = 1;
        $this->detail_jumlah_tersedia = 0;
        $this->detail_diskon = 0;
        $this->detail_biaya_lain = 0;
        $this->satuan_data = [];
        $this->pembelian_detail_data = [];

        // Dispatch event to refocus barcode input
        $this->dispatch('refocus-barcode');
    }

    public function updatedDetailPembelianDetailId()
    {
        if ($this->detail_pembelian_detail_id) {

            $pembelianDetail = collect($this->pembelian_detail_data)
                ->firstWhere('id', $this->detail_pembelian_detail_id);

            if ($pembelianDetail && $this->detail_satuan_id && $this->detail_barang_id) {
                $detail_satuan_id = DB::table('barang_satuan')
                    ->where('barang_id', $this->detail_barang_id)
                    ->where('satuan_id', $this->detail_satuan_id)
                    ->first();
                $konversi_satuan_terkecil = $detail_satuan_id->konversi_satuan_terkecil ?? 1;

                $this->detail_harga_beli = round($pembelianDetail['harga_satuan'], 2);

                // Hitung stock yang sudah digunakan dalam session details untuk pembelian detail ini
                $stockTerpakai = 0;
                if (is_array($this->details) && count($this->details) > 0) {
                    foreach ($this->details as $detail) {
                        if (
                            isset($detail['pembelian_detail_id']) &&
                            $detail['pembelian_detail_id'] == $this->detail_pembelian_detail_id
                        ) {
                            // Konversi ke satuan terkecil untuk perhitungan yang akurat
                            $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                            $stockTerpakai += $detail['jumlah'] * $konversi;
                        }
                    }
                }

                // Kurangi stock tersedia dengan stock yang sudah digunakan dalam session
                $stockTersediaAdjusted = max(0, $pembelianDetail['stok_tersedia'] - $stockTerpakai);

                if ($konversi_satuan_terkecil == 1) {
                    $this->detail_jumlah_tersedia = $stockTersediaAdjusted * $konversi_satuan_terkecil;
                } else {
                    $this->detail_jumlah_tersedia = $stockTersediaAdjusted / $konversi_satuan_terkecil;
                }

                $rencana_harga_jual = round($pembelianDetail['rencana_harga_jual'] ?? 0, 2);
                $this->detail_harga_satuan = $rencana_harga_jual;
            }
        }
    }

    public function addDetail()
    {
        $this->validate([
            'detail_barang_id' => 'required',
            'detail_satuan_id' => 'required',
            'detail_gudang_id' => 'required',
            'detail_harga_satuan' => 'required|numeric|min:0',
            'detail_jumlah' => 'required|numeric|min:0.1|max:' . $this->detail_jumlah_tersedia,
        ]);

        // Get barang, satuan, gudang info
        $barang = Barang::find($this->detail_barang_id);
        $satuan = collect($this->satuan_data)->firstWhere('id', $this->detail_satuan_id);
        $gudang = collect($this->gudang_data)->firstWhere('id', $this->detail_gudang_id);
        // Get available batches ordered by FIFO
        $availableBatches = DB::table('pembelian_detail as b')
            ->select(
                'b.id',
                'b.harga_satuan',
                'b.pembelian_id',
                DB::raw('(b.jumlah * b.konversi_satuan_terkecil) as jumlah_beli'),
                DB::raw('ROUND((b.jumlah * b.konversi_satuan_terkecil) - IFNULL((SELECT SUM(a.jumlah * a.konversi_satuan_terkecil) FROM penjualan_detail a WHERE a.pembelian_detail_id = b.id), 0), 2) as stok_tersedia'),
                'b.rencana_harga_jual'
            )
            ->where('b.barang_id', $this->detail_barang_id)
            ->where('b.gudang_id', $this->detail_gudang_id)
            ->havingRaw('stok_tersedia > 0')
            ->orderBy('b.created_at', 'asc')
            ->get();

        // Adjust available batches by subtracting stock already used in session
        $availableBatches = $availableBatches->map(function ($batch) {
            $stockTerpakai = 0;
            if (is_array($this->details) && count($this->details) > 0) {
                foreach ($this->details as $detail) {
                    if (
                        isset($detail['pembelian_detail_id']) &&
                        $detail['pembelian_detail_id'] == $batch->id
                    ) {
                        // Konversi ke satuan terkecil untuk perhitungan yang akurat
                        $konversi = $detail['konversi_satuan_terkecil'] ?? 1;
                        $stockTerpakai += $detail['jumlah'] * $konversi;
                    }
                }
            }

            // Adjust stock tersedia
            $batch->stok_tersedia = max(0, $batch->stok_tersedia - $stockTerpakai);
            return $batch;
        })->filter(function ($batch) {
            // Only keep batches with available stock
            return $batch->stok_tersedia > 0;
        });

        if (!$barang || !$satuan || !$gudang) {
            $this->error('Error', 'Data barang, satuan, atau gudang tidak valid.');
            return;
        }

        // Calculate how many units we need to fulfill from each batch
        $remainingQuantity = $this->detail_jumlah * $satuan['konversi_satuan_terkecil'];
        $batchDetails = [];

        foreach ($availableBatches as $batch) {
            if ($remainingQuantity <= 0) break;

            $quantityFromBatch = min($batch->stok_tersedia, $remainingQuantity);

            $batchDetails[] = [
                'pembelian_detail_id' => $batch->id,
                'nomor_pembelian'  => DB::table('pembelian')->where('id', $batch->pembelian_id)->value('nomor_pembelian'),
                'jumlah' => $quantityFromBatch / $satuan['konversi_satuan_terkecil'],
                'harga_beli' => $batch->harga_satuan,
                'harga_jual' => $this->detail_harga_satuan
            ];

            $remainingQuantity -= $quantityFromBatch;
        }

        if ($remainingQuantity > 0) {
            $this->error('Error', 'Stok tidak mencukupi untuk memenuhi pesanan.');
            return;
        }

        // Add details for each batch used
        foreach ($batchDetails as $batchDetail) {
            $subtotal = $batchDetail['harga_jual'] * $batchDetail['jumlah'];
            $profit = ($batchDetail['harga_jual'] - $batchDetail['harga_beli']) * $batchDetail['jumlah'];

            $this->details[] = [
                'id' => null,
                'barang_id' => $this->detail_barang_id,
                'barang_nama' => $barang->nama_barang,
                'satuan_id' => $this->detail_satuan_id,
                'satuan_nama' => $satuan['name'],
                'gudang_id' => $this->detail_gudang_id,
                'gudang_nama' => $gudang['name'],
                'pembelian_detail_id' => $batchDetail['pembelian_detail_id'],
                'nomor_pembelian'   => $batchDetail['nomor_pembelian'],
                'harga_satuan' => $batchDetail['harga_jual'],
                'harga_beli' => $batchDetail['harga_beli'],
                'jumlah' => $batchDetail['jumlah'],
                'konversi_satuan_terkecil' => $satuan['konversi_satuan_terkecil'],
                'subtotal' => $subtotal,
                'profit' => $profit - (($this->detail_diskon * $batchDetail['jumlah']) / $this->detail_jumlah) + (($this->detail_biaya_lain * $batchDetail['jumlah']) / $this->detail_jumlah),
                'diskon' => ($this->detail_diskon * $batchDetail['jumlah']) / $this->detail_jumlah,
                'biaya_lain' => ($this->detail_biaya_lain * $batchDetail['jumlah']) / $this->detail_jumlah,
            ];
        }

        $this->resetDetailForm();
        $this->calculateTotals();
        $this->saveToSession();

        // Debug log untuk memastikan addDetail berhasil
        Log::info('Item added to details', [
            'user_id' => $this->user_id,
            'barang_id' => $barang->id,
            'barang_nama' => $barang->nama_barang,
            'total_details' => count($this->details),
            'last_detail' => end($this->details)
        ]);

        $this->success('Berhasil', 'Item berhasil ditambahkan.');
        $this->resetValidation();
    }

    public function removeDetail($index)
    {
        unset($this->details[$index]);
        $this->details = array_values($this->details);
        $this->calculateTotals();
        $this->saveToSession();
        $this->success('Berhasil', 'Item berhasil dihapus.');
    }

    public function addPayment()
    {
        $this->validate([
            'payment_jenis' => 'required',
            'payment_jumlah' => 'required|numeric|min:1',
            'payment_keterangan' => 'required',
        ]);

        // Check if payment would exceed total
        $new_total_payment = $this->total_payment + $this->payment_jumlah;
        // if ($new_total_payment > $this->total_harga) {
        //     $this->warning('Peringatan', 'Jumlah pembayaran melebihi total yang harus dibayar. Sisa yang perlu dibayar: Rp ' . number_format($this->sisa_pembayaran, 0, ',', '.'));
        //     return;
        // }

        // Calculate kembalian for this payment
        $roundedTotal = round($this->total_harga / 100) * 100;
        $currentTotalPayment = collect($this->payments)->sum('jumlah') + $this->payment_jumlah;
        $kembalian = max(0, $currentTotalPayment - $roundedTotal);

        // Save payment to database immediately if editing
        if ($this->type === 'edit' && $this->penjualan_ID) {
            $payment = PembayaranPenjualan::create([
                'penjualan_id' => $this->penjualan_ID,
                'user_id' => $this->user_id,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
                'kembalian' => $kembalian,
            ]);

            // Add to local array with database ID
            $this->payments[] = [
                'id' => $payment->id,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
                'kembalian' => $kembalian,
            ];

            // Update penjualan status
            Penjualan::where('id', $this->penjualan_ID)->update([
                'status' => $this->status,
                'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
            ]);
        } else {
            // Just add to local array for create mode
            $this->payments[] = [
                'id' => null,
                'jenis_pembayaran' => $this->payment_jenis,
                'jumlah' => $this->payment_jumlah,
                'keterangan' => $this->payment_keterangan,
                'kembalian' => $kembalian,
            ];
        }

        $this->resetPaymentForm();
        $this->calculateTotals();
        $this->saveToSession();

        if ($this->status === 'lunas') {
            $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Penjualan telah LUNAS!');
        } else {
            $this->success('Berhasil', 'Pembayaran berhasil ditambahkan. Total: ' . count($this->payments) . ' pembayaran.');
        }

        $this->resetValidation();
    }

    public function removePayment($index)
    {
        // Delete the payment record from database if it has an ID
        if (isset($this->payments[$index]['id']) && $this->payments[$index]['id']) {
            $paymentId = $this->payments[$index]['id'];
            PembayaranPenjualan::where('id', $paymentId)->delete();

            // If in edit mode, update the transaction status too
            if ($this->type === 'edit' && $this->penjualan_ID) {
                // Remove from local array
                unset($this->payments[$index]);
                $this->payments = array_values($this->payments);
                $this->calculateTotals();

                // Update penjualan status
                Penjualan::where('id', $this->penjualan_ID)->update([
                    'status' => $this->status,
                    'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
                ]);
            }
        } else {
            // Just remove from local array if no ID (not yet saved to database)
            unset($this->payments[$index]);
            $this->payments = array_values($this->payments);
            $this->calculateTotals();
        }

        $this->saveToSession();
        $this->success('Berhasil', 'Pembayaran berhasil dihapus.');
    }

    private function resetDetailForm()
    {
        $this->detail_barang_id = null;
        $this->detail_satuan_id = null;
        $this->detail_gudang_id = null;
        $this->detail_pembelian_detail_id = null;
        $this->detail_harga_satuan = 0;
        $this->detail_harga_beli = 0;
        $this->detail_jumlah = 1;
        $this->detail_jumlah_tersedia = 0;
        $this->detail_diskon = 0;
        $this->detail_biaya_lain = 0;
        $this->satuan_data = [];
        $this->pembelian_detail_data = [];
    }

    private function resetPaymentForm()
    {
        $this->payment_jenis = 'cash';
        $this->payment_jumlah = 0;
        $this->payment_keterangan = '';
    }

    public function calculateTotals()
    {
        try {
            // Ensure arrays are valid before processing
            $details = is_array($this->details) ? $this->details : [];
            $payments = is_array($this->payments) ? $this->payments : [];

            // Calculate subtotal, total diskon, dan total biaya lain dari detail items
            $this->subtotal_sebelum_diskon = 0;
            $total_diskon = 0;
            $total_biaya_lain = 0;

            foreach ($details as $detail) {
                if (!is_array($detail)) continue;

                $subtotal = $this->sanitizeNumeric($detail['subtotal'] ?? 0, 0);
                $diskon = $this->sanitizeNumeric($detail['diskon'] ?? 0, 0);
                $biaya_lain = $this->sanitizeNumeric($detail['biaya_lain'] ?? 0, 0);

                $this->subtotal_sebelum_diskon += $subtotal;
                $total_diskon += $diskon;
                $total_biaya_lain += $biaya_lain;
            }

            // Calculate total harga: subtotal - total diskon + total biaya lain
            $this->total_harga = max(0, $this->subtotal_sebelum_diskon - $total_diskon + $total_biaya_lain);

            // Calculate total payment with enhanced validation
            $this->total_payment = collect($payments)->sum(function ($payment) {
                if (!is_array($payment) || !isset($payment['jumlah'])) {
                    return 0;
                }
                return $this->sanitizeNumeric($payment['jumlah'], 0);
            });

            // Round total_harga to nearest hundred before calculating remaining payment and kembalian
            $roundedTotal = round($this->total_harga / 100) * 100;
            $this->sisa_pembayaran = (float)$roundedTotal - (float)$this->total_payment;

            // Calculate total kembalian (only if payment exceeds total)
            $this->total_kembalian = max(0, $this->total_payment - $roundedTotal);

            // Auto-calculate status based on payment
            $this->updatePaymentStatus();

            // Dispatch browser event to update UI if needed
            $this->dispatch('totals-updated', [
                'subtotal' => $this->subtotal_sebelum_diskon,
                'total' => $this->total_harga,
                'payment' => $this->total_payment,
                'remaining' => $this->sisa_pembayaran
            ]);
        } catch (\Exception $e) {
            Log::warning('Error calculating totals: ' . $e->getMessage());
            // Set safe defaults
            $this->subtotal_sebelum_diskon = 0;
            $this->total_harga = 0;
            $this->total_payment = 0;
            $this->sisa_pembayaran = 0;
            $this->status = 'belum_bayar';
        }
    }

    private function updatePaymentStatus()
    {
        if ($this->total_payment == 0) {
            $this->status = 'belum_bayar';
        } elseif ($this->total_payment < round($this->total_harga / 100) * 100) {
            $this->status = 'belum_lunas';
        } else {
            $this->status = 'lunas';
        }
    }



    public function updatedDetailHargaSatuan($value)
    {
        try {
            $this->detail_harga_satuan = $this->sanitizeNumeric($value, 0);
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::warning('Error updating detail_harga_satuan: ' . $e->getMessage());
            $this->detail_harga_satuan = 0;
        }
    }

    public function updatedDetailJumlah($value)
    {
        try {
            $this->detail_jumlah = $this->sanitizeNumeric($value, 1, 0.01);
            $this->calculateTotals();
        } catch (\Exception $e) {
            Log::warning('Error updating detail_jumlah: ' . $e->getMessage());
            $this->detail_jumlah = 1;
        }
    }

    public function updated($field)
    {
        // Handle other field updates that don't need special processing
        if (in_array($field, ['customer_id', 'tanggal_penjualan', 'keterangan'])) {
            $this->saveToSession();
        }
    }

    public function store()
    {
        $this->validate([
            'nomor_penjualan' => 'required|unique:penjualan,nomor_penjualan',
            'tanggal_penjualan' => 'required|date',
            'customer_id' => 'required|exists:customer,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            // Create Penjualan
            $penjualan = Penjualan::create([
                'nomor_penjualan' => $this->generateNomorPenjualan_final(),
                'tanggal_penjualan' => $this->tanggal_penjualan,
                'customer_id' => $this->customer_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
                'status' => $this->status,
            ]);

            // Create Details
            foreach ($this->details as $detail) {
                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'pembelian_detail_id' => $detail['pembelian_detail_id'],
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'subtotal' => $detail['subtotal'],
                    'profit' => $detail['profit'],
                    'diskon' => $detail['diskon'] ?? 0,
                    'biaya_lain' => $detail['biaya_lain'] ?? 0,
                ]);

                // Update Gudang Stock (reduce stock)
                $gudangStock = GudangStock::where([
                    'gudang_id' => $detail['gudang_id'],
                    'barang_id' => $detail['barang_id']
                ])->first();

                if ($gudangStock) {
                    // Calculate quantity in smallest unit
                    $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                    // Reduce stock
                    $gudangStock->decrement('jumlah', $jumlahTerkecil);

                    // Create stock transaction
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'penjualan_detail_id' => $penjualanDetail->id,
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'tipe' => 'keluar',
                    ]);
                }
            }

            // Create Payments (if any)
            if (count($this->payments) > 0) {
                $totalPaid = 0;
                $roundedTotal = round($this->total_harga / 100) * 100;

                foreach ($this->payments as $payment) {
                    $totalPaid += $payment['jumlah'];
                    $kembalian = max(0, $totalPaid - $roundedTotal);

                    PembayaranPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'user_id' => $this->user_id,
                        'jenis_pembayaran' => $payment['jenis_pembayaran'],
                        'jumlah' => $payment['jumlah'],
                        'keterangan' => $payment['keterangan'],
                        'kembalian' => $kembalian,
                    ]);
                }
            }
        });

        $this->clearSession();
        $this->success('Notifikasi', 'Berhasil menyimpan transaksi penjualan.');
        $lastInsertedId = Penjualan::latest()->first()->id;
        return $this->redirectRoute('penjualan.show', ['id' => $lastInsertedId], navigate: true);
    }

    public function update()
    {
        $this->validate([
            'nomor_penjualan' => 'required|unique:penjualan,nomor_penjualan,' . $this->penjualan_ID,
            'tanggal_penjualan' => 'required|date',
            'customer_id' => 'required|exists:customer,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            $penjualan = Penjualan::findOrFail($this->penjualan_ID);

            // Handle stock reversal for old details before deletion
            foreach ($penjualan->penjualanDetails as $oldDetail) {
                $stockTransaction = TransaksiGudangStock::where('penjualan_detail_id', $oldDetail->id)->first();

                if ($stockTransaction) {
                    $gudangStock = GudangStock::find($stockTransaction->gudang_stock_id);

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit to reverse
                        $jumlahTerkecil = $oldDetail->jumlah * $oldDetail->konversi_satuan_terkecil;

                        // Add back the stock (reverse the sale)
                        $gudangStock->increment('jumlah', $jumlahTerkecil);
                    }

                    // Delete the old stock transaction
                    $stockTransaction->delete();
                }
            }

            // Delete old details and payments
            $penjualan->penjualanDetails()->delete();
            $penjualan->pembayaranPenjualan()->delete();

            // Update Penjualan
            $penjualan->update([
                //'nomor_penjualan' => $this->nomor_penjualan,
                'tanggal_penjualan' => $this->tanggal_penjualan,
                'customer_id' => $this->customer_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => round($this->total_harga / 100) * 100, // Round to nearest hundred
                'status' => $this->status,
            ]);

            // Recreate details with new stock transactions
            foreach ($this->details as $detail) {
                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'pembelian_detail_id' => $detail['pembelian_detail_id'],
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'subtotal' => $detail['subtotal'],
                    'profit' => $detail['profit'],
                    'diskon' => $detail['diskon'] ?? 0,
                    'biaya_lain' => $detail['biaya_lain'] ?? 0,
                ]);

                // Update Gudang Stock (reduce stock)
                $gudangStock = GudangStock::where([
                    'gudang_id' => $detail['gudang_id'],
                    'barang_id' => $detail['barang_id']
                ])->first();

                if ($gudangStock) {
                    // Calculate quantity in smallest unit
                    $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                    // Reduce stock
                    $gudangStock->decrement('jumlah', $jumlahTerkecil);

                    // Create new stock transaction
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'penjualan_detail_id' => $penjualanDetail->id,
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'tipe' => 'keluar',
                    ]);
                }
            }

            // Recreate payments (if any)
            if (count($this->payments) > 0) {
                foreach ($this->payments as $payment) {
                    PembayaranPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'user_id' => $this->user_id,
                        'jenis_pembayaran' => $payment['jenis_pembayaran'],
                        'jumlah' => $payment['jumlah'],
                        'keterangan' => $payment['keterangan'],
                    ]);
                }
            }
        });

        $this->clearSession();
        $this->success('Notifikasi', 'Berhasil mengupdate transaksi penjualan.');
        return $this->redirectRoute('penjualan.show', ['id' => $this->penjualan_ID],  navigate: true);
    }

    public function quickFillPayment()
    {
        if ($this->sisa_pembayaran > 0) {
            $this->payment_jumlah = $this->sisa_pembayaran;
            $this->payment_keterangan = 'Pelunasan penjualan ' . $this->nomor_penjualan;
            $this->saveToSession();
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
        $this->customer_id = null;
        $this->calculateTotals();
        $this->saveToSession(); // Simpan state kosong ke session
        $this->success('Berhasil', 'Data sementara telah dihapus.');
    }

    public function addPaymentAndSave()
    {
        // Validate payment data
        $this->validate([
            'payment_jenis' => 'required',
            'payment_jumlah' => 'required|numeric|min:1',
            'payment_keterangan' => 'required',
        ]);

        // Validate transaction data
        $this->validate([
            'nomor_penjualan' => 'required|unique:penjualan,nomor_penjualan',
            'tanggal_penjualan' => 'required|date',
            'customer_id' => 'required|exists:customer,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        // Check if payment would exceed total
        $new_total_payment = $this->total_payment + $this->payment_jumlah;
        // if ($new_total_payment > $this->total_harga) {
        //     $this->warning('Peringatan', 'Jumlah pembayaran melebihi total yang harus dibayar. Sisa yang perlu dibayar: Rp ' . number_format($this->sisa_pembayaran, 0, ',', '.'));
        //     return;
        // }

        // Add payment to array
        $this->payments[] = [
            'id' => null,
            'jenis_pembayaran' => $this->payment_jenis,
            'jumlah' => $this->payment_jumlah,
            'keterangan' => $this->payment_keterangan,
        ];

        $this->calculateTotals();

        // Save the transaction
        DB::transaction(function () {
            // Create Penjualan
            $penjualan = Penjualan::create([
                'nomor_penjualan' => $this->generateNomorPenjualan_final(),
                'tanggal_penjualan' => $this->tanggal_penjualan,
                'customer_id' => $this->customer_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => (float) round($this->total_harga / 100) * 100, // Round to nearest hundred
                'status' => $this->status,
            ]);

            // Create Details
            foreach ($this->details as $detail) {
                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'pembelian_detail_id' => $detail['pembelian_detail_id'],
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'subtotal' => $detail['subtotal'],
                    'profit' => $detail['profit'],
                    'diskon' => $detail['diskon'] ?? 0,
                    'biaya_lain' => $detail['biaya_lain'] ?? 0,
                ]);

                // Update Gudang Stock (reduce stock)
                $gudangStock = GudangStock::where([
                    'gudang_id' => $detail['gudang_id'],
                    'barang_id' => $detail['barang_id']
                ])->first();

                if ($gudangStock) {
                    // Calculate quantity in smallest unit
                    $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                    // Reduce stock
                    $gudangStock->decrement('jumlah', $jumlahTerkecil);

                    // Create stock transaction
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'penjualan_detail_id' => $penjualanDetail->id,
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'tipe' => 'keluar',
                    ]);
                }
            }

            // Create Payments
            $totalPaid = 0;
            $roundedTotal = round($this->total_harga / 100) * 100;

            foreach ($this->payments as $payment) {
                $totalPaid += $payment['jumlah'];
                $kembalian = max(0, $totalPaid - $roundedTotal);

                PembayaranPenjualan::create([
                    'penjualan_id' => $penjualan->id,
                    'user_id' => $this->user_id,
                    'jenis_pembayaran' => $payment['jenis_pembayaran'],
                    'jumlah' => $payment['jumlah'],
                    'keterangan' => $payment['keterangan'],
                    'kembalian' => $kembalian,
                ]);
            }
        });

        $this->clearSession();

        if ($this->status === 'lunas') {
            $this->success('Berhasil!', 'Transaksi penjualan berhasil disimpan dengan status LUNAS!');
        } else {
            $this->success('Berhasil!', 'Transaksi penjualan berhasil disimpan dengan status ' . strtoupper(str_replace('_', ' ', $this->status)) . '.');
        }
        $getLatestId = Penjualan::latest()->first()->id;
        return $this->redirectRoute('penjualan.show', ['id' => $getLatestId], navigate: true);
    }

    public function addPaymentAndUpdate()
    {
        // Validate payment data
        $this->validate([
            'payment_jenis' => 'required',
            'payment_jumlah' => 'required|numeric|min:1',
            'payment_keterangan' => 'required',
        ]);

        // Validate transaction data
        $this->validate([
            'nomor_penjualan' => 'required|unique:penjualan,nomor_penjualan,' . $this->penjualan_ID,
            'tanggal_penjualan' => 'required|date',
            'customer_id' => 'required|exists:customer,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|array|min:1',
        ]);

        // Check if payment would exceed total
        $new_total_payment = $this->total_payment + $this->payment_jumlah;
        // if ($new_total_payment > $this->total_harga) {
        //     $this->warning('Peringatan', 'Jumlah pembayaran melebihi total yang harus dibayar. Sisa yang perlu dibayar: Rp ' . number_format($this->sisa_pembayaran, 0, ',', '.'));
        //     return;
        // }

        // Add payment to array
        $this->payments[] = [
            'id' => null,
            'jenis_pembayaran' => $this->payment_jenis,
            'jumlah' => $this->payment_jumlah,
            'keterangan' => $this->payment_keterangan,
        ];

        $this->calculateTotals();

        DB::transaction(function () {
            $penjualan = Penjualan::findOrFail($this->penjualan_ID);

            // Handle stock reversal for old details before deletion
            foreach ($penjualan->penjualanDetails as $oldDetail) {
                $stockTransaction = TransaksiGudangStock::where('penjualan_detail_id', $oldDetail->id)->first();

                if ($stockTransaction) {
                    $gudangStock = GudangStock::find($stockTransaction->gudang_stock_id);

                    if ($gudangStock) {
                        // Calculate quantity in smallest unit to reverse
                        $jumlahTerkecil = $oldDetail->jumlah * $oldDetail->konversi_satuan_terkecil;

                        // Add back the stock (reverse the sale)
                        $gudangStock->increment('jumlah', $jumlahTerkecil);
                    }

                    // Delete the old stock transaction
                    $stockTransaction->delete();
                }
            }

            // Delete old details and payments
            $penjualan->penjualanDetails()->delete();
            $penjualan->pembayaranPenjualan()->delete();

            // Update Penjualan
            $penjualan->update([
                // 'nomor_penjualan' => $this->nomor_penjualan,
                'tanggal_penjualan' => $this->tanggal_penjualan,
                'customer_id' => $this->customer_id,
                'user_id' => $this->user_id,
                'keterangan' => $this->keterangan,
                'total_harga' => (float) round($this->total_harga / 100) * 100, // Round to nearest hundred
                'status' => $this->status,
            ]);

            // Recreate details with new stock transactions
            foreach ($this->details as $detail) {
                $penjualanDetail = PenjualanDetail::create([
                    'penjualan_id' => $penjualan->id,
                    'pembelian_detail_id' => $detail['pembelian_detail_id'],
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'gudang_id' => $detail['gudang_id'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'jumlah' => $detail['jumlah'],
                    'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                    'subtotal' => $detail['subtotal'],
                    'profit' => $detail['profit'],
                    'diskon' => $detail['diskon'] ?? 0,
                    'biaya_lain' => $detail['biaya_lain'] ?? 0,
                ]);

                // Update Gudang Stock (reduce stock)
                $gudangStock = GudangStock::where([
                    'gudang_id' => $detail['gudang_id'],
                    'barang_id' => $detail['barang_id']
                ])->first();

                if ($gudangStock) {
                    // Calculate quantity in smallest unit
                    $jumlahTerkecil = $detail['jumlah'] * $detail['konversi_satuan_terkecil'];

                    // Reduce stock
                    $gudangStock->decrement('jumlah', $jumlahTerkecil);

                    // Create new stock transaction
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'penjualan_detail_id' => $penjualanDetail->id,
                        'jumlah' => $detail['jumlah'],
                        'konversi_satuan_terkecil' => $detail['konversi_satuan_terkecil'],
                        'tipe' => 'keluar',
                    ]);
                }
            }

            // Recreate payments (if any)
            if (count($this->payments) > 0) {
                $totalPaid = 0;
                $roundedTotal = round($this->total_harga / 100) * 100;

                foreach ($this->payments as $payment) {
                    $totalPaid += $payment['jumlah'];
                    $kembalian = max(0, $totalPaid - $roundedTotal);

                    PembayaranPenjualan::create([
                        'penjualan_id' => $penjualan->id,
                        'user_id' => $this->user_id,
                        'jenis_pembayaran' => $payment['jenis_pembayaran'],
                        'jumlah' => $payment['jumlah'],
                        'keterangan' => $payment['keterangan'],
                        'kembalian' => $kembalian,
                    ]);
                }
            }
        });

        $this->clearSession();

        if ($this->status === 'lunas') {
            $this->success('Berhasil!', 'Transaksi penjualan berhasil diupdate dengan status LUNAS!');
        } else {
            $this->success('Berhasil!', 'Transaksi penjualan berhasil diupdate dengan status ' . strtoupper(str_replace('_', ' ', $this->status)) . '.');
        }

        return $this->redirectRoute('penjualan.show', ['id' => $this->penjualan_ID],  navigate: true);
    }

    public function render()
    {
        try {
            // Final state validation before rendering
            $this->validateComponentState();

            return view('livewire.penjualan.form');
        } catch (\Exception $e) {
            Log::error('Render error in penjualan form: ' . $e->getMessage());

            // Reset component to safe state
            $this->clearSession();
            $this->details = [];
            $this->payments = [];
            $this->satuan_data = [];
            $this->pembelian_detail_data = [];
            $this->calculateTotals();

            // Show error message to user
            $this->error('Error', 'Terjadi kesalahan dalam memuat data. Data telah direset ke keadaan awal.');

            return view('livewire.penjualan.form');
        }
    }
}

/* End of file Form.php */
/* Location: ./app/Livewire/Penjualan/Form.php */
/* Created at 2025-07-03 23:22:50 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */