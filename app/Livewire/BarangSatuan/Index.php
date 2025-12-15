<?php
namespace App\Livewire\BarangSatuan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\BarangSatuan;


#[Title('List Barang satuan')]
class Index extends Component
{ 
    use WithPagination,Toast;
    public $search = ''; // Properti untuk pencarian
    public $sortField = 'created_at'; // Kolom untuk sorting
    public $sortDirection = 'desc'; // Arah sorting
    public $idToDelete = null; // ID yang akan dihapus
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    public function mount(){
        $this->breadcrumbs = [['label' => 'Home', 'href' => route('home')], ['label' => 'Data Barang satuan']];
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
            $data = BarangSatuan::findOrFail($this->idToDelete);
            // Hapus data
            $data->delete();
            // Toast Message
             $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            $count  = BarangSatuan::count();
            if ($count == 0) {
                return redirect(route('barang-satuan.index'));
            } else {
                return $this->redirectRoute('barang-satuan.index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        $data = BarangSatuan::join('barang','barang_satuan.barang_id','=','barang.id')->join('satuan','barang_satuan.satuan_id','=','satuan.id')->select('barang_satuan.*','barang.nama_barang','satuan.nama_satuan')->whereRaw('LOWER(barang_satuan.barang_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(barang_satuan.satuan_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(barang_satuan.konversi_satuan_terkecil) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(barang_satuan.is_satuan_terkecil) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(barang.nama_barang) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(satuan.nama_satuan) LIKE ?', ["%{$this->search}%"]) 
               
                ->orderBy($this->sortField, $this->sortDirection) // Sorting
                ->paginate(10)
                ->withQueryString(); // Mempertahankan query string saat paginasi
            $currentPage = $data->currentPage();
            $this->start = ($currentPage - 1) * $this->perPage + 1;
            return view('livewire.barang-satuan.index', [
                'barang_satuan_data' => $data
            ]);
    }


}

/* End of file komponen index */
/* Location: ./app/Livewire/BarangSatuan/Index.php */
/* Created at 2025-07-03 23:23:42 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */