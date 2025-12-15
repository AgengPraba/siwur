<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Penjualan;
use App\Models\Customer;
use App\Models\Akses;
use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;
use App\Exports\LaporanProfitExport;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithPagination, Toast;

    // Filter properties
    public $search = '';
    public $customer_id = '';
    public $start_date = '';
    public $end_date = '';
    public $status = '';
    public $sort_by = 'tanggal_penjualan';
    public $sort_direction = 'desc';
    public int $perPage = 10;
    // Summary properties
    public $total_penjualan = 0;
    public $total_profit = 0;
    public $total_transaksi = 0;
    public $profit_margin = 0;
    public $total_diskon = 0;
    public $total_biaya_lain = 0;
    public $subtotal_sebelum_adjustment = 0;
    public $breadcrumbs = [];

    // Modal properties
    public $selectedPenjualan = null;

    public function mount()
    {
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Laporan Profit']
        ];
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
        $this->calculateSummary();
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->calculateSummary();
    }

    public function updatedCustomerId()
    {
        $this->resetPage();
        $this->calculateSummary();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
        $this->calculateSummary();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
        $this->calculateSummary();
    }

    public function updatedStatus()
    {
        $this->resetPage();
        $this->calculateSummary();
    }

    public function sortBy($field)
    {
        if ($this->sort_by === $field) {
            $this->sort_direction = $this->sort_direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort_by = $field;
            $this->sort_direction = 'asc';
        }
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->customer_id = '';
        $this->start_date = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->end_date = Carbon::now()->format('Y-m-d');
        $this->status = '';
        $this->sort_by = 'tanggal_penjualan';
        $this->sort_direction = 'desc';
        $this->resetPage();
        $this->calculateSummary();
        $this->success('Berhasil', 'Filter berhasil direset.');
    }

    public function showDetail($penjualanId)
    {
        $this->selectedPenjualan = Penjualan::with(['customer', 'user', 'penjualanDetails.barang', 'penjualanDetails.satuan'])->find($penjualanId);
    }

    public function closeDetail()
    {
        $this->selectedPenjualan = null;
    }

    public function calculateSummary()
    {
        // Get base query for filtering
        $baseQuery = $this->getBaseQueryForSummary();
        
        $this->total_transaksi = $baseQuery->count();
        $this->total_penjualan = $baseQuery->sum('total_harga');
        
        // Create separate query for aggregation without ORDER BY and LIMIT
        $userTokoId = $this->getUserTokoId();
        
        $summaryQuery = Penjualan::query()
            ->join('penjualan_detail', 'penjualan.id', '=', 'penjualan_detail.penjualan_id')
            ->where('penjualan.toko_id', $userTokoId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nomor_penjualan', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('nama_customer', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->customer_id, function ($query) {
                $query->where('customer_id', $this->customer_id);
            })
            ->when($this->start_date, function ($query) {
                $query->whereDate('tanggal_penjualan', '>=', $this->start_date);
            })
            ->when($this->end_date, function ($query) {
                $query->whereDate('tanggal_penjualan', '<=', $this->end_date);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->selectRaw('
                SUM(penjualan_detail.profit) as total_profit,
                SUM(penjualan_detail.diskon) as total_diskon,
                SUM(penjualan_detail.biaya_lain) as total_biaya_lain,
                SUM(penjualan_detail.subtotal) as subtotal_sebelum_adjustment
            ')
            ->first();
            
        $this->total_profit = $summaryQuery->total_profit ?? 0;
        $this->total_diskon = $summaryQuery->total_diskon ?? 0;
        $this->total_biaya_lain = $summaryQuery->total_biaya_lain ?? 0;
        $this->subtotal_sebelum_adjustment = $summaryQuery->subtotal_sebelum_adjustment ?? 0;
        
        // Calculate profit margin
        $this->profit_margin = $this->total_penjualan > 0 ? ($this->total_profit / $this->total_penjualan) * 100 : 0;
    }

    public function getBaseQueryForSummary()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        return Penjualan::query()
            ->where('toko_id', $userTokoId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nomor_penjualan', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('nama_customer', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->customer_id, function ($query) {
                $query->where('customer_id', $this->customer_id);
            })
            ->when($this->start_date, function ($query) {
                $query->whereDate('tanggal_penjualan', '>=', $this->start_date);
            })
            ->when($this->end_date, function ($query) {
                $query->whereDate('tanggal_penjualan', '<=', $this->end_date);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            });
    }

    public function getBaseQuery()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        return Penjualan::query()
            ->with(['customer:id,nama_customer', 'user:id,name', 'penjualanDetails:id,penjualan_id,barang_id,satuan_id,jumlah,harga_satuan,subtotal,profit,diskon,biaya_lain', 'penjualanDetails.barang:id,nama_barang', 'penjualanDetails.satuan:id,nama_satuan'])
            ->where('toko_id', $userTokoId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nomor_penjualan', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function ($customerQuery) {
                          $customerQuery->where('nama_customer', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function ($userQuery) {
                          $userQuery->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->customer_id, function ($query) {
                $query->where('customer_id', $this->customer_id);
            })
            ->when($this->start_date, function ($query) {
                $query->whereDate('tanggal_penjualan', '>=', $this->start_date);
            })
            ->when($this->end_date, function ($query) {
                $query->whereDate('tanggal_penjualan', '<=', $this->end_date);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy($this->sort_by, $this->sort_direction);
    }

    public function with()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        return [
            'penjualans' => $this->getBaseQuery()->paginate($this->perPage),
            'customers' => Customer::where('toko_id', $userTokoId)->orderBy('nama_customer')->get(),
            'status_options' => [
                '' => 'Semua Status',
                'belum_bayar' => 'Belum Bayar',
                'belum_lunas' => 'Belum Lunas',
                'lunas' => 'Lunas',
            ],
        ];
    }
    
    /**
     * Mendapatkan toko_id dari user yang sedang login melalui tabel akses
     */
    private function getUserTokoId()
    {
        $userId = Auth::id();
        $akses = Akses::where('user_id', $userId)->first();
        
        if (!$akses) {
            // Jika tidak ada akses, kembalikan null atau throw exception
            throw new \Exception('User tidak memiliki akses ke toko manapun.');
        }
        
        return $akses->toko_id;
    }
    
    /**
     * Get filters array for exports
     */
    private function getFiltersForExport()
    {
        $customerName = '';
        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            $customerName = $customer ? $customer->nama_customer : '';
        }
        
        return [
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'customer_id' => $this->customer_id,
            'customer_name' => $customerName,
            'status' => $this->status,
            'search' => $this->search,
        ];
    }
    
    /**
     * Get summary array for exports
     */
    private function getSummaryForExport()
    {
        return [
            'total_transaksi' => $this->total_transaksi,
            'total_penjualan' => $this->total_penjualan,
            'total_profit' => $this->total_profit,
            'profit_margin' => $this->profit_margin,
            'total_diskon' => $this->total_diskon,
            'total_biaya_lain' => $this->total_biaya_lain,
            'subtotal_sebelum_adjustment' => $this->subtotal_sebelum_adjustment,
        ];
    }
    
    /**
     * Export to Excel
     */
    public function exportExcel()
    {
        $this->calculateSummary();
        
        $penjualans = $this->getBaseQuery()->get();
        $filters = $this->getFiltersForExport();
        $summary = $this->getSummaryForExport();
        
        $filename = 'Laporan_Profit_' . Carbon::parse($this->start_date)->format('Ymd') . '_' . Carbon::parse($this->end_date)->format('Ymd') . '.xlsx';
        
        $this->success('Berhasil', 'Laporan Excel sedang diunduh...');
        
        return Excel::download(new LaporanProfitExport($filters, $penjualans, $summary), $filename);
    }
    
    /**
     * Export to Print/PDF view
     */
    public function exportPrint()
    {
        $this->calculateSummary();
        
        $penjualans = $this->getBaseQuery()->get();
        $filters = $this->getFiltersForExport();
        $summary = $this->getSummaryForExport();
        
        // Encode data to pass via URL
        $encodedData = base64_encode(json_encode([
            'filters' => $filters,
            'summary' => $summary,
        ]));
        
        // Dispatch browser event to open print view
        $this->dispatch('open-print-view', url: route('laporan-profit.print', ['data' => $encodedData]));
    }
}; ?>
<div class=dark:bg-gray-900" x-data @open-print-view.window="window.open($event.detail.url, '_blank')">
    <x-breadcrumbs :items="$breadcrumbs" />
    <!-- Header -->
    <div class="my-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">📊 Laporan Profit Penjualan</h1>
            <p class="text-gray-600 dark:text-gray-400">Analisis profit dan performa penjualan secara detail</p>
        </div>
        
        <!-- Export Buttons -->
        <div class="flex flex-wrap gap-3">
            <!-- Export Excel Button -->
            <button wire:click="exportExcel" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait"
                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 gap-2 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                <span wire:loading wire:target="exportExcel">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
            
            <!-- Print/PDF Button -->
            <button wire:click="exportPrint" wire:loading.attr="disabled" wire:loading.class="opacity-50 cursor-wait"
                class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 gap-2 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span wire:loading.remove wire:target="exportPrint">Cetak PDF</span>
                <span wire:loading wire:target="exportPrint">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
        <!-- Total Transaksi -->
        <div
            class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Transaksi</p>
                    <p class="text-2xl font-bold">{{ number_format($total_transaksi) }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Penjualan -->
        <div
            class="bg-gradient-to-r from-green-500 to-green-600 dark:from-green-600 dark:to-green-700 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Penjualan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($total_penjualan, 0, ',', '.') }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Profit -->
        <div
            class="bg-gradient-to-r from-purple-500 to-purple-600 dark:from-purple-600 dark:to-purple-700 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Profit</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($total_profit, 0, ',', '.') }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Profit Margin -->
        <div
            class="bg-gradient-to-r from-orange-500 to-orange-600 dark:from-orange-600 dark:to-orange-700 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Profit Margin</p>
                    <p class="text-2xl font-bold">{{ number_format($profit_margin, 1) }}%</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Diskon -->
        <div
            class="bg-gradient-to-r from-red-500 to-red-600 dark:from-red-600 dark:to-red-700 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Total Diskon</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($total_diskon, 0, ',', '.') }}</p>
                </div>
                <div class="bg-red-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Biaya Lain -->
        <div
            class="bg-gradient-to-r from-indigo-500 to-indigo-600 dark:from-indigo-600 dark:to-indigo-700 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium">Biaya Lain</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($total_biaya_lain, 0, ',', '.') }}</p>
                </div>
                <div class="bg-indigo-400 bg-opacity-30 rounded-lg p-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">🔍 Filter & Pencarian</h2>
            <button wire:click="resetFilters" class="btn btn-outline btn-sm dark:text-gray-300 dark:hover:bg-gray-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </path>
                </svg>
                Reset Filter
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- Search -->
            <div class="xl:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pencarian</label>
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Cari nomor penjualan, customer, atau user..."
                    class="input input-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:placeholder-gray-400">
            </div>

            <!-- Customer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer</label>
                <select wire:model.live="customer_id"
                    class="select select-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Semua Customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->nama_customer }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Mulai</label>
                <input type="date" wire:model.live="start_date"
                    class="input input-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tanggal Akhir</label>
                <input type="date" wire:model.live="end_date"
                    class="input input-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>

            <!-- Status -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                <select wire:model.live="status"
                    class="select select-bordered w-full dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach ($status_options as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">📋 Data Penjualan & Profit</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="table table-zebra w-full">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="text-left">
                            <button wire:click="sortBy('nomor_penjualan')"
                                class="flex items-center space-x-1 hover:text-blue-600 dark:text-white dark:hover:text-blue-400">
                                <span>No. Penjualan</span>
                                @if ($sort_by === 'nomor_penjualan')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sort_direction === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-left">
                            <button wire:click="sortBy('tanggal_penjualan')"
                                class="flex items-center space-x-1 hover:text-blue-600 dark:text-white dark:hover:text-blue-400">
                                <span>Tanggal</span>
                                @if ($sort_by === 'tanggal_penjualan')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sort_direction === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-left dark:text-white">Customer</th>
                        <th class="text-center dark:text-white">Items</th>
                        <th class="text-right">
                            <button wire:click="sortBy('total_harga')"
                                class="flex items-center space-x-1 hover:text-blue-600 ml-auto dark:text-white dark:hover:text-blue-400">
                                <span>Total Penjualan</span>
                                @if ($sort_by === 'total_harga')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if ($sort_direction === 'asc')
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        @endif
                                    </svg>
                                @endif
                            </button>
                        </th>
                        <th class="text-right dark:text-white">Diskon</th>
                        <th class="text-right dark:text-white">Biaya Lain</th>
                        <th class="text-right dark:text-white">Total Profit</th>
                        <th class="text-right dark:text-white">Margin (%)</th>
                        <th class="text-center dark:text-white">Status</th>
                        <th class="text-center dark:text-white">Aksi</th>
                    </tr>
                </thead>
                <tbody class="dark:bg-gray-800">
                    @forelse($penjualans as $penjualan)
                        @php
                            $totalProfit = $penjualan->penjualanDetails->sum('profit');
                            $totalDiskon = $penjualan->penjualanDetails->sum('diskon');
                            $totalBiayaLain = $penjualan->penjualanDetails->sum('biaya_lain');
                            $totalItems = $penjualan->penjualanDetails->count();
                            $totalQuantity = $penjualan->penjualanDetails->sum('jumlah');
                            $profitMargin = $penjualan->total_harga > 0 ? ($totalProfit / $penjualan->total_harga) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="font-medium text-blue-600 dark:text-blue-400">{{ $penjualan->nomor_penjualan }}</td>
                            <td class="text-gray-600 dark:text-gray-300">
                                {{ $penjualan->tanggal_penjualan->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="font-medium text-gray-900 dark:text-white">
                                    {{ $penjualan->customer->nama_customer ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $penjualan->user->name ?? 'N/A' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="text-sm">
                                    <div class="font-medium dark:text-white">{{ $totalItems }} items</div>
                                    <div class="text-gray-500 dark:text-gray-400">{{ number_format($totalQuantity, 0) }} qty</div>
                                </div>
                            </td>
                            <td class="text-right font-medium text-green-600 dark:text-green-400">
                                Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}
                            </td>
                            <td class="text-right">
                                @if($totalDiskon > 0)
                                    <span class="text-red-600 dark:text-red-400 font-medium">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        Rp {{ number_format($totalDiskon, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($totalBiayaLain > 0)
                                    <span class="text-indigo-600 dark:text-indigo-400 font-medium">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                        Rp {{ number_format($totalBiayaLain, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="text-right font-medium {{ $totalProfit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                Rp {{ number_format($totalProfit, 0, ',', '.') }}
                            </td>
                            <td class="text-right font-medium {{ $profitMargin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ number_format($profitMargin, 1) }}%
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $penjualan->status_badge_class }} badge-sm">
                                    {{ $penjualan->status_label }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button wire:click="showDetail({{ $penjualan->id }})"
                                    class="btn btn-ghost btn-sm text-blue-600 dark:text-blue-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-8">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Tidak ada data
                                        penjualan</p>
                                    <p class="text-gray-400 dark:text-gray-500">Coba ubah filter atau rentang tanggal
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($penjualans->hasPages())
            <div class="mt-6">
                <x-pagination :rows="$penjualans" wire:model.live="perPage" />
            </div>
        @endif
    </div>

    <!-- Detail Modal -->
    @if ($selectedPenjualan)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            wire:click="closeDetail">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto"
                @click.stop>
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Profit -
                        {{ $selectedPenjualan->nomor_penjualan }}</h3>
                    <button wire:click="closeDetail" class="btn btn-ghost btn-sm dark:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Penjualan Info -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Customer</p>
                                <p class="font-medium dark:text-white">
                                    {{ $selectedPenjualan->customer->nama_customer ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Tanggal</p>
                                <p class="font-medium dark:text-white">
                                    {{ $selectedPenjualan->tanggal_penjualan->format('d M Y, H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                                <span class="badge {{ $selectedPenjualan->status_badge_class }} badge-sm">
                                    {{ $selectedPenjualan->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Items -->
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead class="dark:bg-gray-700">
                                <tr>
                                    <th class="dark:text-white">Barang</th>
                                    <th class="text-right dark:text-white">Qty</th>
                                    <th class="text-right dark:text-white">Harga Satuan</th>
                                    <th class="text-right dark:text-white">Subtotal</th>
                                    <th class="text-right dark:text-white">Diskon</th>
                                    <th class="text-right dark:text-white">Biaya Lain</th>
                                    <th class="text-right dark:text-white">Subtotal Final</th>
                                    <th class="text-right dark:text-white">Profit</th>
                                    <th class="text-right dark:text-white">Margin (%)</th>
                                </tr>
                            </thead>
                            <tbody class="dark:bg-gray-800">
                                @foreach ($selectedPenjualan->penjualanDetails as $detail)
                                    @php
                                        $subtotalFinal = $detail->subtotal - $detail->diskon + $detail->biaya_lain;
                                        $margin = $subtotalFinal > 0 ? ($detail->profit / $subtotalFinal) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="font-medium dark:text-white">
                                                {{ $detail->barang->nama_barang ?? 'N/A' }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $detail->satuan->nama_satuan ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-right dark:text-gray-300">
                                            {{ number_format($detail->jumlah, 2) }}</td>
                                        <td class="text-right dark:text-gray-300">Rp
                                            {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-right font-medium dark:text-gray-300">Rp
                                            {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                        <td class="text-right {{ $detail->diskon > 0 ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-400 dark:text-gray-600' }}">
                                            @if($detail->diskon > 0)
                                                Rp {{ number_format($detail->diskon, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right {{ $detail->biaya_lain > 0 ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-400 dark:text-gray-600' }}">
                                            @if($detail->biaya_lain > 0)
                                                Rp {{ number_format($detail->biaya_lain, 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-right font-bold dark:text-white">
                                            Rp {{ number_format($subtotalFinal, 0, ',', '.') }}</td>
                                        <td class="text-right font-medium {{ $detail->profit >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            Rp {{ number_format($detail->profit, 0, ',', '.') }}
                                        </td>
                                        <td class="text-right font-medium {{ $margin >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($margin, 1) }}%
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                @php
                                    $totalSubtotal = $selectedPenjualan->penjualanDetails->sum('subtotal');
                                    $totalDiskonDetail = $selectedPenjualan->penjualanDetails->sum('diskon');
                                    $totalBiayaLainDetail = $selectedPenjualan->penjualanDetails->sum('biaya_lain');
                                    $totalSubtotalFinal = $totalSubtotal - $totalDiskonDetail + $totalBiayaLainDetail;
                                    $totalProfitDetail = $selectedPenjualan->penjualanDetails->sum('profit');
                                    $totalMarginDetail = $selectedPenjualan->total_harga > 0 ? ($totalProfitDetail / $selectedPenjualan->total_harga) * 100 : 0;
                                @endphp
                                <tr class="font-bold">
                                    <td colspan="3" class="dark:text-white">Total</td>
                                    <td class="text-right dark:text-white">Rp {{ number_format($totalSubtotal, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $totalDiskonDetail > 0 ? 'text-red-600 dark:text-red-400' : 'dark:text-white' }}">
                                        Rp {{ number_format($totalDiskonDetail, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $totalBiayaLainDetail > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'dark:text-white' }}">
                                        Rp {{ number_format($totalBiayaLainDetail, 0, ',', '.') }}</td>
                                    <td class="text-right dark:text-white">Rp {{ number_format($totalSubtotalFinal, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $totalProfitDetail >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        Rp {{ number_format($totalProfitDetail, 0, ',', '.') }}</td>
                                    <td class="text-right {{ $totalMarginDetail >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                        {{ number_format($totalMarginDetail, 1) }}%</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
