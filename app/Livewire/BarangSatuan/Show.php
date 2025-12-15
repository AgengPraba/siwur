<?php
namespace App\Livewire\BarangSatuan;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\BarangSatuan;

#[Title('Show Barang satuan')]
class Show extends Component
{ 
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
    $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Barang satuan', 'href' => route('barang-satuan.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data
        $this->data = BarangSatuan::join('barang','barang_satuan.barang_id','=','barang.id')->join('satuan','barang_satuan.satuan_id','=','satuan.id')->select('barang_satuan.*','barang.nama_barang','satuan.nama_satuan')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.barang-satuan.show', [
            'barang_satuan_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/BarangSatuan/Show.php */
/* Created at 2025-07-03 23:23:42 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */