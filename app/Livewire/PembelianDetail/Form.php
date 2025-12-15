<?php
namespace App\Livewire\PembelianDetail;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\PembelianDetail;


#[Title('Form Pembelian detail')]
class Form extends Component
{ 


public $breadcrumbs;
public $type = 'create';
public $pembelian_detail_ID;
public $pembelian_detail;
  
public $pembelian_id;
public $barang_id;
public $satuan_id;
public $harga_satuan;
public $jumlah;
public $konversi_satuan_terkecil;
public $subtotal;
public $barang_data;
public $pembelian_data;
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
			
    $columns_pembelian = Schema::getColumnListing('pembelian');
		$field0_pembelian = $columns_pembelian[0]; // Nama kolom pertama
		$field1_pembelian = $columns_pembelian[1]; // Nama kolom kedua
    $this->pembelian_data = DB::table('pembelian')
         ->select(
                DB::raw($field0_pembelian.' as id'),
                DB::raw($field1_pembelian.' as name')
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
                // Ambil data pembelian_detail berdasarkan ID
                $data = PembelianDetail::findOrFail($id);
                $this->type = 'edit';
                if ($data) {
                    $this->pembelian_detail_ID = $data->id;
$this->pembelian_id = $data->pembelian_id;
$this->barang_id = $data->barang_id;
$this->satuan_id = $data->satuan_id;
$this->harga_satuan = $data->harga_satuan;
$this->jumlah = $data->jumlah;
$this->konversi_satuan_terkecil = $data->konversi_satuan_terkecil;
$this->subtotal = $data->subtotal;

                    
            }
        }
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Pembelian detail', 'href' => route('pembelian-detail.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];

    }

     public function update(){
     $this->validate([
				'pembelian_id' => 'required|max:200',
				'barang_id' => 'required|max:200',
				'satuan_id' => 'required|max:200',
				'harga_satuan' => 'required|max:200',
				'jumlah' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'subtotal' => 'required|max:200',
			]);

        // Update data 
        PembelianDetail::findOrFail($this->pembelian_detail_ID)->update([
				'pembelian_id' => $this->pembelian_id,
				'barang_id' => $this->barang_id,
				'satuan_id' => $this->satuan_id,
				'harga_satuan' => $this->harga_satuan,
				'jumlah' => $this->jumlah,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'subtotal' => $this->subtotal,
			]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('pembelian-detail.index', navigate: true);
    }

    public function store()
    {
    $this->validate([
				'pembelian_id' => 'required|max:200',
				'barang_id' => 'required|max:200',
				'satuan_id' => 'required|max:200',
				'harga_satuan' => 'required|max:200',
				'jumlah' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'subtotal' => 'required|max:200',
			]);
       

        PembelianDetail::create([
				'pembelian_id' => $this->pembelian_id,
				'barang_id' => $this->barang_id,
				'satuan_id' => $this->satuan_id,
				'harga_satuan' => $this->harga_satuan,
				'jumlah' => $this->jumlah,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'subtotal' => $this->subtotal,
			]);
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('pembelian-detail.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.pembelian-detail.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/PembelianDetail/Form.php */
/* Created at 2025-07-03 23:23:07 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */