<?php

namespace App\Livewire\StockOpname;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;
use Carbon\Carbon;
use Livewire\Attributes\Title;


#[Title('Data Stock Opname')]
class Index extends Component
{
    use WithPagination, Toast;

    public $breadcrumbs;
    public $search = '';
    public $perPage = 10;
    public $filterGudang = '';
    public $filterSelisih = '';
    public $tanggal_mulai;
    public $tanggal_selesai;
    
    // Modal properties
    public $deleteModal = false;
    public $itemToDelete = null;

    protected $queryString = ['search', 'filterGudang', 'filterSelisih', 'tanggal_mulai', 'tanggal_selesai'];

    public function mount()
    {
        // Set default date range to current month
        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Stock Opname'],
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterGudang()
    {
        $this->resetPage();
    }

    public function updatingFilterSelisih()
    {
        $this->resetPage();
    }

    public function updatingTanggalMulai()
    {
        $this->resetPage();
        $this->validateDateRange();
    }

    public function updatingTanggalSelesai()
    {
        $this->resetPage();
        $this->validateDateRange();
    }
    
    private function validateDateRange()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $startDate = Carbon::parse($this->tanggal_mulai);
            $endDate = Carbon::parse($this->tanggal_selesai);
            
            if ($startDate->gt($endDate)) {
                $this->addError('tanggal_selesai', 'Tanggal selesai tidak boleh lebih kecil dari tanggal mulai');
            } else {
                $this->resetErrorBag(['tanggal_selesai']);
            }
        }
    }

    public function resetFilter()
    {
        $this->reset(['search', 'filterGudang', 'filterSelisih']);
        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
        $this->success('Filter berhasil direset', position: 'toast-top');
    }

    public function printOpname($id)
    {
        $this->info('Membuka jendela cetak...', position: 'toast-top');
        $this->dispatch('openPrintWindow', route('stock-opname.print', $id));
    }

    public function openDeleteModal($id)
    {
        $opname = StockOpname::find($id);
        
        if (!$opname) {
            $this->error('Data stock opname tidak ditemukan', position: 'toast-top');
            return;
        }

        $this->itemToDelete = $opname;
        $this->deleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->deleteModal = false;
        $this->itemToDelete = null;
    }

    public function confirmDelete()
    {
        if (!$this->itemToDelete) {
            $this->error('Data tidak ditemukan', position: 'toast-top');
            return;
        }

        try {
            DB::beginTransaction();
            
            // Check if user has access to this toko
            $user = Auth::user();
            if (!$user || !$user->akses || $user->akses->toko_id !== $this->itemToDelete->toko_id) {
                throw new \Exception('Anda tidak memiliki akses untuk menghapus data ini');
            }
            
            // Load details untuk revert stock adjustment
            $this->itemToDelete->load('details');
            
            // Get the opname number for pembelian and penjualan lookup
            $nomorOpname = $this->itemToDelete->nomor_opname;
            
            // Delete related pembelian transactions
            $pembelianTransactions = DB::table('pembelian')->where('keterangan', 'like', '%' . $nomorOpname . '%')
                ->orWhere('nomor_pembelian', 'like', '%' . $nomorOpname . '%')
                ->get();
                
            foreach ($pembelianTransactions as $pembelian) {
                // Get all pembelian detail ids to find related transactions
                $pembelianDetailIds = DB::table('pembelian_detail')
                    ->where('pembelian_id', $pembelian->id)
                    ->pluck('id')
                    ->toArray();
                    
                // Delete transaksi gudang stock associated with this pembelian
                if (!empty($pembelianDetailIds)) {
                    DB::table('transaksi_gudang_stock')
                        ->whereIn('pembelian_detail_id', $pembelianDetailIds)
                        ->delete();
                }
                    
                // Delete pembelian details
                DB::table('pembelian_detail')->where('pembelian_id', $pembelian->id)->delete();
                
                // Delete the pembelian header
                DB::table('pembelian')->where('id', $pembelian->id)->delete();
            }
            
            // Delete related penjualan transactions
            $penjualanTransactions = DB::table('penjualan')->where('keterangan', 'like', '%' . $nomorOpname . '%')
                ->orWhere('nomor_penjualan', 'like', '%' . $nomorOpname . '%')
                ->get();
                
            foreach ($penjualanTransactions as $penjualan) {
                // Get all penjualan detail ids to find related transactions
                $penjualanDetailIds = DB::table('penjualan_detail')
                    ->where('penjualan_id', $penjualan->id)
                    ->pluck('id')
                    ->toArray();
                    
                // Delete transaksi gudang stock associated with this penjualan
                if (!empty($penjualanDetailIds)) {
                    DB::table('transaksi_gudang_stock')
                        ->whereIn('penjualan_detail_id', $penjualanDetailIds)
                        ->delete();
                }
                    
                // Delete penjualan details
                DB::table('penjualan_detail')->where('penjualan_id', $penjualan->id)->delete();
                
                // Delete the penjualan header
                DB::table('penjualan')->where('id', $penjualan->id)->delete();
            }
            
            // Delete the opname (ini akan trigger revertStockAdjustment di boot method)
            $this->itemToDelete->delete();
            
            DB::commit();
            
            $this->success("Stock opname {$nomorOpname} dan transaksi terkait berhasil dihapus", position: 'toast-top');
            $this->closeDeleteModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal menghapus stock opname: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function render()
    {
        $query = StockOpname::with(['user', 'gudang', 'details'])
            ->withCount('details')
            ->where('toko_id', Auth::user()->akses->toko_id);

        // Apply date filter with validation
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $startDate = Carbon::parse($this->tanggal_mulai)->startOfDay();
            $endDate = Carbon::parse($this->tanggal_selesai)->endOfDay();
            
            // Ensure start date is not after end date
            if ($startDate->lte($endDate)) {
                $query->whereBetween('tanggal_opname', [$startDate, $endDate]);
            }
        } elseif ($this->tanggal_mulai) {
            $query->whereDate('tanggal_opname', '>=', Carbon::parse($this->tanggal_mulai));
        } elseif ($this->tanggal_selesai) {
            $query->whereDate('tanggal_opname', '<=', Carbon::parse($this->tanggal_selesai));
        }

        // Apply search filter (nama operator/user/gudang)
        if (!empty($this->search)) {
            $query->where(function($q){
                $q->where('nomor_opname', 'like', '%'.$this->search.'%')
                  ->orWhereHas('gudang', function($g){
                      $g->where('nama_gudang', 'like', '%'.$this->search.'%');
                  })
                  ->orWhereHas('user', function($u){
                      $u->where('name', 'like', '%'.$this->search.'%');
                  });
            });
        }

        // Apply gudang filter
        if (!empty($this->filterGudang)) {
            $query->where('gudang_id', $this->filterGudang);
        }

        // Apply selisih filter
        if (!empty($this->filterSelisih)) {
            $query->whereHas('details', function($q) {
                switch($this->filterSelisih) {
                    case 'plus':
                        $q->where('selisih', '>', 0);
                        break;
                    case 'minus':
                        $q->where('selisih', '<', 0);
                        break;
                    case 'netral':
                        $q->where('selisih', '=', 0);
                        break;
                }
            });
        }

        $opnames = $query->orderBy('tanggal_opname', 'desc')->paginate($this->perPage);

        // Aggregates for summary cards - apply same filters
        $summaryQuery = StockOpname::where('toko_id', Auth::user()->akses->toko_id);
        
        // Apply same date filter for summaries
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $startDate = Carbon::parse($this->tanggal_mulai)->startOfDay();
            $endDate = Carbon::parse($this->tanggal_selesai)->endOfDay();
            
            if ($startDate->lte($endDate)) {
                $summaryQuery->whereBetween('tanggal_opname', [$startDate, $endDate]);
            }
        } elseif ($this->tanggal_mulai) {
            $summaryQuery->whereDate('tanggal_opname', '>=', Carbon::parse($this->tanggal_mulai));
        } elseif ($this->tanggal_selesai) {
            $summaryQuery->whereDate('tanggal_opname', '<=', Carbon::parse($this->tanggal_selesai));
        }
        
        // Apply gudang filter for summaries
        if (!empty($this->filterGudang)) {
            $summaryQuery->where('gudang_id', $this->filterGudang);
        }
        
        $totalOpname = $summaryQuery->count();
        
        $totalItems = DB::table('stock_opname_detail')
            ->join('stock_opname', 'stock_opname_detail.stock_opname_id', '=', 'stock_opname.id')
            ->where('stock_opname.toko_id', Auth::user()->akses->toko_id)
            ->when($this->tanggal_mulai && $this->tanggal_selesai, function($q) {
                $startDate = Carbon::parse($this->tanggal_mulai)->startOfDay();
                $endDate = Carbon::parse($this->tanggal_selesai)->endOfDay();
                if ($startDate->lte($endDate)) {
                    $q->whereBetween('stock_opname.tanggal_opname', [$startDate, $endDate]);
                }
            })
            ->when($this->filterGudang, function($q) {
                $q->where('stock_opname.gudang_id', $this->filterGudang);
            })
            ->count();
            
        $totalStokFisik = DB::table('stock_opname_detail')
            ->join('stock_opname', 'stock_opname_detail.stock_opname_id', '=', 'stock_opname.id')
            ->where('stock_opname.toko_id', Auth::user()->akses->toko_id)
            ->when($this->tanggal_mulai && $this->tanggal_selesai, function($q) {
                $startDate = Carbon::parse($this->tanggal_mulai)->startOfDay();
                $endDate = Carbon::parse($this->tanggal_selesai)->endOfDay();
                if ($startDate->lte($endDate)) {
                    $q->whereBetween('stock_opname.tanggal_opname', [$startDate, $endDate]);
                }
            })
            ->when($this->filterGudang, function($q) {
                $q->where('stock_opname.gudang_id', $this->filterGudang);
            })
            ->selectRaw('COALESCE(SUM(stok_fisik),0) as total')
            ->value('total');
            
        $totalSelisih = DB::table('stock_opname_detail')
            ->join('stock_opname', 'stock_opname_detail.stock_opname_id', '=', 'stock_opname.id')
            ->where('stock_opname.toko_id', Auth::user()->akses->toko_id)
            ->when($this->tanggal_mulai && $this->tanggal_selesai, function($q) {
                $startDate = Carbon::parse($this->tanggal_mulai)->startOfDay();
                $endDate = Carbon::parse($this->tanggal_selesai)->endOfDay();
                if ($startDate->lte($endDate)) {
                    $q->whereBetween('stock_opname.tanggal_opname', [$startDate, $endDate]);
                }
            })
            ->when($this->filterGudang, function($q) {
                $q->where('stock_opname.gudang_id', $this->filterGudang);
            })
            ->selectRaw('COALESCE(SUM(selisih),0) as total')
            ->value('total');

        // Get gudang options for filter
        $gudangOptions = \App\Models\Gudang::where('toko_id', Auth::user()->akses->toko_id)
            ->select('id', 'nama_gudang')
            ->get();

        return view('livewire.stock-opname.index', compact('opnames','totalOpname','totalItems','totalStokFisik','totalSelisih','gudangOptions'));
    }
}