<div class="w-full">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <!-- Modern Header Section -->
    <div class="">
        <div class="max-w mx-auto">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="p-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg">
                        <x-icon name="o-clipboard-document-list" class="w-8 h-8 text-white" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-300 bg-clip-text text-transparent">
                            Stock Opname
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Kelola record stock opname dan penyesuaian stok gudang secara real-time
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-button icon="o-plus" class="btn-primary shadow-lg hover:shadow-xl transition-all duration-200"
                        :href="route('stock-opname.create')" wire:navigate>
                        <span class="hidden sm:inline">Buat Opname Baru</span>
                        <span class="sm:hidden">Buat</span>
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w mx-auto py-6 space-y-6">

        <!-- Enhanced Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
                class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20 dark:border-gray-700/30 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Opname</p>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                            {{ number_format($totalOpname ?? $opnames->total()) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Records tersimpan</p>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/50 rounded-xl">
                        <x-icon name="o-document-text" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div
                class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20 dark:border-gray-700/30 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Item</p>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                            {{ number_format($totalItems ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Item dicek</p>
                    </div>
                    <div class="p-3 bg-amber-100 dark:bg-amber-900/50 rounded-xl">
                        <x-icon name="o-cube" class="w-6 h-6 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
            </div>

            <div
                class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20 dark:border-gray-700/30 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Stok Fisik</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">
                            {{ number_format($totalStokFisik ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Total unit</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-xl">
                        <x-icon name="o-check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <div
                class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/20 dark:border-gray-700/30 hover:shadow-xl transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Adjustment</p>
                        <p @class([
                            'text-2xl font-bold mt-1',
                            'text-green-600 dark:text-green-400' => ($totalSelisih ?? 0) > 0,
                            'text-red-600 dark:text-red-400' => ($totalSelisih ?? 0) < 0,
                            'text-gray-600 dark:text-gray-400' => ($totalSelisih ?? 0) == 0,
                        ])>
                            {{ ($totalSelisih ?? 0) > 0 ? '+' : '' }}{{ number_format($totalSelisih ?? 0) }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">Selisih total</p>
                    </div>
                    <div @class([
                        'p-3 rounded-xl',
                        'bg-green-100 dark:bg-green-900/50' => ($totalSelisih ?? 0) > 0,
                        'bg-red-100 dark:bg-red-900/50' => ($totalSelisih ?? 0) < 0,
                        'bg-gray-100 dark:bg-gray-900/50' => ($totalSelisih ?? 0) == 0,
                    ])>
                        <x-icon name="o-chart-pie" @class([
                            'w-6 h-6',
                            'text-green-600 dark:text-green-400' => ($totalSelisih ?? 0) > 0,
                            'text-red-600 dark:text-red-400' => ($totalSelisih ?? 0) < 0,
                            'text-gray-600 dark:text-gray-400' => ($totalSelisih ?? 0) == 0,
                        ]) />
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Filter Section -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 dark:border-gray-700/30">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <x-icon name="o-funnel" class="w-5 h-5 mr-2 text-indigo-600" />
                        Filter & Pencarian
                        @if ($search || $filterGudang || $filterSelisih || $tanggal_mulai || $tanggal_selesai)
                            <span
                                class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Filter Aktif
                            </span>
                        @endif
                    </h3>
                    <x-button wire:click="resetFilter" icon="o-arrow-path" class="btn-outline btn-sm">
                        Reset
                    </x-button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pencarian</label>
                        <x-input wire:model.live.debounce.300ms="search" placeholder="Nomor opname..."
                            icon="o-magnifying-glass"
                            class="w-full bg-gray-50/50 dark:bg-gray-700/50 border-gray-200/50 dark:border-gray-600/50" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gudang</label>
                        <x-select wire:model.live="filterGudang" :options="$gudangOptions->prepend(['id' => '', 'nama_gudang' => 'Semua Gudang'])" option-label="nama_gudang"
                            option-value="id" class="w-full bg-gray-50/50 dark:bg-gray-700/50" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Selisih</label>
                        <x-select wire:model.live="filterSelisih" :options="[
                            ['id' => '', 'name' => 'Semua'],
                            ['id' => 'plus', 'name' => 'Kelebihan (+)'],
                            ['id' => 'minus', 'name' => 'Kekurangan (-)'],
                            ['id' => 'netral', 'name' => 'Sesuai (0)'],
                        ]" option-label="name"
                            option-value="id" class="w-full bg-gray-50/50 dark:bg-gray-700/50" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Mulai</label>
                        <x-input wire:model.live="tanggal_mulai" type="date"
                            class="w-full bg-gray-50/50 dark:bg-gray-700/50" />
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal
                            Selesai</label>
                        <x-input wire:model.live="tanggal_selesai" type="date"
                            class="w-full bg-gray-50/50 dark:bg-gray-700/50" />
                        @error('tanggal_selesai')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Data Table -->
        <div
            class="bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 dark:border-gray-700/30 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/50 dark:border-gray-700/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        Data Stock Opname
                    </h3>
                    <div class="flex items-center space-x-2 text-sm text-gray-500">
                        <span>Menampilkan {{ $opnames->count() }} dari {{ $opnames->total() }} data</span>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Nomor Opname</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Tanggal</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Gudang</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Items</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Adjustment</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Operator</th>
                            <th
                                class="px-6 py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/50 dark:divide-gray-700/50">
                        @forelse ($opnames as $op)
                            <tr
                                class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-all duration-200 group">
                                <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 text-xs font-medium">
                                        {{ $loop->iteration + ($opnames->currentPage() - 1) * $opnames->perPage() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $op->nomor_opname }}</span>
                                        @if ($op->keterangan)
                                            <span
                                                class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ Str::limit($op->keterangan, 30) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ \Carbon\Carbon::parse($op->tanggal_opname)->format('d M Y') }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($op->tanggal_opname)->format('H:i') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="p-2 bg-blue-100 dark:bg-blue-900/50 rounded-lg mr-3">
                                            <x-icon name="o-building-storefront"
                                                class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                                        </div>
                                        <span
                                            class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $op->gudang->nama_gudang ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                                            <x-icon name="o-cube" class="w-3 h-3 mr-1" />
                                            {{ number_format($op->details_count ?? $op->details->count()) }} items
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $totalSelisih = $op->details->sum('selisih');
                                    @endphp
                                    <div class="flex items-center">
                                        @if ($totalSelisih > 0)
                                            <div
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200">
                                                <x-icon name="o-arrow-up" class="w-3 h-3 mr-1" />
                                                {{ number_format($totalSelisih) }}
                                            </div>
                                        @elseif($totalSelisih < 0)
                                            <div
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200">
                                                <x-icon name="o-arrow-down" class="w-3 h-3 mr-1" />
                                                {{ number_format($totalSelisih) }}
                                            </div>
                                        @else
                                            <div
                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-200">
                                                Sesuai
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="p-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mr-2">
                                            <x-icon name="o-user" class="w-3 h-3 text-gray-600 dark:text-gray-400" />
                                        </div>
                                        <span
                                            class="text-sm text-gray-900 dark:text-gray-100">{{ $op->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-2">
                                        <x-button icon="o-eye"
                                            class="btn-sm bg-blue-500 hover:bg-blue-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                            :href="route('stock-opname.show', $op->id)" wire:navigate>
                                        </x-button>
                                        <x-button icon="o-printer"
                                            class="btn-sm bg-gray-500 hover:bg-gray-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                            wire:click="printOpname({{ $op->id }})" spinner="printOpname">
                                        </x-button>
                                        <x-button icon="o-trash"
                                            class="btn-sm bg-red-500 hover:bg-red-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                            wire:click="openDeleteModal({{ $op->id }})">
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                                            <x-icon name="o-clipboard-document-list" class="w-8 h-8 text-gray-400" />
                                        </div>
                                        <div class="text-gray-500 dark:text-gray-400">
                                            <p class="text-lg font-medium">Belum ada data stock opname</p>
                                            <p class="text-sm mt-1">Mulai dengan membuat stock opname pertama Anda</p>
                                        </div>
                                        <x-button icon="o-plus" class="btn-primary" :href="route('stock-opname.create')" wire:navigate>
                                            Buat Stock Opname
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Enhanced Pagination -->
            @if ($opnames->hasPages())
                <div
                    class="px-6 py-4 border-t border-gray-200/50 dark:border-gray-700/50 bg-gray-50/30 dark:bg-gray-700/30">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Menampilkan <span class="font-medium">{{ $opnames->firstItem() }}</span> sampai
                            <span class="font-medium">{{ $opnames->lastItem() }}</span> dari
                            <span class="font-medium">{{ $opnames->total() }}</span> hasil
                        </div>
                        <div class="flex items-center space-x-2">
                            {{ $opnames->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Enhanced Delete Confirmation Modal -->
    <x-modal wire:model="deleteModal" class="backdrop-blur-sm"
        box-class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border border-red-200 dark:border-red-800">

        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full">
                    <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Konfirmasi Hapus</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>

            @if ($itemToDelete)
                <!-- Item Details -->
                <div
                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                            <x-icon name="o-clipboard-document-list" class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-red-900 dark:text-red-100">
                                {{ $itemToDelete->nomor_opname }}
                            </div>
                            <div class="text-sm text-red-700 dark:text-red-300 mt-1">
                                <div class="flex items-center space-x-4">
                                    <span>📅
                                        {{ \Carbon\Carbon::parse($itemToDelete->tanggal_opname)->format('d M Y H:i') }}</span>
                                    <span>📦 {{ $itemToDelete->details->count() }} items</span>
                                    <span>🏢 {{ $itemToDelete->gudang->nama_gudang ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Message -->
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <x-icon name="o-exclamation-circle"
                            class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" />
                        <div class="text-sm text-yellow-800 dark:text-yellow-200">
                            <p class="font-medium mb-1">Perhatian!</p>
                            <p>Stock adjustment yang telah dilakukan akan dikembalikan ke nilai semula. Data stock
                                opname akan dihapus permanen dan tidak dapat dipulihkan.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3">
                <x-button class="btn-outline" wire:click="$set('deleteModal', false)">
                    Batal
                </x-button>
                <x-button
                    class="bg-red-600 hover:bg-red-700 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-200"
                    wire:click="confirmDelete" spinner="confirmDelete">
                    <x-icon name="o-trash" class="w-4 h-4 mr-2" />
                    Hapus Sekarang
                </x-button>
            </div>
        </div>
    </x-modal>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openPrintWindow', (url) => {
            window.open(url, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        });
    });
</script>
