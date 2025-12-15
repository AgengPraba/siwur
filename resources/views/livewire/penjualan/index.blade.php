<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-card class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Penjualan</p>
                    <p class="text-2xl font-bold">{{ $stats['total_count'] }}</p>
                </div>
                <div class="p-3 bg-blue-400 bg-opacity-50 rounded-full">
                    <x-icon name="o-shopping-cart" class="w-6 h-6" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium">Status Pembayaran</p>
                    <div class="flex gap-2 mt-1">
                        <span class="text-sm" title="Belum Bayar">🔴 {{ $stats['belum_bayar_count'] }}</span>
                        <span class="text-sm" title="Belum Lunas">🟡 {{ $stats['belum_lunas_count'] }}</span>
                        <span class="text-sm" title="Lunas">🟢 {{ $stats['lunas_count'] }}</span>
                    </div>
                </div>
                <div class="p-3 bg-yellow-400 bg-opacity-50 rounded-full">
                    <x-icon name="o-clock" class="w-6 h-6" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Pembayaran</p>
                    <p class="text-lg font-bold">Rp {{ number_format($stats['total_pembayaran'], 0, ',', '.') }}</p>
                    <p class="text-xs text-purple-200">Kembalian: Rp
                        {{ number_format($stats['total_kembalian'], 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-purple-400 bg-opacity-50 rounded-full">
                    <x-icon name="o-credit-card" class="w-6 h-6" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-r from-red-500 to-red-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Biaya & Diskon</p>
                    <p class="text-xs text-red-200">Diskon: -Rp {{ number_format($stats['total_diskon'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-red-200">Biaya Lain: +Rp
                        {{ number_format($stats['total_biaya_lain'], 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-red-400 bg-opacity-50 rounded-full">
                    <x-icon name="o-receipt-percent" class="w-6 h-6" />
                </div>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 gap-4 pb-4">
        <x-card title="Riwayat Penjualan" shadow separator>

            <!-- Header Actions -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('penjualan.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-medium rounded-lg text-sm transition-all duration-200 shadow hover:shadow-lg">
                        <x-icon name="o-plus-circle" class="w-5 h-5 mr-2" />
                        Tambah Penjualan
                    </a>
                    <x-button label="{{ $showFilters ? 'Sembunyikan Filter' : 'Tampilkan Filter' }}"
                        wire:click="$toggle('showFilters')" icon="o-funnel" class="btn-outline" />
                    @if ($search || $filterStatus || $filterCustomer || $filterDateFrom || $filterDateTo)
                        <x-button label="Reset Filter" wire:click="clearFilters" icon="o-x-mark"
                            class="btn-outline btn-error" />
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
                    <x-select wire:model.live="perPage" :options="[
                        ['id' => 10, 'name' => '10 per halaman'],
                        ['id' => 25, 'name' => '25 per halaman'],
                        ['id' => 50, 'name' => '50 per halaman'],
                    ]" class="w-full sm:w-40" />
                    <x-input wire:model.live.debounce.500ms="search" autocomplete="off" placeholder="Cari penjualan..."
                        icon="o-magnifying-glass" class="w-full sm:w-60" />
                </div>
            </div>

            <!-- Advanced Filters -->
            <div x-show="$wire.showFilters" x-collapse class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <x-select wire:model.live="filterStatus" :options="[
                        ['id' => '', 'name' => 'Semua Status'],
                        ['id' => 'belum_bayar', 'name' => 'Belum Bayar'],
                        ['id' => 'belum_lunas', 'name' => 'Belum Lunas'],
                        ['id' => 'lunas', 'name' => 'Lunas'],
                    ]" label="Status Pembayaran"
                        placeholder="Pilih Status" />

                    <x-select wire:model.live="filterCustomer" :options="$customers->map(
                        fn($customer) => ['id' => $customer->id, 'name' => $customer->nama_customer],
                    )" label="Customer"
                        placeholder="Pilih Customer" />

                    <x-input wire:model.live="filterDateFrom" label="Tanggal Dari" type="date" />

                    <x-input wire:model.live="filterDateTo" label="Tanggal Sampai" type="date" />
                </div>
            </div>

            <!-- Table Container -->
            <div class="overflow-hidden">
                <!-- Mobile Cards View -->
                <div class="block md:hidden space-y-4">
                    @forelse ($penjualan_data as $penjualan)
                        <x-card class="hover:shadow-md transition-shadow duration-200">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h3 class="font-semibold text-lg">{{ $penjualan->nomor_penjualan }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $penjualan->tanggal_penjualan->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                @if ($penjualan->status == 'belum_bayar')
                                    <x-badge value="Belum Bayar" class="badge-error" />
                                @elseif($penjualan->status == 'belum_lunas')
                                    <x-badge value="Belum Lunas" class="badge-warning" />
                                @elseif($penjualan->status == 'lunas')
                                    <x-badge value="Lunas" class="badge-success" />
                                @else
                                    <x-badge value="Unknown" class="badge-neutral" />
                                @endif
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Customer:</span>
                                    <span
                                        class="text-sm font-medium">{{ $penjualan->customer->nama_customer ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">User:</span>
                                    <span class="text-sm font-medium">{{ $penjualan->user->name ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Items:</span>
                                    <span class="text-sm font-medium">{{ $penjualan->penjualanDetails->count() }}
                                        item(s) (Qty:
                                        {{ number_format($penjualan->penjualanDetails->sum('jumlah'), 0, ',', '.') }})</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Total:</span>
                                    <span class="text-sm font-bold text-green-600">Rp
                                        {{ number_format($penjualan->total_harga, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Pembayaran:</span>
                                    <span class="text-sm font-medium text-purple-600">Rp
                                        {{ number_format($penjualan->total_pembayaran, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Kembalian:</span>
                                    <span class="text-sm font-medium text-teal-600">Rp
                                        {{ number_format($penjualan->computed_kembalian, 0, ',', '.') }}</span>
                                </div>
                                @if ($penjualan->total_diskon > 0)
                                    <div class="flex justify-between" title="Total diskon dari semua item">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            <x-icon name="o-receipt-percent" class="w-3 h-3 inline mr-1" />Diskon:
                                        </span>
                                        <span class="text-xs text-red-500">-Rp
                                            {{ number_format($penjualan->total_diskon, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                @if ($penjualan->total_biaya_lain > 0)
                                    <div class="flex justify-between" title="Total biaya tambahan dari semua item">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            <x-icon name="o-plus-circle" class="w-3 h-3 inline mr-1" />Biaya Lain:
                                        </span>
                                        <span class="text-xs text-blue-500">+Rp
                                            {{ number_format($penjualan->total_biaya_lain, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex justify-end gap-2">
                                <x-button icon="o-eye" class="btn-success btn-sm" :href="route('penjualan.show', $penjualan->id)" wire:navigate />
                                <x-button icon="o-pencil" class="btn-info btn-sm" :href="route('penjualan.edit', $penjualan->id)" wire:navigate />
                                <x-button icon="o-trash" class="btn-error btn-sm"
                                    x-on:click="$wire.idToDelete = '{{ $penjualan->id }}'" />
                            </div>
                        </x-card>
                    @empty
                        <x-card>
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <x-icon name="o-document" class="w-16 h-16 text-gray-400 mb-4" />
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Tidak Ada Data
                                </h3>
                                <p class="text-gray-500 dark:text-gray-400">Belum ada data penjualan yang tersedia.</p>
                            </div>
                        </x-card>
                    @endforelse
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full min-w-full">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800">
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    #
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('nomor_penjualan')">
                                    <div class="flex items-center space-x-1">
                                        <span>No. Penjualan</span>
                                        @if ($sortField === 'nomor_penjualan')
                                            <x-icon
                                                name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}"
                                                class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('tanggal_penjualan')">
                                    <div class="flex items-center space-x-1">
                                        <span>Tanggal</span>
                                        @if ($sortField === 'tanggal_penjualan')
                                            <x-icon
                                                name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}"
                                                class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Customer
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kasir
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Items
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('total_harga')">
                                    <div class="flex items-center space-x-1">
                                        <span>Total</span>
                                        @if ($sortField === 'total_harga')
                                            <x-icon
                                                name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}"
                                                class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Pembayaran
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Kembalian
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700"
                                    wire:click="sortBy('status')">
                                    <div class="flex items-center space-x-1">
                                        <span>Status</span>
                                        @if ($sortField === 'status')
                                            <x-icon
                                                name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}"
                                                class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Aksi </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($penjualan_data as $penjualan)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $start + $loop->index }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $penjualan->nomor_penjualan }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $penjualan->tanggal_penjualan->format('d M Y') }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $penjualan->tanggal_penjualan->format('H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $penjualan->customer->nama_customer ?? '-' }}
                                        </div>
                                        @if ($penjualan->customer)
                                            <div class="text-sm text-gray-500">
                                                {{ $penjualan->customer->no_hp }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $penjualan->user->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-100">
                                            {{ $penjualan->penjualanDetails->count() }} item(s)
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Total Qty:
                                            {{ number_format($penjualan->penjualanDetails->sum('jumlah'), 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-green-600">
                                            Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}
                                        </div>
                                        @if ($penjualan->total_diskon > 0)
                                            <div class="text-xs text-red-500" title="Total diskon dari semua item">
                                                <x-icon name="o-receipt-percent" class="w-3 h-3 inline mr-1" />
                                                Diskon: -Rp {{ number_format($penjualan->total_diskon, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        @if ($penjualan->total_biaya_lain > 0)
                                            <div class="text-xs text-blue-500"
                                                title="Total biaya tambahan dari semua item">
                                                <x-icon name="o-plus-circle" class="w-3 h-3 inline mr-1" />
                                                Biaya Lain: +Rp
                                                {{ number_format($penjualan->total_biaya_lain, 0, ',', '.') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-purple-600">
                                            Rp {{ number_format($penjualan->total_pembayaran, 0, ',', '.') }}
                                        </div>
                                        @if ($penjualan->pembayaranPenjualan->count() > 0)
                                            <div class="text-xs text-gray-500">
                                                {{ $penjualan->pembayaranPenjualan->count() }} pembayaran
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400">
                                                Belum ada pembayaran
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-teal-600">
                                            Rp {{ number_format($penjualan->computed_kembalian, 0, ',', '.') }}
                                        </div>
                                        @if ($penjualan->computed_kembalian > 0)
                                            <div class="text-xs text-teal-500">
                                                Ada kembalian
                                            </div>
                                        @else
                                            <div class="text-xs text-gray-400">
                                                Tidak ada kembalian
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($penjualan->status == 'belum_bayar')
                                            <x-badge value="Belum Bayar" class="badge-error" />
                                        @elseif($penjualan->status == 'belum_lunas')
                                            <x-badge value="Belum Lunas" class="badge-warning" />
                                        @elseif($penjualan->status == 'lunas')
                                            <x-badge value="Lunas" class="badge-success" />
                                        @else
                                            <x-badge value="Unknown" class="badge-neutral" />
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center space-x-2">
                                            <x-button icon="o-eye" class="btn-success btn-sm" :href="route('penjualan.show', $penjualan->id)"
                                                wire:navigate tooltip="Lihat Detail" />
                                            <x-button icon="o-pencil" class="btn-info btn-sm" :href="route('penjualan.edit', $penjualan->id)"
                                                wire:navigate tooltip="Edit" />
                                            <x-button icon="o-trash" class="btn-error btn-sm"
                                                x-on:click="$wire.idToDelete = '{{ $penjualan->id }}'"
                                                tooltip="Hapus" />
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-12">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <x-icon name="o-document" class="w-16 h-16 text-gray-400 mb-4" />
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Tidak
                                                Ada Data</h3>
                                            <p class="text-gray-500 dark:text-gray-400">Belum ada data penjualan yang
                                                tersedia.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if ($penjualan_data->hasPages())
                <div class="mt-6">
                    <x-pagination :rows="$penjualan_data" wire:model.live="perPage" />
                </div>
            @endif
        </x-card>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-show="$wire.idToDelete" x-cloak
        x-transition>
        <div class="max-w-md mx-4 bg-white dark:bg-gray-800 rounded-lg shadow-xl"
            x-on:click.outside="$wire.idToDelete = null">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-600" />
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Hapus Penjualan</h3>
                    </div>
                </div>
                <div class="mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Apakah Anda yakin ingin menghapus data penjualan ini? Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
                <div class="flex justify-end space-x-3">
                    <x-button label="Batal" class="btn-outline" x-on:click="$wire.idToDelete = null" />
                    <x-button label="Hapus" icon="o-trash" class="btn-error" wire:click="destroy" />
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />
</div>
