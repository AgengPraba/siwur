
<div>
    <x-breadcrumbs :items="$breadcrumbs"/>
    <x-header />
    
    <!-- Info Card Toko -->
    @if(auth()->user() && auth()->user()->akses && auth()->user()->akses->toko)
    <div class="mb-6">
        <x-card class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-blue-200 dark:border-blue-700">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                    <x-icon name="o-building-storefront" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">Toko: {{ auth()->user()->akses->toko->nama_toko ?? 'Tidak Diketahui' }}</h3>
                    <p class="text-sm text-blue-600 dark:text-blue-300">Mengelola data jenis barang untuk toko Anda</p>
                </div>
            </div>
        </x-card>
    </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4">
        <div class="md:col-span-12">
            <x-card title="📦 Daftar Jenis Barang" subtitle="Kelola kategori barang untuk toko Anda" shadow separator>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <x-button label="➕ Tambah Jenis Barang" link="{{ route('jenis-barang.create') }}" wire:navigate
                            icon="o-plus" class="btn-primary shadow-lg hover:shadow-xl transition-all duration-200" />

                    <div class="w-full md:w-80">
                        <x-input wire:model.live.debounce.500ms="search" autocomplete="off"
                            placeholder="🔍 Cari jenis barang..." 
                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>
            
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            <th class="w-16 px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-100 dark:bg-blue-800 text-blue-600 dark:text-blue-400 rounded-full text-xs font-bold">#</span>
                            </th>
                            
                            <th class="px-4 py-3 text-left" wire:click="sortBy('nama_jenis_barang')">
                                <div class="flex items-center gap-2 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors select-none">
                                    <span class="font-semibold">Nama Jenis Barang</span>
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
                            
                            <th class="px-4 py-3 text-left" wire:click="sortBy('keterangan')">
                                <div class="flex items-center gap-2 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors select-none">
                                    <span class="font-semibold">Keterangan</span>
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
                            
                            <th class="w-48 px-4 py-3 text-center">
                                <span class="font-semibold">Aksi</span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @if (!$jenis_barang_data->isEmpty())
                            @foreach ($jenis_barang_data as $jenis_barang)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full text-sm font-bold shadow-sm">
                                            {{ $start + $loop->index }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">{{ strtoupper(substr($jenis_barang->nama_jenis_barang, 0, 2)) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $jenis_barang->nama_jenis_barang }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">Jenis Barang</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-3">
                                        <div class="max-w-xs">
                                            <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">{{ $jenis_barang->keterangan ?: 'Tidak ada keterangan' }}</p>
                                        </div>
                                    </td>
                                    
                                    <td class="px-4 py-3">
                                        <div class="flex justify-center gap-2">
                                            <x-button icon="o-eye" class="btn-success btn-sm text-white shadow-sm hover:shadow-md transition-all duration-200" 
                                                :href="route('jenis-barang.show', $jenis_barang->id)" wire:navigate tooltip="Lihat Detail">
                                                Lihat
                                            </x-button>
                                            <x-button icon="o-pencil" class="btn-warning btn-sm text-white shadow-sm hover:shadow-md transition-all duration-200"
                                                :href="route('jenis-barang.edit', $jenis_barang->id)" wire:navigate tooltip="Edit Data">
                                                Edit
                                            </x-button>
                                            <x-button icon="o-trash" class="btn-error btn-sm text-white shadow-sm hover:shadow-md transition-all duration-200"
                                                x-on:click="$wire.idToDelete = '{{ $jenis_barang->id }}'" tooltip="Hapus Data">
                                                Hapus
                                            </x-button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="px-4 py-12 text-center" colspan="4">
                                    <div class="flex flex-col items-center justify-center gap-4">
                                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <x-icon name="o-document-text" class="w-8 h-8 text-gray-400" />
                                        </div>
                                        <div class="text-center">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-1">Belum Ada Data</h3>
                                            <p class="text-gray-500 dark:text-gray-400 text-sm">Mulai dengan menambahkan jenis barang pertama Anda</p>
                                        </div>
                                        <x-button label="➕ Tambah Jenis Barang" link="{{ route('jenis-barang.create') }}" wire:navigate
                                                icon="o-plus" class="btn-primary btn-sm" />
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
            </table>

            <x-pagination :rows="$jenis_barang_data" wire:model.live="perPage" />
        </div>
    </div>
    </x-card>
    <!-- Modal Konfirmasi Hapus -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
        x-show="$wire.idToDelete" x-cloak 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="container max-w-md mx-4" x-on:click.outside="$wire.idToDelete = null">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="bg-gradient-to-r from-red-500 to-pink-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-white">Konfirmasi Hapus</h3>
                            <p class="text-red-100 text-sm">Tindakan ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="px-6 py-6">
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-icon name="o-trash" class="w-8 h-8 text-red-600 dark:text-red-400" />
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Hapus Jenis Barang?</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                            Apakah Anda yakin ingin menghapus jenis barang ini? <br>
                            <span class="font-medium text-red-600 dark:text-red-400">Data yang dihapus tidak dapat dikembalikan.</span>
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex gap-3">
                        <x-button 
                            icon="o-x-mark" 
                            label="Batal" 
                            class="flex-1 btn-outline border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700" 
                            x-on:click="$wire.idToDelete = null" />
                        <x-button 
                            icon="o-trash" 
                            label="Ya, Hapus" 
                            class="flex-1 btn-error text-white shadow-lg hover:shadow-xl transition-all duration-200" 
                            wire:click="destroy" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-back-refresh />
</div>