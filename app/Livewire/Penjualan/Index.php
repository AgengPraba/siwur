<?php

namespace App\Livewire\Penjualan;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use App\Models\Penjualan;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\LivewireTenancy;

#[Title('List Penjualan')]
class Index extends Component
{
    use WithPagination, Toast, LivewireTenancy;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterCustomer' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $idToDelete = null;
    public $breadcrumbs;
    public int $perPage = 10;
    public int $start;

    // Filter properties
    public $filterStatus = '';
    public $filterCustomer = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $showFilters = false;

    public function mount()
    {
        // Check toko access
        if (!$this->checkTokoAccess()) {
            return redirect()->route('home');
        }

        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Riwayat Penjualan']
        ];

        try {
            $sessionKey = 'penjualan_temp_' . Auth::user()->id;
            session()->forget($sessionKey);
          
        } catch (\Exception $e) {
            Log::warning('Failed to clear penjualan session data: ' . $e->getMessage());
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function updatingFilterCustomer()
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
        $this->filterCustomer = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->resetPage();
    }

    public function destroy()
    {
        if ($this->idToDelete != null) {
            $data = Penjualan::findOrFail($this->idToDelete);
            if ($data->status != 'belum_bayar') {
                $this->error('Data penjualan tidak bisa dihapus karena sudah dibayar.');
                return;
            }

            try {
                DB::beginTransaction();
                // Get penjualan details first
                $penjualanDetails = $data->penjualanDetails;

                // Process each detail
                foreach ($penjualanDetails as $detail) {
                    // Increment stock in gudang_stock
                    $getTransaksi = $detail->transaksiGudangStock()->first();
                    $stockToAdd = $detail->jumlah * $detail->konversi_satuan_terkecil;
                    DB::table('gudang_stock')
                        ->where('id', $getTransaksi->gudang_stock_id)
                        ->increment('jumlah', $stockToAdd);
                    // Delete related transaksi_gudang_stock records
                    $detail->transaksiGudangStock()->delete();
                }
                $data->penjualanDetails()->delete(); //hapus detail penjualan
                $data->pembayaranPenjualan()->delete(); //hapus pembayaran penjualan
                $data->delete(); //hapus penjualan

                DB::commit();

                $this->success('Berhasil!', 'Data penjualan berhasil dihapus.');
                $this->idToDelete = null;

                $count = Penjualan::count();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Gagal!', 'Terjadi kesalahan saat menghapus data penjualan.');
            }

            if ($count == 0) {
                return redirect(route('penjualan.index'));
            } else {
                return $this->redirectRoute('penjualan.index', navigate: true);
            }
        }
    }

    public function getStatsProperty()
    {
        $query = Penjualan::query();
        
        // Scope to current toko - trait HasTenancy akan otomatis menambahkan filter toko_id
        // Tidak perlu menambahkan filter manual karena global scope sudah menangani

        // Apply same filters as main query
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(nomor_penjualan) LIKE ?', ["%{$this->search}%"])
                    ->orWhereHas('customer', function ($customer) {
                        $customer->whereRaw('LOWER(nama_customer) LIKE ?', ["%{$this->search}%"]);
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterCustomer) {
            $query->where('customer_id', $this->filterCustomer);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('tanggal_penjualan', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('tanggal_penjualan', '<=', $this->filterDateTo);
        }

        // Get penjualan IDs for detail calculations
        $penjualanIds = $query->pluck('id');
        
        $totalChange = 0.0;

        if ($penjualanIds->isNotEmpty()) {
            $penjualanTotals = DB::table('penjualan')
                ->whereIn('id', $penjualanIds)
                ->select('id', 'total_harga', 'kembalian')
                ->get();

            $paymentsPerSale = DB::table('pembayaran_penjualan')
                ->whereIn('penjualan_id', $penjualanIds)
                ->select('penjualan_id', DB::raw('SUM(jumlah) as total_bayar'))
                ->groupBy('penjualan_id')
                ->pluck('total_bayar', 'penjualan_id');

            foreach ($penjualanTotals as $row) {
                $storedChange = (float) ($row->kembalian ?? 0);
                if ($storedChange > 0) {
                    $totalChange += $storedChange;
                    continue;
                }

                $paid = (float) ($paymentsPerSale[$row->id] ?? 0);
                $total = (float) ($row->total_harga ?? 0);
                $computedChange = max($paid - $total, 0);
                $totalChange += $computedChange;
            }
        }

        return [
            'total_count' => $query->count(),
            'total_amount' => $query->sum('total_harga'),
            'total_diskon' => DB::table('penjualan_detail')
                ->whereIn('penjualan_id', $penjualanIds)
                ->sum('diskon'),
            'total_biaya_lain' => DB::table('penjualan_detail')
                ->whereIn('penjualan_id', $penjualanIds)
                ->sum('biaya_lain'),
            'total_pembayaran' => DB::table('pembayaran_penjualan')
                ->whereIn('penjualan_id', $penjualanIds)
                ->sum('jumlah'),
            'total_kembalian' => $totalChange,
            'total_items' => DB::table('penjualan_detail')
                ->whereIn('penjualan_id', $penjualanIds)
                ->count(),
            'total_quantity' => DB::table('penjualan_detail')
                ->whereIn('penjualan_id', $penjualanIds)
                ->sum('jumlah'),
            'belum_bayar_count' => (clone $query)->where('status', 'belum_bayar')->count(),
            'belum_lunas_count' => (clone $query)->where('status', 'belum_lunas')->count(),
            'lunas_count' => (clone $query)->where('status', 'lunas')->count(),
        ];
    }

    public function render()
    {
        $query = Penjualan::with(['customer', 'user', 'penjualanDetails', 'pembayaranPenjualan'])
            ->select('penjualan.*');
        
        // Scope to current toko - trait HasTenancy akan otomatis menambahkan filter toko_id
        // Tidak perlu menambahkan filter manual karena global scope sudah menangani

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(nomor_penjualan) LIKE ?', ["%{$this->search}%"])
                    ->orWhereHas('customer', function ($customer) {
                        $customer->whereRaw('LOWER(nama_customer) LIKE ?', ["%{$this->search}%"]);
                    })
                    ->orWhereHas('user', function ($user) {
                        $user->whereRaw('LOWER(name) LIKE ?', ["%{$this->search}%"]);
                    });
            });
        }

        // Apply status filter
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Apply customer filter
        if ($this->filterCustomer) {
            $query->where('customer_id', $this->filterCustomer);
        }

        // Apply date filters
        if ($this->filterDateFrom) {
            $query->whereDate('tanggal_penjualan', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('tanggal_penjualan', '<=', $this->filterDateTo);
        }

        $data = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage)
            ->withQueryString();

        $data->setCollection($data->getCollection()->map(function (Penjualan $penjualan) {
            $totalPaid = $penjualan->pembayaranPenjualan->sum('jumlah');
            $storedChange = (float) ($penjualan->kembalian ?? 0);
            $computedChange = max($totalPaid - (float) $penjualan->total_harga, 0);
            $penjualan->computed_kembalian = $storedChange > 0 ? $storedChange : $computedChange;

            return $penjualan;
        }));

        $currentPage = $data->currentPage();
        $this->start = ($currentPage - 1) * $this->perPage + 1;

        // Get customers for filter dropdown - hanya customer dari toko yang sama
        $customers = Customer::select('id', 'nama_customer')
            ->orderBy('nama_customer')
            ->get();

        return view('livewire.penjualan.index', [
            'penjualan_data' => $data,
            'customers' => $customers,
            'stats' => $this->stats
        ]);
    }
}

/* End of file komponen index */
/* Location: ./app/Livewire/Penjualan/Index.php */
/* Created at 2025-07-03 23:22:50 */
/* Mohammad Irham Akbar CRUD Laravel 12 Livewire Mary-UI */