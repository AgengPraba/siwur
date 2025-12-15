<?php
namespace App\Livewire\TransaksiGudangStock;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\TransaksiGudangStock;

#[Title('Show Transaksi gudang stock')]
class Show extends Component
{ 
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
    $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Transaksi gudang stock', 'href' => route('transaksi-gudang-stock.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data
        $this->data = TransaksiGudangStock::join('gudang_stock','transaksi_gudang_stock.gudang_stock_id','=','gudang_stock.id')->join('pembelian_detail','transaksi_gudang_stock.pembelian_detail_id','=','pembelian_detail.id')->join('penjualan_detail','transaksi_gudang_stock.penjualan_detail_id','=','penjualan_detail.id')->select('transaksi_gudang_stock.*','gudang_stock.gudang_id','pembelian_detail.pembelian_id','penjualan_detail.penjualan_id')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.transaksi-gudang-stock.show', [
            'transaksi_gudang_stock_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/TransaksiGudangStock/Show.php */
/* Created at 2025-07-03 23:24:00 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */