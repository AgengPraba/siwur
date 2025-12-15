<?php

namespace App\Livewire\ReturPembelian;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReturPembelian;
use App\Models\Supplier;
use App\Models\Gudang;
use App\Models\GudangStock;
use App\Models\TransaksiGudangStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;
use Carbon\Carbon;

#[Title('Data Retur Pembelian')]

class Index extends Component
{
    use WithPagination, Toast;
    
    public $breadcrumbs;
    
    // Filter properties
    public $search = '';
    public $filterSupplier = '';
    public $filterGudang = '';
    public $tanggal_mulai = '';
    public $tanggal_selesai = '';
    public $perPage = 10;
    
    // Modal properties
    public $deleteModal = false;
    public $itemToDelete = null;
    
    // Data for dropdowns
    public $supplier_data = [];
    public $gudang_data = [];

    public function mount()
    {

        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        $this->loadDropdownData();

        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Retur Pembelian'],
        ];
    }
    

    private function loadDropdownData()
    {
        // Load suppliers
        $this->supplier_data = Supplier::forCurrentUserToko()
            ->orderBy('nama_supplier')
            ->get()
            ->map(function($supplier) {
                return ['id' => $supplier->id, 'nama_supplier' => $supplier->nama_supplier];
            })->toArray();

        // Load gudang
        $this->gudang_data = Gudang::forCurrentUserToko()
            ->orderBy('nama_gudang')
            ->get()
            ->map(function($gudang) {
                return ['id' => $gudang->id, 'nama' => $gudang->nama_gudang];
            })->toArray();
    }

    public function resetFilter()
    {
        $this->reset(['search', 'filterSupplier', 'filterGudang', 'tanggal_mulai', 'tanggal_selesai']);
        $this->resetPage();
        $this->success('Filter berhasil direset');
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function closeDeleteModal()
    {
        $this->deleteModal = false;
        $this->itemToDelete = null;
    }

    public function openDeleteModal($id)
    {
        $retur = ReturPembelian::forCurrentUserToko()->find($id);
        if (!$retur) {
            $this->error('Data retur tidak ditemukan');
            return;
        }

        $this->itemToDelete = $retur;
        $this->deleteModal = true;
    }

    public function confirmDelete()
    {
        if ($this->itemToDelete) {
            try {
                DB::beginTransaction();
                
                // Stock reversal is handled automatically by model events
                $this->itemToDelete->delete();
                
                DB::commit();
                $this->success('Retur pembelian berhasil dihapus dan stok telah dikembalikan');
                
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Gagal menghapus retur: ' . $e->getMessage());
            }
            
            $this->itemToDelete = null;
        }
        $this->closeDeleteModal();
    }

    public function render()
    {
        $query = ReturPembelian::forCurrentUserToko()
            ->with(['supplier', 'gudang', 'dibuatOleh', 'details'])
            ->orderBy('created_at', 'desc');

        // Build the base query for statistics (before pagination)
        $statsQuery = ReturPembelian::forCurrentUserToko()
            ->with(['details']);

        // Apply filters
        if ($this->search) {
            $filterClosure = function($q) {
                $q->where('nomor_retur_pembelian', 'like', '%' . $this->search . '%')
                  ->orWhere('catatan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('supplier', function($sq) {
                      $sq->where('nama_supplier', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('dibuatOleh', function($uq) {
                      $uq->where('name', 'like', '%' . $this->search . '%');
                  });
            };
            $query->where($filterClosure);
            $statsQuery->where($filterClosure);
        }

        if ($this->filterSupplier) {
            $query->bySupplier($this->filterSupplier);
            $statsQuery->bySupplier($this->filterSupplier);
        }

        if ($this->filterGudang) {
            $query->where('gudang_id', $this->filterGudang);
            $statsQuery->where('gudang_id', $this->filterGudang);
        }

        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $query->byDateRange($this->tanggal_mulai, $this->tanggal_selesai);
            $statsQuery->byDateRange($this->tanggal_mulai, $this->tanggal_selesai);
        }

        // Calculate statistics
        $totalRetur = $statsQuery->count();
        $allReturs = $statsQuery->get();
        $totalItems = $allReturs->sum('total_items');
        $totalNilai = $allReturs->sum('total_nilai_retur');
        
        // Calculate this month's count
        $totalBulanIni = ReturPembelian::forCurrentUserToko()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $returs = $query->paginate($this->perPage);

        return view('livewire.retur-pembelian.index', [
            'returs' => $returs,
            'totalRetur' => $totalRetur,
            'totalItems' => $totalItems,
            'totalNilai' => $totalNilai,
            'totalBulanIni' => $totalBulanIni
        ]);
    }
}