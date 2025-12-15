<?php

namespace App\Livewire\ReturPembelian;

use App\Models\ReturPembelian;
use Livewire\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use Toast;
    
    #[Title('Detail Retur Pembelian')]
    
    public $toko_id;
    public $breadcrumbs;
    public ReturPembelian $returPembelian;
    public bool $showApprovalModal = false;
    public bool $showVoidModal = false;
    
    public function mount(int $id)
    {
        $this->toko_id = Auth::user()->akses->toko_id;

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Data Retur Pembelian', 'link' => route('retur-pembelian.index')],
            ['label' => 'Lihat'],
        ];
        $this->returPembelian = ReturPembelian::with([
            'pembelian.supplier',
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
        return redirect()->route('retur-pembelian.print', $this->returPembelian->id);
    }
    
    public function render()
    {
        $breadcrumbs = [
            ['name' => 'Retur Pembelian', 'url' => route('retur-pembelian.index')],
            ['name' => 'Detail', 'url' => null]
        ];
        
        $alasanReturLabels = [
            'rusak' => 'Barang Rusak',
            'tidak_sesuai' => 'Tidak Sesuai Pesanan',
            'kelebihan' => 'Kelebihan Pengiriman',
            'kadaluarsa' => 'Mendekati Kadaluarsa',
            'lainnya' => 'Lainnya',
        ];
        
        return view('livewire.retur-pembelian.show', compact('breadcrumbs', 'alasanReturLabels'));
    }
}