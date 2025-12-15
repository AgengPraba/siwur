<div class="w-full">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    <div class="max-w mx-auto space-y-6">
        <!-- Header Section -->
        <div>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1
                        class="text-3xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 dark:from-green-400 dark:to-emerald-400 bg-clip-text text-transparent">
                        Retur Penjualan
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Kelola retur barang penjualan dari customer</p>
                </div>

                <div class="flex items-center gap-3">
                    <x-button icon="o-plus"
                        class="btn-primary bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 border-0 shadow-lg hover:shadow-xl transition-all duration-200"
                        :href="route('retur-penjualan.create')" wire:navigate>
                        <span class="hidden sm:inline">Buat Retur</span>
                        <span class="sm:hidden">Tambah</span>
                    </x-button>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/50 dark:border-gray-700/50 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <x-icon name="o-funnel" class="w-5 h-5 mr-2 text-green-500" />
                    Filter & Pencarian
                </h3>
                <x-button wire:click="resetFilter" icon="o-arrow-path" class="btn-ghost btn-sm text-gray-500">
                    Reset
                </x-button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="lg:col-span-1">
                    <x-input wire:model.live.debounce.300ms="search" placeholder="Cari nomor retur, customer..."
                        icon="o-magnifying-glass" class="input-bordered" />
                </div>

                <!-- Customer Filter -->
                <div>
                    <x-select wire:model.live="filterCustomer" placeholder="Pilih Customer" :options="$customer_data"
                        option-label="nama_customer" option-value="id" class="select-bordered" />
                </div>

                <!-- Date Range -->
                <div>
                    <x-input wire:model.live="tanggal_mulai" type="date" class="input-bordered"
                        placeholder="Tanggal Mulai" />
                </div>
                <div>
                    <x-input wire:model.live="tanggal_selesai" type="date" class="input-bordered"
                        placeholder="Tanggal Selesai" />
                </div>
            </div>

            @if ($search || $filterCustomer || $tanggal_mulai || $tanggal_selesai)
                <div class="mt-3 flex flex-wrap gap-2">
                    @if ($search)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <x-icon name="o-magnifying-glass" class="w-3 h-3 mr-1" />
                            Pencarian: {{ $search }}
                        </span>
                    @endif
                    @if ($filterCustomer)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <x-icon name="o-user" class="w-3 h-3 mr-1" />
                            Customer:
                            {{ collect($customer_data)->firstWhere('id', $filterCustomer)['nama_customer'] ?? $filterCustomer }}
                        </span>
                    @endif
                    @if ($tanggal_mulai || $tanggal_selesai)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                            <x-icon name="o-calendar" class="w-3 h-3 mr-1" />
                            Periode: {{ $tanggal_mulai ? date('d/m/Y', strtotime($tanggal_mulai)) : 'Awal' }} -
                            {{ $tanggal_selesai ? date('d/m/Y', strtotime($tanggal_selesai)) : 'Akhir' }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Total Retur</p>
                        <p class="text-3xl font-bold">{{ number_format($totalRetur ?? $returs->total()) }}</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <x-icon name="o-document-text" class="w-8 h-8" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Item</p>
                        <p class="text-3xl font-bold">{{ number_format($totalItems ?? 0) }}</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <x-icon name="o-cube" class="w-8 h-8" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">Total Nilai</p>
                        <p class="text-3xl font-bold">
                            {{ $totalNilai ? 'Rp ' . number_format($totalNilai, 0, ',', '.') : 'Rp 0' }}</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <x-icon name="o-banknotes" class="w-8 h-8" />
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Bulan Ini</p>
                        <p class="text-3xl font-bold">{{ number_format($totalBulanIni ?? 0) }}</p>
                    </div>
                    <div class="bg-white/20 rounded-lg p-3">
                        <x-icon name="o-chart-bar" class="w-8 h-8" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Data Retur Penjualan
                    </h3>
                    <div class="flex items-center gap-2">
                        <select wire:model.live="perPage" class="select select-bordered select-sm">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                        <span class="text-sm text-gray-500">per halaman</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50">
                        <tr class="border-b border-gray-200/50 dark:border-gray-700/50">
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Nomor Retur</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customer</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                        @forelse ($returs as $retur)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-300">
                                    {{ $loop->iteration + ($returs->currentPage() - 1) * $returs->perPage() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $retur->nomor_retur_penjualan }}
                                        </span>
                                        @if ($retur->catatan)
                                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ Str::limit($retur->catatan, 30) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $retur->tanggal_retur->format('d/m/Y') }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $retur->tanggal_retur->format('H:i') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 w-8 h-8">
                                            <div
                                                class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                                {{ substr($retur->customer->nama_customer ?? 'N', 0, 1) }}
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $retur->customer->nama_customer ?? '-' }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $retur->details->count() }} item
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($retur->details->sum('total_harga') ?? 0, 0, ',', '.') }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $retur->dibuatOleh->name ?? '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <x-button icon="o-eye"
                                            class="btn-sm bg-green-500 hover:bg-green-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                            :href="route('retur-penjualan.show', $retur->id)" wire:navigate>
                                        </x-button>
                                        <x-button icon="o-printer"
                                            class="btn-sm bg-gray-500 hover:bg-gray-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                            :href="route('retur-penjualan.print', $retur->id)" wire:navigate>
                                        </x-button>
                                        <x-button icon="o-trash"
                                            class="btn-sm bg-red-500 hover:bg-red-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                            wire:click="openDeleteModal({{ $retur->id }})">
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div
                                            class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <x-icon name="o-document-text" class="w-8 h-8 text-gray-400" />
                                        </div>
                                        <div class="text-center">
                                            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Tidak ada
                                                data retur</h3>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                Belum ada retur penjualan yang dibuat.
                                            </p>
                                        </div>
                                        <x-button icon="o-plus" class="btn-primary btn-sm" :href="route('retur-penjualan.create')"
                                            wire:navigate>
                                            Buat Retur Pertama
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($returs->hasPages())
                <div class="px-6 py-4 border-t border-gray-200/50 dark:border-gray-700/50">
                    {{ $returs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Delete Modal -->
    <x-modal wire:model="deleteModal" class="backdrop-blur">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-600" />
            </div>

            <div class="text-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                    Konfirmasi Hapus
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Apakah Anda yakin ingin menghapus retur penjualan ini?
                    <br>Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>

            <div class="flex gap-3 justify-center">
                <x-button wire:click="closeDeleteModal" class="btn-outline">
                    Batal
                </x-button>
                <x-button wire:click="confirmDelete" class="btn-error" spinner="confirmDelete">
                    Ya, Hapus
                </x-button>
            </div>
        </div>
    </x-modal>
</div>
