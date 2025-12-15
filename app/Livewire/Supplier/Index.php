<?php
namespace App\Livewire\Supplier;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Supplier;
use App\Traits\LivewireTenancy;

#[Title('List Supplier')]
class Index extends Component
{ 
    use WithPagination, Toast, LivewireTenancy;
    
    public $search = ''; 
    public $sortField = 'created_at'; 
    public $sortDirection = 'desc'; 
    public $idToDelete = null; 
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;
    public $totalSuppliers = 0;

    public function mount()
    {
        $this->checkTokoAccess();
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')], 
            ['label' => 'Data Supplier']
        ];
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        $this->totalSuppliers = $this->scopeToCurrentToko(Supplier::query())
                                    ->where('is_opname', false)
                                    ->count();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete()
    {
        if ($this->idToDelete != null) {
            try {
                $supplier = Supplier::findOrFail($this->idToDelete);
                
                // Validate ownership
                if (!$this->validateTokoOwnership($supplier)) {
                    $this->error('Akses Ditolak!', 'Anda tidak memiliki akses untuk menghapus data ini.');
                    $this->idToDelete = null;
                    return;
                }
                
                // Check if supplier has related purchases
                if ($supplier->pembelian()->exists()) {
                    $this->error('Peringatan!', 'Supplier tidak dapat dihapus karena memiliki data pembelian terkait.');
                    $this->idToDelete = null;
                    return;
                }
                
                $namaSupplier = $supplier->nama_supplier;
                $supplier->delete();
                
                $this->success('Berhasil!', "Data supplier '{$namaSupplier}' berhasil dihapus.");
                
                // Reset ID yang akan dihapus
                $this->idToDelete = null;
                
                // Reload statistics
                $this->loadStatistics();
                
            } catch (\Exception $e) {
                $this->error('Gagal!', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
                $this->idToDelete = null;
            }
        }
    }
        
    public function render()
    {
        $query = $this->scopeToCurrentToko(Supplier::query())
                     ->where('is_opname', false);
        
        // Enhanced search functionality
        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nama_supplier', 'like', "%{$searchTerm}%")
                  ->orWhere('alamat', 'like', "%{$searchTerm}%")
                  ->orWhere('no_hp', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('keterangan', 'like', "%{$searchTerm}%");
            });
        }
        
        $data = $query->orderBy($this->sortField, $this->sortDirection)
                     ->paginate($this->perPage)
                     ->withQueryString();
                     
        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;
        
        return view('livewire.supplier.index', [
            'supplier_data' => $data,
            'currentToko' => $this->getCurrentToko()
        ]);
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/Supplier/Index.php */
/* Created at 2025-07-03 23:22:37 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */