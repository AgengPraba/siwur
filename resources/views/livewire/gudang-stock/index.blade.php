<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="gap-4 pb-4 mt-6">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        <x-icon name="o-building-storefront" class="size-8 inline mr-3 text-blue-600" />
                        Data Stok Gudang
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Monitoring dan laporan stok barang di seluruh gudang
                    </p>
                </div>
                <div
                    class="hidden md:flex items-center space-x-2 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 px-4 py-2 rounded-lg">
                    <x-icon name="o-cube" class="size-5 text-blue-600" />
                    <span class="text-sm font-medium text-blue-700 dark:text-blue-300">
                        Total: {{ $gudang_stock_data->total() }} Item
                    </span>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex-1 max-w-md">
                        <div class="relative">
                            <x-icon name="o-magnifying-glass"
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 size-5 text-gray-400" />
                            <x-input wire:model.live.debounce.500ms="search" autocomplete="off"
                                placeholder="Cari gudang, barang, satuan, atau jumlah stok..."
                                class="pl-10 bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600" />
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-dropdown label="Cetak" class="btn-primary" right>
                            <x-menu-item title="Harian" icon="o-calendar" wire:click="exportPDFDaily" />
                            <x-menu-item title="Mingguan" icon="o-numbered-list" wire:click="exportPDFWeekly" />
                            <x-menu-item title="Bulanan" icon="o-calendar-days" wire:click="exportPDFMonthly" />
                            <x-menu-item title="Custom" icon="o-adjustments-horizontal" wire:click="exportPDFCustom" />
                        </x-dropdown>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800 border-b border-gray-200 dark:border-gray-600">
                                <th class="px-6 py-4 text-left">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">#</span>
                                </th>
                                <th class="px-6 py-4 text-left cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    wire:click="sortBy('nama_gudang')">
                                    <div class="flex items-center gap-2 select-none">
                                        <x-icon name="o-building-storefront" class="size-4 text-blue-600" />
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Nama
                                            Gudang</span>
                                        @if ($sortField === 'nama_gudang')
                                            @if ($sortDirection === 'asc')
                                                <x-icon name="o-chevron-up" class="size-4 text-blue-600" />
                                            @else
                                                <x-icon name="o-chevron-down" class="size-4 text-blue-600" />
                                            @endif
                                        @else
                                            <x-icon name="o-chevron-up-down" class="size-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    wire:click="sortBy('nama_barang')">
                                    <div class="flex items-center gap-2 select-none">
                                        <x-icon name="o-cube" class="size-4 text-green-600" />
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Nama
                                            Barang</span>
                                        @if ($sortField === 'nama_barang')
                                            @if ($sortDirection === 'asc')
                                                <x-icon name="o-chevron-up" class="size-4 text-green-600" />
                                            @else
                                                <x-icon name="o-chevron-down" class="size-4 text-green-600" />
                                            @endif
                                        @else
                                            <x-icon name="o-chevron-up-down" class="size-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    wire:click="sortBy('satuan_terkecil')">
                                    <div class="flex items-center gap-2 select-none">
                                        <x-icon name="o-scale" class="size-4 text-orange-600" />
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Satuan
                                            Terkecil</span>
                                        @if ($sortField === 'satuan_terkecil')
                                            @if ($sortDirection === 'asc')
                                                <x-icon name="o-chevron-up" class="size-4 text-orange-600" />
                                            @else
                                                <x-icon name="o-chevron-down" class="size-4 text-orange-600" />
                                            @endif
                                        @else
                                            <x-icon name="o-chevron-up-down" class="size-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-left cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    wire:click="sortBy('jumlah')">
                                    <div class="flex items-center gap-2 select-none">
                                        <x-icon name="o-calculator" class="size-4 text-purple-600" />
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Jumlah
                                            Stok</span>
                                        @if ($sortField === 'jumlah')
                                            @if ($sortDirection === 'asc')
                                                <x-icon name="o-chevron-up" class="size-4 text-purple-600" />
                                            @else
                                                <x-icon name="o-chevron-down" class="size-4 text-purple-600" />
                                            @endif
                                        @else
                                            <x-icon name="o-chevron-up-down" class="size-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-4 text-center">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Detail</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if (!$gudang_stock_data->isEmpty())
                                @foreach ($gudang_stock_data as $gudang_stock)
                          
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div
                                                class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                    {{ $start + $loop->index }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                                    <x-icon name="o-building-storefront" class="size-5 text-blue-600" />
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $gudang_stock->nama_gudang }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        ID: {{ $gudang_stock->gudang_id }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                                                    <x-icon name="o-cube" class="size-5 text-green-600" />
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $gudang_stock->nama_barang }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        ID: {{ $gudang_stock->barang_id }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="flex-shrink-0 w-10 h-10 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center">
                                                    <x-icon name="o-scale" class="size-5 text-orange-600" />
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $gudang_stock->satuan_terkecil ?? 'N/A' }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        Satuan dasar
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                              
                                                @php
                                                    $stockLevel = $gudang_stock->jumlah;
                                                    $stockColor =
                                                        $stockLevel > 50
                                                            ? 'green'
                                                            : ($stockLevel > 20
                                                                ? 'yellow'
                                                                : 'red');
                                                    $stockBg =
                                                        $stockLevel > 50
                                                            ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                                            : ($stockLevel > 20
                                                                ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
                                                                : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400');
                                                @endphp
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stockBg }}">
                                                    <span
                                                        class="w-2 h-2 {{ $stockLevel > 50 ? 'bg-green-400' : ($stockLevel > 20 ? 'bg-yellow-400' : 'bg-red-400') }} rounded-full mr-1.5"></span>
                                                    {{ number_format($gudang_stock->jumlah, 2, '.', '') }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <x-button icon="o-eye"
                                                class="btn-sm bg-blue-600 hover:bg-blue-700 text-white border-0 shadow-sm"
                                                :href="route('gudang-stock.show', $gudang_stock->id)" wire:navigate>
                                                Lihat Detail
                                            </x-button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-6 py-12 text-center" colspan="6">
                                        <div class="flex flex-col items-center justify-center gap-4">
                                            <div
                                                class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                <x-icon name="o-archive-box-x-mark" class="size-8 text-gray-400" />
                                            </div>
                                            <div class="text-center">
                                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">Tidak Ada
                                                    Data</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data stok
                                                    gudang yang tersedia</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if (!$gudang_stock_data->isEmpty())
                    <div
                        class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                        <x-pagination :rows="$gudang_stock_data" wire:model.live="perPage" />
                    </div>
                @endif
            </div>
        </div>

        <x-back-refresh />
    </div>
</div>
