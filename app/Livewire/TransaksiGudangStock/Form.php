<?php
namespace App\Livewire\TransaksiGudangStock;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\TransaksiGudangStock;


#[Title('Form Transaksi gudang stock')]
class Form extends Component
{ 


public $breadcrumbs;
public $type = 'create';
public $transaksi_gudang_stock_ID;
public $transaksi_gudang_stock;
  
public $gudang_stock_id;
public $pembelian_detail_id;
public $penjualan_detail_id;
public $jumlah;
public $konversi_satuan_terkecil;
public $tipe;
public $gudang_stock_data;
public $pembelian_detail_data;
public $penjualan_detail_data;
use Toast;


    public function mount($id = null)
    {
     
			
    $columns_gudang_stock = Schema::getColumnListing('gudang_stock');
		$field0_gudang_stock = $columns_gudang_stock[0]; // Nama kolom pertama
		$field1_gudang_stock = $columns_gudang_stock[1]; // Nama kolom kedua
    $this->gudang_stock_data = DB::table('gudang_stock')
         ->select(
                DB::raw($field0_gudang_stock.' as id'),
                DB::raw($field1_gudang_stock.' as name')
            )->get()->toArray();
			
    $columns_pembelian_detail = Schema::getColumnListing('pembelian_detail');
		$field0_pembelian_detail = $columns_pembelian_detail[0]; // Nama kolom pertama
		$field1_pembelian_detail = $columns_pembelian_detail[1]; // Nama kolom kedua
    $this->pembelian_detail_data = DB::table('pembelian_detail')
         ->select(
                DB::raw($field0_pembelian_detail.' as id'),
                DB::raw($field1_pembelian_detail.' as name')
            )->get()->toArray();
			
    $columns_penjualan_detail = Schema::getColumnListing('penjualan_detail');
		$field0_penjualan_detail = $columns_penjualan_detail[0]; // Nama kolom pertama
		$field1_penjualan_detail = $columns_penjualan_detail[1]; // Nama kolom kedua
    $this->penjualan_detail_data = DB::table('penjualan_detail')
         ->select(
                DB::raw($field0_penjualan_detail.' as id'),
                DB::raw($field1_penjualan_detail.' as name')
            )->get()->toArray();
        if($id){
                // Ambil data transaksi_gudang_stock berdasarkan ID
                $data = TransaksiGudangStock::findOrFail($id);
                $this->type = 'edit';
                if ($data) {
                    $this->transaksi_gudang_stock_ID = $data->id;
$this->gudang_stock_id = $data->gudang_stock_id;
$this->pembelian_detail_id = $data->pembelian_detail_id;
$this->penjualan_detail_id = $data->penjualan_detail_id;
$this->jumlah = $data->jumlah;
$this->konversi_satuan_terkecil = $data->konversi_satuan_terkecil;
$this->tipe = $data->tipe;

                    
            }
        }
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Transaksi gudang stock', 'href' => route('transaksi-gudang-stock.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];

    }

     public function update(){
     $this->validate([
				'gudang_stock_id' => 'required|max:200',
				'pembelian_detail_id' => 'required|max:200',
				'penjualan_detail_id' => 'required|max:200',
				'jumlah' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'tipe' => 'required|max:200',
			]);

        // Update data 
        TransaksiGudangStock::findOrFail($this->transaksi_gudang_stock_ID)->update([
				'gudang_stock_id' => $this->gudang_stock_id,
				'pembelian_detail_id' => $this->pembelian_detail_id,
				'penjualan_detail_id' => $this->penjualan_detail_id,
				'jumlah' => $this->jumlah,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'tipe' => $this->tipe,
			]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('transaksi-gudang-stock.index', navigate: true);
    }

    public function store()
    {
    $this->validate([
				'gudang_stock_id' => 'required|max:200',
				'pembelian_detail_id' => 'required|max:200',
				'penjualan_detail_id' => 'required|max:200',
				'jumlah' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'tipe' => 'required|max:200',
			]);
       

        TransaksiGudangStock::create([
				'gudang_stock_id' => $this->gudang_stock_id,
				'pembelian_detail_id' => $this->pembelian_detail_id,
				'penjualan_detail_id' => $this->penjualan_detail_id,
				'jumlah' => $this->jumlah,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'tipe' => $this->tipe,
			]);
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('transaksi-gudang-stock.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.transaksi-gudang-stock.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/TransaksiGudangStock/Form.php */
/* Created at 2025-07-03 23:24:00 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */