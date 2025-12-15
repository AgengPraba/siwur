<div class="max-w-4xl mx-auto">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    @php
        $role = $akses_data->role ?? '-';
        $badgeClass = match($role) {
            'admin' => 'badge-error',
            'kasir' => 'badge-info',
            'staff_gudang' => 'badge-warning',
            'akuntan' => 'badge-success',
            default => 'badge-ghost'
        };
        $roleDesc = match($role) {
            'admin' => 'Memiliki akses penuh ke seluruh sistem',
            'kasir' => 'Fokus pada transaksi penjualan dan retur penjualan',
            'staff_gudang' => 'Fokus pada inventory, stock opname, dan transaksi gudang',
            'akuntan' => 'Fokus pada laporan keuangan',
            default => '-'
        };
    @endphp

    <x-card shadow separator>
        <x-slot:title>
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <x-icon name="o-user" class="w-5 h-5 text-primary" />
                </div>
                <span>Detail User</span>
            </div>
        </x-slot:title>

        <!-- User Profile Header -->
        <div class="flex flex-col sm:flex-row items-center gap-6 p-6 bg-base-200/50 rounded-xl mb-6">
            <x-avatar placeholder="{{ strtoupper(substr($user_data->name, 0, 2)) }}" class="!w-20 !h-20 !text-2xl" />
            <div class="text-center sm:text-left">
                <h2 class="text-2xl font-bold">{{ $user_data->name }}</h2>
                <p class="text-base-content/60">{{ $user_data->email }}</p>
                <div class="mt-2">
                    <span class="badge {{ $badgeClass }} badge-lg gap-1">
                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-base-200/30 rounded-lg">
                <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Deskripsi Role</div>
                <div class="font-medium">{{ $roleDesc }}</div>
            </div>

            <div class="p-4 bg-base-200/30 rounded-lg">
                <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Status Email</div>
                <div>
                    @if($user_data->email_verified_at)
                        <span class="badge badge-success gap-1">
                            <x-icon name="o-check-circle" class="w-4 h-4" />
                            Terverifikasi
                        </span>
                    @else
                        <span class="badge badge-warning gap-1">
                            <x-icon name="o-exclamation-circle" class="w-4 h-4" />
                            Belum Terverifikasi
                        </span>
                    @endif
                </div>
            </div>

            <div class="p-4 bg-base-200/30 rounded-lg">
                <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Dibuat Pada</div>
                <div class="font-medium">{{ $user_data->created_at->format('d M Y, H:i') }}</div>
            </div>

            <div class="p-4 bg-base-200/30 rounded-lg">
                <div class="text-xs text-base-content/50 uppercase tracking-wider mb-1">Terakhir Diperbarui</div>
                <div class="font-medium">{{ $user_data->updated_at->format('d M Y, H:i') }}</div>
            </div>
        </div>

        <x-slot:actions>
            <x-button :href="route('user.index')" wire:navigate class="btn-ghost"
                icon="o-arrow-left">Kembali</x-button>
            <x-button :href="route('user.edit', $user_data->id)" wire:navigate class="btn-primary"
                icon="o-pencil">Edit User</x-button>
        </x-slot:actions>
    </x-card>

    <x-back-refresh />
</div>
