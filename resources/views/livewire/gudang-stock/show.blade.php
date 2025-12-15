<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="space-y-8 mt-6">
        <!-- Stock Information Card -->
        <x-card
            class="bg-gradient-to-br from-blue-50 via-indigo-50 to-blue-100 dark:from-gray-800 dark:via-indigo-900/30 dark:to-gray-900 overflow-hidden relative"
            shadow>
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-5 dark:opacity-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 100 100"
                    preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="currentColor" stroke-width="0.5" />
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)" />
                </svg>
            </div>

            <div class="flex flex-col md:flex-row items-center justify-between mb-8 relative z-10 gap-6">
                <!-- Icon & Title -->
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div
                            class="p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-xl shadow-lg">
                            <x-icon name="o-archive-box" class="w-9 h-9 text-white" />
                        </div>
                        <div
                            class="absolute -top-2 -right-2 w-5 h-5 bg-yellow-400 rounded-full flex items-center justify-center shadow">
                            <x-icon name="o-star" class="w-3 h-3 text-white" />
                        </div>
                        <div class="absolute -bottom-1 -left-1 w-3.5 h-3.5 bg-green-400 rounded-full opacity-80"></div>
                    </div>
                    <div>
                        <h1
                            class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-gray-900 via-blue-800 to-indigo-900 dark:from-white dark:via-blue-200 dark:to-indigo-200 bg-clip-text text-transparent">
                            Detail Stock Gudang
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 text-base font-medium">
                            Informasi lengkap stock barang di gudang
                        </p>
                        <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <div class="flex items-center space-x-1">
                                <x-icon name="o-clock" class="w-4 h-4" />
                                <span>Update: {{ $gudang_stock_data->updated_at->diffForHumans() }}</span>
                            </div>
                            <div class="w-1 h-1 bg-gray-400 rounded-full"></div>
                            <div class="flex items-center space-x-1">
                                <x-icon name="o-cube" class="w-4 h-4" />
                                <span>ID: #{{ $gudang_stock_data->id }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Status & Quick Stats (Gabung) -->
                @php
                    $stockStatus = '';
                    $stockIcon = '';
                    $stockGradient = '';
                    $stockBg = '';
                    if ($gudang_stock_data->jumlah > 50) {
                        $stockStatus = 'Stok Aman';
                        $stockIcon = 'o-check-circle';
                        $stockGradient = 'from-green-500 to-emerald-600';
                        $stockBg = 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800';
                    } elseif ($gudang_stock_data->jumlah > 20) {
                        $stockStatus = 'Stok Sedang';
                        $stockIcon = 'o-exclamation-triangle';
                        $stockGradient = 'from-amber-500 to-orange-600';
                        $stockBg = 'bg-amber-50 dark:bg-amber-900/20 border-amber-200 dark:border-amber-800';
                    } else {
                        $stockStatus = 'Stok Rendah';
                        $stockIcon = 'o-exclamation-circle';
                        $stockGradient = 'from-red-500 to-rose-600';
                        $stockBg = 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800';
                    }
                @endphp
                <div class="flex flex-col md:flex-row items-center gap-4">
                    <div class="flex items-center px-5 py-3 {{ $stockBg }} border-2 rounded-xl shadow-md">
                        <div class="p-2 bg-gradient-to-br {{ $stockGradient }} rounded-lg shadow">
                            <x-icon :name="$stockIcon" class="w-5 h-5 text-white" />
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-bold text-gray-900 dark:text-white">{{ $stockStatus }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400">Status Stock</div>
                        </div>
                        <div class="mx-4 h-8 border-l border-gray-200 dark:border-gray-700"></div>
                        <div class="flex flex-col items-start">
                            <div class="flex items-center space-x-1 text-blue-600 dark:text-blue-400">
                                <x-icon name="o-scale" class="w-4 h-4" />
                                <span class="font-medium">{{ number_format($gudang_stock_data->jumlah, 2) }}</span>
                                <span
                                    class="text-gray-500 dark:text-gray-400 ml-1">{{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 relative z-10">
                <!-- Barang Info -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2">
                        <x-icon name="o-cube" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Informasi Barang</label>
                    </div>
                    <div
                        class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="space-y-3">

                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Nama:</span>
                                <span
                                    class="text-sm font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">{{ $gudang_stock_data->nama_barang }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Satuan:</span>
                                <span
                                    class="text-sm font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">{{ $gudang_stock_data->satuan_terkecil ?? '-' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Keterangan:</span>
                                <span
                                    class="text-sm font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">{{ $gudang_stock_data->keterangan ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gudang Info -->
                <div class="space-y-3">
                    <div class="flex items-center space-x-2">
                        <x-icon name="o-building-storefront" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Informasi Gudang</label>
                    </div>
                    <div
                        class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-shadow duration-300">
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Nama:</span>
                                <span
                                    class="text-sm font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">{{ $gudang_stock_data->nama_gudang }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Stock ID:</span>
                                <span
                                    class="text-sm font-medium bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-md">#{{ $gudang_stock_data->id }}</span>
                            </div>
                        </div>
                    </div>
                </div>



               
            </div>
        </x-card>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Masuk Card -->
            <x-card
                class="bg-gradient-to-br from-green-50 via-emerald-50 to-green-100 dark:from-green-900/30 dark:via-emerald-900/20 dark:to-green-900/10 overflow-hidden relative group"
                shadow>
                <!-- Background Pattern -->
                <div
                    class="absolute inset-0 opacity-10 dark:opacity-20 transition-opacity duration-300 group-hover:opacity-20 dark:group-hover:opacity-30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 100 100"
                        preserveAspectRatio="none">
                        <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="currentColor"
                            stroke-width="0.5" stroke-dasharray="5,5" />
                    </svg>
                </div>

                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl shadow-lg">
                            <x-icon name="o-arrow-trending-up" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                {{ number_format($stockStats['total_masuk_satuan_terkecil'], 2) }}
                            </div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Masuk</div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-500 mt-1 bg-green-100 dark:bg-green-900/30 px-2 py-0.5 rounded-full inline-block">
                                {{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block opacity-20 dark:opacity-10">
                        <x-icon name="o-arrow-trending-up" class="w-16 h-16 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </x-card>

            <!-- Total Keluar Card -->
            <x-card
                class="bg-gradient-to-br from-red-50 via-rose-50 to-red-100 dark:from-red-900/30 dark:via-rose-900/20 dark:to-red-900/10 overflow-hidden relative group"
                shadow>
                <!-- Background Pattern -->
                <div
                    class="absolute inset-0 opacity-10 dark:opacity-20 transition-opacity duration-300 group-hover:opacity-20 dark:group-hover:opacity-30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 100 100"
                        preserveAspectRatio="none">
                        <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="currentColor"
                            stroke-width="0.5" stroke-dasharray="5,5" />
                    </svg>
                </div>

                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-gradient-to-br from-red-500 to-rose-600 rounded-xl shadow-lg">
                            <x-icon name="o-arrow-trending-down" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                                {{ number_format($stockStats['total_keluar_satuan_terkecil'], 2) }}
                            </div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Keluar</div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-500 mt-1 bg-red-100 dark:bg-red-900/30 px-2 py-0.5 rounded-full inline-block">
                                {{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block opacity-20 dark:opacity-10">
                        <x-icon name="o-arrow-trending-down" class="w-16 h-16 text-red-600 dark:text-red-400" />
                    </div>
                </div>
            </x-card>

            <!-- Saldo Stock Card -->
            <x-card
                class="bg-gradient-to-br from-purple-50 via-violet-50 to-purple-100 dark:from-purple-900/30 dark:via-violet-900/20 dark:to-purple-900/10 overflow-hidden relative group"
                shadow>
                <!-- Background Pattern -->
                <div
                    class="absolute inset-0 opacity-10 dark:opacity-20 transition-opacity duration-300 group-hover:opacity-20 dark:group-hover:opacity-30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 100 100"
                        preserveAspectRatio="none">
                        <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="currentColor"
                            stroke-width="0.5" stroke-dasharray="5,5" />
                    </svg>
                </div>

                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-gradient-to-br from-purple-500 to-violet-600 rounded-xl shadow-lg">
                            <x-icon name="o-chart-bar" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                {{ number_format($stockStats['saldo_satuan_terkecil'], 2) }}
                            </div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Saldo Stock</div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-500 mt-1 bg-purple-100 dark:bg-purple-900/30 px-2 py-0.5 rounded-full inline-block">
                                {{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block opacity-20 dark:opacity-10">
                        <x-icon name="o-chart-bar" class="w-16 h-16 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
            </x-card>

            <!-- Total Transaksi Card -->
            <x-card
                class="bg-gradient-to-br from-blue-50 via-cyan-50 to-blue-100 dark:from-blue-900/30 dark:via-cyan-900/20 dark:to-blue-900/10 overflow-hidden relative group"
                shadow>
                <!-- Background Pattern -->
                <div
                    class="absolute inset-0 opacity-10 dark:opacity-20 transition-opacity duration-300 group-hover:opacity-20 dark:group-hover:opacity-30">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-full h-full" viewBox="0 0 100 100"
                        preserveAspectRatio="none">
                        <path d="M0,0 L100,0 L100,100 L0,100 Z" fill="none" stroke="currentColor"
                            stroke-width="0.5" stroke-dasharray="5,5" />
                    </svg>
                </div>

                <div class="flex items-center justify-between relative z-10">
                    <div class="flex items-center space-x-4">
                        <div class="p-4 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl shadow-lg">
                            <x-icon name="o-document-text" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($stockStats['total_transaksi'], 0) }}
                            </div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Transaksi</div>
                            <div
                                class="text-xs text-gray-500 dark:text-gray-500 mt-1 bg-blue-100 dark:bg-blue-900/30 px-2 py-0.5 rounded-full inline-block">
                                Riwayat
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:block opacity-20 dark:opacity-10">
                        <x-icon name="o-document-text" class="w-16 h-16 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Transaction History -->
        <x-card shadow separator>
            <x-slot:title>
                <div class="flex items-center space-x-2">
                    <x-icon name="o-clock" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    <span class="text-lg font-semibold">Riwayat Transaksi Stock</span>
                </div>
            </x-slot:title>

            <x-slot:menu>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Urutan: Terlama</span>
                        <x-icon name="o-arrow-up" class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    </div>
                    {{-- <x-button wire:click="hitungUlangStock" spinner="hitungUlangStock" size="sm"
                        icon="o-arrow-path" class="bg-indigo-600 hover:bg-indigo-700 text-white">
                        Hitung Ulang Stock
                    </x-button> --}}
                </div>
            </x-slot:menu>

            @php
                // Menghitung stock awal
                // Stock awal = stock saat ini - (total masuk - total keluar)
                $stockAwal = 0;
            @endphp

            @if ($transactions->count() > 0)
                <!-- Stock Awal Info -->
                <div
                    class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <x-icon name="o-scale" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                            <span class="font-medium text-gray-700 dark:text-gray-300">Stock Awal:</span>
                        </div>
                        <div class="font-bold text-blue-700 dark:text-blue-300 text-lg">
                            {{ number_format($stockAwal, 2) }} {{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                                <th
                                    class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300 rounded-tl-lg">
                                    Tanggal</th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Tipe
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Sumber
                                </th>
                                <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">
                                    Referensi</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Jumlah
                                </th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">
                                    Konversi</th>
                                <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Total
                                    (Satuan Terkecil)</th>
                                <th
                                    class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 rounded-tr-lg">
                                    Stock Setelah Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @php
                                /**
                                 * Perhitungan Stock Berjalan
                                 *
                                 * 1. Menghitung stock awal:
                                 *    Stock awal = stock saat ini - (total masuk - total keluar)
                                 *    Ini memberikan nilai stock sebelum ada transaksi apapun
                                 *
                                 * 2. Menghitung stock berjalan untuk setiap transaksi:
                                 *    - Mulai dari stock awal
                                 *    - Untuk setiap transaksi (diurutkan dari terlama ke terbaru):
                                 *      - Simpan stock sebelum transaksi
                                 *      - Tambahkan/kurangkan jumlah transaksi (dalam satuan terkecil)
                                 *      - Hasilnya adalah stock setelah transaksi
                                 */

                                // Gunakan variabel $stockAwal yang sudah dideklarasikan sebelumnya
                                $runningStock = $stockAwal;

                                // Menghitung stock berjalan untuk setiap transaksi
                                $stockHistory = [];

                                // Hitung stock berjalan untuk setiap transaksi
                                // Transaksi sudah diurutkan dari terlama ke terbaru di controller
                                foreach ($transactions as $trans) {
                                    $totalSatuanTerkecil = $trans->jumlah * $trans->konversi_satuan_terkecil;

                                    // Simpan stock sebelum transaksi
                                    $stockHistory[$trans->id] = $runningStock;

                                    // Update running stock berdasarkan tipe transaksi
                                    if ($trans->tipe == 'masuk') {
                                        $runningStock += $totalSatuanTerkecil;
                                    } else {
                                        $runningStock -= $totalSatuanTerkecil;
                                    }
                                }
                            @endphp

                            @foreach ($transactions as $transaction)
                                @php
                                    $totalSatuanTerkecil =
                                        $transaction->jumlah * $transaction->konversi_satuan_terkecil;
                                    $stockBefore = $stockHistory[$transaction->id] ?? 0;

                                    if ($transaction->tipe == 'masuk') {
                                        $stockAfter = $stockBefore + $totalSatuanTerkecil;
                                    } else {
                                        $stockAfter = $stockBefore - $totalSatuanTerkecil;
                                    }
                                @endphp

                                <tr
                                    class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                    <td class="px-4 py-3">
                                        <div class="text-sm">
                                            <div class="font-medium">{{ $transaction->created_at->format('d/m/Y') }}
                                            </div>
                                            <div class="text-gray-500">{{ $transaction->created_at->format('H:i') }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($transaction->tipe == 'masuk')
                                            <div class="flex items-center space-x-1">
                                                <span
                                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-100 dark:bg-green-900/30">
                                                    <x-icon name="o-arrow-down"
                                                        class="w-3.5 h-3.5 text-green-600 dark:text-green-400" />
                                                </span>
                                                <span
                                                    class="font-medium text-green-600 dark:text-green-400">Masuk</span>
                                            </div>
                                        @else
                                            <div class="flex items-center space-x-1">
                                                <span
                                                    class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 dark:bg-red-900/30">
                                                    <x-icon name="o-arrow-up"
                                                        class="w-3.5 h-3.5 text-red-600 dark:text-red-400" />
                                                </span>
                                                <span class="font-medium text-red-600 dark:text-red-400">Keluar</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $source = $transaction->getSource();
                                        @endphp
                                        @if ($source == 'pembelian')
                                            <div
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                                <x-icon name="o-shopping-cart" class="w-3.5 h-3.5 mr-1" />
                                                Pembelian
                                            </div>
                                        @elseif($source == 'penjualan')
                                            <div
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                                <x-icon name="o-shopping-bag" class="w-3.5 h-3.5 mr-1" />
                                                Penjualan
                                            </div>
                                        @else
                                            <div
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                                <x-icon name="o-adjustments-horizontal" class="w-3.5 h-3.5 mr-1" />
                                                Adjustment
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($transaction->pembelianDetail)
                                            <div class="flex items-start space-x-2">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <span
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                                        <x-icon name="o-building-storefront" class="w-4 h-4" />
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        {{ $transaction->pembelianDetail->pembelian->supplier->nama_supplier ?? 'N/A' }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                        <x-icon name="o-book-open" class="w-3.5 h-3.5 mr-1" />
                                                        #{{ $transaction->pembelianDetail->pembelian_id ?? '' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @elseif($transaction->penjualanDetail)
                                            <div class="flex items-start space-x-2">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <span
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                                        <x-icon name="o-user" class="w-4 h-4" />
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        {{ $transaction->penjualanDetail->penjualan->customer->nama_customer ?? 'N/A' }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                        <x-icon name="o-book-open" class="w-3.5 h-3.5 mr-1" />
                                                        #{{ $transaction->penjualanDetail->penjualan_id ?? '' }}
                                                    </p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center text-gray-500 dark:text-gray-400">
                                                <x-icon name="o-minus" class="w-4 h-4 mr-1" />
                                                <span>Tidak ada referensi</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center justify-end space-x-1">
                                            <span
                                                class="font-medium {{ $transaction->tipe == 'masuk' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $transaction->tipe == 'masuk' ? '+' : '-' }}{{ number_format($transaction->jumlah, 2) }}
                                            </span>
                                            <span class="text-gray-500 dark:text-gray-400 text-xs">
                                                {{ $transaction->satuan ?? '' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div
                                            class="inline-flex items-center justify-end px-2 py-1 rounded-md bg-gray-50 dark:bg-gray-800">
                                            <span class="text-gray-600 dark:text-gray-400 font-mono">
                                                × {{ number_format($transaction->konversi_satuan_terkecil, 2) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex flex-col items-end">
                                            <span
                                                class="font-medium {{ $transaction->tipe == 'masuk' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $transaction->tipe == 'masuk' ? '+' : '-' }}{{ number_format($totalSatuanTerkecil, 2) }}
                                            </span>
                                            <div
                                                class="text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 px-2 py-0.5 rounded-md mt-1">
                                                {{ number_format($transaction->jumlah, 2) }} ×
                                                {{ number_format($transaction->konversi_satuan_terkecil, 2) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-block">
                                            <div
                                                class="px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                                                <div class="font-medium text-blue-700 dark:text-blue-300">
                                                    {{ number_format($stockAfter, 2) }}
                                                </div>
                                                <div class="text-xs text-blue-500 dark:text-blue-400 mt-0.5">
                                                    {{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-16 px-4">
                    <div class="relative mx-auto w-24 h-24 mb-6">
                        <div class="absolute inset-0 bg-indigo-100 dark:bg-indigo-900/30 rounded-full animate-pulse">
                        </div>
                        <div
                            class="absolute inset-2 bg-white dark:bg-gray-900 rounded-full flex items-center justify-center">
                            <x-icon name="o-document-text" class="w-10 h-10 text-indigo-500 dark:text-indigo-400" />
                        </div>
                        <div
                            class="absolute -top-1 -right-1 w-6 h-6 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                            <x-icon name="o-clock" class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">Belum Ada Transaksi</h3>
                    <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto mb-6">Transaksi stock akan muncul di
                        sini setelah ada aktivitas pembelian atau penjualan untuk item ini.</p>
                    <div
                        class="flex flex-col sm:flex-row items-center justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <a href="#"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                            <x-icon name="o-shopping-cart" class="w-4 h-4 mr-2" />
                            Buat Pembelian
                        </a>
                        <a href="#"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-150">
                            <x-icon name="o-adjustments-horizontal" class="w-4 h-4 mr-2" />
                            Adjustment Stock
                        </a>
                    </div>
                </div>
            @endif



            <x-slot:actions>
                <div class="flex justify-between items-center w-full">
                    <x-button :href="route('gudang-stock.index')" wire:navigate class="btn-primary" icon="o-arrow-left">
                        Kembali ke Daftar
                    </x-button>

                    <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                        <x-icon name="o-information-circle" class="w-4 h-4" />
                        <span>Data terakhir diperbarui: {{ $gudang_stock_data->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </x-slot:actions>
        </x-card>
    </div>

    <div class="mt-6 flex justify-center">
        <div
            class="inline-flex items-center space-x-2 bg-white dark:bg-gray-800 rounded-full shadow-md px-4 py-2 border border-gray-100 dark:border-gray-700">
            <a href="{{ url()->previous() }}"
                class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors duration-150">
                <x-icon name="o-arrow-left" class="w-4 h-4" />
            </a>
            <div class="w-px h-5 bg-gray-200 dark:bg-gray-700"></div>
            <button onclick="window.location.reload()"
                class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-indigo-100 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors duration-150">
                <x-icon name="o-arrow-path" class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Modal Konfirmasi Hitung Ulang Stock -->
    <x-modal title="Konfirmasi Hitung Ulang Stock" wire:model="showKonfirmasiModal">
        <div class="space-y-4">
            <div
                class="flex items-center space-x-3 text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border border-amber-100 dark:border-amber-800">
                <x-icon name="o-exclamation-triangle" class="w-6 h-6 flex-shrink-0" />
                <p class="text-sm">Apakah Anda yakin ingin menghitung ulang stock? Proses ini akan memperbarui jumlah
                    stock berdasarkan semua transaksi yang ada.</p>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Barang:</span>
                    <span
                        class="text-sm text-gray-900 dark:text-white font-semibold">{{ $gudang_stock_data->nama_barang }}</span>
                </div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Gudang:</span>
                    <span
                        class="text-sm text-gray-900 dark:text-white font-semibold">{{ $gudang_stock_data->nama_gudang }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Stock Saat Ini:</span>
                    <span
                        class="text-sm text-blue-600 dark:text-blue-400 font-semibold">{{ number_format($gudang_stock_data->jumlah, 2) }}
                        {{ $gudang_stock_data->satuan_terkecil ?? 'unit' }}</span>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <div class="flex justify-end space-x-2">
                <x-button label="Batal" wire:click="$set('showKonfirmasiModal', false)" class="btn-outline" />
                <x-button label="Ya, Hitung Ulang" wire:click="prosesHitungUlangStock" class="btn-primary"
                    spinner="prosesHitungUlangStock" />
            </div>
        </x-slot:actions>
    </x-modal>
</div>
