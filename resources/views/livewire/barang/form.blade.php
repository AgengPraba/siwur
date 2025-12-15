<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <div class="grid grid-cols-1 md:grid-cols-12">
        <div class="md:col-span-12">
            <!-- Card dengan design modern -->
            <div class="bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Header Card -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 dark:from-blue-700 dark:to-blue-800 px-8 py-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                                @if($type == 'create')
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white">
                                    {{ $type == 'create' ? 'Tambah Barang Baru' : 'Edit Barang' }}
                                </h1>
                                <p class="text-blue-100 mt-1">
                                    {{ $type == 'create' ? 'Lengkapi form di bawah untuk menambah barang baru' : 'Perbarui informasi barang yang dipilih' }}
                                </p>
                            </div>
                        </div>
                        
                        <!-- Toko Info -->
                        @php
                            $user = Auth::user();
                            $akses = $user->akses ?? null;
                            $namaToko = $akses && $akses->toko ? $akses->toko->nama_toko : 'Toko Tidak Diketahui';
                        @endphp
                        <div class="hidden md:flex items-center gap-2 bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm">
                            <x-icon name="o-building-storefront" class="w-5 h-5 text-white" />
                            <span class="text-white font-medium">{{ $namaToko }}</span>
                        </div>
                    </div>
                    
                    <!-- Mobile Toko Info -->
                    <div class="md:hidden mt-4 flex items-center gap-2 bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm w-fit">
                        <x-icon name="o-building-storefront" class="w-4 h-4 text-white" />
                        <span class="text-white text-sm font-medium">{{ $namaToko }}</span>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator class="space-y-6">
                        
                        <!-- Informasi Dasar Barang -->
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                Informasi Dasar Barang
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input 
                                        wire:model="kode_barang" 
                                        label="Kode Barang" 
                                        placeholder="Masukkan kode barang..." 
                                        class="text-lg"
                                        hint="Kode barang harus unik"
                                    />
                                </div>
                                <div>
                                    <x-input 
                                        wire:model="nama_barang" 
                                        label="Nama Barang" 
                                        placeholder="Masukkan nama barang..." 
                                        class="text-lg"
                                        hint="Nama barang harus mudah diingat"
                                    />
                                </div>
                                
                                <div class="md:col-span-2">
                                    <x-textarea 
                                        wire:model="keterangan" 
                                        label="Keterangan" 
                                        placeholder="Masukkan keterangan atau deskripsi barang..." 
                                        rows="3"
                                        hint="Opsional: Deskripsi detail tentang barang"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Kategori dan Satuan -->
                        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-6 border border-amber-200 dark:border-amber-700">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                                Kategori & Satuan
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <x-select 
                                    wire:model="jenis_barang_id" 
                                    label="Jenis Barang" 
                                    :options="$jenis_barang_data"
                                    placeholder="-- Pilih Jenis Barang --" 
                                    class="select-bordered"
                                    hint="Kategori/jenis dari barang ini"
                                />
                                
                                <x-select 
                                    wire:model="satuan_terkecil_id" 
                                    label="Satuan Terkecil" 
                                    :options="$satuan_data"
                                    placeholder="-- Pilih Satuan Terkecil --" 
                                    class="select-bordered"
                                    hint="Satuan terkecil untuk perhitungan stok"
                                />
                            </div>
                            
                            <!-- Info Box -->
                            <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div class="text-sm text-blue-800 dark:text-blue-200">
                                        <p class="font-medium">Informasi Satuan Terkecil:</p>
                                        <p class="mt-1">Sistem akan otomatis membuat entry di tabel Barang-Satuan dengan konversi 1:1 untuk satuan terkecil yang dipilih.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-600">
                            <x-button 
                                icon="o-backspace" 
                                :href="route('barang.index')" 
                                label="Batal" 
                                class="btn-ghost btn-lg text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700"
                                wire:navigate 
                            />
                            <x-button 
                                icon="o-check" 
                                label="{{ $type == 'create' ? 'Simpan Barang' : 'Perbarui Barang' }}" 
                                type="submit" 
                                class="btn-primary btn-lg px-8 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-200" 
                                spinner 
                            />
                        </div>
                    </x-form>
                </div>
            </div>
        </div>
    </div>
    
    <x-back-refresh />

    {{-- Custom Styles --}}
    <style>
        /* Custom animations and effects */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-slide-in {
            animation: slideInUp 0.4s ease-out;
        }
        
        /* Hover effects for inputs */
        .input:focus, .select:focus, .textarea:focus {
            transform: translateY(-1px);
            transition: transform 0.2s ease-in-out;
        }
        
        /* Button hover effects */
        .btn:hover {
            transform: translateY(-2px);
            transition: all 0.2s ease-in-out;
        }
    </style>

    {{-- Custom Scripts --}}
    <script>
        // Add smooth animations when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const formCard = document.querySelector('.bg-gradient-to-br');
            if (formCard) {
                formCard.classList.add('animate-slide-in');
            }
        });
    </script>
</div>
