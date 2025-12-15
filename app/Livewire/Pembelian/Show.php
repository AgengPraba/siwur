<?php
namespace App\Livewire\Pembelian;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Title;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PembayaranPembelian;
use Illuminate\Support\Facades\Auth;

#[Title('Show Pembelian')]
class Show extends Component
{ 
    use Toast;
 
    public $data;
    public $details;
    public $payments;
    public $breadcrumbs;

    public function mount($id)
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $akses = $user->akses ?? null;
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => "Data Pembelian", 'link' => route('pembelian.index')],
            ['label' => 'Lihat'],
        ];

        // Ambil data pembelian dengan relasi - HasTenancy trait menghandle filtering otomatis
        $this->data = Pembelian::with(['supplier', 'user'])
            ->findOrFail($id);

        // Ambil detail pembelian dengan relasi
        $this->details = PembelianDetail::with(['barang', 'satuan', 'gudang'])
            ->where('pembelian_id', $id)
            ->get();

        // Ambil pembayaran pembelian dengan relasi
        $this->payments = PembayaranPembelian::with(['user'])
            ->where('pembelian_id', $id)
            ->get();
    }

    public function getTotalKembalianProperty()
    {
        return $this->payments->sum('kembalian') ?? 0;
    }

    public function printPurchase()
    {
        return redirect()->route('pembelian.print', ['id' => $this->data->id]);
    }

    public function printPayment()
    {
        return redirect()->route('pembelian.print.payment', ['id' => $this->data->id]);
    }

    public function render()
    {
        $total_paid = $this->payments->sum('jumlah') ?? 0;
        $remaining = $this->data->total_harga - $total_paid;
        
        return view('livewire.pembelian.show', [
            'pembelian_data' => $this->data,
            'pembelian_details' => $this->details,
            'pembelian_payments' => $this->payments,
            'total_kembalian' => $this->totalKembalian,
            'total_paid' => $total_paid,
            'remaining' => $remaining,
        ]);
    }
}

/* End of file komponen show */
/* Location: ./app/Livewire/Pembelian/Show.php */
/* Created at 2025-07-03 23:23:02 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */