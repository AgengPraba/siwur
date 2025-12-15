<?php
namespace App\Livewire\PembelianDetail;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\PembelianDetail;

#[Title('Show Pembelian detail')]
class Show extends Component
{ 
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
    $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Pembelian detail', 'href' => route('pembelian-detail.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data
        $this->data = PembelianDetail::join('barang','pembelian_detail.barang_id','=','barang.id')->join('pembelian','pembelian_detail.pembelian_id','=','pembelian.id')->join('satuan','pembelian_detail.satuan_id','=','satuan.id')->select('pembelian_detail.*','barang.nama_barang','pembelian.nomor_pembelian','satuan.nama_satuan')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.pembelian-detail.show', [
            'pembelian_detail_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/PembelianDetail/Show.php */
/* Created at 2025-07-03 23:23:07 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */