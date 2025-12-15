<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-green-500 dark:bg-green-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $type == 'create' ? 'Tambah Supplier Baru' : 'Edit Data Supplier' }}
                </h1>
                <p class="text-gray-600 dark:text-gray-300 mt-1">
                    {{ $type == 'create' ? 'Lengkapi informasi supplier untuk menambahkan data baru' : 'Perbarui informasi supplier sesuai kebutuhan' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 dark:from-green-600 dark:to-emerald-700 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                    </path>
                </svg>
                Informasi Supplier
            </h2>
        </div>

        <div class="p-6">
            <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Supplier -->
                    <div class="md:col-span-2">
                        <x-input wire:model.live="nama_supplier" label="Nama Supplier"
                            placeholder="Masukkan nama lengkap supplier" icon="o-building-office"
                            class="input-bordered focus:border-green-500 focus:ring-green-500" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input wire:model.live="email" label="Email" placeholder="supplier@email.com" type="email"
                            icon="o-envelope" class="input-bordered focus:border-green-500 focus:ring-green-500" />
                    </div>

                    <!-- No HP -->
                    <div>
                        <x-input wire:model.live="no_hp" label="Nomor Telepon" placeholder="08xxxxxxxxxx" icon="o-phone"
                            class="input-bordered focus:border-green-500 focus:ring-green-500" />
                    </div>

                    <!-- Alamat -->
                    <div class="md:col-span-2">
                        <x-textarea wire:model.live="alamat" label="Alamat Lengkap"
                            placeholder="Masukkan alamat lengkap supplier" rows="3"
                            class="textarea-bordered focus:border-green-500 focus:ring-green-500" />
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2">
                        <x-textarea wire:model.live="keterangan" label="Keterangan (Opsional)"
                            placeholder="Catatan tambahan tentang supplier" rows="2"
                            class="textarea-bordered focus:border-green-500 focus:ring-green-500" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <x-button type="submit" icon="o-check"
                        label="{{ $type == 'create' ? 'Simpan Supplier' : 'Update Supplier' }}"
                        class="btn-primary flex-1 sm:flex-none px-8 py-3 text-white bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 dark:from-green-600 dark:to-emerald-700 dark:hover:from-green-700 dark:hover:to-emerald-800 border-0 shadow-lg"
                        spinner="{{ $type == 'create' ? 'store' : 'update' }}" />

                    @if ($type == 'create')
                        <x-button wire:click="resetForm" icon="o-arrow-path" label="Reset Form"
                            class="btn-outline btn-secondary flex-1 sm:flex-none px-6 py-3" />
                    @endif

                    <x-button :href="route('supplier.index')" icon="o-arrow-left" label="Kembali"
                        class="btn-outline btn-error flex-1 sm:flex-none px-6 py-3" wire:navigate />
                </div>
            </x-form>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="mt-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-green-500 dark:text-green-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300 mb-1">Tips Pengisian:</h3>
                <ul class="text-sm text-green-700 dark:text-green-400 space-y-1">
                    <li>• Pastikan nama supplier unik dalam toko Anda</li>
                    <li>• Email supplier harus valid dan dapat dihubungi</li>
                    <li>• Nomor telepon untuk komunikasi pemesanan</li>
                    <li>• Alamat lengkap untuk pengiriman dokumen</li>
                    <li>• Keterangan dapat berisi informasi khusus supplier</li>
                </ul>
            </div>
        </div>
    </div>
</div>
