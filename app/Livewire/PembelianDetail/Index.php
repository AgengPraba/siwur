<?php
namespace App\Livewire\PembelianDetail;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\PembelianDetail;


#[Title('List Pembelian detail')]
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
        $this->breadcrumbs = [['label' => 'Home', 'href' => route('home')], ['label' => 'Data Pembelian detail']];
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
            $data = PembelianDetail::findOrFail($this->idToDelete);
            // Hapus data
            $data->delete();
            // Toast Message
             $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            $count  = PembelianDetail::count();
            if ($count == 0) {
                return redirect(route('pembelian-detail.index'));
            } else {
                return $this->redirectRoute('pembelian-detail.index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        $data = PembelianDetail::join('barang','pembelian_detail.barang_id','=','barang.id')->join('pembelian','pembelian_detail.pembelian_id','=','pembelian.id')->join('satuan','pembelian_detail.satuan_id','=','satuan.id')->select('pembelian_detail.*','barang.nama_barang','pembelian.nomor_pembelian','satuan.nama_satuan')->whereRaw('LOWER(pembelian_detail.pembelian_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.barang_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.satuan_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.harga_satuan) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.jumlah) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.konversi_satuan_terkecil) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.subtotal) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(barang.nama_barang) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian.nomor_pembelian) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(satuan.nama_satuan) LIKE ?', ["%{$this->search}%"]) 
               
                ->orderBy($this->sortField, $this->sortDirection) // Sorting
                ->paginate(10)
                ->withQueryString(); // Mempertahankan query string saat paginasi
            $currentPage = $data->currentPage();
            $this->start = ($currentPage - 1) * $this->perPage + 1;
            return view('livewire.pembelian-detail.index', [
                'pembelian_detail_data' => $data
            ]);
    }


}

/* End of file komponen index */
/* Location: ./app/Livewire/PembelianDetail/Index.php */
/* Created at 2025-07-03 23:23:07 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */