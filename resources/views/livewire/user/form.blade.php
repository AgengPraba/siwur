<div class="max-w-4xl mx-auto">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    
    <!-- Form Card -->
    <x-card shadow separator>
        <x-slot:title>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <x-icon name="o-user-plus" class="w-5 h-5 text-primary" />
                </div>
                <span>{{ $type == 'create' ? 'Tambah User Baru' : 'Edit Data User' }}</span>
            </div>
        </x-slot:title>
        <x-slot:subtitle>
            {{ $type == 'create' ? 'Lengkapi informasi user untuk menambahkan data baru' : 'Perbarui informasi user sesuai kebutuhan' }}
        </x-slot:subtitle>

        <div>
            <x-form wire:submit.prevent="{{ $type == 'create' ? 'store' : 'update' }}" no-separator>
                <div class="space-y-6">
                    <!-- Nama -->
                    <x-input wire:model.live="name" label="Nama Lengkap"
                        placeholder="Masukkan nama lengkap user" icon="o-user" />

                    <!-- Email -->
                    <x-input wire:model.live="email" label="Email" placeholder="user@email.com" type="email"
                        icon="o-envelope" />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <x-input wire:model.live="password" 
                            label="{{ $type == 'create' ? 'Password' : 'Password Baru (opsional)' }}" 
                            placeholder="{{ $type == 'create' ? 'Masukkan password' : 'Kosongkan jika tidak diubah' }}" 
                            type="password"
                            icon="o-lock-closed" />

                        <!-- Confirm Password -->
                        <x-input wire:model.live="password_confirmation" label="Konfirmasi Password" 
                            placeholder="Ulangi password" type="password"
                            icon="o-lock-closed" />
                    </div>

                    <!-- Role -->
                    <x-select wire:model.live="role" label="Role" placeholder="-- Pilih Role --"
                        :options="$roles" option-value="id" option-label="name" icon="o-shield-check"
                        :disabled="$type === 'edit'" />
                </div>

                <!-- Role Description -->
                @if($role)
                <div class="mt-6">
                    @php
                        $roleInfo = match($role) {
                            'admin' => ['color' => 'error', 'icon' => 'o-shield-check', 'desc' => 'Memiliki akses penuh ke seluruh sistem termasuk master data, transaksi, inventory, dan laporan.'],
                            'kasir' => ['color' => 'info', 'icon' => 'o-shopping-cart', 'desc' => 'Fokus pada transaksi penjualan, retur penjualan, dan akses baca ke data customer dan barang.'],
                            'staff_gudang' => ['color' => 'warning', 'icon' => 'o-cube', 'desc' => 'Fokus pada inventory, stock opname, transaksi gudang, dan akses baca ke pembelian.'],
                            'akuntan' => ['color' => 'success', 'icon' => 'o-document-chart-bar', 'desc' => 'Fokus pada laporan keuangan dan akses baca ke seluruh transaksi pembelian dan penjualan.'],
                            default => ['color' => 'ghost', 'icon' => 'o-user', 'desc' => '-']
                        };
                    @endphp
                    <x-alert icon="{{ $roleInfo['icon'] }}" class="alert-{{ $roleInfo['color'] }}">
                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', $role)) }}:</span>
                        {{ $roleInfo['desc'] }}
                    </x-alert>
                </div>
                @endif

                <!-- Action Buttons -->
                <x-slot:actions>
                    <x-button :href="route('user.index')" icon="o-arrow-left" label="Kembali"
                        class="btn-ghost" wire:navigate />
                    
                    @if ($type == 'create')
                        <x-button wire:click="resetForm" icon="o-arrow-path" label="Reset"
                            class="btn-outline" type="button" />
                    @endif

                    <x-button type="submit" icon="o-check"
                        label="{{ $type == 'create' ? 'Simpan User' : 'Update User' }}"
                        class="btn-primary"
                        spinner="{{ $type == 'create' ? 'store' : 'update' }}" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-card>

    <!-- Tips Section -->
    <div class="mt-6">
        <x-alert icon="o-information-circle" class="alert-info">
            <div>
                <div class="font-semibold mb-1">Tips Pengisian:</div>
                <ul class="text-sm space-y-1 list-disc list-inside opacity-80">
                    <li>Email harus unik dan valid</li>
                    <li>Password minimal 8 karakter</li>
                    <li>Pilih role sesuai dengan tanggung jawab user</li>
                    <li>Role menentukan akses menu yang tersedia</li>
                </ul>
            </div>
        </x-alert>
    </div>
</div>
