<?php

namespace App\Livewire\StockOpname;

use Livewire\Component;
use App\Models\StockOpname;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

#[Title('Detail Stock Opname')]
class Show extends Component
{
    use Toast;
    
    public $opnameId;
    public $opname;
    public $breadcrumbs;

    public function mount($id)
    {
        $this->opnameId = $id;
        
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $this->opname = StockOpname::with(['details.gudangStock.barang', 'details.gudangStock.satuanTerkecil', 'user', 'gudang', 'toko'])
            ->where('toko_id', $user->akses->toko_id)
            ->findOrFail($id);
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Stock Opname', 'link' => route('stock-opname.index')],   
            ['label' => 'Lihat'],
        ];
    }

    public function print()
    {
        $this->info('Fitur cetak akan membuka jendela baru...', position: 'toast-top');
        $this->dispatch('openPrintWindow', route('stock-opname.print', $this->opname->id));
    }

    public function render()
    {
        return view('livewire.stock-opname.show');
    }
}