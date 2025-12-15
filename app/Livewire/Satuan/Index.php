<?php
namespace App\Livewire\Satuan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Satuan;
use Illuminate\Support\Facades\Auth;


#[Title('List Satuan')]
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
    public $tokoId; // Toko ID dari user yang login

    public function mount(){
        // Ambil toko_id dari user yang login melalui relasi akses
        $this->tokoId = Auth::user()->akses->toko_id ?? null;
        
        if (!$this->tokoId) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $this->breadcrumbs = [['label' => 'Home', 'link' => route('home')], ['label' => 'Data Satuan']];
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
            // Pastikan data yang akan dihapus milik toko yang sama
            $data = Satuan::where('id', $this->idToDelete)
                         ->where('toko_id', $this->tokoId)
                         ->firstOrFail();
            
            // Hapus data
            $data->delete();
            // Toast Message
            $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            
            $count = Satuan::where('toko_id', $this->tokoId)->count();
            if ($count == 0) {
                return redirect(route('satuan.index'));
            } else {
                return $this->redirectRoute('satuan.index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        $data = Satuan::select('satuan.*')
                ->where('toko_id', $this->tokoId)
                ->where(function($query) {
                    $query->whereRaw('LOWER(satuan.nama_satuan) LIKE ?', ["%{$this->search}%"])
                          ->orWhereRaw('LOWER(satuan.keterangan) LIKE ?', ["%{$this->search}%"]);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
                ->withQueryString();
                
        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;
        
        return view('livewire.satuan.index', [
            'satuan_data' => $data
        ]);
    }


}

/* End of file komponen index */
/* Location: ./app/Livewire/Satuan/Index.php */
/* Created at 2025-07-03 23:21:53 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */