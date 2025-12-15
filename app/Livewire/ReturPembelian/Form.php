<?php

namespace App\Livewire\ReturPembelian;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\ReturPembelian;
use App\Models\ReturPembelianDetail;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\Satuan;
use App\Models\Gudang;
use App\Models\GudangStock;
use App\Models\TransaksiGudangStock;
use App\Models\PenjualanDetail;
use Livewire\Component;
use Livewire\Attributes\Title;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Form extends Component
{
    use Toast;

    #[Title('Form Retur Pembelian')]
    public $breadcrumbs;
    
    // Form properties
    public $toko_id;
    
    // Retur properties
    #[Rule('required|exists:pembelian,id')]
    public ?int $pembelian_id = null;
    
    #[Rule('required')]
    public string $nomor_retur = '';
    
    #[Rule('required|date')]
    public string $tanggal_retur = '';
    
    #[Rule('nullable|string|max:500')]
    public string $catatan = '';
    
    // Related objects
    public ?Pembelian $selectedPembelian = null;
    public ?Supplier $supplier = null;
    public int $gudang_id;
    
    // Detail properties
    public array $details = [];
    
    // UI states
    public bool $showPembelianModal = false;
    public string $searchPembelian = '';
    public array $availablePembelians = [];
    
    public function mount()
    {
        $this->toko_id = Auth::user()->akses->toko_id;
        $this->tanggal_retur = Carbon::now()->format('Y-m-d H:i');
        $this->generateNomorRetur();
        $this->loadAvailablePembelians();

        $user = Auth::user();

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Retur Pembelian ', 'link' => route('retur-pembelian.index')],
            ['label'=> 'Tambah Retur'],
        ];
    }
    
    protected function generateNomorRetur()
    {
        $lastRetur = ReturPembelian::where('toko_id', $this->toko_id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();
            
        $nextNumber = $lastRetur ? (intval(substr($lastRetur->nomor_retur_pembelian, -4)) + 1) : 1;
        $this->nomor_retur = 'RBL-'. $this->toko_id . '-' . now()->format('Ymd') . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    protected function loadAvailablePembelians()
    {
        // Get pembelian_ids yang sudah ada penjualannya (via pembelian_detail_id di penjualan_detail)
        $pembelianIdsWithSales = PenjualanDetail::whereNotNull('pembelian_detail_id')
            ->join('pembelian_detail', 'penjualan_detail.pembelian_detail_id', '=', 'pembelian_detail.id')
            ->pluck('pembelian_detail.pembelian_id')
            ->unique()
            ->toArray();
        
        $query = Pembelian::with(['supplier', 'pembelianDetails'])
            ->where('toko_id', $this->toko_id)
            ->whereIn('status', ['lunas', 'belum_bayar', 'belum_lunas'])
            ->whereDoesntHave('returPembelian') // Exclude pembelian yang sudah ada retur
            ->whereNotIn('id', $pembelianIdsWithSales) // Exclude pembelian yang sudah ada penjualannya
            ->latest();
            
        if ($this->searchPembelian) {
            $query->where(function ($q) {
                $q->where('nomor_pembelian', 'like', '%' . $this->searchPembelian . '%')
                  ->orWhereHas('supplier', function ($sq) {
                      $sq->where('nama_supplier', 'like', '%' . $this->searchPembelian . '%');
                  });
            });
        }
        
        $this->availablePembelians = $query->limit(10)->get()->map(function($pembelian) {
            return [
                'id' => $pembelian->id,
                'nomor_pembelian' => $pembelian->nomor_pembelian,
                'tanggal_pembelian' => $pembelian->tanggal_pembelian,
                'total_harga' => $pembelian->total_harga,
                'status' => $pembelian->status,
                'supplier' => [
                    'nama_supplier' => $pembelian->supplier->nama_supplier ?? ''
                ],
                'pembelian_details' => $pembelian->pembelianDetails->toArray()
            ];
        })->toArray();
    }
    
    public function updatedSearchPembelian()
    {
        $this->loadAvailablePembelians();
    }
    
    public function selectPembelian($pembelianId)
    {
        // Validasi: cek apakah pembelian sudah pernah ada penjualannya
        $hasSales = PenjualanDetail::whereNotNull('pembelian_detail_id')
            ->whereHas('pembelianDetail', function ($q) use ($pembelianId) {
                $q->where('pembelian_id', $pembelianId);
            })
            ->exists();
        
        if ($hasSales) {
            $this->error('Pembelian ini tidak dapat diretur karena sudah ada penjualan terkait.');
            return;
        }
        
        $this->pembelian_id = $pembelianId;
        $this->selectedPembelian = Pembelian::with(['supplier', 'pembelianDetails.barang', 'pembelianDetails.satuan'])->find($pembelianId);
        $this->supplier = $this->selectedPembelian->supplier;
        
        // Set gudang_id dari pembelian yang dipilih
        // Ambil gudang_id dari pembelian detail pertama (asumsi semua detail menggunakan gudang yang sama)
        $firstDetail = $this->selectedPembelian->pembelianDetails->first();
        if ($firstDetail && $firstDetail->gudang_id) {
            $this->gudang_id = $firstDetail->gudang_id;
        }
        
        // Load available items for return
        $this->loadPembelianDetails();
        
        $this->showPembelianModal = false;
        $this->searchPembelian = '';
        
        $this->success('Pembelian berhasil dipilih: ' . $this->selectedPembelian->nomor_pembelian);
    }
    
    public function clearPembelian()
    {
        $this->reset(['pembelian_id', 'selectedPembelian', 'supplier', 'details']);
        $this->success('Pembelian telah dihapus');
    }
    
    public function setAllReturQty()
    {
        foreach ($this->details as $index => $detail) {
            $this->details[$index]['qty_retur'] = $detail['qty_tersedia'] ?? $detail['qty_beli'];
            $this->details[$index]['total'] = $this->details[$index]['qty_retur'] * $detail['harga_beli'];
        }
        $this->success('Semua item telah diset untuk diretur');
    }
    
    public function clearAllReturQty()
    {
        foreach ($this->details as $index => $detail) {
            $this->details[$index]['qty_retur'] = 0;
            $this->details[$index]['total'] = 0;
            $this->details[$index]['alasan_retur'] = '';
        }
        $this->success('Semua quantity retur telah direset');
    }
    
    protected function loadPembelianDetails()
    {
        if (!$this->selectedPembelian) return;
        
        $this->details = [];
        
        foreach ($this->selectedPembelian->pembelianDetails as $detail) {
            // Check how much has already been returned for this barang
            $alreadyReturned = ReturPembelianDetail::whereHas('returPembelian', function ($q) {
                $q->where('pembelian_id', $this->pembelian_id);
            })
            ->where('barang_id', $detail->barang_id)
            ->sum('qty_retur');
            
            $availableQty = $detail->jumlah - $alreadyReturned;
            
            if ($availableQty > 0) {
                // Get nama barang with fallback
                $namaBarang = 'Barang tidak ditemukan';
                if ($detail->barang) {
                    $namaBarang = $detail->barang->nama_barang;
                } else {
                    // Manual query as fallback
                    $barang = \App\Models\Barang::withoutGlobalScopes()->find($detail->barang_id);
                    if ($barang) {
                        $namaBarang = $barang->nama_barang;
                    }
                }
                
                // Get satuan name with fallback
                $satuanName = '';
                if ($detail->satuan) {
                    $satuanName = $detail->satuan->nama;
                } else {
                    // Manual query as fallback
                    $satuan = \App\Models\Satuan::find($detail->satuan_id);
                    if ($satuan) {
                        $satuanName = $satuan->nama;
                    }
                }
                
                $this->details[] = [
                    'id' => null,
                    'pembelian_detail_id' => $detail->id,
                    'barang_id' => $detail->barang_id,
                    'nama_barang' => $namaBarang,
                    'satuan' => $satuanName,
                    'satuan_id' => $detail->satuan_id,
                    'qty_beli' => $detail->jumlah,
                    'qty_tersedia' => $availableQty,
                    'qty_retur' => 0,
                    'harga_beli' => $detail->harga_satuan,
                    'total' => 0,
                    'alasan_retur' => '',
                ];
            }
        }
    }
    
    public function updatedDetails($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1];
        
        if ($field === 'qty_retur') {
            $qty = floatval($value);
            $maxQty = $this->details[$index]['qty_tersedia'] ?? $this->details[$index]['qty_beli'];
            
            if ($qty > $maxQty) {
                $this->details[$index]['qty_retur'] = $maxQty;
                $qty = $maxQty;
                $this->warning('Jumlah retur tidak boleh melebihi jumlah yang tersedia: ' . number_format($maxQty, 0));
            }
            
            if ($qty < 0) {
                $this->details[$index]['qty_retur'] = 0;
                $qty = 0;
            }
            
            $this->details[$index]['total'] = $qty * $this->details[$index]['harga_beli'];
            
            // Reset alasan retur HANYA jika qty berubah menjadi 0
            if ($qty == 0) {
                $this->details[$index]['alasan_retur'] = '';
            }
            
            // Force refresh UI untuk memastikan perubahan ter-update
            $this->dispatch('$refresh');
        }
    }
    
    // Debug method - uncomment untuk debugging
    public function debugDetails()
    {
        $detailsToSave = collect($this->details)->filter(function ($detail) {
            return $detail['qty_retur'] > 0;
        });
        
        dd([
            'all_details' => $this->details,
            'details_to_save' => $detailsToSave->toArray(),
            'items_without_reason' => $detailsToSave->filter(function ($detail) {
                return empty(trim($detail['alasan_retur'] ?? ''));
            })->toArray()
        ]);
    }
    
    public function save()
    {
        $this->validate();
        
        // Validate details with better type checking
        $detailsToSave = collect($this->details)->filter(function ($detail) {
            // Pastikan qty_retur adalah numeric dan > 0
            $qty = is_numeric($detail['qty_retur']) ? (float)$detail['qty_retur'] : 0;
            return $qty > 0;
        });
        
        if ($detailsToSave->isEmpty()) {
            $this->error('Minimal harus ada 1 item yang diretur dengan quantity > 0');
            return;
        }
        
        // Validate alasan retur untuk item yang akan diretur dengan logging
        $itemsWithoutReason = $detailsToSave->filter(function ($detail) {
            // Cek apakah alasan_retur kosong (null, empty string, atau hanya whitespace)
            $alasan = trim($detail['alasan_retur'] ?? '');
            return empty($alasan);
        });
        
        // Debug: lihat items yang tidak punya alasan
        if ($itemsWithoutReason->isNotEmpty()) {
            $this->error('Semua item yang diretur harus memiliki alasan retur. Periksa ' . $itemsWithoutReason->count() . ' item.');
            return;
        }
        
        try {
            DB::beginTransaction();
            
            $data = [
                'toko_id' => $this->toko_id,
                'pembelian_id' => $this->pembelian_id,
                'supplier_id' => $this->supplier->id, // Field wajib dari database
                'gudang_id' => $this->gudang_id, // Gunakan property yang sudah ada
                'nomor_retur_pembelian' => $this->nomor_retur, // Field yang benar dari database
                'tanggal_retur' => $this->tanggal_retur,
                'catatan' => $this->catatan,
                'dibuat_oleh' => Auth::id(), // Field wajib dari database
            ];
            
            $returPembelian = ReturPembelian::create($data);
            
            // Save details and update stock
            foreach ($detailsToSave as $detail) {
                $detailData = [
                    'retur_pembelian_id' => $returPembelian->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty_retur' => $detail['qty_retur'],
                    'harga_satuan' => $detail['harga_beli'],
                    'total_harga' => $detail['total'],
                    'alasan_retur' => $detail['alasan_retur'],
                ];
                
                $returDetail = ReturPembelianDetail::create($detailData);
                
                // Update Gudang Stock (reduce stock for return to supplier)
                $gudangStock = GudangStock::where([
                    'gudang_id' => $this->gudang_id,
                    'barang_id' => $detail['barang_id']
                ])->first();
                
                if ($gudangStock) {
                    // Calculate quantity in smallest unit
                    // Get konversi from the original pembelian detail
                    $pembelianDetail = PembelianDetail::where([
                        'pembelian_id' => $this->pembelian_id,
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id']
                    ])->first();
                    
                    $konversiSatuanTerkecil = $pembelianDetail ? $pembelianDetail->konversi_satuan_terkecil : 1;
                    $jumlahTerkecil = $detail['qty_retur'] * $konversiSatuanTerkecil;
                    
                    // Check if there's enough stock to return
                    if ($gudangStock->jumlah >= $jumlahTerkecil) {
                        // Reduce stock (retur pembelian = barang keluar dari gudang)
                        $gudangStock->decrement('jumlah', $jumlahTerkecil);
                        
                        // Create stock transaction record
                        TransaksiGudangStock::create([
                            'gudang_stock_id' => $gudangStock->id,
                            'pembelian_detail_id' => null, // Tidak terkait langsung dengan pembelian detail
                            'penjualan_detail_id' => null,
                            'jumlah' => $detail['qty_retur'],
                            'konversi_satuan_terkecil' => $konversiSatuanTerkecil,
                            'tipe' => 'keluar', // Retur pembelian = stock keluar
                        ]);
                    } else {
                        // Log warning: insufficient stock for return
                        Log::warning("Insufficient stock for return. Available: {$gudangStock->jumlah}, Required: {$jumlahTerkecil}");
                        
                        // Still create transaction but with warning
                        $gudangStock->update(['jumlah' => 0]); // Set to 0 if negative
                        
                        TransaksiGudangStock::create([
                            'gudang_stock_id' => $gudangStock->id,
                            'pembelian_detail_id' => null,
                            'penjualan_detail_id' => null,
                            'jumlah' => $detail['qty_retur'],
                            'konversi_satuan_terkecil' => $konversiSatuanTerkecil,
                            'tipe' => 'keluar',
                        ]);
                    }
                } else {
                    // Create new gudang stock with 0 quantity if doesn't exist
                    $pembelianDetail = PembelianDetail::where([
                        'pembelian_id' => $this->pembelian_id,
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id']
                    ])->first();
                    
                    $konversiSatuanTerkecil = $pembelianDetail ? $pembelianDetail->konversi_satuan_terkecil : 1;
                    
                    $gudangStock = GudangStock::create([
                        'gudang_id' => $this->gudang_id,
                        'barang_id' => $detail['barang_id'],
                        'jumlah' => 0, // Start with 0 since we're returning
                    ]);
                    
                    // Create stock transaction record
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'pembelian_detail_id' => null,
                        'penjualan_detail_id' => null,
                        'jumlah' => $detail['qty_retur'],
                        'konversi_satuan_terkecil' => $konversiSatuanTerkecil,
                        'tipe' => 'keluar',
                    ]);
                }
            }
            
            DB::commit();
            
            $this->success('Retur pembelian berhasil disimpan');
            return redirect()->route('retur-pembelian.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal menyimpan retur: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        $alasanReturOptions = [
            ['value' => 'rusak', 'name' => 'Barang Rusak'],
            ['value' => 'tidak_sesuai', 'name' => 'Tidak Sesuai Pesanan'],
            ['value' => 'kelebihan', 'name' => 'Kelebihan Pengiriman'],
            ['value' => 'kadaluarsa', 'name' => 'Mendekati Kadaluarsa'],
            ['value' => 'lainnya', 'name' => 'Lainnya'],
        ];
        
        return view('livewire.retur-pembelian.form', compact( 'alasanReturOptions'));
    }
}