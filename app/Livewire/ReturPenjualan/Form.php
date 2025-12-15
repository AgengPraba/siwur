<?php

namespace App\Livewire\ReturPenjualan;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\ReturPenjualan;
use App\Models\ReturPenjualanDetail;
use App\Models\Customer;
use App\Models\Barang;
use App\Models\Gudang;
use App\Models\GudangStock;
use App\Models\TransaksiGudangStock;
use App\Traits\HasTenancy;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Form extends Component
{
    use Toast;
    
    #[Title('Form Retur Penjualan')]
    
    // Form properties
    public ?int $toko_id = null;
    
    // Retur properties
    #[Rule('required|exists:penjualan,id')]
    public ?int $penjualan_id = null;
    
    #[Rule('required')]
    public string $nomor_retur = '';
    
    #[Rule('required|date')]
    public string $tanggal_retur = '';
    
    #[Rule('nullable|string|max:500')]
    public string $catatan = '';

    public $breadcrumbs;
    
    // Related objects
    public ?Penjualan $selectedPenjualan = null;
    public ?Customer $customer = null;
    public int $gudang_id;
    
    // Detail properties
    public array $details = [];
    
    // UI states
    public bool $showPenjualanModal = false;
    public string $searchPenjualan = '';
    public array $availablePenjualans = [];
    
    public function mount()
    {
        $this->toko_id = Auth::user()->akses->toko_id;
        $this->tanggal_retur = now()->format('Y-m-d H:i');

        $namaTokoLabel = Auth::user()->akses->toko->nama_toko ?? 'Toko Tidak Diketahui';
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Retur Penjualan - ' . $namaTokoLabel, 'href' => route('retur-penjualan.index')],
        ];

        $this->generateNomorRetur();
        $this->loadAvailablePenjualans();
    }
    
    protected function generateNomorRetur()
    {
        $lastRetur = ReturPenjualan::where('toko_id', $this->toko_id)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();
            
        $nextNumber = $lastRetur ? (intval(substr($lastRetur->nomor_retur_penjualan, -4)) + 1) : 1;
        $this->nomor_retur = 'RJL-'. $this->toko_id .'-'. now()->format('Ym') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
    
    protected function loadAvailablePenjualans()
    {
        $query = Penjualan::with(['customer', 'penjualanDetails'])
            ->where('toko_id', $this->toko_id)
            ->whereDoesntHave('returPenjualan') // Exclude penjualan yang sudah ada retur
            ->latest();
            
        if ($this->searchPenjualan) {
            $query->where(function ($q) {
                $q->where('nomor_penjualan', 'like', '%' . $this->searchPenjualan . '%')
                  ->orWhereHas('customer', function ($cq) {
                      $cq->where('nama_customer', 'like', '%' . $this->searchPenjualan . '%');
                  });
            });
        }
        
        $this->availablePenjualans = $query->limit(10)->get()->map(function($penjualan) {
            return [
                'id' => $penjualan->id,
                'nomor_penjualan' => $penjualan->nomor_penjualan,
                'tanggal_penjualan' => $penjualan->tanggal_penjualan,
                'total_harga' => $penjualan->total_harga,
                'customer' => [
                    'nama_customer' => $penjualan->customer->nama_customer ?? ''
                ],
                'penjualan_details' => $penjualan->penjualanDetails->toArray()
            ];
        })->toArray();
    }
    
    public function updatedSearchPenjualan()
    {
        $this->loadAvailablePenjualans();
    }
    
    public function selectPenjualan($penjualanId)
    {
        $this->penjualan_id = $penjualanId;
        $this->selectedPenjualan = Penjualan::with(['customer', 'penjualanDetails.barang', 'penjualanDetails.satuan'])->find($penjualanId);
        $this->customer = $this->selectedPenjualan->customer;
        
        // Set gudang_id dari penjualan yang dipilih
        // Ambil gudang_id dari penjualan detail pertama (asumsi semua detail menggunakan gudang yang sama)
        $firstDetail = $this->selectedPenjualan->penjualanDetails->first();
        if ($firstDetail && $firstDetail->gudang_id) {
            $this->gudang_id = $firstDetail->gudang_id;
        }
        
        // Load available items for return
        $this->loadPenjualanDetails();
        
        $this->showPenjualanModal = false;
        $this->searchPenjualan = '';
        
        $this->success('Penjualan berhasil dipilih: ' . $this->selectedPenjualan->nomor_penjualan);
    }
    
    public function clearPenjualan()
    {
        $this->reset(['penjualan_id', 'selectedPenjualan', 'customer', 'details']);
        $this->success('Penjualan telah dihapus');
    }
    
    public function setAllReturQty()
    {
        foreach ($this->details as $index => $detail) {
            $this->details[$index]['qty_retur'] = $detail['qty_tersedia'] ?? $detail['qty_jual'];
            $this->details[$index]['total'] = $this->details[$index]['qty_retur'] * $detail['harga_jual'];
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
    
    protected function loadPenjualanDetails()
    {
        if (!$this->selectedPenjualan) return;
        
        $this->details = [];
        
        foreach ($this->selectedPenjualan->penjualanDetails as $detail) {
            // Check how much has already been returned for this barang from this penjualan
            $alreadyReturned = ReturPenjualanDetail::whereHas('returPenjualan', function ($q) {
                $q->where('penjualan_id', $this->penjualan_id);
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
                $satuanData = [];
                if ($detail->satuan) {
                    $satuanName = $detail->satuan->nama_satuan;
                    $satuanData = [
                        'nama_satuan' => $detail->satuan->nama_satuan
                    ];
                } else {
                    // Manual query as fallback
                    $satuan = \App\Models\Satuan::find($detail->satuan_id);
                    if ($satuan) {
                        $satuanName = $satuan->nama_satuan;
                        $satuanData = [
                            'nama_satuan' => $satuan->nama_satuan
                        ];
                    }
                }
                
                $this->details[] = [
                    'id' => null,
                    'penjualan_detail_id' => $detail->id,
                    'barang_id' => $detail->barang_id,
                    'nama_barang' => $namaBarang,
                    'satuan' => $satuanData,
                    'satuan_id' => $detail->satuan_id,
                    'qty_jual' => $detail->jumlah,
                    'qty_tersedia' => $availableQty,
                    'qty_retur' => 0,
                    'harga_jual' => $detail->harga_satuan,
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
            $maxQty = $this->details[$index]['qty_tersedia'] ?? $this->details[$index]['qty_jual'];
            
            if ($qty > $maxQty) {
                $this->details[$index]['qty_retur'] = $maxQty;
                $qty = $maxQty;
                $this->warning('Jumlah retur tidak boleh melebihi jumlah yang tersedia: ' . number_format($maxQty, 0));
            }
            
            if ($qty < 0) {
                $this->details[$index]['qty_retur'] = 0;
                $qty = 0;
            }
            
            $this->details[$index]['total'] = $qty * $this->details[$index]['harga_jual'];
            
            // Reset alasan retur HANYA jika qty berubah menjadi 0
            if ($qty == 0) {
                $this->details[$index]['alasan_retur'] = '';
            }
            
            // Force refresh UI untuk memastikan perubahan ter-update
            $this->dispatch('$refresh');
        }
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
                'penjualan_id' => $this->penjualan_id,
                'customer_id' => $this->customer->id,
                'gudang_id' => $this->gudang_id, // Gunakan property yang sudah ada
                'nomor_retur_penjualan' => $this->nomor_retur, // Map property to database field
                'tanggal_retur' => $this->tanggal_retur,
                'catatan' => $this->catatan,
                'dibuat_oleh' => Auth::id(),
            ];
            
            $returPenjualan = ReturPenjualan::create($data);
            
            // Save details and update stock
            foreach ($detailsToSave as $detail) {
                $detailData = [
                    'retur_penjualan_id' => $returPenjualan->id,
                    'barang_id' => $detail['barang_id'],
                    'satuan_id' => $detail['satuan_id'],
                    'qty_retur' => $detail['qty_retur'],
                    'harga_satuan' => $detail['harga_jual'],
                    'total_harga' => $detail['total'],
                    'alasan_retur' => $detail['alasan_retur'],
                ];
                
                $returDetail = ReturPenjualanDetail::create($detailData);
                
                // Update Gudang Stock (increase stock for return from customer)
                $gudangStock = GudangStock::where([
                    'gudang_id' => $this->gudang_id,
                    'barang_id' => $detail['barang_id']
                ])->first();
                
                if ($gudangStock) {
                    // Calculate quantity in smallest unit
                    // Get konversi from the original penjualan detail
                    $penjualanDetail = PenjualanDetail::where([
                        'penjualan_id' => $this->penjualan_id,
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id']
                    ])->first();
                    
                    $konversiSatuanTerkecil = $penjualanDetail ? $penjualanDetail->konversi_satuan_terkecil : 1;
                    $jumlahTerkecil = $detail['qty_retur'] * $konversiSatuanTerkecil;
                    
                    // Increase stock (retur penjualan = barang masuk ke gudang)
                    $gudangStock->increment('jumlah', $jumlahTerkecil);
                    // Create stock transaction record
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'pembelian_detail_id' => null,
                        'penjualan_detail_id' => $detail['penjualan_detail_id'],
                        'jumlah' => $detail['qty_retur'],
                        'konversi_satuan_terkecil' => $konversiSatuanTerkecil,
                        'tipe' => 'masuk', // Retur penjualan = stock masuk
                    ]);
                } else {
                    // Create new gudang stock if doesn't exist
                    $konversiSatuanTerkecil = 1; // Default konversi
                    $penjualanDetail = PenjualanDetail::where([
                        'penjualan_id' => $this->penjualan_id,
                        'barang_id' => $detail['barang_id'],
                        'satuan_id' => $detail['satuan_id']
                    ])->first();
                    
                    if ($penjualanDetail) {
                        $konversiSatuanTerkecil = $penjualanDetail->konversi_satuan_terkecil;
                    }
                    
                    $jumlahTerkecil = $detail['qty_retur'] * $konversiSatuanTerkecil;
                    
                    $gudangStock = GudangStock::create([
                        'gudang_id' => $this->gudang_id,
                        'barang_id' => $detail['barang_id'],
                        'jumlah' => $jumlahTerkecil,
                    ]);
                    
                    // Create stock transaction record
                    TransaksiGudangStock::create([
                        'gudang_stock_id' => $gudangStock->id,
                        'pembelian_detail_id' => null,
                        'penjualan_detail_id' => null,
                        'jumlah' => $detail['qty_retur'],
                        'konversi_satuan_terkecil' => $konversiSatuanTerkecil,
                        'tipe' => 'masuk', // Retur penjualan = stock masuk
                    ]);
                }
            }
            
            DB::commit();
            
            $this->success('Retur penjualan berhasil disimpan');
            return redirect()->route('retur-penjualan.index');
            
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
        
        return view('livewire.retur-penjualan.form', compact( 'alasanReturOptions'));
    }
}