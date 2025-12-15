<?php

namespace App\Livewire\Barang;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Barang;
use Illuminate\Support\Facades\Auth;


#[Title('Data Barang')]
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
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        $akses = $user->akses ?? null;
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        $namaTokoLabel = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Barang']
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset halaman saat pencarian diubah
    }

    public function updatingPerPage()
    {
        $this->resetPage(); // Reset halaman saat perPage diubah
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'; // Toggle arah sorting
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc'; // Set default sorting ke ascending
        }
        $this->resetPage(); // Reset ke halaman pertama setelah sorting
    }

    public function destroy()
    {
        if ($this->idToDelete != null) {
            try {
                $data = Barang::findOrFail($this->idToDelete);
                $namaBarang = $data->nama_barang;

                $data->barangSatuan()->delete();
                $data->aturanHarga()->delete();
                
                // Check if barang can be deleted
                $deleteCheck = $data->canBeDeleted();
                
                if (!$deleteCheck['can_delete']) {
                    // Show error message if barang cannot be deleted
                    $this->error('Tidak Dapat Dihapus!', $deleteCheck['reason']);
                    $this->idToDelete = null;
                    return;
                }
                
                // Hapus data
                $data->delete();

                // Toast Message
                $this->success('Berhasil!', "Data barang '{$namaBarang}' berhasil dihapus.");

                // Reset ID yang akan dihapus
                $this->idToDelete = null;

                // Check if we need to redirect to previous page
                $count = Barang::count();
                if ($count == 0) {
                    return redirect(route('barang.index'));
                } else {
                    return $this->redirectRoute('barang.index', navigate: true);
                }
            } catch (\Exception $e) {
                $this->error('Error!', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
                $this->idToDelete = null;
            }
        }
    }

    public function render()
    {
        $query = Barang::join('jenis_barang', 'barang.jenis_barang_id', '=', 'jenis_barang.id')
            ->join('satuan', 'barang.satuan_terkecil_id', '=', 'satuan.id')
            ->select('barang.*', 'jenis_barang.nama_jenis_barang', 'satuan.nama_satuan');

        // Apply search filters
        if (!empty($this->search)) {
            $searchTerm = strtolower($this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(barang.kode_barang) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(barang.nama_barang) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(barang.keterangan) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(jenis_barang.nama_jenis_barang) LIKE ?', ["%{$searchTerm}%"])
                    ->orWhereRaw('LOWER(satuan.nama_satuan) LIKE ?', ["%{$searchTerm}%"]);
            });
        }

        // Apply sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        // Get paginated data
        $data = $query->paginate($this->perPage)->withQueryString();

        // Calculate start number for pagination
        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;

        return view('livewire.barang.index', [
            'barang_data' => $data
        ]);
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/Barang/Index.php */
/* Created at 2025-07-03 23:23:37 */
/* Updated with modern design enhancements */