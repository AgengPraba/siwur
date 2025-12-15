<?php

namespace App\Livewire\Pembelian;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Mary\Traits\Toast;
use App\Models\Pembelian;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

#[Title('List Pembelian')]
class Index extends Component
{
    use WithPagination, Toast;

    public $search = ''; // Properti untuk pencarian
    public $sortField = 'created_at'; // Kolom untuk sorting
    public $sortDirection = 'desc'; // Arah sorting
    public $idToDelete = null; // ID yang akan dihapus
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    // Filter properties
    public $filterStatus = '';
    public $filterSupplier = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $viewMode = 'table'; // 'card' or 'table'

    // Stats properties
    public $totalPembelian = 0;
    public $totalNilai = 0;
    public $pembelianBulanIni = 0;
    public $supplierAktif = 0;
    public $totalDiskonBulanIni = 0;
    public $totalBiayaLainBulanIni = 0;

    public function mount()
    {
        // Cek apakah user memiliki akses toko
        $user = Auth::user();
        if (!$user || !$user->akses || !$user->akses->toko_id) {
            abort(403, 'Anda tidak memiliki akses ke toko manapun.');
        }
        
        $akses = $user->akses ?? null;
        $namaTokoLabel = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';
        
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => "Riwayat Pembelian"]
        ];
        $this->loadStats();
    }

    public function loadStats()
    {
        // Semua query otomatis terfilter berdasarkan toko_id melalui HasTenancy trait
        $this->totalPembelian = Pembelian::count();
        $this->totalNilai = Pembelian::sum('total_harga');
        $this->pembelianBulanIni = Pembelian::whereMonth('tanggal_pembelian', now()->month)
            ->whereYear('tanggal_pembelian', now()->year)
            ->count();
        $this->supplierAktif = Supplier::whereHas('pembelian')->count();
        
        // Calculate total discount and additional costs for current month
        $pembelianBulanIni = Pembelian::whereMonth('tanggal_pembelian', now()->month)
            ->whereYear('tanggal_pembelian', now()->year)
            ->with('pembelianDetails')
            ->get();
            
        $this->totalDiskonBulanIni = $pembelianBulanIni->sum('total_diskon');
        $this->totalBiayaLainBulanIni = $pembelianBulanIni->sum('total_biaya_lain');
    }

    #[Computed]
    public function suppliers()
    {
        // Supplier otomatis terfilter berdasarkan toko_id melalui HasTenancy trait
        return Supplier::whereHas('pembelian')
            ->orderBy('nama_supplier')
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->nama_supplier
                ];
            })
            ->toArray();
    }

    #[Computed]
    public function statusOptions()
    {
        return [
            ['id' => 'belum_bayar', 'name' => 'Belum Bayar'],
            ['id' => 'belum_lunas', 'name' => 'Belum Lunas'],
            ['id' => 'lunas', 'name' => 'Lunas']
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterSupplier()
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->filterStatus = '';
        $this->filterSupplier = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'card' ? 'table' : 'card';
    }

    public function destroy()
    {
        if ($this->idToDelete != null) {
            $data = Pembelian::findOrFail($this->idToDelete);
            if ($data->status != 'belum_bayar') {
                $this->error('Data pembelian tidak bisa dihapus karena sudah dibayar.');
                return;
            }

            try {
                DB::beginTransaction();
                // Get penjualan details first
                $pembelianDetails = $data->pembelianDetails;
                // Process each detail
                foreach ($pembelianDetails as $detail) {
                    // Increment stock in gudang_stock
                    $getTransaksi = $detail->transaksiGudangStock()->first();
                    $stockToAdd = $detail->jumlah * $detail->konversi_satuan_terkecil;
                    DB::table('gudang_stock')
                        ->where('id', $getTransaksi->gudang_stock_id)
                        ->decrement('jumlah', $stockToAdd);
                    // Delete related transaksi_gudang_stock records
                    $detail->transaksiGudangStock()->delete();
                }
               
                $data->pembelianDetails()->delete(); //hapus detail pembelian
                $data->pembayaranPembelian()->delete(); //hapus pembayaran pembelian
                $data->delete(); //hapus pembelian

                DB::commit();

                $this->success('Berhasil!', 'Data pembelian berhasil dihapus.');
                $this->idToDelete = null;

                $count = Pembelian::count();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Gagal!', 'Terjadi kesalahan saat menghapus data pembelian.');
            }
            $this->loadStats(); // Refresh stats after deletion

            $count = Pembelian::count();
            if ($count == 0) {
                return redirect(route('pembelian.index'));
            } else {
                return $this->redirectRoute('pembelian.index', navigate: true);
            }
        }
    }

    public function getStatusLabel($status)
    {
        $labels = [
            'belum_bayar' => 'Belum Bayar',
            'belum_lunas' => 'Belum Lunas',
            'lunas' => 'Lunas'
        ];

        return $labels[$status] ?? $status;
    }

    public function getStatusClass($status)
    {
        $classes = [
            'belum_bayar' => 'bg-red-500',
            'belum_lunas' => 'bg-yellow-500',
            'lunas' => 'bg-green-500'
        ];

        return $classes[$status] ?? 'bg-gray-500';
    }

    private function getStatusLabels()
    {
        return [
            'belum_bayar' => 'Belum Bayar',
            'belum_lunas' => 'Belum Lunas',
            'lunas' => 'Lunas'
        ];
    }

    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public function getDiscountInfo($pembelian)
    {
        if ($pembelian->total_diskon > 0) {
            $percentage = $pembelian->subtotal > 0 ? round(($pembelian->total_diskon / $pembelian->subtotal) * 100, 1) : 0;
            return [
                'amount' => $pembelian->total_diskon,
                'percentage' => $percentage,
                'formatted' => $this->formatCurrency($pembelian->total_diskon) . ' (' . $percentage . '%)'
            ];
        }
        return null;
    }

    public function render()
    {
        // Query otomatis terfilter berdasarkan toko_id melalui HasTenancy trait
        // Menggunakan alias tabel untuk menghindari ambiguitas kolom toko_id
        $query = Pembelian::from('pembelian as p')
            ->join('supplier as s', 'p.supplier_id', '=', 's.id')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->select('p.*', 's.nama_supplier', 'u.name')
            ->with([
                'pembayaranPembelian',
                'pembelianDetails' => function ($query) {
                    $query->select('id', 'pembelian_id', 'subtotal', 'diskon', 'jumlah', 'biaya_lain');
                }
            ]);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(p.nomor_pembelian) LIKE ?', ["%" . strtolower($this->search) . "%"])
                    ->orWhereRaw('LOWER(s.nama_supplier) LIKE ?', ["%" . strtolower($this->search) . "%"])
                    ->orWhereRaw('LOWER(u.name) LIKE ?', ["%" . strtolower($this->search) . "%"])
                    ->orWhereRaw('LOWER(p.keterangan) LIKE ?', ["%" . strtolower($this->search) . "%"]);
            });
        }

        // Apply status filter
        if ($this->filterStatus) {
            $query->where('p.status', $this->filterStatus);
        }

        // Apply supplier filter
        if ($this->filterSupplier) {
            $query->where('p.supplier_id', $this->filterSupplier);
        }

        // Apply date filters
        if ($this->filterDateFrom) {
            $query->whereDate('p.tanggal_pembelian', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('p.tanggal_pembelian', '<=', $this->filterDateTo);
        }

        $data = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();

        // Add total kembalian calculation for each pembelian
        $data->getCollection()->transform(function ($pembelian) {
            $pembelian->total_kembalian = $pembelian->pembayaranPembelian->sum('kembalian') ?? 0;
            return $pembelian;
        });

        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;

        return view('livewire.pembelian.index', [
            'pembelian_data' => $data,
            'total_diskon' => $data->sum('total_diskon'),
        ]);
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/Pembelian/Index.php */
/* Created at 2025-07-03 23:23:02 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */