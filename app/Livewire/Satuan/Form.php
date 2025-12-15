<?php
namespace App\Livewire\Satuan;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\Satuan;
use Illuminate\Support\Facades\Auth;


#[Title('Form Satuan')]
class Form extends Component
{ 
    use Toast;
    
    public $breadcrumbs;
    public $type = 'create';
    public $satuan_ID;
    public $satuan;
    public $tokoId; // Toko ID dari user yang login
      
    public $nama_satuan;
    public $keterangan;


    public function mount($id = null)
    {
        // Ambil toko_id dari user yang login melalui relasi akses
        $this->tokoId = Auth::user()->akses->toko_id ?? null;
        
        if (!$this->tokoId) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        if($id){
            // Ambil data satuan berdasarkan ID dan pastikan milik toko yang sama
            $data = Satuan::where('id', $id)
                         ->where('toko_id', $this->tokoId)
                         ->firstOrFail();
            $this->type = 'edit';
            if ($data) {
                $this->satuan_ID = $data->id;
                $this->nama_satuan = $data->nama_satuan;
                $this->keterangan = $data->keterangan;
            }
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Satuan', 'link' => route('satuan.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }

    public function update(){
        $this->validate([
            'nama_satuan' => 'required|max:200',
            'keterangan' => 'required|max:200',
        ]);

        // Update data dan pastikan milik toko yang sama
        Satuan::where('id', $this->satuan_ID)
              ->where('toko_id', $this->tokoId)
              ->firstOrFail()
              ->update([
                  'nama_satuan' => $this->nama_satuan,
                  'keterangan' => $this->keterangan,
              ]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('satuan.index', navigate: true);
    }

    public function store()
    {
        $this->validate([
            'nama_satuan' => 'required|max:200',
            'keterangan' => 'required|max:200',
        ]);

        Satuan::create([
            'nama_satuan' => $this->nama_satuan,
            'keterangan' => $this->keterangan,
            'toko_id' => $this->tokoId,
        ]);
        
        //flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        //redirect
        return $this->redirectRoute('satuan.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.satuan.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/Satuan/Form.php */
/* Created at 2025-07-03 23:21:53 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */