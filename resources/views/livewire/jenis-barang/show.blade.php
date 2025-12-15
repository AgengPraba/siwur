
<div>
    <x-breadcrumbs :items="$breadcrumbs" />
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
                    <p class="text-sm text-blue-600 dark:text-blue-300">Detail informasi jenis barang</p>
                </div>
            </div>
        </x-card>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-4 mt-6">
        <!-- Detail Card -->
        <div class="lg:col-span-2">
            <x-card shadow separator class="overflow-hidden">
                <!-- Header dengan Gradient -->
                <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4 -mx-6 -mt-6 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <x-icon name="o-eye" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">Detail Jenis Barang</h2>
                            <p class="text-white/80 text-sm">Informasi lengkap kategori barang</p>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="space-y-6">
                    <!-- Nama Jenis Barang -->
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/20 dark:to-blue-900/20 p-6 rounded-xl border border-green-200 dark:border-green-700">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gradient-to-r from-green-400 to-blue-500 rounded-xl flex items-center justify-center">
                                <span class="text-white font-bold text-lg">{{ strtoupper(substr($jenis_barang_data->nama_jenis_barang, 0, 2)) }}</span>
                            </div>
                            <div class="flex-1">
                                <label class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 block">Nama Jenis Barang</label>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $jenis_barang_data->nama_jenis_barang }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="bg-gray-50 dark:bg-gray-800/50 p-6 rounded-xl border border-gray-200 dark:border-gray-700">
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-3 block flex items-center gap-2">
                            <x-icon name="o-document-text" class="w-4 h-4" />
                            Keterangan
                        </label>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
                                {{ $jenis_barang_data->keterangan ?: 'Tidak ada keterangan yang tersedia.' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Informasi Waktu -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg border border-blue-200 dark:border-blue-700">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="o-calendar-days" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Dibuat Pada</span>
                            </div>
                            <p class="text-blue-700 dark:text-blue-300 font-semibold">
                                {{ $jenis_barang_data->created_at ? $jenis_barang_data->created_at->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                        
                        <div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg border border-amber-200 dark:border-amber-700">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="o-clock" class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                                <span class="text-sm font-medium text-amber-900 dark:text-amber-100">Terakhir Diupdate</span>
                            </div>
                            <p class="text-amber-700 dark:text-amber-300 font-semibold">
                                {{ $jenis_barang_data->updated_at ? $jenis_barang_data->updated_at->format('d M Y, H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <x-button 
                            icon="o-pencil" 
                            label="Edit Data" 
                            :href="route('jenis-barang.edit', $jenis_barang_data->id)" 
                            class="flex-1 btn-warning text-white shadow-lg hover:shadow-xl transition-all duration-200" 
                            wire:navigate />
                        <x-button 
                            icon="o-arrow-left" 
                            :href="route('jenis-barang.index')" 
                            label="Kembali ke Daftar" 
                            class="flex-1 btn-outline border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700" 
                            wire:navigate />
                    </div>
                </div>
            </x-card>
        </div>
        
        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-card title="📊 Informasi Tambahan" shadow separator class="sticky top-6">
                <div class="space-y-4">
                    <!-- Status Card -->
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-700">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-sm font-medium text-green-900 dark:text-green-100">Status</span>
                        </div>
                        <p class="text-green-700 dark:text-green-300 font-semibold">✅ Aktif</p>
                    </div>
                    
                    <!-- ID Card -->
                    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="o-hashtag" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">ID</span>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 font-mono font-semibold">#{{ $jenis_barang_data->id }}</p>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                        <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100 mb-3">⚡ Aksi Cepat</h4>
                        <div class="space-y-2">
                            <x-button 
                                icon="o-pencil" 
                                label="Edit" 
                                :href="route('jenis-barang.edit', $jenis_barang_data->id)" 
                                class="w-full btn-sm btn-warning text-white" 
                                wire:navigate />
                            <x-button 
                                icon="o-list-bullet" 
                                label="Lihat Semua" 
                                :href="route('jenis-barang.index')" 
                                class="w-full btn-sm btn-outline" 
                                wire:navigate />
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    
    <x-back-refresh />
</div>