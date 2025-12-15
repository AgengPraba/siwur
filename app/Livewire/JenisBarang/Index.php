<?php

namespace App\Livewire\JenisBarang;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\JenisBarang;
use Illuminate\Support\Facades\Auth;


#[Title('List Jenis barang')]
class Index extends Component
{
    use WithPagination, Toast;
    public $search = ''; // Properti untuk pencarian
    public $sortField = 'created_at'; // Kolom untuk sorting
    public $sortDirection = 'desc'; // Arah sorting
    public $idToDelete = null; // ID yang akan dihapus
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    public function mount()
    {
        $this->breadcrumbs = [['label' => 'Home', 'link' => route('home')], ['label' => 'Data Jenis barang']];
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
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
            $data = JenisBarang::findOrFail($this->idToDelete);
            // Hapus data
            $data->delete();
            // Toast Message
            $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            $count  = JenisBarang::count();
            if ($count == 0) {
                return redirect(route('jenis-barang.index'));
            } else {
                return $this->redirectRoute('jenis-barang.index', navigate: true);
            }
        }
    }

    public function render()
    {
        $data = JenisBarang::forCurrentUser()
            ->select('jenis_barang.*')
            ->where(function ($query) {
                $query->whereRaw('LOWER(jenis_barang.nama_jenis_barang) LIKE ?', ["%{$this->search}%"])
                    ->orWhereRaw('LOWER(jenis_barang.keterangan) LIKE ?', ["%{$this->search}%"]);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10)
            ->withQueryString();

        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;

        return view('livewire.jenis-barang.index', [
            'jenis_barang_data' => $data
        ]);
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/JenisBarang/Index.php */
/* Created at 2025-07-03 23:23:15 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */