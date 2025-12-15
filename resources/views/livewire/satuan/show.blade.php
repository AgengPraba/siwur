
<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <!-- Header Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-6 text-white shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <x-icon name="o-eye" class="w-8 h-8" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">Detail Satuan</h2>
                    <p class="text-indigo-100">Informasi lengkap satuan barang</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-4">
        <!-- Detail Card -->
        <div class="lg:col-span-2">
            <x-card title="📋 Informasi Satuan" subtitle="Detail lengkap data satuan" shadow separator>
                
                <!-- Satuan Info -->
                <div class="space-y-6">
                    <!-- Header Info -->
                    <div class="flex items-center gap-4 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <x-icon name="o-scale" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $satuan_data->nama_satuan }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Satuan Barang</p>
                        </div>
                    </div>
                    
                    <!-- Detail Fields -->
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Nama Satuan -->
                        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center gap-3 mb-2">
                                <x-icon name="o-scale" class="w-5 h-5 text-blue-500" />
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Nama Satuan</h4>
                            </div>
                            <p class="text-lg font-medium text-gray-900 dark:text-gray-100 ml-8">{{ $satuan_data->nama_satuan }}</p>
                        </div>
                        
                        <!-- Keterangan -->
                        <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="flex items-center gap-3 mb-2">
                                <x-icon name="o-document-text" class="w-5 h-5 text-green-500" />
                                <h4 class="font-semibold text-gray-700 dark:text-gray-300">Keterangan</h4>
                            </div>
                            <p class="text-gray-900 dark:text-gray-100 ml-8 leading-relaxed">
                                {{ $satuan_data->keterangan ?: 'Tidak ada keterangan' }}
                            </p>
                        </div>
                        
                        <!-- Metadata -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Created At -->
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="flex items-center gap-3 mb-2">
                                    <x-icon name="o-calendar-days" class="w-5 h-5 text-purple-500" />
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Dibuat</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 ml-8">
                                    {{ $satuan_data->created_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                            
                            <!-- Updated At -->
                            <div class="p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm">
                                <div class="flex items-center gap-3 mb-2">
                                    <x-icon name="o-clock" class="w-5 h-5 text-orange-500" />
                                    <h4 class="font-semibold text-gray-700 dark:text-gray-300">Diperbarui</h4>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 ml-8">
                                    {{ $satuan_data->updated_at->format('d M Y, H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <x-slot:actions>
                    <div class="flex flex-col sm:flex-row gap-3 pt-6">
                        <x-button icon="o-pencil" 
                                :href="route('satuan.edit', $satuan_data->id)" 
                                wire:navigate 
                                class="btn-primary hover:btn-primary-focus transition-all duration-200 shadow-md flex-1">
                            Edit Satuan
                        </x-button>
                        <x-button icon="o-arrow-left" 
                                :href="route('satuan.index')" 
                                wire:navigate 
                                class="btn-ghost hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            Kembali
                        </x-button>
                    </div>
                </x-slot:actions>
            </x-card>
        </div>
        
        <!-- Action Card -->
        <div class="lg:col-span-1">
            <x-card title="⚡ Aksi Cepat" shadow class="h-fit">
                <div class="space-y-3">
                    <x-button icon="o-pencil" 
                            :href="route('satuan.edit', $satuan_data->id)" 
                            wire:navigate 
                            class="btn-primary w-full hover:btn-primary-focus transition-all duration-200 shadow-sm">
                        Edit Satuan
                    </x-button>
                    
                    <x-button icon="o-list-bullet" 
                            :href="route('satuan.index')" 
                            wire:navigate 
                            class="btn-outline w-full hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Lihat Semua Satuan
                    </x-button>
                    
                    <x-button icon="o-plus" 
                            :href="route('satuan.create')" 
                            wire:navigate 
                            class="btn-success w-full hover:btn-success-focus transition-all duration-200 shadow-sm">
                        Tambah Satuan Baru
                    </x-button>
                </div>
            </x-card>
            
            <!-- Info Card -->
            <x-card title="ℹ️ Informasi" shadow class="h-fit mt-4">
                <div class="space-y-3 text-sm">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <p class="text-blue-700 dark:text-blue-300">
                            <strong>💡 Tips:</strong> Satuan ini dapat digunakan untuk mengatur unit barang di toko Anda.
                        </p>
                    </div>
                    
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                        <p class="text-amber-700 dark:text-amber-300">
                            <strong>⚠️ Perhatian:</strong> Jika satuan ini sudah digunakan pada barang, maka tidak dapat dihapus.
                        </p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    
    <x-back-refresh />
</div>