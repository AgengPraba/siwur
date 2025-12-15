<?php
namespace App\Livewire\TransaksiGudangStock;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\TransaksiGudangStock;


#[Title('Riwayat Transaksi Gudang Stock')]
class Index extends Component
{ 
    use WithPagination, Toast;
    public $search = ''; // Properti untuk pencarian
    public $sortField = 'created_at'; // Kolom untuk sorting
    public $sortDirection = 'desc'; // Arah sorting
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;
    public $filterTipe = ''; // Filter untuk tipe transaksi
    public $filterSource = ''; // Filter untuk sumber transaksi

    public function mount(){
        $this->breadcrumbs = [
            ['label' => 'Home', 'href' => route('home')], 
            ['label' => 'Riwayat Transaksi Gudang Stock']
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Reset halaman saat pencarian diubah
    }

    public function updatingFilterTipe()
    {
        $this->resetPage();
    }

    public function updatingFilterSource()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'; // Toggle arah sorting
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc'; // Set default sorting ke ascending
        }
    }

    public function render()
    {
        $query = TransaksiGudangStock::with(['gudangStock.gudang', 'gudangStock.barang', 'pembelianDetail.pembelian', 'penjualanDetail.penjualan'])
            ->select('transaksi_gudang_stock.*');

        // Apply search filter
        if ($this->search) {
            $query->whereHas('gudangStock.barang', function($q) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$this->search}%"]);
            })
            ->orWhereHas('gudangStock.gudang', function($q) {
                $q->whereRaw('LOWER(nama) LIKE ?', ["%{$this->search}%"]);
            })
            ->orWhereRaw('LOWER(tipe) LIKE ?', ["%{$this->search}%"])
            ->orWhereRaw('LOWER(jumlah) LIKE ?', ["%{$this->search}%"]);
        }

        // Apply tipe filter
        if ($this->filterTipe) {
            $query->where('tipe', $this->filterTipe);
        }

        // Apply source filter
        if ($this->filterSource) {
            if ($this->filterSource === 'pembelian') {
                $query->whereNotNull('pembelian_detail_id');
            } elseif ($this->filterSource === 'penjualan') {
                $query->whereNotNull('penjualan_detail_id');
            } elseif ($this->filterSource === 'adjustment') {
                $query->whereNull('pembelian_detail_id')->whereNull('penjualan_detail_id');
            }
        }

        $data = $query->orderBy($this->sortField, $this->sortDirection)
               ->paginate($this->perPage)
               ->withQueryString();

        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;

        return view('livewire.transaksi-gudang-stock.index', [
            'transaksi_gudang_stock_data' => $data
        ]);
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/TransaksiGudangStock/Index.php */
/* Created at 2025-07-03 23:24:00 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */