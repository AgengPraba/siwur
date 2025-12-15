<?php
namespace App\Livewire\Gudang;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\Gudang;
use App\Traits\LivewireTenancy;


#[Title('Form Gudang')]
class Form extends Component
{ 
    use Toast, LivewireTenancy;

    public $breadcrumbs;
    public $type = 'create';
    public $gudang_ID;
    public $gudang;
      
    public $nama_gudang;
    public $keterangan;


    public function mount($id = null)
    {
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }
        
        if($id){
            // Ambil data gudang berdasarkan ID
            $data = Gudang::findOrFail($id);
            
            // Validate ownership
            if (!$this->validateTokoOwnership($data)) {
                return redirect()->route('gudang.index');
            }
            
            $this->type = 'edit';
            if ($data) {
                $this->gudang_ID = $data->id;
                $this->nama_gudang = $data->nama_gudang;
                $this->keterangan = $data->keterangan;
            }
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Gudang', 'link' => route('gudang.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }

    public function update(){
        $this->validate([
            'nama_gudang' => 'required|max:200',
            'keterangan' => 'required|max:200',
        ]);

        $data = Gudang::findOrFail($this->gudang_ID);
        
        // Validate ownership
        if (!$this->validateTokoOwnership($data)) {
            return;
        }

        // Update data 
        $data->update([
            'nama_gudang' => $this->nama_gudang,
            'keterangan' => $this->keterangan,
        ]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('gudang.index', navigate: true);
    }

    public function store()
    {
        $this->validate([
            'nama_gudang' => 'required|max:200',
            'keterangan' => 'required|max:200',
        ]);

        // toko_id akan otomatis diset oleh trait HasTenancy
        Gudang::create([
            'nama_gudang' => $this->nama_gudang,
            'keterangan' => $this->keterangan,
        ]);
        
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('gudang.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.gudang.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/Gudang/Form.php */
/* Created at 2025-07-03 23:23:21 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */