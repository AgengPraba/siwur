
<div>
    <x-breadcrumbs :items="$breadcrumbs"/>
    <x-header />
    
    <!-- Info Card Toko -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold mb-2">Manajemen Satuan</h2>
                    <p class="text-blue-100">Kelola satuan barang untuk toko Anda</p>
                </div>
                <div class="text-right">
                    <div class="bg-white/20 rounded-lg p-3 flex flex-col items-center justify-center">
                        <x-icon name="o-scale" class="w-8 h-8 mb-1" />
                        <p class="text-sm font-medium">Total: {{ $satuan_data->total() }} Satuan</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4">
        <div class="md:col-span-12">
            <x-card title="Daftar Satuan" subtitle="Kelola semua satuan barang di toko Anda" shadow separator>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <x-button label="Tambah Satuan" link="{{ route('satuan.create') }}" wire:navigate
                            icon="o-plus" class="btn-primary hover:btn-primary-focus transition-all duration-200 shadow-md" />

                    <div class="w-full md:w-80">
                        <x-input wire:model.live.debounce.500ms="search" autocomplete="off"
                            placeholder="🔍 Cari nama satuan atau keterangan..." 
                            class="input-bordered focus:input-primary" />
                    </div>
                </div>
            
            <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <table class="w-full min-w-[600px] table-auto">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                            <tr class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                <th class="w-16 p-4 text-center border-b border-gray-200 dark:border-gray-600">
                                    <span class="text-gray-500">#</span>
                                </th>
                                
                                <th class="p-4 text-left border-b border-gray-200 dark:border-gray-600" wire:click="sortBy('nama_satuan')">
                                    <div class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors select-none">
                                        <x-icon name="o-scale" class="w-4 h-4" />
                                        <span>Nama Satuan</span>
                                        @if ($sortField === 'nama_satuan')
                                            @if ($sortDirection === 'asc')
                                               <x-icon name="o-chevron-up" class="w-4 h-4 text-primary" />
                                            @else
                                                <x-icon name="o-chevron-down" class="w-4 h-4 text-primary" />
                                            @endif
                                        @else
                                            <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                
                                <th class="p-4 text-left border-b border-gray-200 dark:border-gray-600" wire:click="sortBy('keterangan')">
                                    <div class="flex items-center gap-2 cursor-pointer hover:text-primary transition-colors select-none">
                                        <x-icon name="o-document-text" class="w-4 h-4" />
                                        <span>Keterangan</span>
                                        @if ($sortField === 'keterangan')
                                            @if ($sortDirection === 'asc')
                                               <x-icon name="o-chevron-up" class="w-4 h-4 text-primary" />
                                            @else
                                                <x-icon name="o-chevron-down" class="w-4 h-4 text-primary" />
                                            @endif
                                        @else
                                            <x-icon name="o-chevron-up-down" class="w-4 h-4 text-gray-400" />
                                        @endif
                                    </div>
                                </th>
                                
                                <th class="w-48 p-4 text-center border-b border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center justify-center gap-2">
                                        <x-icon name="o-cog-6-tooth" class="w-4 h-4" />
                                        <span>Aksi</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @if (!$satuan_data->isEmpty())
                                @foreach ($satuan_data as $satuan)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <td class="p-4 text-center text-sm font-medium text-gray-600 dark:text-gray-400">
                                            <div class="w-8 h-8 bg-primary/10 rounded-full flex items-center justify-center mx-auto">
                                                <span class="text-primary font-semibold">{{ $start + $loop->index }}</span>
                                            </div>
                                        </td>
                                        
                                        <td class="p-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <x-icon name="o-scale" class="w-5 h-5 text-white" />
                                                </div>
                                                <div>
                                                    <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $satuan->nama_satuan }}</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">Satuan Barang</p>
                                                </div>
                                            </div>
                                        </td>
                                        
                                        <td class="p-4">
                                            <div class="max-w-xs">
                                                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">{{ $satuan->keterangan ?: 'Tidak ada keterangan' }}</p>
                                            </div>
                                        </td>
                                        
                                        <td class="p-4">
                                            <div class="flex justify-center gap-2">
                                                <x-button icon="o-eye" class="btn-success btn-sm text-white hover:btn-success-focus transition-all duration-200 shadow-sm" 
                                                    :href="route('satuan.show', $satuan->id)" wire:navigate tooltip="Lihat Detail">
                                                    Lihat
                                                </x-button>
                                                <x-button icon="o-pencil" class="btn-info btn-sm text-white hover:btn-info-focus transition-all duration-200 shadow-sm"
                                                    :href="route('satuan.edit', $satuan->id)" wire:navigate tooltip="Edit Data">
                                                    Edit
                                                </x-button>
                                                <x-button icon="o-trash" class="btn-error btn-sm text-white hover:btn-error-focus transition-all duration-200 shadow-sm"
                                                    x-on:click="$wire.idToDelete = '{{ $satuan->id }}'" tooltip="Hapus Data">
                                                    Hapus
                                                </x-button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="p-12 text-center" colspan="4">
                                        <div class="flex flex-col items-center justify-center gap-4">
                                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                                <x-icon name="o-scale" class="w-8 h-8 text-gray-400" />
                                            </div>
                                            <div class="text-center">
                                                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum Ada Satuan</h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Mulai dengan menambahkan satuan barang pertama Anda</p>
                                                <x-button label="➕ Tambah Satuan" link="{{ route('satuan.create') }}" wire:navigate
                                                    icon="o-plus" class="btn-primary btn-sm" />
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
            </table>

            <x-pagination :rows="$satuan_data" wire:model.live="perPage" />
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
        <div class="container max-w-md px-4" x-on:click.outside="$wire.idToDelete = null">
            <div class="rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800 border border-gray-200 dark:border-gray-700"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95">
                
                <!-- Header -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="mb-6">
                    <p class="text-gray-700 dark:text-gray-300">Apakah Anda yakin ingin menghapus satuan ini? Data yang sudah dihapus tidak dapat dikembalikan.</p>
                </div>
                
                <!-- Actions -->
                <div class="flex justify-end gap-3">
                    <x-button icon="o-x-mark" class="btn-ghost btn-sm hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        x-on:click="$wire.idToDelete = null">
                        Batal
                    </x-button>
                    <x-button icon="o-trash" class="btn-error btn-sm text-white hover:btn-error-focus transition-all duration-200 shadow-sm" 
                        wire:click="destroy">
                        Ya, Hapus
                    </x-button>
                </div>
            </div>
        </div>
    </div>
    <x-back-refresh />
</div>