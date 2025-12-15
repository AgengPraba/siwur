<?php
namespace App\Livewire\BarangSatuan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\BarangSatuan;


#[Title('Form Barang satuan')]
class Form extends Component
{ 


public $breadcrumbs;
public $type = 'create';
public $barang_satuan_ID;
public $barang_satuan;
  
public $barang_id;
public $satuan_id;
public $konversi_satuan_terkecil;
public $is_satuan_terkecil;
public $barang_data;
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
			
    $columns_satuan = Schema::getColumnListing('satuan');
		$field0_satuan = $columns_satuan[0]; // Nama kolom pertama
		$field1_satuan = $columns_satuan[1]; // Nama kolom kedua
    $this->satuan_data = DB::table('satuan')
         ->select(
                DB::raw($field0_satuan.' as id'),
                DB::raw($field1_satuan.' as name')
            )->get()->toArray();
        if($id){
                // Ambil data barang_satuan berdasarkan ID
                $data = BarangSatuan::findOrFail($id);
                $this->type = 'edit';
                if ($data) {
                    $this->barang_satuan_ID = $data->id;
$this->barang_id = $data->barang_id;
$this->satuan_id = $data->satuan_id;
$this->konversi_satuan_terkecil = $data->konversi_satuan_terkecil;
$this->is_satuan_terkecil = $data->is_satuan_terkecil;

                    
            }
        }
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Barang satuan', 'href' => route('barang-satuan.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];

    }

     public function update(){
     $this->validate([
				'barang_id' => 'required|max:200',
				'satuan_id' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'is_satuan_terkecil' => 'required|max:200',
			]);

        // Update data 
        BarangSatuan::findOrFail($this->barang_satuan_ID)->update([
				'barang_id' => $this->barang_id,
				'satuan_id' => $this->satuan_id,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'is_satuan_terkecil' => $this->is_satuan_terkecil,
			]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('barang-satuan.index', navigate: true);
    }

    public function store()
    {
    $this->validate([
				'barang_id' => 'required|max:200',
				'satuan_id' => 'required|max:200',
				'konversi_satuan_terkecil' => 'required|max:200',
				'is_satuan_terkecil' => 'required|max:200',
			]);
       

        BarangSatuan::create([
				'barang_id' => $this->barang_id,
				'satuan_id' => $this->satuan_id,
				'konversi_satuan_terkecil' => $this->konversi_satuan_terkecil,
				'is_satuan_terkecil' => $this->is_satuan_terkecil,
			]);
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('barang-satuan.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.barang-satuan.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/BarangSatuan/Form.php */
/* Created at 2025-07-03 23:23:42 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */