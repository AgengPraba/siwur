
<div>
    <x-breadcrumbs :items="$breadcrumbs"/>
    <x-header />
    
    <!-- Informasi Toko Saat Ini -->
    @if($this->getCurrentToko())
    <div class="mb-6">
        <x-card class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border-blue-200 dark:border-blue-700">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg">
                    <x-icon name="o-building-storefront" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <h3 class="font-semibold text-blue-900 dark:text-blue-100">Toko Aktif</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300">{{ $this->getCurrentToko()->nama_toko }}</p>
                    @if($this->getCurrentToko()->alamat_toko)
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $this->getCurrentToko()->alamat_toko }}</p>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
    @endif
    
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4">
        <div class="md:col-span-12">
            <x-card title="Manajemen Gudang" subtitle="Kelola data gudang untuk toko Anda" shadow separator>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <x-button label="Tambah Gudang" link="{{ route('gudang.create') }}" wire:navigate
                                icon="o-plus" class="btn-primary shadow-lg hover:shadow-xl transition-all duration-200" />
                        <div class="hidden md:flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <x-icon name="o-information-circle" class="w-4 h-4" />
                            <span>Total: {{ $gudang_data->total() }} gudang</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-full md:w-80">
                            <x-input wire:model.live.debounce.500ms="search" autocomplete="off"
                                placeholder="Cari nama gudang atau keterangan..." 
                                icon="o-magnifying-glass"
                                class="shadow-sm" />
                        </div>
                    </div>
                </div>
            
       <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <table class="w-full min-w-[600px]">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        <th class="w-16 p-4 text-left">#</th>
                        
                        
					
                                    <th class="p-4 text-left" wire:click="sortBy('nama_gudang')">
                                        <div class="flex items-center gap-2 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors select-none">
                                            <span>Nama Gudang</span>
                                            @if ($sortField === 'nama_gudang')
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
					
                                    <th class="p-4 text-left" wire:click="sortBy('keterangan')">
                                        <div class="flex items-center gap-2 cursor-pointer hover:text-blue-600 dark:hover:text-blue-400 transition-colors select-none">
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
                        <th class="w-48 p-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @if (!$gudang_data->isEmpty())
                        @foreach ($gudang_data as $gudang)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                               <td class="p-4 text-center font-medium text-gray-900 dark:text-gray-100">{{ $start + $loop->index }}</td>
                                
								<td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                            <x-icon name="o-building-office" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ $gudang->nama_gudang }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">Gudang #{{ $gudang->id }}</div>
                                        </div>
                                    </div>
                                </td>
								<td class="p-4">
                                    <div class="text-gray-900 dark:text-gray-100">{{ $gudang->keterangan }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Dibuat: {{ $gudang->created_at->format('d M Y H:i') }}
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-2">
                                        <x-button icon="o-eye" class="btn-success btn-sm text-white shadow-sm hover:shadow-md transition-all" 
                                            :href="route('gudang.show', $gudang->id)" wire:navigate tooltip="Lihat Detail">
                                            Lihat
                                        </x-button>
                                        <x-button icon="o-pencil" class="btn-info btn-sm text-white shadow-sm hover:shadow-md transition-all"
                                            :href="route('gudang.edit', $gudang->id)" wire:navigate tooltip="Edit Data">
                                            Edit
                                        </x-button>
                                        
                                        <x-button icon="o-trash" class="btn-error btn-sm text-white shadow-sm hover:shadow-md transition-all"
                                            x-on:click="$wire.idToDelete = '{{ $gudang->id }}'" tooltip="Hapus Data">
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
                                    <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                                        <x-icon name="o-building-office" class="w-12 h-12 text-gray-400" />
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Belum Ada Gudang</h3>
                                        <p class="text-gray-500 dark:text-gray-400 mb-4">Mulai dengan menambahkan gudang pertama untuk toko Anda</p>
                                        <x-button label="Tambah Gudang" link="{{ route('gudang.create') }}" wire:navigate
                                                icon="o-plus" class="btn-primary" />
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <x-pagination :rows="$gudang_data" wire:model.live="perPage" />
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
                
                <div class="flex items-center gap-4 mb-4">
                    <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-full">
                        <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Konfirmasi Hapus</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
                    </div>
                </div>
                
                <div class="mb-6">
                    <p class="text-gray-700 dark:text-gray-300">Apakah Anda yakin ingin menghapus gudang ini? Semua data yang terkait akan ikut terhapus.</p>
                </div>
                
                <div class="flex justify-end gap-3">
                    <x-button icon="o-x-mark" class="btn-ghost btn-sm" 
                        x-on:click="$wire.idToDelete = null">
                        Batal
                    </x-button>
                    <x-button icon="o-trash" class="btn-error btn-sm text-white shadow-lg hover:shadow-xl transition-all" 
                        wire:click="destroy" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="destroy">Hapus Gudang</span>
                        <span wire:loading wire:target="destroy">Menghapus...</span>
                    </x-button>
                </div>
            </div>
        </div>
    </div>
    <x-back-refresh />
</div>