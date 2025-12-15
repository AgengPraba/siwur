<div class="dark:bg-gray-900 dark:text-white min-h-screen">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-3 mb-4 lg:mb-0">
                <div class="p-3 bg-green-500 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Data Supplier</h1>
                    <p class="text-gray-600 dark:text-gray-300 mt-1">Kelola informasi supplier dan vendor bisnis Anda
                    </p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <x-button :href="route('supplier.create')" icon="o-plus" label="Tambah Supplier"
                    class="btn-primary bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white border-0 shadow-lg px-6 py-3"
                    wire:navigate />
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Supplier</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalSuppliers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Supplier Aktif</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalSuppliers }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Toko</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currentToko->nama_toko ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 mb-6">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <!-- Search -->
                <div class="flex-1 max-w-md">
                    <x-input wire:model.live.debounce.300ms="search"
                        placeholder="Cari supplier, email, telepon, alamat..." icon="o-magnifying-glass"
                        class="input-bordered focus:border-green-500 focus:ring-green-500" />
                </div>


            </div>
        </div>

        <!-- Supplier Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                            wire:click="sortBy('nama_supplier')">
                            <div class="flex items-center gap-2">
                                <span>Nama Supplier</span>
                                @if ($sortField === 'nama_supplier')
                                    @if ($sortDirection === 'asc')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    @endif
                                @endif
                            </div>
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Kontak
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Alamat
                        </th>
                        <th
                            class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Tanggal Dibuat
                        </th>
                        <th
                            class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @if (!$supplier_data->isEmpty())
                        @foreach ($supplier_data as $supplier)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <div
                                        class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                                        <span
                                            class="text-sm font-medium text-green-600 dark:text-green-300">{{ $start + $loop->index }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center mr-4">
                                            <span
                                                class="text-white font-semibold text-sm">{{ strtoupper(substr($supplier->nama_supplier, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ $supplier->nama_supplier }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">ID:
                                                {{ $supplier->id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        @if ($supplier->no_hp)
                                            <div class="flex items-center text-sm text-gray-900 dark:text-gray-100">
                                                <svg class="w-4 h-4 mr-2 text-gray-400 dark:text-gray-500"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                    </path>
                                                </svg>
                                                {{ $supplier->no_hp }}
                                            </div>
                                        @endif
                                        @if ($supplier->email)
                                            <div class="flex items-center text-sm text-gray-900 dark:text-gray-100">
                                                <svg class="w-4 h-4 mr-2 text-gray-400 dark:text-gray-500"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                    </path>
                                                </svg>
                                                {{ Str::limit($supplier->email, 25) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">
                                        {{ Str::limit($supplier->alamat, 50) }}</div>
                                    @if ($supplier->keterangan)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ Str::limit($supplier->keterangan, 40) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $supplier->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ $supplier->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-button icon="o-eye"
                                            class="btn-sm bg-green-100 text-green-700 hover:bg-green-200 border-green-200"
                                            :href="route('supplier.show', $supplier->id)" wire:navigate tooltip="Lihat Detail" />
                                        <x-button icon="o-pencil"
                                            class="btn-sm bg-blue-100 text-blue-700 hover:bg-blue-200 border-blue-200"
                                            :href="route('supplier.edit', $supplier->id)" wire:navigate tooltip="Edit" />
                                        <x-button icon="o-trash" class="btn-error btn-sm text-white"
                                            x-on:click="$wire.idToDelete = '{{ $supplier->id }}'">
                                        </x:button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td class="px-6 py-12 text-center" colspan="6">
                                <div class="flex flex-col items-center justify-center gap-4">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum ada
                                            supplier</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Mulai dengan
                                            menambahkan supplier pertama Anda</p>
                                        <x-button :href="route('supplier.create')" wire:navigate icon="o-plus"
                                            label="Tambah Supplier"
                                            class="btn-primary bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white border-0" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if (!$supplier_data->isEmpty())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700 dark:text-gray-300">
                        Menampilkan {{ $supplier_data->firstItem() }} - {{ $supplier_data->lastItem() }} dari
                        {{ $supplier_data->total() }} supplier
                    </div>
                    <x-pagination :rows="$supplier_data" wire:model.live="perPage" />
                </div>
            </div>
        @endif
    </div>

    <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/70 bg-opacity-50" x-show="$wire.idToDelete"
        x-cloak x-transition>
        <div class="container max-w-screen-sm px-4" x-on:click.outside="$wire.idToDelete = null">
            <div class="rounded-lg bg-white p-4 shadow-lg dark:bg-zinc-800">
                <div class="text-lg font-semibold">Hapus Supplier</div>
                <div class="mt-4 text-sm">Apakah Anda Yakin Menghapus Data ini?</div>
                <div class="mt-4 flex justify-end gap-2">
                    <x-button icon="o-trash" responsive class="btn-error btn-sm text-white" wire:click="delete">Hapus
                    </x-button>
                    <x-button icon="o-x-mark" responsive class="btn-sm" x-on:click="$wire.idToDelete = null">Batal
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />
</div>


