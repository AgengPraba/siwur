
<div class="bg-gray-50 dark:bg-gray-900">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <!-- Header Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/20 rounded-lg flex items-center justify-center">
                                <x-icon name="o-arrow-uturn-left" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                            </div>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Retur Penjualan</h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <span class="font-medium">{{ $returPenjualan->nomor_retur }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $returPenjualan->tanggal_retur_formatted }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-button icon="o-printer" wire:click="print"
                        target="_blank"
                            class="btn-primary btn-sm">
                            Print
                        </x-button>
                        <x-button icon="o-arrow-left" :href="route('retur-penjualan.index')" wire:navigate 
                            class="btn-outline btn-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                            Kembali
                        </x-button>
                    </div>
                </div>
            </div>

            <!-- Summary Badge -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                            <x-icon name="o-arrow-uturn-left" class="w-4 h-4 mr-1.5" />
                            Retur Penjualan
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300">
                            <x-icon name="o-cube" class="w-4 h-4 mr-1.5" />
                            {{ $returPenjualan->total_items }} Item
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $returPenjualan->total_formatted }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Nilai Retur</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informasi Retur -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="o-document-text" class="w-5 h-5 mr-2 text-blue-600 dark:text-blue-400" />
                        Informasi Retur
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Nomor Retur</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $returPenjualan->nomor_retur }}</dd>
                        </div>
                    </div>
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Retur</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $returPenjualan->tanggal_retur_formatted }}</dd>
                        </div>
                    </div>
                    @if ($returPenjualan->keterangan)
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Keterangan</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700 rounded-lg p-3">{{ $returPenjualan->keterangan }}</dd>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Informasi Penjualan -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="o-shopping-bag" class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" />
                        Penjualan Terkait
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Nomor Penjualan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $returPenjualan->penjualan->nomor_penjualan }}</dd>
                        </div>
                    </div>
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal Penjualan</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ $returPenjualan->penjualan->tanggal_penjualan }}</dd>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                        <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Customer</dt>
                        <dd class="mt-1">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-full flex items-center justify-center">
                                    <x-icon name="o-user" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $returPenjualan->penjualan->customer->nama_customer ?? '-' }}</p>
                                    @if (($returPenjualan->penjualan->customer->no_hp ?? null))
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ $returPenjualan->penjualan->customer->no_hp }}</p>
                                    @endif
                                </div>
                            </div>
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Informasi Audit -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="o-clock" class="w-5 h-5 mr-2 text-purple-600 dark:text-purple-400" />
                        Informasi Audit
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <dt class="text-sm font-medium text-gray-600 dark:text-gray-400">Dibuat</dt>
                            <dd class="mt-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $returPenjualan->created_at_formatted }}</p>
                                @if ($returPenjualan->dibuatOleh)
                                    <p class="text-xs text-gray-600 dark:text-gray-400">oleh {{ $returPenjualan->dibuatOleh->name }}</p>
                                @endif
                            </dd>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Detail Items -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                        <x-icon name="o-cube" class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" />
                        Item yang Diretur
                    </h3>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-300">
                        {{ count($returPenjualan->details) }} Item
                    </span>
                </div>
            </div>

            <div class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    No
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Nama Barang
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Satuan
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Qty Retur
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Harga Jual
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Total
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Alasan Retur
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($returPenjualan->details as $index => $detail)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-8 h-8 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center mr-3">
                                                <x-icon name="o-cube" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $detail->barang->nama_barang ?? '-' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ $detail->satuan->nama_satuan ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                        {{ number_format($detail->qty_retur, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900 dark:text-white">
                                        Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($detail->total_harga, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300">
                                            <x-icon name="o-exclamation-triangle" class="w-3 h-3 mr-1" />
                                            {{ $alasanReturLabels[$detail->alasan_retur] ?? $detail->alasan_retur }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Total {{ count($returPenjualan->details) }} item diretur
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Nilai Retur</p>
                        <p class="text-xl font-bold text-orange-600 dark:text-orange-400">
                            {{ $returPenjualan->total_formatted }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>