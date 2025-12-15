<?php

use Livewire\Volt\Component;
use App\Models\PembayaranPembelian;
use App\Models\PembayaranPenjualan;
use App\Models\Akses;
use App\Services\LaporanPembayaranExcelService;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;
    public $jenis_laporan = 'penjualan'; // Default to 'penjualan', options: 'penjualan', 'pembelian'
    public $tanggal_mulai;
    public $tanggal_selesai;
    public $jenis_pembayaran = '';
    public $search = '';
    public $perPage = 10;
    public $breadcrumbs = [];
    
    public function mount()
    {
        // Set default date range to current month
        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');
        // Set breadcrumbs
        $this->breadcrumbs = [
            ['label' => 'Home', 'link' => route('home')],
            ['label' => 'Laporan Pembayaran']
        ];
    }
    
    public function updatedSearch()
    {
        $this->resetPage();
    }
    
    public function updatedJenisLaporan()
    {
        $this->resetPage();
    }
    
    public function resetFilter()
    {
        $this->reset(['search', 'jenis_pembayaran']);
        $this->tanggal_mulai = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->tanggal_selesai = Carbon::now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }
    
    public function exportExcel()
    {
        try {
            $filters = [
                'jenis_laporan' => $this->jenis_laporan,
                'tanggal_mulai' => $this->tanggal_mulai,
                'tanggal_selesai' => $this->tanggal_selesai,
                'jenis_pembayaran' => $this->jenis_pembayaran,
                'search' => $this->search
            ];
            
            $excelService = new LaporanPembayaranExcelService();
            return $excelService->generateExcel($filters);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat export Excel: ' . $e->getMessage()
            ]);
        }
    }
    
    public function exportPdf()
    {
        try {
            // Get all data without pagination for PDF
            $filters = [
                'jenis_laporan' => $this->jenis_laporan,
                'tanggal_mulai' => $this->tanggal_mulai,
                'tanggal_selesai' => $this->tanggal_selesai,
                'jenis_pembayaran' => $this->jenis_pembayaran,
                'search' => $this->search
            ];
            
            $data = $this->getAllDataForPrint();
            $statistik = $this->getStatistik();
            
            // Generate the print view with proper data passing
            $printView = view('components.laporan-pembayaran-print', compact('data', 'filters', 'statistik'))->render();
            
            // Dispatch event with the HTML content
            $this->dispatch('open-print-window', ['html' => $printView]);
            
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Terjadi kesalahan saat membuat laporan: ' . $e->getMessage()
            ]);
        }
    }
    
    public function getData()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        if ($this->jenis_laporan === 'penjualan') {
            $query = PembayaranPenjualan::with(['penjualan.customer', 'user'])
                ->whereHas('penjualan', function ($q) use ($userTokoId) {
                    // Filter berdasarkan toko_id dari user yang login
                    $q->where('toko_id', $userTokoId);
                    
                    $q->when($this->search, function ($query) {
                        return $query->whereHas('customer', function ($q) {
                            $q->where('nama_customer', 'like', '%' . $this->search . '%');
                        });
                    });
                });
        } else {
            $query = PembayaranPembelian::with(['pembelian.supplier', 'user'])
                ->whereHas('pembelian', function ($q) use ($userTokoId) {
                    // Filter berdasarkan toko_id dari user yang login
                    $q->where('toko_id', $userTokoId);
                    
                    $q->when($this->search, function ($query) {
                        return $query->whereHas('supplier', function ($q) {
                            $q->where('nama_supplier', 'like', '%' . $this->search . '%');
                        });
                    });
                });
        }
        
        // Apply date filters
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay()
            ]);
        }
        
        // Apply payment type filter
        if ($this->jenis_pembayaran) {
            $query->where('jenis_pembayaran', $this->jenis_pembayaran);
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($this->perPage);
    }
    
    public function getStatistik()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        $stats = [];
        
        if ($this->jenis_laporan === 'penjualan') {
            $query = PembayaranPenjualan::whereHas('penjualan', function ($q) use ($userTokoId) {
                $q->where('toko_id', $userTokoId);
            });
        } else {
            $query = PembayaranPembelian::whereHas('pembelian', function ($q) use ($userTokoId) {
                $q->where('toko_id', $userTokoId);
            });
        }
        
        // Apply date filters
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay()
            ]);
        }
        
        // Apply payment type filter
        if ($this->jenis_pembayaran) {
            $query->where('jenis_pembayaran', $this->jenis_pembayaran);
        }
        
        $data = $query->get();
        
        $stats['total_transaksi'] = $data->count();
        $stats['total_cash'] = $data->where('jenis_pembayaran', 'cash')->count();
        $stats['total_transfer'] = $data->where('jenis_pembayaran', 'transfer')->count();
        
        // Handle different payment types based on report type
        if ($this->jenis_laporan === 'penjualan') {
            $stats['total_kredit'] = $data->where('jenis_pembayaran', 'kredit')->count();
        } else {
            $stats['total_check'] = $data->where('jenis_pembayaran', 'check')->count();
            $stats['total_other'] = $data->where('jenis_pembayaran', 'other')->count();
        }
        
        if ($this->jenis_laporan === 'penjualan') {
            $stats['total_pembayaran'] = $data->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
            $stats['total_kembalian'] = $data->sum('kembalian');
            $stats['nilai_cash'] = $data->where('jenis_pembayaran', 'cash')->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
            $stats['nilai_transfer'] = $data->where('jenis_pembayaran', 'transfer')->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
            $stats['nilai_kredit'] = $data->where('jenis_pembayaran', 'kredit')->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
        } else {
            $stats['total_pembayaran'] = $data->sum('jumlah');
            $stats['total_kembalian'] = 0;
            $stats['nilai_cash'] = $data->where('jenis_pembayaran', 'cash')->sum('jumlah');
            $stats['nilai_transfer'] = $data->where('jenis_pembayaran', 'transfer')->sum('jumlah');
            $stats['nilai_check'] = $data->where('jenis_pembayaran', 'check')->sum('jumlah');
            $stats['nilai_other'] = $data->where('jenis_pembayaran', 'other')->sum('jumlah');
        }
        
        return $stats;
    }
    
    public function getTotalPembayaran()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        if ($this->jenis_laporan === 'penjualan') {
            $query = PembayaranPenjualan::whereHas('penjualan', function ($q) use ($userTokoId) {
                $q->where('toko_id', $userTokoId);
            });
        } else {
            $query = PembayaranPembelian::whereHas('pembelian', function ($q) use ($userTokoId) {
                $q->where('toko_id', $userTokoId);
            });
        }
        
        // Apply date filters
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay()
            ]);
        }
        
        // Apply payment type filter
        if ($this->jenis_pembayaran) {
            $query->where('jenis_pembayaran', $this->jenis_pembayaran);
        }
        
        if ($this->jenis_laporan === 'penjualan') {
            // Untuk penjualan: jumlah - kembalian
            $data = $query->get();
            return $data->sum(function($item) {
                return $item->jumlah - ($item->kembalian ?? 0);
            });
        } else {
            // Untuk pembelian: tetap jumlah saja
            return $query->sum('jumlah');
        }
    }
    
    /**
     * Mendapatkan semua data untuk print (tanpa pagination)
     */
    public function getAllDataForPrint()
    {
        // Dapatkan toko_id dari user yang login melalui tabel akses
        $userTokoId = $this->getUserTokoId();
        
        if ($this->jenis_laporan === 'penjualan') {
            $query = PembayaranPenjualan::with(['penjualan.customer', 'user'])
                ->whereHas('penjualan', function ($q) use ($userTokoId) {
                    // Filter berdasarkan toko_id dari user yang login
                    $q->where('toko_id', $userTokoId);
                    
                    $q->when($this->search, function ($query) {
                        return $query->whereHas('customer', function ($q) {
                            $q->where('nama_customer', 'like', '%' . $this->search . '%');
                        });
                    });
                });
        } else {
            $query = PembayaranPembelian::with(['pembelian.supplier', 'user'])
                ->whereHas('pembelian', function ($q) use ($userTokoId) {
                    // Filter berdasarkan toko_id dari user yang login
                    $q->where('toko_id', $userTokoId);
                    
                    $q->when($this->search, function ($query) {
                        return $query->whereHas('supplier', function ($q) {
                            $q->where('nama_supplier', 'like', '%' . $this->search . '%');
                        });
                    });
                });
        }
        
        // Apply date filters
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->tanggal_mulai)->startOfDay(),
                Carbon::parse($this->tanggal_selesai)->endOfDay()
            ]);
        }
        
        // Apply payment type filter
        if ($this->jenis_pembayaran) {
            $query->where('jenis_pembayaran', $this->jenis_pembayaran);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
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
}; ?>

<div class="bg-gray-50 dark:bg-gray-900">
    <div class="no-print">
        <x-breadcrumbs :items="$breadcrumbs" />
        <x-header />
    </div>

    <div class="mx-auto pace-y-6">
    <style>
    /* Custom Pagination Styling untuk kontras yang lebih baik */
  
    
    .pagination-wrapper .flex a,
    .pagination-wrapper .flex span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
       
        transition: all 0.2s ease-in-out;
    }
    
    /* Light mode - Inactive pagination links */
    .pagination-wrapper .flex a {
        background-color: #ffffff;
        color: #374151;
        border-color: #d1d5db;
    }
    
    .pagination-wrapper .flex a:hover {
        background-color: #f3f4f6;
        color: #1f2937;
        border-color: #9ca3af;
    }
    
    /* Light mode - Active pagination link */
    .pagination-wrapper .flex span[aria-current="page"] {
        background-color: #3b82f6 !important;
        color: #ffffff !important;
        border-color: #3b82f6 !important;
        font-weight: 600;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }
    
    /* Light mode - Disabled pagination links */
    .pagination-wrapper .flex span:not([aria-current]) {
        background-color: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: not-allowed;
    }
    
    /* Dark mode styles */
    @media (prefers-color-scheme: dark) {
        .pagination-wrapper .flex a {
            background-color: #374151;
            color: #d1d5db;
            border-color: #4b5563;
        }
        
        .pagination-wrapper .flex a:hover {
            background-color: #4b5563;
            color: #f9fafb;
            border-color: #6b7280;
        }
        
        .pagination-wrapper .flex span[aria-current="page"] {
            background-color: cyan !important;
            color: #ffffff !important;
            border-color: cyan !important;
        }
        
        .pagination-wrapper .flex span:not([aria-current]) {
            background-color: #1f2937;
            color: #6b7280;
            border-color: #374151;
        }
    }
    
    /* Dark mode class-based (untuk Tailwind dark: prefix) */
    .dark .pagination-wrapper .flex a {
        background-color: #374151;
        color: #d1d5db;
        border-color: #4b5563;
    }
    
    .dark .pagination-wrapper .flex a:hover {
        background-color: #4b5563;
        color: #f9fafb;
        border-color: #6b7280;
    }
    
    .dark .pagination-wrapper .flex span[aria-current="page"] {
        background-color: cyan !important;
        color: #ffffff !important;
        border-color: cyan !important;
    }
    
    .dark .pagination-wrapper .flex span:not([aria-current]) {
        background-color: #1f2937;
        color: #6b7280;
        border-color: #374151;
    }
    
    /* Previous/Next button styling */
    .pagination-wrapper .flex a[rel="prev"],
    .pagination-wrapper .flex a[rel="next"] {
        font-weight: 500;
    }
    
    .pagination-wrapper .flex a[rel="prev"]:hover,
    .pagination-wrapper .flex a[rel="next"]:hover {
        background-color: #e5e7eb;
        color: #1f2937;
    }
    
    .dark .pagination-wrapper .flex a[rel="prev"]:hover,
    .dark .pagination-wrapper .flex a[rel="next"]:hover {
        background-color: #4b5563;
        color: #f9fafb;
    }
    
    /* Dots styling */
    .pagination-wrapper .flex span:not([aria-current]):not([rel]) {
        background-color: transparent !important;
        border-color: transparent !important;
        color: #9ca3af;
        cursor: default;
    }
    
    .dark .pagination-wrapper .flex span:not([aria-current]):not([rel]) {
        color: #6b7280;
    }
    
    /* Simplified print styles for current page */
    @media print {
        .no-print {
            display: none !important;
        }
    }
    </style>
    
        <!-- Main Content Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <!-- Header with Title and Export Buttons -->
            <div class="p-6 sm:px-8 bg-gradient-to-r from-blue-600 to-indigo-600 dark:from-blue-800 dark:to-indigo-800 border-b">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h2 class="font-semibold text-xl text-white leading-tight">
                            Laporan Pembayaran {{ $jenis_laporan === 'penjualan' ? 'Penjualan' : 'Pembelian' }}
                        </h2>
                    </div>
                    
                    <!-- Export Buttons -->
                    <div class="flex flex-col sm:flex-row gap-2 no-print">
                        <button 
                            wire:click="exportPdf" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:bg-red-800 transition duration-150 ease-in-out"
                            wire:loading.attr="disabled"
                            wire:target="exportPdf">
                            <svg wire:loading.remove wire:target="exportPdf" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <svg wire:loading wire:target="exportPdf" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportPdf">Cetak Laporan</span>
                            <span wire:loading wire:target="exportPdf">Printing...</span>
                        </button>
                        
                        <button 
                            wire:click="exportExcel" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:bg-green-800 transition duration-150 ease-in-out"
                            wire:loading.attr="disabled"
                            wire:target="exportExcel">
                            <svg wire:loading.remove wire:target="exportExcel" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <svg wire:loading wire:target="exportExcel" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                            <span wire:loading wire:target="exportExcel">Generating...</span>
                        </button>
                    </div>
                </div>
                
                <!-- Export Helper Text -->
                <div class="mt-3 text-sm text-blue-100 dark:text-blue-200 no-print">
                    <div class="flex flex-col sm:flex-row gap-4 text-xs">
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Cetak laporan langsung</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Excel untuk analisis akuntansi</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 no-print">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="jenis_laporan" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Laporan</label>
                        <select id="jenis_laporan" wire:model.live="jenis_laporan" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="penjualan">Pembayaran Penjualan</option>
                            <option value="pembelian">Pembayaran Pembelian</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="jenis_pembayaran" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Pembayaran</label>
                        <select id="jenis_pembayaran" wire:model.live="jenis_pembayaran" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Semua</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            @if($jenis_laporan === 'penjualan')
                                <option value="kredit">Kredit</option>
                            @else
                                <option value="check">Check</option>
                                <option value="other">Other</option>
                            @endif
                        </select>
                    </div>
                    
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pencarian</label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="text" id="search" wire:model.live.debounce.300ms="search" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 pl-10" placeholder="{{ $jenis_laporan === 'penjualan' ? 'Nama Customer' : 'Nama Supplier' }}">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Mulai</label>
                        <input type="date" id="tanggal_mulai" wire:model.live="tanggal_mulai" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    
                    <div>
                        <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Selesai</label>
                        <input type="date" id="tanggal_selesai" wire:model.live="tanggal_selesai" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    
                    <div class="flex items-end">
                        <button wire:click="resetFilter" class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-800 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-200 active:bg-gray-700 transition">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Reset Filter
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/50 dark:to-indigo-900/50 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 print-info">Ringkasan Laporan</h3>
                    
                    @php
                        $statistik = $this->getStatistik();
                    @endphp
                    
                    <!-- Summary Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $jenis_laporan === 'penjualan' ? 'Total Pembayaran Bersih' : 'Total Pembayaran' }}
                            </p>
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($statistik['total_pembayaran'], 0, ',', '.') }}</p>
                            @if($jenis_laporan === 'penjualan' && $statistik['total_kembalian'] > 0)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                    Kembalian: Rp {{ number_format($statistik['total_kembalian'], 0, ',', '.') }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Transaksi</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($statistik['total_transaksi'], 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">pembayaran</p>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Periode</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">sampai {{ \Carbon\Carbon::parse($tanggal_selesai)->format('d M Y') }}</p>
                        </div>
                        
                        <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Jenis Pembayaran</p>
                            <p class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $jenis_pembayaran ? ucfirst($jenis_pembayaran) : 'Semua' }}</p>
                            @if(!$jenis_pembayaran)
                                @if($jenis_laporan === 'penjualan')
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">cash, transfer, kredit</p>
                                @else
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">cash, transfer, check, other</p>
                                @endif
                            @endif
                        </div>
                    </div>
                    
                    <!-- Payment Method Breakdown -->
                    @if($jenis_laporan === 'penjualan')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 p-4 rounded-lg border border-green-200 dark:border-green-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-800 dark:text-green-300">Cash</p>
                                        <p class="text-lg font-bold text-green-900 dark:text-green-200">{{ $statistik['total_cash'] }} transaksi</p>
                                        <p class="text-sm text-green-700 dark:text-green-400">Rp {{ number_format($statistik['nilai_cash'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-green-100 dark:bg-green-800 rounded-full">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Transfer</p>
                                        <p class="text-lg font-bold text-blue-900 dark:text-blue-200">{{ $statistik['total_transfer'] }} transaksi</p>
                                        <p class="text-sm text-blue-700 dark:text-blue-400">Rp {{ number_format($statistik['nilai_transfer'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-blue-100 dark:bg-blue-800 rounded-full">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/30 dark:to-yellow-800/30 p-4 rounded-lg border border-yellow-200 dark:border-yellow-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Kredit</p>
                                        <p class="text-lg font-bold text-yellow-900 dark:text-yellow-200">{{ $statistik['total_kredit'] }} transaksi</p>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-400">Rp {{ number_format($statistik['nilai_kredit'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-yellow-100 dark:bg-yellow-800 rounded-full">
                                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Payment Method Breakdown for Pembelian -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30 p-4 rounded-lg border border-green-200 dark:border-green-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-green-800 dark:text-green-300">Cash</p>
                                        <p class="text-lg font-bold text-green-900 dark:text-green-200">{{ $statistik['total_cash'] }} transaksi</p>
                                        <p class="text-sm text-green-700 dark:text-green-400">Rp {{ number_format($statistik['nilai_cash'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-green-100 dark:bg-green-800 rounded-full">
                                        <svg class="w-6 h-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-blue-800 dark:text-blue-300">Transfer</p>
                                        <p class="text-lg font-bold text-blue-900 dark:text-blue-200">{{ $statistik['total_transfer'] }} transaksi</p>
                                        <p class="text-sm text-blue-700 dark:text-blue-400">Rp {{ number_format($statistik['nilai_transfer'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-blue-100 dark:bg-blue-800 rounded-full">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 p-4 rounded-lg border border-purple-200 dark:border-purple-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-purple-800 dark:text-purple-300">Check</p>
                                        <p class="text-lg font-bold text-purple-900 dark:text-purple-200">{{ $statistik['total_check'] }} transaksi</p>
                                        <p class="text-sm text-purple-700 dark:text-purple-400">Rp {{ number_format($statistik['nilai_check'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-purple-100 dark:bg-purple-800 rounded-full">
                                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900/30 dark:to-gray-800/30 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-300">Other</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-gray-200">{{ $statistik['total_other'] }} transaksi</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-400">Rp {{ number_format($statistik['nilai_other'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-full">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Table Section -->
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $jenis_laporan === 'penjualan' ? 'Customer' : 'Supplier' }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode Transaksi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Pembayaran</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                {{ $jenis_laporan === 'penjualan' ? 'Jumlah Bersih' : 'Jumlah' }}
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($this->getData() as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        @if($jenis_laporan === 'penjualan')
                                            {{ $item->penjualan->customer->nama_customer ?? '-' }}
                                        @else
                                            {{ $item->pembelian->supplier->nama_supplier ?? '-' }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    @if($jenis_laporan === 'penjualan')
                                        {{ $item->penjualan->nomor_penjualan ?? '-' }}
                                    @else
                                        {{ $item->pembelian->nomor_pembelian ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $paymentTypeConfig = [
                                            'cash' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                            'transfer' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                            'kredit' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                            'check' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                            'other' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
                                        ];
                                        $paymentClass = $paymentTypeConfig[$item->jenis_pembayaran] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $paymentClass }}">
                                        {{ ucfirst($item->jenis_pembayaran) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    @if($jenis_laporan === 'penjualan')
                                        Rp {{ number_format($item->jumlah - ($item->kembalian ?? 0), 0, ',', '.') }}
                                        @if($item->kembalian > 0)
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                (Bayar: Rp {{ number_format($item->jumlah, 0, ',', '.') }}, Kembalian: Rp {{ number_format($item->kembalian, 0, ',', '.') }})
                                            </div>
                                        @endif
                                    @else
                                        Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item->user->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $item->keterangan ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                        
                        @if($this->getData()->isEmpty())
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center justify-center py-8">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="mt-2 text-gray-500 dark:text-gray-400">Tidak ada data yang ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 no-print">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span class="text-sm text-gray-700 dark:text-gray-300 mr-2">Tampilkan</span>
                        <select wire:model.live="perPage" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="text-sm text-gray-700 dark:text-gray-300 ml-2">data per halaman</span>
                    </div>
                    
                    <div class="pagination-wrapper">
                        {{ $this->getData()->links() }}
                    </div>
                </div>
            </div>
        </div>
        

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Listen for print event from Livewire
    Livewire.on('open-print-window', function(eventData) {
        try {
            // Livewire 3 sends event data as array, so get the first element
            const data = Array.isArray(eventData) ? eventData[0] : eventData;
            
            // Debug log
            console.log('Received print data:', data);
            
            // Validate that we have HTML content
            if (!data || !data.html || data.html.trim() === '') {
                alert('Error: Tidak ada data laporan yang dapat dicetak.');
                console.error('Invalid data received:', data);
                return;
            }
            
            // Create new window for printing
            const printWindow = window.open('', '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
            
            if (printWindow) {
                // Write the HTML content to the new window
                printWindow.document.write(data.html);
                printWindow.document.close();
                
                // Wait for content to load then print
                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.print();
                        
                        // Close window after printing (optional)
                        setTimeout(function() {
                            printWindow.close();
                        }, 1000);
                    }, 500);
                };
                
                // Handle error if window fails to load
                printWindow.onerror = function(error) {
                    console.error('Print window error:', error);
                    alert('Terjadi kesalahan saat membuka jendela cetak.');
                };
                
            } else {
                // Fallback if popup is blocked
                alert('Popup diblokir! Silakan izinkan popup untuk mencetak laporan.');
            }
            
        } catch (error) {
            console.error('Print error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        }
    });
});
</script>
