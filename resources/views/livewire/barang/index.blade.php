<div x-data="{}">
    <!-- Header Section -->
    <div class="mb-6">
        <x-breadcrumbs :items="$breadcrumbs" />
        <div class="mt-4">
            <div class="flex items-center gap-4 mb-2">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl">
                    <x-icon name="o-building-storefront" class="w-8 h-8 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Barang</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola data barang dalam sistem inventory</p>
                </div>
            </div>
            @php
                $user = Auth::user();
                $akses = $user->akses ?? null;
                $namaToko = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';
            @endphp
            <div class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                <x-icon name="o-building-storefront" class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2" />
                <span class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ $namaToko }}</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Barang</p>
                    <p class="text-2xl font-bold">{{ $barang_data->total() }}</p>
                </div>
                <div class="p-3 bg-blue-400 bg-opacity-30 rounded-lg">
                    <x-icon name="o-cube" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Halaman</p>
                    <p class="text-2xl font-bold">{{ $barang_data->currentPage() }} / {{ $barang_data->lastPage() }}</p>
                </div>
                <div class="p-3 bg-green-400 bg-opacity-30 rounded-lg">
                    <x-icon name="o-document-duplicate" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Per Halaman</p>
                    <p class="text-2xl font-bold">{{ $perPage }}</p>
                </div>
                <div class="p-3 bg-purple-400 bg-opacity-30 rounded-lg">
                    <x-icon name="o-view-columns" class="w-6 h-6" />
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Ditampilkan</p>
                    <p class="text-2xl font-bold">{{ $barang_data->count() }}</p>
                </div>
                <div class="p-3 bg-orange-400 bg-opacity-30 rounded-lg">
                    <x-icon name="o-eye" class="w-6 h-6" />
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 border-b border-gray-200 dark:border-gray-600">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- Title -->
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <x-icon name="o-cube" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Barang</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola dan lihat semua data barang</p>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <x-button label="Tambah Barang" :href="route('barang.create')" wire:navigate icon="o-plus"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md" />
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icon name="o-magnifying-glass" class="h-5 w-5 text-gray-400" />
                        </div>
                        <input wire:model.live.debounce.500ms="search" type="text"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                            placeholder="Cari nama barang, keterangan, jenis, atau satuan...">
                    </div>
                </div>

                <!-- Per Page Selector -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tampilkan:</label>
                    <select wire:model.live="perPage"
                        class="border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="5">5</option>
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 dark:bg-gray-600">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-16">
                            No
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                            wire:click="sortBy('kode_barang')">
                            <div class="flex items-center gap-2">
                                <span>Kode Barang</span>
                                @if ($sortField === 'kode_barang')
                                    @if ($sortDirection === 'asc')
                                        <x-icon name="o-chevron-up" class="w-4 h-4 text-blue-500" />
                                    @else
                                        <x-icon name="o-chevron-down" class="w-4 h-4 text-blue-500" />
                                    @endif
                                @else
                                    <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                            wire:click="sortBy('nama_barang')">
                            <div class="flex items-center gap-2">
                                <span>Nama Barang</span>
                                @if ($sortField === 'nama_barang')
                                    @if ($sortDirection === 'asc')
                                        <x-icon name="o-chevron-up" class="w-4 h-4 text-blue-500" />
                                    @else
                                        <x-icon name="o-chevron-down" class="w-4 h-4 text-blue-500" />
                                    @endif
                                @else
                                    <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                            wire:click="sortBy('keterangan')">
                            <div class="flex items-center gap-2">
                                <span>Keterangan</span>
                                @if ($sortField === 'keterangan')
                                    @if ($sortDirection === 'asc')
                                        <x-icon name="o-chevron-up" class="w-4 h-4 text-blue-500" />
                                    @else
                                        <x-icon name="o-chevron-down" class="w-4 h-4 text-blue-500" />
                                    @endif
                                @else
                                    <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                            wire:click="sortBy('nama_jenis_barang')">
                            <div class="flex items-center gap-2">
                                <span>Jenis Barang</span>
                                @if ($sortField === 'nama_jenis_barang')
                                    @if ($sortDirection === 'asc')
                                        <x-icon name="o-chevron-up" class="w-4 h-4 text-blue-500" />
                                    @else
                                        <x-icon name="o-chevron-down" class="w-4 h-4 text-blue-500" />
                                    @endif
                                @else
                                    <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                @endif
                            </div>
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors"
                            wire:click="sortBy('nama_satuan')">
                            <div class="flex items-center gap-2">
                                <span>Satuan</span>
                                @if ($sortField === 'nama_satuan')
                                    @if ($sortDirection === 'asc')
                                        <x-icon name="o-chevron-up" class="w-4 h-4 text-blue-500" />
                                    @else
                                        <x-icon name="o-chevron-down" class="w-4 h-4 text-blue-500" />
                                    @endif
                                @else
                                    <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                @endif
                            </div>
                        </th>

                        <th
                            class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider w-40">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @if (!$barang_data->isEmpty())
                        @foreach ($barang_data as $barang)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full text-blue-600 dark:text-blue-400 font-semibold">
                                        {{ $start + $loop->index }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $barang->kode_barang }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $barang->nama_barang }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate"
                                        title="{{ $barang->keterangan }}">
                                        {{ $barang->keterangan ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-3 py-1 text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full">
                                        {{ $barang->nama_jenis_barang }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-3 py-1 text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded-full">
                                        {{ $barang->nama_satuan }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-button icon="o-eye"
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md text-xs flex items-center gap-1"
                                            :href="route('barang.show', $barang->id)" wire:navigate title="Lihat Detail">
                                            Lihat
                                        </x-button>

                                        <x-button icon="o-pencil"
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md text-xs flex items-center gap-1"
                                            :href="route('barang.edit', $barang->id)" wire:navigate title="Edit Data">
                                            Edit
                                        </x-button>

                                        <x-button icon="o-trash" type="button"
                                            wire:click="$set('idToDelete', '{{ $barang->id }}')"
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md text-xs flex items-center gap-1"
                                            title="Hapus Data">

                                            Hapus
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div
                                        class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                        <x-icon name="o-cube" class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Tidak Ada
                                            Data</h3>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                                            @if ($search)
                                                Tidak ditemukan barang yang sesuai dengan pencarian
                                                "{{ $search }}"
                                            @else
                                                Belum ada data barang yang tersedia
                                            @endif
                                        </p>
                                    </div>
                                    @if (!$search)
                                        <x-button label="Tambah Barang Pertama" :href="route('barang.create')" wire:navigate
                                            icon="o-plus"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-200" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if (!$barang_data->isEmpty())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
                <x-pagination :rows="$barang_data" wire:model.live="perPage" />
            </div>
        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    @if ($idToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto" wire:transition>
            <!-- Background Overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" wire:click="$set('idToDelete', null)">
            </div>

            <!-- Modal Container -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <div
                    class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 w-full max-w-md mx-auto">

                    <!-- Modal Header -->
                    <div
                        class="relative overflow-hidden rounded-t-2xl bg-gradient-to-br from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20 px-6 py-6">
                        <!-- Decorative circles -->
                        <div
                            class="absolute -top-4 -right-4 w-20 h-20 bg-red-100 dark:bg-red-800/30 rounded-full opacity-20">
                        </div>
                        <div
                            class="absolute -bottom-2 -left-2 w-16 h-16 bg-orange-100 dark:bg-orange-800/30 rounded-full opacity-20">
                        </div>

                        <div class="relative flex items-start space-x-4">
                            <!-- Warning Icon -->
                            <div class="flex-shrink-0">
                                <div
                                    class="w-14 h-14 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center shadow-lg">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                        </path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Title and Description -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                                    ⚠️ Konfirmasi Penghapusan
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                    Data yang dihapus <span class="font-semibold text-red-600 dark:text-red-400">tidak
                                        dapat dikembalikan</span>.
                                    Pastikan Anda benar-benar ingin menghapus data ini.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-6 bg-white dark:bg-gray-900">
                        <div
                            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
                            <div class="flex items-center space-x-3">
                                <div
                                    class="w-8 h-8 bg-red-100 dark:bg-red-800/50 rounded-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                        Anda akan menghapus data barang
                                    </p>
                                    <p class="text-xs text-red-600 dark:text-red-300 mt-1">
                                        Tindakan ini bersifat permanen dan tidak dapat dibatalkan
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3">
                            <!-- Cancel Button -->
                            <button type="button" wire:click="$set('idToDelete', null)"
                                class="flex-1 px-4 py-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium rounded-xl transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-300 dark:focus:ring-gray-600">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    <span>Batal</span>
                                </div>
                            </button>

                            <!-- Delete Button -->
                            <button type="button" wire:click="destroy"
                                class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-medium rounded-xl transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-300 shadow-lg hover:shadow-xl">
                                <div class="flex items-center justify-center space-x-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                    <span>Hapus Data</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Close button (X) -->
                    <button type="button" wire:click="$set('idToDelete', null)"
                        class="absolute top-4 right-4 w-8 h-8 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-back-refresh />
</div>
