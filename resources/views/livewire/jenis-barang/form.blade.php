
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
                    <p class="text-sm text-blue-600 dark:text-blue-300">{{ $type == 'create' ? 'Menambahkan' : 'Mengedit' }} jenis barang untuk toko Anda</p>
                </div>
            </div>
        </x-card>
    </div>
    @endif
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-4 mt-6">
        <!-- Form Card -->
        <div class="lg:col-span-2">
            <x-card shadow separator class="overflow-hidden">
                <!-- Header dengan Gradient -->
                <div class="bg-gradient-to-r from-{{ $type == 'create' ? 'green' : 'blue' }}-500 to-{{ $type == 'create' ? 'blue' : 'purple' }}-600 px-6 py-4 -mx-6 -mt-6 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                            <x-icon name="{{ $type == 'create' ? 'o-plus' : 'o-pencil' }}" class="w-6 h-6 text-white" />
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-white">{{ $type == 'create' ? 'Tambah' : 'Edit' }} Jenis Barang</h2>
                            <p class="text-white/80 text-sm">{{ $type == 'create' ? 'Buat kategori barang baru' : 'Perbarui informasi jenis barang' }}</p>
                        </div>
                    </div>
                </div>
                
                <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator class="space-y-6">
                    <!-- Nama Jenis Barang -->
                    <div class="space-y-2">
                        <x-input 
                            wire:model="nama_jenis_barang" 
                            label="Nama Jenis Barang" 
                            placeholder="Contoh: Elektronik, Makanan, Pakaian..." 
                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                            hint="Masukkan nama kategori barang yang mudah diingat" />
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="space-y-2">
                        <x-textarea 
                            wire:model="keterangan" 
                            label="Keterangan" 
                            placeholder="Deskripsi singkat tentang jenis barang ini..." 
                            rows="4"
                            class="border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                            hint="Berikan penjelasan yang membantu mengidentifikasi jenis barang ini" />
                    </div>
                    
                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <x-button 
                            icon="o-check" 
                            label="{{ $type == 'create' ? 'Simpan Data' : 'Update Data' }}" 
                            type="submit" 
                            class="flex-1 btn-primary shadow-lg hover:shadow-xl transition-all duration-200" 
                            spinner="{{ $type == 'create' ? 'store' : 'update' }}" />
                        <x-button 
                            icon="o-arrow-left" 
                            :href="route('jenis-barang.index')" 
                            label="Kembali" 
                            class="flex-1 btn-outline border-gray-300 hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700" 
                            wire:navigate />
                    </div>
                </x-form>
            </x-card>
        </div>
        
        <!-- Info Panel -->
        <div class="lg:col-span-1">
            <x-card title="💡 Tips & Panduan" shadow separator class="sticky top-6">
                <div class="space-y-4 text-sm">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">📦 Nama Jenis Barang</h4>
                        <p class="text-blue-700 dark:text-blue-300">Gunakan nama yang jelas dan mudah dipahami. Contoh: "Elektronik", "Makanan & Minuman", "Pakaian".</p>
                    </div>
                    
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-700">
                        <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2">📝 Keterangan</h4>
                        <p class="text-green-700 dark:text-green-300">Berikan deskripsi yang membantu tim Anda memahami kategori ini dengan lebih baik.</p>
                    </div>
                    
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-700">
                        <h4 class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2">⚡ Saran</h4>
                        <p class="text-yellow-700 dark:text-yellow-300">Buat kategori yang tidak terlalu spesifik agar mudah digunakan untuk berbagai produk.</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    
    <x-back-refresh />
</div>