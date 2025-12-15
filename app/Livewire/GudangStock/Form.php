<?php
namespace App\Livewire\GudangStock;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\GudangStock;


#[Title('Form Gudang stock')]
class Form extends Component
{ 


public $breadcrumbs;
public $type = 'create';
public $gudang_stock_ID;
public $gudang_stock;
  
public $gudang_id;
public $barang_id;
public $jumlah;
public $barang_data;
public $gudang_data;
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
			
    $columns_gudang = Schema::getColumnListing('gudang');
		$field0_gudang = $columns_gudang[0]; // Nama kolom pertama
		$field1_gudang = $columns_gudang[1]; // Nama kolom kedua
    $this->gudang_data = DB::table('gudang')
         ->select(
                DB::raw($field0_gudang.' as id'),
                DB::raw($field1_gudang.' as name')
            )->get()->toArray();
        if($id){
                // Ambil data gudang_stock berdasarkan ID
                $data = GudangStock::findOrFail($id);
                $this->type = 'edit';
                if ($data) {
                    $this->gudang_stock_ID = $data->id;
$this->gudang_id = $data->gudang_id;
$this->barang_id = $data->barang_id;
$this->jumlah = $data->jumlah;

                    
            }
        }
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')],
            ['label' => 'Data Gudang stock', 'href' => route('gudang-stock.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];

    }

     public function update(){
     $this->validate([
				'gudang_id' => 'required|max:200',
				'barang_id' => 'required|max:200',
				'jumlah' => 'required|max:200',
			]);

        // Update data 
        GudangStock::findOrFail($this->gudang_stock_ID)->update([
				'gudang_id' => $this->gudang_id,
				'barang_id' => $this->barang_id,
				'jumlah' => $this->jumlah,
			]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('gudang-stock.index', navigate: true);
    }

    public function store()
    {
    $this->validate([
				'gudang_id' => 'required|max:200',
				'barang_id' => 'required|max:200',
				'jumlah' => 'required|max:200',
			]);
       

        GudangStock::create([
				'gudang_id' => $this->gudang_id,
				'barang_id' => $this->barang_id,
				'jumlah' => $this->jumlah,
			]);
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('gudang-stock.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.gudang-stock.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/GudangStock/Form.php */
/* Created at 2025-07-03 23:23:50 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */