<?php
namespace App\Livewire\PenjualanDetail;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\PenjualanDetail;

#[Title('Show Penjualan detail')]
class Show extends Component
{ 
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
    $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Penjualan detail', 'href' => route('penjualan-detail.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data
        $this->data = PenjualanDetail::join('barang','penjualan_detail.barang_id','=','barang.id')->join('pembelian_detail','penjualan_detail.pembelian_detail_id','=','pembelian_detail.id')->join('penjualan','penjualan_detail.penjualan_id','=','penjualan.id')->join('satuan','penjualan_detail.satuan_id','=','satuan.id')->select('penjualan_detail.*','barang.nama_barang','pembelian_detail.pembelian_id','penjualan.nomor_penjualan','satuan.nama_satuan')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.penjualan-detail.show', [
            'penjualan_detail_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/PenjualanDetail/Show.php */
/* Created at 2025-07-03 23:22:56 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */