
<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <!-- Header Card -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-lg p-6 text-white shadow-lg">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                    <x-icon name="{{ $type == 'create' ? 'o-plus' : 'o-pencil' }}" class="w-8 h-8" />
                </div>
                <div>
                    <h2 class="text-2xl font-bold mb-1">
                        {{ $type == 'create' ? 'Tambah Satuan Baru' : 'Edit Satuan' }}
                    </h2>
                    <p class="text-green-100">
                        {{ $type == 'create' ? 'Buat satuan barang baru untuk toko Anda' : 'Perbarui informasi satuan barang' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-4">
        <!-- Form Card -->
        <div class="lg:col-span-2">
            <x-card title="{{ $type == 'create' ? 'Form Tambah' : 'Form Edit' }} Satuan" 
                   subtitle="Lengkapi informasi satuan barang di bawah ini" 
                   shadow separator class="h-fit">
                   
                <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator class="space-y-6">
                    
                    <!-- Nama Satuan -->
                    <div class="space-y-2">
                        <x-input wire:model="nama_satuan" 
                               label="Nama Satuan" 
                               placeholder="Contoh: Kilogram, Meter, Buah, dll" 
                               class="input-bordered focus:input-primary"
                               hint="Masukkan nama satuan yang akan digunakan" />
                    </div>
                    
                    <!-- Keterangan -->
                    <div class="space-y-2">
                        <x-textarea wire:model="keterangan" 
                                  label="Keterangan" 
                                  placeholder="Deskripsi atau penjelasan tambahan tentang satuan ini..." 
                                  class="textarea-bordered focus:textarea-primary min-h-24"
                                  hint="Opsional: Berikan keterangan untuk memperjelas penggunaan satuan" />
                    </div>
                    
                    <x-slot:actions>
                        <div class="flex flex-col sm:flex-row gap-3 pt-4">
                            <x-button icon="o-check" 
                                    label="{{ $type == 'create' ? 'Simpan Satuan' : 'Update Satuan' }}" 
                                    type="submit" 
                                    class="btn-primary hover:btn-primary-focus transition-all duration-200 shadow-md flex-1" 
                                    spinner />
                            <x-button icon="o-arrow-left" 
                                    :href="route('satuan.index')" 
                                    label="Kembali" 
                                    class="btn-ghost hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" 
                                    wire:navigate />
                        </div>
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
        
        <!-- Info Card -->
        <div class="lg:col-span-1">
            <x-card title="💡 Tips & Panduan" shadow class="h-fit">
                <div class="space-y-4 text-sm">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                        <h4 class="font-semibold text-blue-800 dark:text-blue-200 mb-2">📏 Contoh Satuan</h4>
                        <ul class="text-blue-700 dark:text-blue-300 space-y-1">
                            <li>• Kilogram (Kg)</li>
                            <li>• Meter (M)</li>
                            <li>• Buah (Pcs)</li>
                            <li>• Liter (L)</li>
                            <li>• Kotak (Box)</li>
                        </ul>
                    </div>
                    
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                        <h4 class="font-semibold text-green-800 dark:text-green-200 mb-2">✅ Best Practice</h4>
                        <ul class="text-green-700 dark:text-green-300 space-y-1 text-xs">
                            <li>• Gunakan nama yang jelas dan mudah dipahami</li>
                            <li>• Konsisten dengan standar yang umum digunakan</li>
                            <li>• Tambahkan keterangan jika diperlukan</li>
                        </ul>
                    </div>
                    
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                        <h4 class="font-semibold text-amber-800 dark:text-amber-200 mb-2">⚠️ Perhatian</h4>
                        <p class="text-amber-700 dark:text-amber-300 text-xs">
                            Satuan yang sudah digunakan pada barang tidak dapat dihapus. Pastikan nama satuan sudah benar sebelum menyimpan.
                        </p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    
    <x-back-refresh />
</div>