<?php

namespace App\Livewire\ReturPenjualan;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ReturPenjualan;
use App\Models\Customer;
use App\Models\Gudang;
use App\Models\GudangStock;
use App\Models\TransaksiGudangStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mary\Traits\Toast;

#[Title('Data Retur Penjualan')]

class Index extends Component
{
    use WithPagination, Toast;
    
    public $breadcrumbs;
    
    // Filter properties
    public $search = '';
    public $filterCustomer = '';
    public $filterGudang = '';
    public $tanggal_mulai = '';
    public $tanggal_selesai = '';
    public $perPage = 10;
    
    // Modal properties
    public $deleteModal = false;
    public $itemToDelete = null;
    
    // Data for dropdowns
    public $customer_data = [];
    public $gudang_data = [];

    public function mount()
    {
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }

        $this->loadDropdownData();

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Retur Penjualan'],
        ];
    }

    private function loadDropdownData()
    {
        // Load customers
        $this->customer_data = Customer::forCurrentUserToko()
            ->orderBy('nama_customer')
            ->get()
            ->map(function($customer) {
                return ['id' => $customer->id, 'nama_customer' => $customer->nama_customer];
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
        $this->reset(['search', 'filterCustomer', 'filterGudang', 'tanggal_mulai', 'tanggal_selesai']);
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
        $retur = ReturPenjualan::forCurrentUserToko()->find($id);
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
                $this->success('Retur penjualan berhasil dihapus dan stok telah dikembalikan');
                
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
        $query = ReturPenjualan::forCurrentUserToko()
            ->with(['customer', 'gudang', 'dibuatOleh', 'details'])
            ->orderBy('created_at', 'desc');

        // Build the base query for statistics (before pagination)
        $statsQuery = ReturPenjualan::forCurrentUserToko()
            ->with(['details']);

        // Apply filters
        if ($this->search) {
            $filterClosure = function($q) {
                $q->where('nomor_retur_penjualan', 'like', '%' . $this->search . '%')
                  ->orWhere('catatan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($cq) {
                      $cq->where('nama_customer', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('dibuatOleh', function($uq) {
                      $uq->where('name', 'like', '%' . $this->search . '%');
                  });
            };
            $query->where($filterClosure);
            $statsQuery->where($filterClosure);
        }

        if ($this->filterCustomer) {
            $query->byCustomer($this->filterCustomer);
            $statsQuery->byCustomer($this->filterCustomer);
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
        
        // Calculate total items and nilai from details using model accessors
        $totalItems = $allReturs->sum('total_items'); // Using model accessor
        $totalNilai = $allReturs->sum('total_nilai_retur'); // Using model accessor
        
        // Calculate this month's count
        $totalBulanIni = ReturPenjualan::forCurrentUserToko()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $returs = $query->paginate($this->perPage);

        return view('livewire.retur-penjualan.index', [
            'returs' => $returs,
            'totalRetur' => $totalRetur,
            'totalItems' => $totalItems,
            'totalNilai' => $totalNilai,
            'totalBulanIni' => $totalBulanIni
        ]);
    }
}