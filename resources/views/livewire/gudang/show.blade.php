
<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <!-- Informasi Toko Saat Ini -->
    @if($this->getCurrentToko())
    <div class="mb-6">
        <x-card class="bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-purple-200 dark:border-purple-700">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 dark:bg-purple-800 rounded-lg">
                    <x-icon name="o-building-storefront" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                </div>
                <div>
                    <h3 class="font-semibold text-purple-900 dark:text-purple-100">Detail Gudang dari</h3>
                    <p class="text-sm text-purple-700 dark:text-purple-300">{{ $this->getCurrentToko()->nama_toko }}</p>
                    @if($this->getCurrentToko()->alamat_toko)
                        <p class="text-xs text-purple-600 dark:text-purple-400">{{ $this->getCurrentToko()->alamat_toko }}</p>
                    @endif
                </div>
            </div>
        </x-card>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 pb-4">
        <div class="md:col-span-8 md:col-start-3">
            <x-card class="shadow-lg border-0">
                <x-slot:title>
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <x-icon name="o-building-office" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $gudang_data->nama_gudang }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Gudang ID: #{{ $gudang_data->id }}</p>
                        </div>
                    </div>
                </x-slot:title>
                <div class="space-y-6">
                    <!-- Informasi Utama -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <x-icon name="o-information-circle" class="w-5 h-5 text-blue-500" />
                            Informasi Gudang
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <x-icon name="o-building-office" class="w-4 h-4 inline mr-1" />
                                        Nama Gudang
                                    </label>
                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <p class="text-gray-900 dark:text-gray-100 font-medium">{{ $gudang_data->nama_gudang }}</p>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <x-icon name="o-document-text" class="w-4 h-4 inline mr-1" />
                                        Keterangan
                                    </label>
                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <p class="text-gray-900 dark:text-gray-100">{{ $gudang_data->keterangan ?: 'Tidak ada keterangan' }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <x-icon name="o-calendar" class="w-4 h-4 inline mr-1" />
                                        Tanggal Dibuat
                                    </label>
                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <p class="text-gray-900 dark:text-gray-100">{{ $gudang_data->created_at->format('d F Y, H:i') }} WIB</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $gudang_data->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        <x-icon name="o-pencil" class="w-4 h-4 inline mr-1" />
                                        Terakhir Diperbarui
                                    </label>
                                    <div class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600">
                                        <p class="text-gray-900 dark:text-gray-100">{{ $gudang_data->updated_at->format('d F Y, H:i') }} WIB</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $gudang_data->updated_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status dan Statistik -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 border border-blue-200 dark:border-blue-700">
                        <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-4 flex items-center gap-2">
                            <x-icon name="o-chart-bar" class="w-5 h-5 text-blue-500" />
                            Status Gudang
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">Aktif</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">Status Gudang</div>
                            </div>
                            
                            <div class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $gudang_data->id }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">ID Gudang</div>
                            </div>
                            
                           
                        </div>
                    </div>
                </div>
                <x-slot:actions>
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                        <x-button :href="route('gudang.index')" wire:navigate class="btn-ghost" 
                            icon="o-arrow-left">Kembali ke Daftar</x-button>
                        
                        <div class="flex gap-3">
                            <x-button :href="route('gudang.edit', $gudang_data->id)" wire:navigate 
                                class="btn-info text-white shadow-lg hover:shadow-xl transition-all" 
                                icon="o-pencil">Edit Gudang</x-button>
                        </div>
                    </div>
                </x-slot:actions>
   </x-card>
  </div>
  </div>
  <x-back-refresh />
</div>