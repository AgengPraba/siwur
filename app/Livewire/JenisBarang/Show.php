<?php
namespace App\Livewire\JenisBarang;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\JenisBarang;
use Illuminate\Support\Facades\Auth;

#[Title('Show Jenis barang')]
class Show extends Component
{ 
 
    public $data;
    public $breadcrumbs;

    public function mount($id)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Jenis barang', 'link' => route('jenis-barang.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data dengan filter toko_id user
        $this->data = JenisBarang::forCurrentUser()
                        ->select('jenis_barang.*')
                        ->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.jenis-barang.show', [
            'jenis_barang_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/JenisBarang/Show.php */
/* Created at 2025-07-03 23:23:15 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */