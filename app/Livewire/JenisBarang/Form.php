<?php
namespace App\Livewire\JenisBarang;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Schema;
use App\Models\JenisBarang;
use Illuminate\Support\Facades\Auth;


#[Title('Form Jenis barang')]
class Form extends Component
{ 


public $breadcrumbs;
public $type = 'create';
public $jenis_barang_ID;
public $jenis_barang;
  
public $nama_jenis_barang;
public $keterangan;
use Toast;


    public function mount($id = null)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
     
        if($id){
            // Ambil data jenis_barang berdasarkan ID dan toko_id user
            $data = JenisBarang::forCurrentUser()->findOrFail($id);
            $this->type = 'edit';
            if ($data) {
                $this->jenis_barang_ID = $data->id;
                $this->nama_jenis_barang = $data->nama_jenis_barang;
                $this->keterangan = $data->keterangan;
            }
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Jenis barang', 'link' => route('jenis-barang.index')],
            ['label' => $this->type == 'create' ? 'Tambah' : 'Edit'],
        ];
    }

    public function update()
    {
        $this->validate([
            'nama_jenis_barang' => 'required|max:200',
            'keterangan' => 'required|max:200',
        ]);

        // Update data dengan memastikan hanya data dari toko user yang bisa diupdate
        $jenisBarang = JenisBarang::forCurrentUser()->findOrFail($this->jenis_barang_ID);
        $jenisBarang->update([
            'nama_jenis_barang' => $this->nama_jenis_barang,
            'keterangan' => $this->keterangan,
        ]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Edit Data.');
        // Redirect
        return $this->redirectRoute('jenis-barang.index', navigate: true);
    }

    public function store()
    {
        $this->validate([
            'nama_jenis_barang' => 'required|max:200',
            'keterangan' => 'required|max:200',
        ]);
       
        // Otomatis mengisi toko_id dari user yang login
        $tokoId = JenisBarang::getCurrentUserTokoId();
        if (!$tokoId) {
            $this->error('Error', 'Anda tidak memiliki akses ke toko manapun.');
            return;
        }

        JenisBarang::create([
            'nama_jenis_barang' => $this->nama_jenis_barang,
            'keterangan' => $this->keterangan,
            'toko_id' => $tokoId,
        ]);
        
        // Flash message
        $this->success('Notifikasi', 'Berhasil Tambah Data.');
        // Redirect
        return $this->redirectRoute('jenis-barang.index', navigate: true);
    }

    /**
     * render
     *
     * @return void
     */
    public function render()
    {

        return view('livewire.jenis-barang.form');
    }


}

/* End of file komponen create */
/* Location: ./app/Livewire/JenisBarang/Form.php */
/* Created at 2025-07-03 23:23:15 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */