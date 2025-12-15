
<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <!-- Informasi Toko Saat Ini -->
    @if($this->getCurrentToko())
    <div class="mb-6">
        <x-card class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-green-200 dark:border-green-700">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-green-100 dark:bg-green-800 rounded-lg">
                    <x-icon name="o-building-storefront" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <h3 class="font-semibold text-green-900 dark:text-green-100">{{ $type == 'create' ? 'Menambah' : 'Mengedit' }} Gudang untuk</h3>
                    <p class="text-sm text-green-700 dark:text-green-300">{{ $this->getCurrentToko()->nama_toko }}</p>
                    @if($this->getCurrentToko()->alamat_toko)
                        <p class="text-xs text-green-600 dark:text-green-400">{{ $this->getCurrentToko()->alamat_toko }}</p>
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
                        <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                            <x-icon name="{{ $type == 'create' ? 'o-plus' : 'o-pencil' }}" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                                {{ $type == 'create' ? 'Tambah Gudang Baru' : 'Edit Gudang' }}
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $type == 'create' ? 'Lengkapi informasi gudang baru' : 'Perbarui informasi gudang' }}
                            </p>
                        </div>
                    </div>
                </x-slot:title>
                
                <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator class="space-y-6">
                
                    <div class="space-y-6">
                        <div>
                            <x-input wire:model="nama_gudang" 
                                label="Nama Gudang" 
                                placeholder="Masukkan nama gudang (contoh: Gudang Utama, Gudang Cabang A)" 
                                icon="o-building-office"
                                class="shadow-sm" 
                                hint="Nama gudang harus unik dan mudah diingat" />
                        </div>
                        
                        <div>
                            <x-textarea wire:model="keterangan" 
                                label="Keterangan" 
                                placeholder="Masukkan keterangan atau deskripsi gudang (opsional)" 
                                rows="3"
                                class="shadow-sm" 
                                hint="Jelaskan fungsi atau lokasi gudang untuk memudahkan identifikasi" />
                        </div>
                    </div>
                        
                    <x-slot:actions>
                        <div class="flex justify-between items-center pt-6 border-t border-gray-200 dark:border-gray-700">
                            <x-button icon="o-arrow-left" :href="route('gudang.index')" 
                                label="Kembali" class="btn-ghost" wire:navigate />
                            
                            <div class="flex gap-3">
                                @if($type == 'edit')
                                    <x-button icon="o-eye" :href="route('gudang.show', $gudang_ID)" 
                                        label="Lihat" class="btn-outline" wire:navigate />
                                @endif
                                <x-button icon="o-check" label="{{ $type == 'create' ? 'Simpan Gudang' : 'Update Gudang' }}" 
                                    type="submit" class="btn-primary shadow-lg hover:shadow-xl transition-all" 
                                    spinner="{{ $type == 'create' ? 'store' : 'update' }}" />
                            </div>
                        </div>
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    </div>
    <x-back-refresh />
</div>