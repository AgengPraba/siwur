<?php
namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Customer;
use App\Traits\LivewireTenancy;

#[Title('List Customer')]
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
        $this->checkTokoAccess();
        $this->breadcrumbs = [['label' => 'Home', 'link' => route('home')], ['label' => 'Data Customer']];
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

    

    public function delete($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            // Validate ownership
            if (!$this->validateTokoOwnership($customer)) {
                $this->error('Akses Ditolak!', 'Anda tidak memiliki akses untuk menghapus data ini.');
                return;
            }
            
            $customer->delete();
            
            $this->success('Berhasil!', 'Data customer berhasil dihapus.');
            
        } catch (\Exception $e) {
            $this->error('Gagal!', 'Terjadi kesalahan saat menghapus data.');
        }
    }

    public function destroy()
    {

        if ($this->idToDelete != null) {
            $data = Customer::findOrFail($this->idToDelete);
            // Hapus data
            $data->delete();
            // Toast Message
             $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            $count  = Customer::count();
            if ($count == 0) {
                return redirect(route('customer.index'));
            } else {
                return $this->redirectRoute('customer.index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        $data = $this->scopeToCurrentToko(Customer::query())
            ->where('is_opname', false)
            ->when($this->search, function ($query) {
                $query->where('nama_customer', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('no_hp', 'like', '%' . $this->search . '%')
                      ->orWhere('alamat', 'like', '%' . $this->search . '%')
                      ->orWhere('keterangan', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10)
            ->withQueryString();
            
        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;
        
        return view('livewire.customer.index', [
            'customer_data' => $data,
            'currentToko' => $this->getCurrentToko()
        ]);
    }


}

/* End of file komponen index */
/* Location: ./app/Livewire/Customer/Index.php */
/* Created at 2025-07-03 23:23:31 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */