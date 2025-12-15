<?php

namespace App\Livewire\ReturPenjualan;

use App\Models\ReturPenjualan;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use Toast;
    
    #[Title('Detail Retur Penjualan')]
    
    public $toko_id;
    public $breadcrumbs;
    public ReturPenjualan $returPenjualan;
    
    public function mount(int $id)
    {
        $this->toko_id = Auth::user()->akses->toko_id;

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Retur Penjualan', 'link' => route('retur-penjualan.index')],
            ['label' => 'Lihat'],
        ];
        
        $this->returPenjualan = ReturPenjualan::with([
            'penjualan.customer',
            'details.barang',
            'details.satuan',
            'gudang',
            'dibuatOleh',
        ])
        ->where('toko_id', $this->toko_id)
        ->findOrFail($id);
    }
    
    public function print()
    {
        return redirect()->route('retur-penjualan.print', $this->returPenjualan->id);
    }
    
    public function render()
    {
        $alasanReturLabels = [
            'rusak' => 'Barang Rusak',
            'tidak_sesuai' => 'Tidak Sesuai Pesanan',
            'kelebihan' => 'Kelebihan Pengiriman',
            'kadaluarsa' => 'Mendekati Kadaluarsa',
            'lainnya' => 'Lainnya',
        ];
        
        return view('livewire.retur-penjualan.show', compact( 'alasanReturLabels'));
    }
}
