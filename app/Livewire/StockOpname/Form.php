<?php

namespace App\Livewire\StockOpname;

use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Gudang;
use App\Models\Barang;
use App\Models\GudangStock;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\TransaksiGudangStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Mary\Traits\Toast;

#[Title('Form Stock Opname')]

class Form extends Component
{
    use Toast;
    public $breadcrumbs;
    public $type = 'create';
    
    #Header Stock Opname
    public $toko_id;
    public $nomor_opname;
    public $tanggal_opname;
    public $user_id;    
    public $gudang_id;
    public $keterangan;

    #Detail Stock Opname
    public $detail_gudang_stock_id;
    public $detail_satuan_name = '';
    public $detail_satuan_id = null;
    public $detail_satuan_data = [];
    
    public $detail_barang_id;
    public $detail_stok_sistem = 0;
    public $detail_stok_fisik = 0;
    public $detail_harga_satuan = 0;

    public $details = [];
    
    // Modal properties
    public $deleteDetailModal = false;
    public $detailToDelete = null;
    public $detailIndexToDelete = null;

    #Data Untuk Dropdown
    public $gudang_data = [];
    public $barang_searchable = [];

    // Options list
    public $getBarangSearch;

    public function mount()
    {
       // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        $this->user_id = Auth::id();
        $this->toko_id = Auth::user()->akses->toko_id;
        $this->tanggal_opname = Carbon::now()->format('Y-m-d H:i');
        $this->loadDropdownData();
        
        // Initialize search results
        $this->search('');

        // Clear any previous session data for new opname
        $this->clearSession();
        
        $this->nomor_opname = StockOpname::generateNomor();
        $this->tanggal_opname = Carbon::now()->format('Y-m-d H:i');
        
        $akses = $user->akses ?? null;
        $namaTokoLabel = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Stock Opname - ' . $namaTokoLabel, 'href' => route('stock-opname.index')],
            ['label' => 'Tambah'],
        ];
    }

    public function search(string $value = '')
    {
        // Get selected barang if exists
        $selectedBarang = collect();
        if ($this->detail_barang_id) {
            $selectedBarang = DB::table('barang')
                ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->leftJoin('gudang_stock', function($join) {
                    $join->on('barang.id', '=', 'gudang_stock.barang_id')
                         ->where('gudang_stock.gudang_id', $this->gudang_id ?? 0);
                })
                ->where('barang.id', $this->detail_barang_id)
                ->select(
                    'barang.id',
                    DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ') - Stok: ', IFNULL(gudang_stock.jumlah, 0)) as name"),
                    'barang.kode_barang'
                )
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                });
        }

        // Search barang based on name, kode_barang, or jenis_barang and filter by gudang if selected
        $searchQuery = DB::table('barang')
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->where('barang.toko_id', $this->toko_id)
            ->where(function ($query) use ($value) {
                if (!empty($value)) {
                    $query->where('barang.kode_barang', 'like', "%$value%")
                        ->orWhere('barang.nama_barang', 'like', "%$value%")
                        ->orWhere('jenis_barang.nama_jenis_barang', 'like', "%$value%");
                }
            });

        // If gudang is selected, join with gudang_stock and show stock info
        if ($this->gudang_id) {
            $searchQuery->leftJoin('gudang_stock', function($join) {
                $join->on('barang.id', '=', 'gudang_stock.barang_id')
                     ->where('gudang_stock.gudang_id', $this->gudang_id);
            })
            ->select(
                'barang.id',
                DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ') - Stok: ', IFNULL(gudang_stock.jumlah, 0)) as name"),
                'barang.kode_barang'
            );
        } else {
            $searchQuery->select(
                'barang.id',
                DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ') - Stok: 0') as name"),
                'barang.kode_barang'
            );
        }

        $searchResults = $searchQuery
            ->take(15)
            ->orderBy('barang.nama_barang')
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->merge($selectedBarang)
            ->unique('id')  // Hapus duplikasi berdasarkan ID
            ->values();     // Re-index array

        $this->getBarangSearch = $searchResults->toArray();
    }

    public function updatedDetailBarangId($val)
    {
        if ($this->detail_barang_id) {
            // Ambil data barang terpisah - satuan harus selalu terisi
            $barang = Barang::with('satuanTerkecil')->find($this->detail_barang_id);
            
            if ($barang) {
                // Set data satuan dari barang (selalu diisi)
                $this->detail_satuan_name = $barang->satuanTerkecil->nama_satuan ?? '';
                $this->detail_satuan_id = $barang->satuanTerkecil->id ?? null;
                $this->detail_satuan_data = $barang->satuanTerkecil ? 
                    [['id' => $barang->satuanTerkecil->id, 'nama' => $barang->satuanTerkecil->nama_satuan]] : [];
                
                // Query stock hanya jika gudang sudah dipilih
                if ($this->gudang_id) {
                    $gudangStock = GudangStock::where('barang_id', $this->detail_barang_id)
                        ->where('gudang_id', $this->gudang_id)
                        ->first();
                    
                    $this->detail_stok_sistem = $gudangStock ? (int)$gudangStock->jumlah : 0;
                    $this->detail_gudang_stock_id = $gudangStock ? $gudangStock->id : null;
                } else {
                    // Reset stok jika gudang belum dipilih
                    $this->detail_stok_sistem = 0;
                    $this->detail_gudang_stock_id = null;
                }
            }
        } else {
            // Reset semua fields jika barang tidak dipilih
            $this->detail_satuan_name = '';
            $this->detail_satuan_id = null;
            $this->detail_satuan_data = [];
            $this->detail_stok_sistem = 0;
            $this->detail_gudang_stock_id = null;
        }
    }

    public function updatedGudangId($val)
    {
        // Jika ada barang yang sudah dipilih, update stok sistem-nya
        if ($this->detail_barang_id) {
            $gudangStock = GudangStock::where('barang_id', $this->detail_barang_id)
                ->where('gudang_id', $this->gudang_id)
                ->first();
            
            $this->detail_stok_sistem = $gudangStock ? (int)$gudangStock->jumlah : 0;
            $this->detail_gudang_stock_id = $gudangStock ? $gudangStock->id : null;
        } else {
            // Reset stok jika belum ada barang yang dipilih
            $this->detail_stok_sistem = 0;
            $this->detail_gudang_stock_id = null;
        }
        
        // Refresh search results to show stock info for new gudang
        $this->search('');
    }

    // Blade binds to gudang_data select; keep in sync with gudang_id
    public function updatedGudangData($val)
    {
        $this->gudang_id = $val;
        // reuse existing logic
        $this->updatedGudangId($val);
    }

    public function updatedDetailGudangStockId($val)
    {
        if ($this->detail_gudang_stock_id) {
            $gs = GudangStock::with(['barang','satuanTerkecil'])->find($this->detail_gudang_stock_id);
            if ($gs) {
                $this->detail_barang_id = $gs->barang_id;
                $this->detail_stok_sistem = (int)$gs->jumlah;
                $this->detail_satuan_name = $gs->satuanTerkecil->nama_satuan ?? '';
                $this->detail_satuan_id = $gs->satuanTerkecil->id ?? null;
                $this->detail_satuan_data = $gs->satuanTerkecil ? [['id' => $gs->satuanTerkecil->id, 'nama' => $gs->satuanTerkecil->nama_satuan]] : [];
            }
        }
    }

    public function addDetail()
    {
        $this->validate([
            'gudang_id' => 'required',
            'detail_barang_id' => 'required',
            'detail_stok_fisik' => 'required|numeric|min:0',
            'detail_harga_satuan' => 'required|numeric|min:0',
        ], [
            'gudang_id.required' => 'Pilih gudang terlebih dahulu',
            'detail_barang_id.required' => 'Pilih barang terlebih dahulu',
            'detail_stok_fisik.required' => 'Stok fisik harus diisi',
            'detail_stok_fisik.numeric' => 'Stok fisik harus berupa angka',
            'detail_stok_fisik.min' => 'Stok fisik tidak boleh kurang dari 0',
            'detail_harga_satuan.required' => 'Harga satuan harus diisi',
            'detail_harga_satuan.numeric' => 'Harga satuan harus berupa angka',
            'detail_harga_satuan.min' => 'Harga satuan tidak boleh kurang dari 0',
        ]);

        // Get or create gudang stock record
        $gs = GudangStock::firstOrCreate(
            [
                'barang_id' => $this->detail_barang_id,
                'gudang_id' => $this->gudang_id
            ],
            [
                'jumlah' => 0,
                'toko_id' => $this->toko_id
            ]
        );

        if (!$gs) {
            $this->addError('detail_barang_id', 'Gagal membuat atau menemukan data stok gudang');
            return;
        }

        // Load barang data
        $barang = Barang::find($this->detail_barang_id);
        if (!$barang) {
            $this->addError('detail_barang_id', 'Data barang tidak ditemukan');
            return;
        }

        // Check if item already exists
        $existingIndex = collect($this->details)->search(function ($item) use ($gs) {
            return $item['gudang_stock_id'] == $gs->id;
        });

        if ($existingIndex !== false) {
            $this->addError('detail_barang_id', 'Barang sudah ditambahkan ke daftar');
            return;
        }

        $selisih = (int)$this->detail_stok_fisik - (int)$this->detail_stok_sistem;
        
        // Determine adjustment type
        $adjustmentType = 'sama';
        $adjustmentInfo = [];
        
        if ($selisih > 0) {
            $adjustmentType = 'plus';
            $adjustmentInfo = [
                'type' => 'penambahan stok sistem',
                'icon' => 'o-arrow-up',
                'class' => 'text-green-600 bg-green-100',
                'label' => "+{$selisih}"
            ];
        } elseif ($selisih < 0) {
            $adjustmentType = 'minus';
            $adjustmentInfo = [
                'type' => 'pengurangan stok sistem',
                'icon' => 'o-arrow-down', 
                'class' => 'text-red-600 bg-red-100',
                'label' => "{$selisih}"
            ];
        } else {
            $adjustmentInfo = [
                'type' => 'stok sistem tidak_berubah',
                'icon' => 'o-minus',
                'class' => 'text-gray-600 bg-gray-100',
                'label' => '0'
            ];
        }

        $this->details[] = [
            'gudang_stock_id' => $gs->id,
            'barang_id' => $barang->id,
            'barang_nama' => $barang->nama_barang,
            'satuan' => $this->detail_satuan_name,
            'satuan_nama' => $this->detail_satuan_name,
            'satuan_id' => $this->detail_satuan_id,
            'before_qty' => $this->detail_stok_sistem,
            'after_qty' => $this->detail_stok_fisik,
            'stok_sistem' => $this->detail_stok_sistem,
            'stok_fisik' => $this->detail_stok_fisik,
            'selisih' => $selisih,
            'adjustment_type' => $adjustmentType,
            'adjustment_info' => $adjustmentInfo,
            'harga_satuan' => $this->detail_harga_satuan,
            'harga_beli' => $barang->harga_beli ?? 0,
            'harga_jual' => $barang->harga_jual ?? 0,
            'subtotal' => abs($selisih) * $this->detail_harga_satuan,
        ];

        // Auto-save to session
        $this->saveToSession();

        // Show success message
        $this->success('Item berhasil ditambahkan');

        // reset form detail
        $this->detail_gudang_stock_id = null;
        $this->detail_barang_id = null;
        $this->detail_stok_sistem = 0;
        $this->detail_stok_fisik = 0;
        $this->detail_harga_satuan = 0;
        $this->detail_satuan_name = '';
        $this->detail_satuan_id = null;
        $this->detail_satuan_data = [];
    }

    public function openDeleteDetailModal($index)
    {
        if (isset($this->details[$index])) {
            $this->detailIndexToDelete = $index;
            $this->detailToDelete = $this->details[$index];
            $this->deleteDetailModal = true;
        }
    }

    public function confirmDeleteDetail()
    {
        if ($this->detailIndexToDelete !== null && isset($this->details[$this->detailIndexToDelete])) {
            try {
                DB::beginTransaction();
                
                $detailToDelete = $this->details[$this->detailIndexToDelete];
                $barangId = $detailToDelete['barang_id'] ?? null;
                $gudangStockId = $detailToDelete['gudang_stock_id'] ?? null;
                $selisih = $detailToDelete['selisih'] ?? 0;
                
                // If we're in edit mode, we need to check for existing transactions
                if ($this->type == 'edit' && $gudangStockId) {
                    // Check for StockOpnameDetail
                    $stockOpnameDetail = StockOpnameDetail::where('gudang_stock_id', $gudangStockId)->first();
                    
                    if ($stockOpnameDetail) {
                        // Revert stock back to original value
                        $gs = GudangStock::find($gudangStockId);
                        if ($gs) {
                            $gs->update(['jumlah' => $detailToDelete['stok_sistem']]);
                        }
                        
                        // Find related transactions
                        if ($selisih > 0) {
                            // This was a stock addition, so find pembelian details
                            $pembelianDetails = PembelianDetail::whereHas('pembelian', function($q) {
                                $q->where('keterangan', 'like', '%Stock opname%');
                            })->where('barang_id', $barangId)->get();
                            
                            foreach ($pembelianDetails as $pd) {
                                // Delete related gudang transactions
                                TransaksiGudangStock::where('referensi_tabel', 'pembelian')
                                    ->where('referensi_id', $pd->pembelian_id)
                                    ->where('barang_id', $barangId)
                                    ->delete();
                                
                                // Delete the detail
                                $pd->delete();
                            }
                        } elseif ($selisih < 0) {
                            // This was a stock reduction, so find penjualan details
                            $penjualanDetails = PenjualanDetail::whereHas('penjualan', function($q) {
                                $q->where('keterangan', 'like', '%Stock opname%');
                            })->where('barang_id', $barangId)->get();
                            
                            foreach ($penjualanDetails as $pd) {
                                // Delete related gudang transactions
                                TransaksiGudangStock::where('referensi_tabel', 'penjualan')
                                    ->where('referensi_id', $pd->penjualan_id)
                                    ->where('barang_id', $barangId)
                                    ->delete();
                                
                                // Delete the detail
                                $pd->delete();
                            }
                        }
                        
                        // Delete stock opname detail
                        $stockOpnameDetail->delete();
                    }
                }
                
                // Remove item from details array
                $removedItem = $this->details[$this->detailIndexToDelete]['barang_nama'] ?? 'Item';
                array_splice($this->details, $this->detailIndexToDelete, 1);
                
                // Auto-save to session
                $this->saveToSession();
                
                DB::commit();
                
                $this->success("Item {$removedItem} berhasil dihapus");
                
                // Reset modal state
                $this->deleteDetailModal = false;
                $this->detailToDelete = null;
                $this->detailIndexToDelete = null;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal menghapus: " . $e->getMessage());
            }
        } else {
            $this->error("Item tidak ditemukan");
        }
    }

    public function removeDetail($index)
    {
        if (isset($this->details[$index])) {
            try {
                DB::beginTransaction();
                
                $detailToRemove = $this->details[$index];
                $barangId = $detailToRemove['barang_id'] ?? null;
                $gudangStockId = $detailToRemove['gudang_stock_id'] ?? null;
                $selisih = $detailToRemove['selisih'] ?? 0;
                
                // If we're in edit mode, we need to check for existing transactions
                if ($this->type == 'edit' && $gudangStockId) {
                    // Check for StockOpnameDetail
                    $stockOpnameDetail = StockOpnameDetail::where('gudang_stock_id', $gudangStockId)->first();
                    
                    if ($stockOpnameDetail) {
                        // Revert stock back to original value
                        $gs = GudangStock::find($gudangStockId);
                        if ($gs) {
                            $gs->update(['jumlah' => $detailToRemove['stok_sistem']]);
                        }
                        
                        // Find related transactions
                        if ($selisih > 0) {
                            // This was a stock addition, so find pembelian details
                            $pembelianDetails = PembelianDetail::whereHas('pembelian', function($q) {
                                $q->where('keterangan', 'like', '%Stock opname%');
                            })->where('barang_id', $barangId)->get();
                            
                            foreach ($pembelianDetails as $pd) {
                                // Delete related gudang transactions
                                TransaksiGudangStock::where('referensi_tabel', 'pembelian')
                                    ->where('referensi_id', $pd->pembelian_id)
                                    ->where('barang_id', $barangId)
                                    ->delete();
                                
                                // Delete the detail
                                $pd->delete();
                            }
                        } elseif ($selisih < 0) {
                            // This was a stock reduction, so find penjualan details
                            $penjualanDetails = PenjualanDetail::whereHas('penjualan', function($q) {
                                $q->where('keterangan', 'like', '%Stock opname%');
                            })->where('barang_id', $barangId)->get();
                            
                            foreach ($penjualanDetails as $pd) {
                                // Delete related gudang transactions
                                TransaksiGudangStock::where('referensi_tabel', 'penjualan')
                                    ->where('referensi_id', $pd->penjualan_id)
                                    ->where('barang_id', $barangId)
                                    ->delete();
                                
                                // Delete the detail
                                $pd->delete();
                            }
                        }
                        
                        // Delete stock opname detail
                        $stockOpnameDetail->delete();
                    }
                }
                
                $removedItem = $this->details[$index]['barang_nama'] ?? 'Item';
                array_splice($this->details, $index, 1);
                
                // Auto-save to session
                $this->saveToSession();
                
                DB::commit();
                
                $this->success("Item {$removedItem} berhasil dihapus");
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Gagal menghapus: " . $e->getMessage());
            }
        } else {
            $this->error("Item dengan index {$index} tidak ditemukan");
        }
    }

    public function save()
    {
        $this->validate([
            'nomor_opname' => 'required',
            'tanggal_opname' => 'required',
            'gudang_id' => 'required'
        ], [
            'nomor_opname.required' => 'Nomor opname harus diisi',
            'tanggal_opname.required' => 'Tanggal opname harus diisi',
            'gudang_id.required' => 'Gudang harus dipilih'
        ]);

        // default save as draft
        return $this->saveToDb();
    }

    public function saveDraft()
    {
        $this->validate([
            'nomor_opname' => 'required',
            'tanggal_opname' => 'required',
            'gudang_id' => 'required'
        ], [
            'nomor_opname.required' => 'Nomor opname harus diisi',
            'tanggal_opname.required' => 'Tanggal opname harus diisi',
            'gudang_id.required' => 'Gudang harus dipilih'
        ]);

        return $this->saveToDb();
    }

    private function saveToDb()
    {
        try {
            DB::beginTransaction();
            
            // Get supplier and customer opname
            $supplierOpname = Supplier::where('toko_id', $this->toko_id)
                                    ->where('is_opname', true)
                                    ->first();
            $customerOpname = Customer::where('toko_id', $this->toko_id)
                                    ->where('is_opname', true)
                                    ->first();
            
            if (!$supplierOpname || !$customerOpname) {
                throw new \Exception('Supplier opname atau Customer opname tidak ditemukan. Pastikan data dummy sudah dibuat.');
            }
            
            $op = StockOpname::create([
                'nomor_opname' => $this->nomor_opname,
                'tanggal_opname' => $this->tanggal_opname,
                'toko_id' => $this->toko_id,
                'user_id' => $this->user_id,
                'gudang_id' => $this->gudang_id,
                'keterangan' => $this->keterangan,
            ]);

            // Group details by adjustment type for batch processing
            $penambahan = [];
            $pengurangan = [];
            $transactionReferences = [];
            
            foreach ($this->details as $index => $d) {
                $detail = $op->details()->create([
                    'gudang_stock_id' => $d['gudang_stock_id'] ?? null,
                    'before_qty' => $d['before_qty'] ?? $d['stok_sistem'],
                    'after_qty' => $d['after_qty'] ?? $d['stok_fisik'],
                    'stok_sistem' => $d['stok_sistem'],
                    'stok_fisik' => $d['stok_fisik'],
                    'selisih' => $d['selisih'],
                    'adjustment_type' => $d['adjustment_type'] ?? 'sama',
                ]);

                // Always adjust stock when saving (real-time adjustment)
                if (!empty($d['gudang_stock_id'])) {
                    $gs = GudangStock::lockForUpdate()->find($d['gudang_stock_id']);
                    if ($gs) {
                        $gs->update([
                            'jumlah' => $d['after_qty']
                        ]);
                        
                        // Group items for transaction creation
                        if ($d['selisih'] > 0) {
                            // Penambahan stok - buat pembelian
                            $penambahan[] = [
                                'barang_id' => $gs->barang_id,
                                'qty' => abs($d['selisih']),
                                'gudang_stock' => $gs,
                                'detail_index' => $index
                            ];
                        } elseif ($d['selisih'] < 0) {
                            // Pengurangan stok - buat penjualan
                            $pengurangan[] = [
                                'barang_id' => $gs->barang_id,
                                'qty' => abs($d['selisih']),
                                'gudang_stock' => $gs,
                                'detail_index' => $index
                            ];
                        }
                    }
                }
            }
            
            // Create pembelian for stock additions
            if (!empty($penambahan)) {
                $pembelian = $this->createPembelianOpname($penambahan, $supplierOpname, $op);
                if ($pembelian) {
                    $transactionReferences['pembelian_id'] = $pembelian->id;
                }
            }
            
            // Create penjualan for stock reductions
            if (!empty($pengurangan)) {
                $penjualan = $this->createPenjualanOpname($pengurangan, $customerOpname, $op);
                if ($penjualan) {
                    $transactionReferences['penjualan_id'] = $penjualan->id;
                }
            }
            
            // Store transaction references in the stock opname record
            if (!empty($transactionReferences)) {
                $op->update([
                    'transaction_references' => json_encode($transactionReferences)
                ]);
            }

            DB::commit();
            
            // Clear session after successful save
            $this->clearSession();
            
            // Create appropriate message
            $message = 'Stock opname berhasil disimpan! Stok telah disesuaikan dan transaksi pembelian/penjualan otomatis telah dibuat.';
            
            $this->success($message, position: 'toast-top');
            
            return redirect()->route('stock-opname.index');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal menyimpan: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    // Summary helpers
    public function getTotalItemsProperty()
    {
        return count($this->details);
    }

    public function getTotalSelisihPlusProperty()
    {
        return collect($this->details)->filter(fn($d)=> $d['selisih'] > 0)->sum('selisih');
    }

    public function getTotalSelisihMinusProperty()
    {
        return collect($this->details)->filter(fn($d)=> $d['selisih'] < 0)->sum(function($d){ return abs($d['selisih']); });
    }

    public function searchBarang(string $value = '')
    {
        // Get currently selected barang if exists
        $selectedOption = collect();
        if ($this->detail_barang_id) {
            $selectedOption = DB::table('barang')
                ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
                ->where('barang.id', $this->detail_barang_id)
                ->select('barang.id', DB::raw("CONCAT(barang.nama_barang, ' (', jenis_barang.nama_jenis_barang, ')') as nama"))
                ->get()
                ->map(function ($item) {
                    return (array) $item;
                });
        }

        // Search barang based on nama_barang or kode_barang - otomatis terfilter berdasarkan toko_id
        $searchResults = DB::table('barang')
            ->join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
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

    private function loadDropdownData()
    {
        // Gudang data - otomatis terfilter berdasarkan toko_id
        $this->gudang_data = DB::table('gudang')
            ->select('id', 'nama_gudang as nama')
            ->where('toko_id', $this->toko_id)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->toArray();
    }

    private function saveToSession()
    {
        $sessionKey = 'opname_temp_' . $this->user_id;
        
        session()->put($sessionKey, [
            'nomor_opname' => $this->nomor_opname,
            'tanggal_opname' => $this->tanggal_opname,
            'gudang_id' => $this->gudang_id,
            'keterangan' => $this->keterangan,
            'details' => $this->details,
        ]);
    }

    private function loadFromSession()
    {
        $sessionKey = 'opname_temp_' . $this->user_id;
        $sessionData = session()->get($sessionKey);

        if ($sessionData) {
            $this->nomor_opname = $sessionData['nomor_opname'] ?? $this->nomor_opname;
            $this->tanggal_opname = $sessionData['tanggal_opname'] ?? $this->tanggal_opname;
            $this->gudang_id = $sessionData['gudang_id'] ?? null;
            $this->keterangan = $sessionData['keterangan'] ?? '';
            $this->details = $sessionData['details'] ?? [];
            
            // Load barang options for selected gudang
            if ($this->gudang_id) {
                $this->updatedGudangId($this->gudang_id);
            }
        }
    }

    private function clearSession()
    {
        $sessionKey = 'opname_temp_' . $this->user_id;
        session()->forget($sessionKey);
    }

    public function startFresh()
    {
        // Clear session and reset form
        $this->clearSession();
        
        // Reset all form fields
        $this->nomor_opname = StockOpname::generateNomor();
        $this->tanggal_opname = Carbon::now()->format('Y-m-d H:i');
        $this->gudang_id = null;
        $this->keterangan = '';
        $this->details = [];
        
        // Reset detail form
        $this->detail_gudang_stock_id = null;
        $this->detail_barang_id = null;
        $this->detail_satuan_name = '';
        $this->detail_satuan_id = null;
        $this->detail_satuan_data = [];
        $this->detail_stok_sistem = 0;
        $this->detail_stok_fisik = 0;
        $this->detail_harga_satuan = 0;
        
        // Clear search results
        $this->getBarangSearch = [];
        
        $this->success('Form telah direset. Mulai stock opname baru.', position: 'toast-top');
    }
    
    private function createPembelianOpname($penambahan, $supplierOpname, $stockOpname)
    {
        // Create pembelian header
        $pembelian = Pembelian::create([
            'nomor_pembelian' => 'PB-OPNAME-' . $stockOpname->nomor_opname,
            'tanggal_pembelian' => $this->tanggal_opname,
            'supplier_id' => $supplierOpname->id,
            'user_id' => $this->user_id,
            'toko_id' => $this->toko_id,
            'total_harga' => 0, // Will be calculated
            'keterangan' => 'Pembelian otomatis dari stock opname: ' . $stockOpname->nomor_opname,
            'status' => 'lunas'
        ]);
        
        $totalPembelian = 0;
        
        foreach ($penambahan as $item) {    
            $barang = Barang::find($item['barang_id']);
            
            // Find the corresponding detail in the details array
            $detailItem = collect($this->details)->first(function($d) use ($item) {
                return $d['gudang_stock_id'] == $item['gudang_stock']->id;
            });
            
            if (!$detailItem) {
                continue; // Skip if no detail found
            }
            
            // Use user-defined harga_satuan
            $hargaSatuan = $detailItem['harga_satuan'] ?? 0;
            $jumlah = $item['qty'];
            $konversiSatuan = 1; // Default conversion factor
            $subtotal = $hargaSatuan * $jumlah;
            $totalPembelian += $subtotal;
            
            // Get or create gudang stock
            $gudangStock = GudangStock::firstOrCreate(
                [
                    'gudang_id' => $this->gudang_id,
                    'barang_id' => $item['barang_id'],
                ],
                ['jumlah' => 0]
            );
            
            // Create pembelian detail with correct field names
            $pembelianDetail = PembelianDetail::create([
                'pembelian_id' => $pembelian->id,
                'barang_id' => $item['barang_id'],
                'satuan_id' => $detailItem['satuan_id'] ?? null,
                'gudang_id' => $this->gudang_id,
                'harga_satuan' => $hargaSatuan,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
                'konversi_satuan_terkecil' => $konversiSatuan,
                'diskon' => 0,
                'biaya_lain' => 0,
                'rencana_harga_jual' => $barang->harga_jual ?? 0
            ]);
            
            // Create transaksi gudang stock
            TransaksiGudangStock::create([
                'gudang_stock_id' => $gudangStock->id,
                'pembelian_detail_id' => $pembelianDetail->id,
                'jumlah' => $jumlah,
                'konversi_satuan_terkecil' => $konversiSatuan,
                'tipe' => 'masuk',
            ]);
        }
        
        // Update total pembelian
        $pembelian->update(['total_harga' => $totalPembelian]);
        
        return $pembelian;
    }
    
    private function createPenjualanOpname($pengurangan, $customerOpname, $stockOpname)
    {
        // Create penjualan header
        $penjualan = Penjualan::create([
            'nomor_penjualan' => 'PJ-OPNAME-' . $stockOpname->nomor_opname,
            'tanggal_penjualan' => $this->tanggal_opname,
            'customer_id' => $customerOpname->id,
            'user_id' => $this->user_id,
            'toko_id' => $this->toko_id,
            'total_harga' => 0, // Will be calculated
            'keterangan' => 'Penjualan otomatis dari stock opname: ' . $stockOpname->nomor_opname,
            'status' => 'lunas'
        ]);
        
        $totalPenjualan = 0;
        
        foreach ($pengurangan as $item) {
            $barang = Barang::find($item['barang_id']);
            
            // Find the corresponding detail in the details array
            $detailItem = collect($this->details)->first(function($d) use ($item) {
                return $d['gudang_stock_id'] == $item['gudang_stock']->id;
            });
            
            if (!$detailItem) {
                continue; // Skip if no detail found
            }
            
            // Use user-defined harga_satuan
            $hargaSatuan = $detailItem['harga_satuan'] ?? 0;
            $jumlah = $item['qty'];
            $konversiSatuan = 1; // Default conversion factor
            $subtotal = $hargaSatuan * $jumlah;
            $totalPenjualan += $subtotal;
            
            // Default pembelianDetailId (will be used if we can't find or create a valid reference)
            $pembelianDetailId = null;
            
            // Try to find a recent pembelian_detail for this barang
            $pembelianDetail = PembelianDetail::whereHas('pembelian', function($q) {
                $q->where('status', 'lunas');
            })
            ->where('barang_id', $item['barang_id'])
            ->orderBy('created_at', 'desc')
            ->first();
            
            if ($pembelianDetail) {
                $pembelianDetailId = $pembelianDetail->id;
            } else {
                // If no existing pembelian_detail found, create a dummy pembelian with details
                $supplierOpname = Supplier::where('toko_id', $this->toko_id)
                                        ->where('is_opname', true)
                                        ->first();
                                        
                if ($supplierOpname) {
                    $dummyPembelian = Pembelian::create([
                        'nomor_pembelian' => 'PB-REF-' . $stockOpname->nomor_opname . '-' . $item['barang_id'],
                        'tanggal_pembelian' => $this->tanggal_opname,
                        'supplier_id' => $supplierOpname->id,
                        'user_id' => $this->user_id,
                        'toko_id' => $this->toko_id,
                        'total_harga' => $subtotal,
                        'keterangan' => 'Pembelian referensi untuk stock opname: ' . $stockOpname->nomor_opname,
                        'status' => 'lunas'
                    ]);
                    
                    $dummyDetail = PembelianDetail::create([
                        'pembelian_id' => $dummyPembelian->id,
                        'barang_id' => $item['barang_id'],
                        'satuan_id' => $detailItem['satuan_id'] ?? null,
                        'gudang_id' => $this->gudang_id,
                        'harga_satuan' => $hargaSatuan,
                        'jumlah' => $jumlah,
                        'subtotal' => $subtotal,
                        'konversi_satuan_terkecil' => $konversiSatuan
                    ]);
                    
                    $pembelianDetailId = $dummyDetail->id;
                }
            }
            
            // Get or create gudang stock
            $gudangStock = GudangStock::firstOrCreate(
                [
                    'gudang_id' => $this->gudang_id,
                    'barang_id' => $item['barang_id'],
                    // 'toko_id' => $this->toko_id
                ],
                ['jumlah' => 0]
            );
            
            // Create penjualan detail with correct field names and pembelian_detail_id
            $penjualanDetail = PenjualanDetail::create([
                'penjualan_id' => $penjualan->id,
                'pembelian_detail_id' => $pembelianDetailId,
                'barang_id' => $item['barang_id'],
                'satuan_id' => $detailItem['satuan_id'] ?? null,
                'gudang_id' => $this->gudang_id,
                'harga_satuan' => $hargaSatuan,
                'jumlah' => $jumlah,
                'subtotal' => $subtotal,
                'konversi_satuan_terkecil' => $konversiSatuan,
                'diskon' => 0,
                'biaya_lain' => 0,
                'harga_pokok' => $barang->harga_pokok ?? 0,
                'profit' => $hargaSatuan - ($barang->harga_pokok ?? 0)
            ]);
            
            // Create transaksi gudang stock
            TransaksiGudangStock::create([
                'gudang_stock_id' => $gudangStock->id,
                'penjualan_detail_id' => $penjualanDetail->id,
                'pembelian_detail_id' => $pembelianDetailId,
                'jumlah' => $jumlah,
                'konversi_satuan_terkecil' => $konversiSatuan,
                'tipe' => 'keluar',
            ]);
        }
        
        // Update total penjualan
        $penjualan->update(['total_harga' => $totalPenjualan]);
        
        return $penjualan;
    }

    public function render()
    {
        return view('livewire.stock-opname.form');
    }
    
    /**
     * Update the harga_satuan for a specific detail item
     */
    public function updateHargaSatuan($index, $value)
    {
        if (isset($this->details[$index])) {
            // Validate the input
            if (!is_numeric($value) || $value < 0) {
                $this->error('Harga satuan harus berupa angka positif');
                return;
            }
            
            // Update the harga_satuan value
            $this->details[$index]['harga_satuan'] = (float) $value;
            
            // Recalculate subtotal
            $selisih = abs($this->details[$index]['selisih']);
            $this->details[$index]['subtotal'] = $selisih * (float) $value;
            
            // Auto-save to session
            $this->saveToSession();
            
            $this->success('Harga satuan berhasil diperbarui');
        }
    }
}