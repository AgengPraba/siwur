<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <div class="grid grid-cols-1 gap-6 pb-6">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 text-white shadow-xl">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-white/20 rounded-lg">
                    <x-icon name="o-clipboard-document-list" class="w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold">Riwayat Transaksi Gudang Stock</h1>
                    <p class="text-blue-100">Pantau semua pergerakan stock dalam gudang</p>
                </div>
            </div>
        </div>

        <!-- Filters and Search Section -->
        <x-card shadow class="border-0 shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6">
                <!-- Search -->
                <div class="md:col-span-2">
                    <x-input 
                        wire:model.live.debounce.500ms="search" 
                        placeholder="Cari barang, gudang, atau tipe transaksi..." 
                        icon="o-magnifying-glass"
                        class="w-full"
                    />
                </div>
                
                <!-- Filter Tipe -->
                <div>
                    <x-select wire:model.live="filterTipe" :options="[
                        ['id' => '', 'name' => 'Semua Tipe'],
                        ['id' => 'masuk', 'name' => 'Stock Masuk'],
                        ['id' => 'keluar', 'name' => 'Stock Keluar']
                    ]" 
                    option-value="id" 
                    option-label="name" 
                    placeholder="Filter Tipe"
                    icon="o-funnel" />
                </div>
                
                <!-- Filter Source -->
                <div>
                    <x-select wire:model.live="filterSource" :options="[
                        ['id' => '', 'name' => 'Semua Sumber'],
                        ['id' => 'pembelian', 'name' => 'Pembelian'],
                        ['id' => 'penjualan', 'name' => 'Penjualan'],
                        ['id' => 'adjustment', 'name' => 'Adjustment']
                    ]" 
                    option-value="id" 
                    option-label="name" 
                    placeholder="Filter Sumber"
                    icon="o-tag" />
                </div>
            </div>
        </x-card>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 border border-green-200 rounded-xl p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <x-icon name="o-arrow-down" class="w-6 h-6 text-green-600" />
                    </div>
                    <div>
                        <p class="text-sm text-green-600 font-medium">Stock Masuk</p>
                        <p class="text-2xl font-bold text-green-800">{{ $transaksi_gudang_stock_data->where('tipe', 'masuk')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <x-icon name="o-arrow-up" class="w-6 h-6 text-red-600" />
                    </div>
                    <div>
                        <p class="text-sm text-red-600 font-medium">Stock Keluar</p>
                        <p class="text-2xl font-bold text-red-800">{{ $transaksi_gudang_stock_data->where('tipe', 'keluar')->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <x-icon name="o-clipboard-document-list" class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Transaksi</p>
                        <p class="text-2xl font-bold text-blue-800">{{ $transaksi_gudang_stock_data->total() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <x-card shadow class="border-0 shadow-lg">
            <div class="overflow-hidden">
                @if (!$transaksi_gudang_stock_data->isEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[800px]">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        #
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" 
                                        wire:click="sortBy('created_at')">
                                        <div class="flex items-center gap-2">
                                            <span>Tanggal</span>
                                            @if ($sortField === 'created_at')
                                                @if ($sortDirection === 'asc')
                                                    <x-icon name="o-chevron-up" class="w-4 h-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Barang & Gudang
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sumber Transaksi
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" 
                                        wire:click="sortBy('tipe')">
                                        <div class="flex items-center gap-2">
                                            <span>Tipe</span>
                                            @if ($sortField === 'tipe')
                                                @if ($sortDirection === 'asc')
                                                    <x-icon name="o-chevron-up" class="w-4 h-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" 
                                        wire:click="sortBy('jumlah')">
                                        <div class="flex items-center gap-2">
                                            <span>Jumlah</span>
                                            @if ($sortField === 'jumlah')
                                                @if ($sortDirection === 'asc')
                                                    <x-icon name="o-chevron-up" class="w-4 h-4" />
                                                @else
                                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                                @endif
                                            @endif
                                        </div>
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($transaksi_gudang_stock_data as $transaksi_gudang_stock)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span class="font-medium">{{ $start + $loop->index }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <div>
                                                <div class="font-medium">{{ $transaksi_gudang_stock->created_at->format('d/m/Y') }}</div>
                                                <div class="text-gray-500 text-xs">{{ $transaksi_gudang_stock->created_at->format('H:i') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div>
                                                <div class="font-medium">{{ $transaksi_gudang_stock->gudangStock->barang->nama ?? '-' }}</div>
                                                <div class="text-gray-500 text-xs">
                                                    <x-icon name="o-building-storefront" class="w-3 h-3 inline mr-1" />
                                                    {{ $transaksi_gudang_stock->gudangStock->gudang->nama ?? '-' }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if($transaksi_gudang_stock->isPembelian())
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="o-shopping-cart" class="w-4 h-4 text-blue-500" />
                                                    <span>Pembelian #{{ $transaksi_gudang_stock->pembelianDetail->pembelian->id ?? '-' }}</span>
                                                </div>
                                            @elseif($transaksi_gudang_stock->isPenjualan())
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="o-banknotes" class="w-4 h-4 text-green-500" />
                                                    <span>Penjualan #{{ $transaksi_gudang_stock->penjualanDetail->penjualan->id ?? '-' }}</span>
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="o-adjustments-horizontal" class="w-4 h-4 text-purple-500" />
                                                    <span>Adjustment</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($transaksi_gudang_stock->tipe === 'masuk')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <x-icon name="o-arrow-down" class="w-3 h-3" />
                                                    Stock Masuk
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <x-icon name="o-arrow-up" class="w-3 h-3" />
                                                    Stock Keluar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <div class="text-right">
                                                <div class="font-bold text-lg">{{ number_format($transaksi_gudang_stock->jumlah, 0) }}</div>
                                                @if($transaksi_gudang_stock->konversi_satuan_terkecil > 0)
                                                    <div class="text-xs text-gray-500">
                                                        ({{ number_format($transaksi_gudang_stock->konversi_satuan_terkecil, 0) }} unit kecil)
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <x-button 
                                                icon="o-eye" 
                                                class="btn-sm bg-blue-500 hover:bg-blue-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                                :href="route('transaksi-gudang-stock.show', $transaksi_gudang_stock->id)" 
                                                wire:navigate
                                                tooltip="Lihat Detail"
                                            >
                                                Detail
                                            </x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="flex flex-col items-center gap-4">
                            <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-full">
                                <x-icon name="o-clipboard-document-list" class="w-12 h-12 text-gray-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tidak Ada Transaksi</h3>
                                <p class="text-gray-500 dark:text-gray-400">Belum ada transaksi gudang stock yang tercatat</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Pagination -->
                @if(!$transaksi_gudang_stock_data->isEmpty())
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        <x-pagination :rows="$transaksi_gudang_stock_data" wire:model.live="perPage" />
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <x-back-refresh />
</div>
