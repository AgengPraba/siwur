<?php
namespace App\Livewire\PenjualanDetail;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\PenjualanDetail;


#[Title('Form Penjualan detail')]
class Form extends Component
{ 


public $breadcrumbs;
public $type = 'create';
public $penjualan_detail_ID;
public $penjualan_detail;
  
public $penjualan_id;
public $pembelian_detail_id;
public $barang_id;
public $satuan_id;
public $harga_satuan;
public $jumlah;
public $konversi_satuan_terkecil;
public $subtotal;
public $profit;
public $barang_data;
public $pembelian_detail_data;
public $penjualan_data;
public $satuan_data;
use Toast;


    public function mount($id = null)
    {
     
			
    $columns_barang = Schema::getColumnListing('barang');
		$field0_barang = $columns_barang[0]; // Nama kolom pertama
		$field1_barang = $columns_barang[1]; // Nama kolom kedua
    $this->barang_data = DB::table('barang')
         ->select(
                DB::raw($field0_barang.' as id'),
                DB::raw($field1_barang.' as name')
            )->get()->toArray();
			
    $columns_pembelian_detail = Schema::getColumnListing('pembelian_detail');
		$field0_pembelian_detail = $columns_pembelian_detail[0]; // Nama kolom pertama
		$field1_pembelian_detail = $columns_pembelian_detail[1]; // Nama kolom kedua
    $this->pembelian_detail_data = DB::table('pembelian_detail')
         ->select(
                DB::raw($field0_pembelian_detail.' as id'),
                DB::raw($field1_pembelian_detail.' as name')
            )->get()->toArray();
			
    $columns_penjualan = Schema::getColumnListing('penjualan');
		$field0_penjualan = $columns_penjualan[0]; // Nama kolom pertama
		$field1_penjualan = $columns_penjualan[1]; // Nama kolom kedua
    $this->penjualan_data = DB::table('penjualan')
         ->select(
                DB::raw($field0_penjualan.' as id'),
                DB::raw($field1_penjualan.' as name')
            )->get()->toArray();
			
    $columns_satuan = Schema::getColumnListing('satuan');
		$field0_satuan = $columns_satuan[0]; // Nama kolom pertama
		$field1_satuan = $columns_satuan[1]; // Nama kolom kedua
    $this->satuan_data = DB::table('satuan')
         ->select(
                DB::raw($field0_satuan.' as id'),
                DB::raw($field1_satuan.' as name')
            )->get()->toArray();
        if($id){
                // Ambil data penjualan_detail berdasarkan ID
                $data = PenjualanDetail::findOrFail($id);
                $this->type = 'edit';
                if ($data) {
                    $this->penjualan_detail_ID = $data->id;
$this->penjualan_id = $data->penjualan_id;
$this->pembelian_detail_id = $data->pembelian_detail_id;
$this->barang_id = $data->barang_id;
$this->satuan_id = $data->satuan_id;
$this->harga_satuan = $data->harga_satuan;
$this->jumlah = $data->jumlah;
$this->konversi_satuan_terkecil = $data->konversi_satuan_terkecil;
$this->subtotal = $data->subtotal;
$this->profit = $data->profit;

                    
            }
        }
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Penjualan detail', 'href' => route('penjualan-detail.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];

    }

     public function update(){
     $this->validate([
				'penjualan_id' => 'required|max:200',
				'pembelian_detail_id' => 'required|max:200',
				'barang_id' => 'required|max:200',
				'satuan_id' => 'required|max:200',
				'harga_satuan' => 'required|max:200',
				'jumlah' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'subtotal' => 'required|max:200',
				'profit' => 'required|max:200',
			]);

        // Update data 
        PenjualanDetail::findOrFail($this->penjualan_detail_ID)->update([
				'penjualan_id' => $this->penjualan_id,
				'pembelian_detail_id' => $this->pembelian_detail_id,
				'barang_id' => $this->barang_id,
				'satuan_id' => $this->satuan_id,
				'harga_satuan' => $this->harga_satuan,
				'jumlah' => $this->jumlah,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'subtotal' => $this->subtotal,
				'profit' => $this->profit,
			]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('penjualan-detail.index', navigate: true);
    }

    public function store()
    {
    $this->validate([
				'penjualan_id' => 'required|max:200',
				'pembelian_detail_id' => 'required|max:200',
				'barang_id' => 'required|max:200',
				'satuan_id' => 'required|max:200',
				'harga_satuan' => 'required|max:200',
				'jumlah' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'subtotal' => 'required|max:200',
				'profit' => 'required|max:200',
			]);
       

        PenjualanDetail::create([
				'penjualan_id' => $this->penjualan_id,
				'pembelian_detail_id' => $this->pembelian_detail_id,
				'barang_id' => $this->barang_id,
				'satuan_id' => $this->satuan_id,
				'harga_satuan' => $this->harga_satuan,
				'jumlah' => $this->jumlah,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'subtotal' => $this->subtotal,
				'profit' => $this->profit,
			]);
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('penjualan-detail.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.penjualan-detail.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/PenjualanDetail/Form.php */
/* Created at 2025-07-03 23:22:56 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */