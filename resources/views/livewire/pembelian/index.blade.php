<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Pembelian</p>
                    <p class="text-2xl font-bold">{{ number_format($totalPembelian) }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-50 rounded-full p-3">
                    <x-icon name="o-shopping-cart" class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Nilai</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($totalNilai) }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-50 rounded-full p-3">
                    <x-icon name="o-currency-dollar" class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Bulan Ini</p>
                    <p class="text-2xl font-bold">{{ number_format($pembelianBulanIni) }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-50 rounded-full p-3">
                    <x-icon name="o-calendar" class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Supplier Aktif</p>
                    <p class="text-2xl font-bold">{{ number_format($supplierAktif) }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-50 rounded-full p-3">
                    <x-icon name="o-building-office" class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm font-medium">Diskon Bulan Ini</p>
                    <p class="text-2xl font-bold">{{ $this->formatCurrency($totalDiskonBulanIni) }}</p>
                </div>
                <div class="bg-red-400 bg-opacity-50 rounded-full p-3">
                    <x-icon name="o-receipt-percent" class="h-6 w-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium">Biaya Lain Bulan Ini</p>
                    <p class="text-2xl font-bold">{{ $this->formatCurrency($totalBiayaLainBulanIni) }}</p>
                </div>
                <div class="bg-indigo-400 bg-opacity-50 rounded-full p-3">
                    <x-icon name="o-plus-circle" class="h-6 w-6" />
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <x-card class="shadow-lg border-0 rounded-xl">
        <!-- Header Section -->
        <div
            class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6 p-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 rounded-t-xl">
            <div class="flex items-center gap-3">
                <div class="bg-blue-500 rounded-full p-2">
                    <x-icon name="o-shopping-cart" class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Riwayat Pembelian</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Kelola semua data pembelian Anda</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-button icon="o-plus"
                    class="btn-primary shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
                    link="{{ route('pembelian.create') }}" wire:navigate>
                    Tambah Pembelian
                </x-button>

                <x-button icon="{{ $viewMode === 'card' ? 'o-table-cells' : 'o-squares-2x2' }}" class="btn-outline"
                    wire:click="toggleViewMode" title="Toggle View Mode">
                    {{ $viewMode === 'card' ? 'Tabel' : 'Kartu' }}
                </x-button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="px-6 pb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <x-input wire:model.live.debounce.300ms="search" placeholder="Cari pembelian..."
                            icon="o-magnifying-glass" class="w-full" />
                    </div>

                    <div>
                        <x-select wire:model.live="filterStatus" :options="$this->statusOptions" placeholder="Pilih Status"
                            class="w-full" />
                    </div>

                    <div>
                        <x-select wire:model.live="filterSupplier" :options="$this->suppliers" placeholder="Pilih Supplier"
                            class="w-full" />
                    </div>

                    <div>
                        <x-input wire:model.live="filterDateFrom" type="date" placeholder="Dari Tanggal"
                            class="w-full" />
                    </div>

                    <div class="flex gap-2">
                        <x-input wire:model.live="filterDateTo" type="date" placeholder="Sampai Tanggal"
                            class="w-full" />
                        <x-button icon="o-x-mark" wire:click="clearFilters" class="btn-outline btn-sm"
                            title="Clear Filters" />
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            @if ($viewMode === 'card')
                <!-- Card View -->
                @if (!$pembelian_data->isEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($pembelian_data as $pembelian)
                            <div
                                class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                                <!-- Card Header -->
                                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="font-bold text-lg">{{ $pembelian->nomor_pembelian }}</h3>
                                            <p class="text-blue-100 text-sm">
                                                {{ date('d M Y H:i:s', strtotime($pembelian->tanggal_pembelian)) }}</p>
                                        </div>
                                        <div class="text-right flex flex-col gap-1">
                                            <span
                                                class="{{ $this->getStatusClass($pembelian->status) }} text-white px-2 py-1 rounded-full text-xs font-medium">
                                                {{ $this->getStatusLabel($pembelian->status) }}
                                            </span>
                                            <div class="flex gap-1">
                                                @if ($pembelian->total_diskon > 0)
                                                    <span
                                                        class="bg-red-500 bg-opacity-80 text-white px-1.5 py-0.5 rounded text-xs"
                                                        title="Ada Diskon">
                                                        <x-icon name="o-receipt-percent" class="h-3 w-3" />
                                                    </span>
                                                @endif
                                                @if ($pembelian->total_biaya_lain > 0)
                                                    <span
                                                        class="bg-indigo-500 bg-opacity-80 text-white px-1.5 py-0.5 rounded text-xs"
                                                        title="Ada Biaya Lain">
                                                        <x-icon name="o-plus-circle" class="h-3 w-3" />
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="o-building-office" class="h-4 w-4 text-gray-500" />
                                            <span
                                                class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $pembelian->nama_supplier }}</span>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <x-icon name="o-user" class="h-4 w-4 text-gray-500" />
                                            <span
                                                class="text-sm text-gray-600 dark:text-gray-400">{{ $pembelian->name }}</span>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <x-icon name="o-cube" class="h-4 w-4 text-gray-500" />
                                                <span
                                                    class="text-sm text-gray-600 dark:text-gray-400">{{ $pembelian->total_items }}
                                                    item</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <x-icon name="o-calculator" class="h-4 w-4 text-gray-500" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">Qty:
                                                    {{ number_format($pembelian->total_quantity, 0) }}</span>
                                            </div>
                                        </div>

                                        <div class="space-y-2">

                                            @if ($pembelian->total_diskon > 0)
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-red-500">Total Diskon:</span>
                                                    <span class="text-sm font-medium text-red-500">
                                                        - {{ $this->formatCurrency($pembelian->total_diskon) }}
                                                        @php
                                                            $discountPercentage = $pembelian->subtotal > 0
                                                                ? round(($pembelian->total_diskon / $pembelian->subtotal) * 100, 1)
                                                                : 0;
                                                        @endphp
                                                        <span class="text-xs">({{ $discountPercentage }}%)</span>
                                                    </span>
                                                </div>
                                            @endif

                                            @if ($pembelian->total_biaya_lain > 0)
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-blue-500">Biaya Lain:</span>
                                                    <span class="text-sm font-medium text-blue-500">+
                                                        {{ $this->formatCurrency($pembelian->total_biaya_lain) }}</span>
                                                </div>
                                            @endif

                                            @if ($pembelian->total_kembalian > 0)
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-orange-500">Kembalian:</span>
                                                    <span
                                                        class="text-sm font-medium text-orange-500">{{ $this->formatCurrency($pembelian->total_kembalian) }}</span>
                                                </div>
                                            @endif

                                            <hr class="border-gray-200 dark:border-gray-600">
                                            <div class="flex items-center justify-between">
                                                <span
                                                    class="text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                                    Harga:</span>
                                                <span
                                                    class="text-lg font-bold text-green-600">{{ $this->formatCurrency($pembelian->total_harga) }}</span>
                                            </div>
                                        </div>

                                        @if ($pembelian->keterangan)
                                            <div
                                                class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-2 rounded-lg">
                                                <p>{{ Str::limit($pembelian->keterangan, 50) }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Card Footer -->
                                <div
                                    class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-t border-gray-200 dark:border-gray-600">
                                    <div class="flex justify-between items-center gap-2">
                                        <x-button icon="o-eye" class="btn-success btn-sm text-white flex-1"
                                            :href="route('pembelian.show', $pembelian->id)" wire:navigate>
                                            Lihat
                                        </x-button>

                                        <x-button icon="o-pencil" class="btn-info btn-sm text-white flex-1"
                                            :href="route('pembelian.edit', $pembelian->id)" wire:navigate>
                                            Edit
                                        </x-button>

                                        <x-button icon="o-trash" class="btn-error btn-sm text-white"
                                            x-on:click="$wire.idToDelete = '{{ $pembelian->id }}'">
                                            Hapus
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State for Cards -->
                    <div class="text-center py-16">
                        <div
                            class="bg-gray-100 dark:bg-gray-800 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
                            <x-icon name="o-shopping-cart" class="h-10 w-10 text-gray-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Belum ada data
                            pembelian</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Tambahkan pembelian pertama Anda untuk memulai
                        </p>
                        <x-button icon="o-plus" class="btn-primary" link="{{ route('pembelian.create') }}"
                            wire:navigate>
                            Tambah Pembelian
                        </x-button>
                    </div>
                @endif
            @else
                <!-- Table View -->
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px] bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">#
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                                    wire:click="sortBy('nomor_pembelian')">
                                    <div class="flex items-center gap-2">
                                        Nomor Pembelian
                                        @if ($sortField === 'nomor_pembelian')
                                            <x-icon
                                                name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}"
                                                class="h-4 w-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer"
                                    wire:click="sortBy('tanggal_pembelian')">
                                    <div class="flex items-center gap-2">
                                        Tanggal
                                        @if ($sortField === 'tanggal_pembelian')
                                            <x-icon
                                                name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}"
                                                class="h-4 w-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Supplier</th>

                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total Diskon</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Biaya Lain</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kembalian</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total Harga</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Status</th>
                                <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if (!$pembelian_data->isEmpty())
                                @foreach ($pembelian_data as $pembelian)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $start + $loop->index }}</td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $pembelian->nomor_pembelian }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ date('d M Y H:i:s', strtotime($pembelian->tanggal_pembelian)) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            <div class="font-medium">{{ $pembelian->nama_supplier }}</div>
                                            <div class="text-xs text-gray-500">{{ $pembelian->total_items }} item,
                                                Qty: {{ number_format($pembelian->total_quantity, 0) }}</div>
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm {{ $pembelian->total_diskon > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                            @if ($pembelian->total_diskon > 0)
                                                <div>- {{ $this->formatCurrency($pembelian->total_diskon) }}</div>
                                                @php
                                                    $discountPercentage =
                                                        $pembelian->subtotal > 0
                                                            ? round(
                                                                ($pembelian->total_diskon / $pembelian->subtotal) * 100,
                                                                1,
                                                            )
                                                            : 0;
                                                @endphp
                                                <div class="text-xs">({{ $discountPercentage }}%)</div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm {{ $pembelian->total_biaya_lain > 0 ? 'text-blue-600 font-medium' : 'text-gray-400' }}">
                                            {{ $pembelian->total_biaya_lain > 0 ? '+ ' . $this->formatCurrency($pembelian->total_biaya_lain) : '-' }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm {{ $pembelian->total_kembalian > 0 ? 'text-orange-600 font-medium' : 'text-gray-400' }}">
                                            {{ $pembelian->total_kembalian > 0 ? $this->formatCurrency($pembelian->total_kembalian) : '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-bold text-green-600">
                                            {{ $this->formatCurrency($pembelian->total_harga) }}</td>
                                        <td class="px-4 py-3">
                                            @if ($pembelian->status === 'lunas')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                    {{ $this->getStatusLabel($pembelian->status) }}
                                                </span>
                                            @elseif($pembelian->status === 'belum_lunas')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                                                    {{ $this->getStatusLabel($pembelian->status) }}
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100">
                                                    {{ $this->getStatusLabel($pembelian->status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex justify-center gap-2">
                                                <x-button icon="o-eye" class="btn-success btn-sm text-white"
                                                    :href="route('pembelian.show', $pembelian->id)" wire:navigate>
                                                    Lihat
                                                </x-button>
                                                <x-button icon="o-pencil" class="btn-info btn-sm text-white"
                                                    :href="route('pembelian.edit', $pembelian->id)" wire:navigate>
                                                    Edit
                                                </x-button>
                                                <x-button icon="o-trash" class="btn-error btn-sm text-white"
                                                    x-on:click="$wire.idToDelete = '{{ $pembelian->id }}'">
                                                    Hapus
                                                </x-button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <x-icon name="o-shopping-cart" class="h-12 w-12 text-gray-400" />
                                            <span class="text-gray-500 dark:text-gray-400">Tidak ada data
                                                pembelian</span>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif

            <!-- Pagination -->
            @if ($pembelian_data->hasPages())
                <div class="mt-6">
                    <x-pagination :rows="$pembelian_data" wire:model.live="perPage" />
                </div>
            @endif
        </div>
    </x-card>

    <!-- Delete Confirmation Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/70 bg-opacity-50"
        x-show="$wire.idToDelete" x-cloak x-transition>
        <div class="container max-w-md px-4" x-on:click.outside="$wire.idToDelete = null">
            <div
                class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-red-100 dark:bg-red-900 rounded-full p-2">
                        <x-icon name="o-exclamation-triangle" class="h-6 w-6 text-red-600 dark:text-red-400" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Hapus Data Pembelian</h3>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Apakah Anda yakin ingin menghapus data pembelian ini?
                    Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex justify-end gap-3">
                    <x-button class="btn-outline" x-on:click="$wire.idToDelete = null">
                        Batal
                    </x-button>
                    <x-button icon="o-trash" class="btn-error text-white" wire:click="destroy">
                        Hapus
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />
</div>
