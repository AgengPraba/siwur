<?php
namespace App\Livewire\PenjualanDetail;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\PenjualanDetail;


#[Title('List Penjualan detail')]
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
        $this->breadcrumbs = [['label' => 'Home', 'href' => route('home')], ['label' => 'Data Penjualan detail']];
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
            $data = PenjualanDetail::findOrFail($this->idToDelete);
            // Hapus data
            $data->delete();
            // Toast Message
             $this->success('Notifikasi', 'Berhasil Hapus Data.');
            // Reset ID yang akan dihapus
            $this->idToDelete = null;
            $count  = PenjualanDetail::count();
            if ($count == 0) {
                return redirect(route('penjualan-detail.index'));
            } else {
                return $this->redirectRoute('penjualan-detail.index', navigate: true);
            }
        }
    }
        
    public function render()
    {
        $data = PenjualanDetail::join('barang','penjualan_detail.barang_id','=','barang.id')->join('pembelian_detail','penjualan_detail.pembelian_detail_id','=','pembelian_detail.id')->join('penjualan','penjualan_detail.penjualan_id','=','penjualan.id')->join('satuan','penjualan_detail.satuan_id','=','satuan.id')->select('penjualan_detail.*','barang.nama_barang','pembelian_detail.pembelian_id','penjualan.nomor_penjualan','satuan.nama_satuan')->whereRaw('LOWER(penjualan_detail.penjualan_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.pembelian_detail_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.barang_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.satuan_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.harga_satuan) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.jumlah) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.konversi_satuan_terkecil) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.subtotal) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan_detail.profit) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(barang.nama_barang) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(pembelian_detail.pembelian_id) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(penjualan.nomor_penjualan) LIKE ?', ["%{$this->search}%"])->orWhereRaw('LOWER(satuan.nama_satuan) LIKE ?', ["%{$this->search}%"]) 
               
                ->orderBy($this->sortField, $this->sortDirection) // Sorting
                ->paginate(10)
                ->withQueryString(); // Mempertahankan query string saat paginasi
            $currentPage = $data->currentPage();
            $this->start = ($currentPage - 1) * $this->perPage + 1;
            return view('livewire.penjualan-detail.index', [
                'penjualan_detail_data' => $data
            ]);
    }


}

/* End of file komponen index */
/* Location: ./app/Livewire/PenjualanDetail/Index.php */
/* Created at 2025-07-03 23:22:56 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */