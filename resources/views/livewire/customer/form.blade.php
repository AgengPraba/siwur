<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center space-x-3">
            <div class="p-3 bg-blue-500 dark:bg-blue-600 rounded-lg">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $type == 'create' ? 'Tambah Customer Baru' : 'Edit Data Customer' }}
                </h1>
                <p class="text-gray-600 dark:text-gray-300 mt-1">
                    {{ $type == 'create' ? 'Lengkapi informasi customer untuk menambahkan data baru' : 'Perbarui informasi customer sesuai kebutuhan' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 dark:from-blue-600 dark:to-indigo-700 px-6 py-4">
            <h2 class="text-xl font-semibold text-white flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                Informasi Customer
            </h2>
        </div>

        <div class="p-6">
            <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Customer -->
                    <div class="md:col-span-2">
                        <x-input wire:model.live="nama_customer" label="Nama Customer"
                            placeholder="Masukkan nama lengkap customer" icon="o-user"
                            class="input-bordered focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Email -->
                    <div>
                        <x-input wire:model.live="email" label="Email" placeholder="customer@email.com" type="email"
                            icon="o-envelope" class="input-bordered focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- No HP -->
                    <div>
                        <x-input wire:model.live="no_hp" label="Nomor Telepon" placeholder="08xxxxxxxxxx" icon="o-phone"
                            class="input-bordered focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Alamat -->
                    <div class="md:col-span-2">
                        <x-textarea wire:model.live="alamat" label="Alamat Lengkap"
                            placeholder="Masukkan alamat lengkap customer" rows="3"
                            class="textarea-bordered focus:border-blue-500 focus:ring-blue-500" />
                    </div>

                    <!-- Keterangan -->
                    <div class="md:col-span-2">
                        <x-textarea wire:model.live="keterangan" label="Keterangan (Opsional)"
                            placeholder="Catatan tambahan tentang customer" rows="2"
                            class="textarea-bordered focus:border-blue-500 focus:ring-blue-500" />
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <x-button type="submit" icon="o-check"
                        label="{{ $type == 'create' ? 'Simpan Customer' : 'Update Customer' }}"
                        class="btn-primary flex-1 sm:flex-none px-8 py-3 text-white bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 dark:from-blue-600 dark:to-indigo-700 dark:hover:from-blue-700 dark:hover:to-indigo-800 border-0 shadow-lg"
                        spinner="{{ $type == 'create' ? 'store' : 'update' }}" />

                    @if ($type == 'create')
                        <x-button wire:click="resetForm" icon="o-arrow-path" label="Reset Form"
                            class="btn-outline btn-secondary flex-1 sm:flex-none px-6 py-3" />
                    @endif

                    <x-button :href="route('customer.index')" icon="o-arrow-left" label="Kembali"
                        class="btn-outline btn-error flex-1 sm:flex-none px-6 py-3" wire:navigate />
                </div>
            </x-form>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300 mb-1">Tips Pengisian:</h3>
                <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1">
                    <li>• Pastikan email customer unik dan valid</li>
                    <li>• Nomor telepon harus dapat dihubungi</li>
                    <li>• Alamat lengkap memudahkan pengiriman</li>
                    <li>• Keterangan dapat diisi untuk catatan khusus</li>
                </ul>
            </div>
        </div>
    </div>
</div>
