<?php
namespace App\Livewire\Satuan;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\Satuan;
use Illuminate\Support\Facades\Auth;

#[Title('Show Satuan')]
class Show extends Component
{ 
    public $data;
    public $breadcrumbs;
    public $tokoId; // Toko ID dari user yang login

    public function mount($id)
    {
        // Ambil toko_id dari user yang login melalui relasi akses
        $this->tokoId = Auth::user()->akses->toko_id ?? null;
        
        if (!$this->tokoId) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Satuan', 'link' => route('satuan.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data dan pastikan milik toko yang sama
        $this->data = Satuan::select('satuan.*')
                           ->where('id', $id)
                           ->where('toko_id', $this->tokoId)
                           ->firstOrFail();
    }

    public function render()
    {
        return view('livewire.satuan.show', [
            'satuan_data' => $this->data,
        ]);
    }


}

/* End of file komponen show */
/* Location: ./app/Livewire/Satuan/Show.php */
/* Created at 2025-07-03 23:21:53 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */