<?php
namespace App\Livewire\Gudang;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Gudang;
use App\Traits\LivewireTenancy;


#[Title('List Gudang')]
class Index extends Component
{ 
    use WithPagination, Toast, LivewireTenancy;
    public $search = ''; // Properti untuk pencarian
    public $sortField = 'created_at'; // Kolom untuk sorting
    public $sortDirection = 'desc'; // Arah sorting
    public $idToDelete = null; // ID yang akan dihapus
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    public function mount(){
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }
        
        $this->breadcrumbs = [['label' => 'Home', 'link' => route('home')], ['label' => 'Data Gudang']];
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset halaman saat pencarian diubah
    }
    
     public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'; // Toggle arah sorting
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc'; // Set default sorting ke ascending
        }
    }

    

    public function destroy()
    {
        if ($this->idToDelete != null) {
            $data = Gudang::findOrFail($this->idToDelete);
            
            // Validate ownership
            if (!$this->validateTokoOwnership($data)) {
                $this->idToDelete = null;
                return;
            }
            
            // Hapus data
            $data->delete();
            // Toast Message
            $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            $count = Gudang::count();
            if ($count == 0) {
                return redirect(route('gudang.index'));
            } else {
                return $this->redirectRoute('gudang.index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        // Model sudah menggunakan global scope untuk toko_id
        $data = Gudang::select('gudang.*')
            ->where(function($query) {
                $query->whereRaw('LOWER(gudang.nama_gudang) LIKE ?', ["%%{$this->search}%%"])
                      ->orWhereRaw('LOWER(gudang.keterangan) LIKE ?', ["%%{$this->search}%%"]);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10)
            ->withQueryString();
            
        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;
        
        return view('livewire.gudang.index', [
            'gudang_data' => $data
        ]);
    }


}

/* End of file komponen index */
/* Location: ./app/Livewire/Gudang/Index.php */
/* Created at 2025-07-03 23:23:21 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */