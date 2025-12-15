<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />
    <div class="pb-4">
        <x-card shadow separator>
            <x-slot:title>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-primary/10 rounded-lg">
                        <x-icon name="o-users" class="w-5 h-5 text-primary" />
                    </div>
                    <span>Manajemen User</span>
                </div>
            </x-slot:title>
            <x-slot:subtitle>Kelola data user dan hak akses sistem</x-slot:subtitle>
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <x-button label="Tambah User" link="{{ route('user.create') }}" wire:navigate icon="o-plus"
                    class="btn-primary" responsive />

                <x-input wire:model.live.debounce.500ms="search" autocomplete="off"
                    placeholder="Cari nama atau email..." icon="o-magnifying-glass" 
                    class="w-full md:w-72" clearable />
            </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead class="bg-base-200">
                            <tr>
                                <th class="w-16 text-center">#</th>
                                <th class="cursor-pointer hover:bg-base-300 transition-colors" wire:click="sortBy('name')">
                                    <div class="flex items-center gap-2">
                                        <span>Nama</span>
                                        @if ($sortField === 'name')
                                            <x-icon name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th class="cursor-pointer hover:bg-base-300 transition-colors" wire:click="sortBy('email')">
                                    <div class="flex items-center gap-2">
                                        <span>Email</span>
                                        @if ($sortField === 'email')
                                            <x-icon name="{{ $sortDirection === 'asc' ? 'o-chevron-up' : 'o-chevron-down' }}" class="w-4 h-4" />
                                        @endif
                                    </div>
                                </th>
                                <th>Role</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($user_data as $user)
                                <tr class="hover:bg-base-200/50 transition-colors">
                                    <td class="text-center font-medium text-base-content/60">{{ $start + $loop->index }}</td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <x-avatar placeholder="{{ strtoupper(substr($user->name, 0, 2)) }}" class="!w-10 !h-10" />
                                            <div>
                                                <div class="font-semibold">{{ $user->name }}</div>
                                                <div class="text-xs text-base-content/60 md:hidden">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="hidden md:table-cell">
                                        <span class="text-base-content/80">{{ $user->email }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $role = $user->akses->role ?? '-';
                                            $badgeClass = match($role) {
                                                'admin' => 'badge-error',
                                                'kasir' => 'badge-info',
                                                'staff_gudang' => 'badge-warning',
                                                'akuntan' => 'badge-success',
                                                default => 'badge-ghost'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} gap-1">
                                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-center gap-1">
                                            <x-button icon="o-eye" class="btn-ghost btn-sm btn-circle" tooltip="Lihat"
                                                :href="route('user.show', $user->id)" wire:navigate />
                                            <x-button icon="o-pencil" class="btn-ghost btn-sm btn-circle" tooltip="Edit"
                                                :href="route('user.edit', $user->id)" wire:navigate />
                                            @if ($user->id !== auth()->id())
                                                <x-button icon="o-trash" class="btn-ghost btn-sm btn-circle text-error" tooltip="Hapus"
                                                    x-on:click="$wire.idToDelete = '{{ $user->id }}'" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-12">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="p-4 bg-base-200 rounded-full">
                                                <x-icon name="o-users" class="w-8 h-8 text-base-content/40" />
                                            </div>
                                            <div>
                                                <p class="font-medium text-base-content/60">Tidak ada data user</p>
                                                <p class="text-sm text-base-content/40">Klik tombol "Tambah User" untuk menambahkan</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <x-pagination :rows="$user_data" wire:model.live="perPage" />
                    </div>
                </div>
        </x-card>
    </div>

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="idToDelete" title="Konfirmasi Hapus" separator>
        <div class="flex flex-col items-center gap-4 py-4">
            <div class="p-4 bg-error/10 rounded-full">
                <x-icon name="o-exclamation-triangle" class="w-12 h-12 text-error" />
            </div>
            <div class="text-center">
                <h3 class="text-lg font-semibold">Hapus User?</h3>
                <p class="text-sm text-base-content/60 mt-1">Tindakan ini tidak dapat dibatalkan. Data user akan dihapus secara permanen.</p>
            </div>
        </div>
        <x-slot:actions>
            <x-button label="Batal" class="btn-ghost" x-on:click="$wire.idToDelete = null" />
            <x-button label="Ya, Hapus" class="btn-error" icon="o-trash" wire:click="destroy" />
        </x-slot:actions>
    </x-modal>

    <x-back-refresh />
</div>
