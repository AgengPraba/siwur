<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="gap-4 mb-6">
        {{-- Main Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mt-6">
            {{-- Content Header --}}
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                        <x-icon name="o-document-text" class="w-5 h-5 mr-2 text-primary" />
                        Detail Stock Opname - {{ $opname->nomor_opname }}
                    </h2>
                    <div class="flex gap-2">
                        <x-button icon="o-printer" class="btn-md  bg-gray-500 hover:bg-gray-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200" wire:click="print" spinner="print">
                            Cetak
                        </x-button>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-6">
                {{-- Header Information --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Nomor Opname</div>
                        <div class="font-semibold text-lg">{{ $opname->nomor_opname }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Tanggal</div>
                        <div class="font-semibold">{{ date('d M Y H:i', strtotime($opname->tanggal_opname)) }}</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Gudang</div>
                        <div class="font-semibold">{{ $opname->gudang->nama_gudang ?? '-' }}</div>
                    </div>
                </div>

                @if ($opname->keterangan)
                    <div class="mb-6">
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 dark:text-blue-400 font-medium mb-1">Keterangan</div>
                            <div class="text-blue-800 dark:text-blue-200">{{ $opname->keterangan }}</div>
                        </div>
                    </div>
                @endif

                {{-- Summary Cards --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Item Dicek</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $opname->details->count() }}</div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Selisih Plus</div>
                        <div class="text-2xl font-bold text-green-600">
                            +{{ $opname->details->where('selisih', '>', 0)->sum('selisih') }}
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 rounded-lg">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Total Selisih Minus</div>
                        <div class="text-2xl font-bold text-red-600">
                            {{ $opname->details->where('selisih', '<', 0)->sum('selisih') }}
                        </div>
                    </div>
                </div>

                {{-- Details Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] bg-white dark:bg-gray-800 rounded-lg">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium">#</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Barang</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Satuan</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Stok Sistem</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Stok Fisik</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Selisih</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($opname->details as $detail)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 text-sm">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        {{ $detail->gudangStock->barang->nama_barang ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        {{ $detail->gudangStock->satuanTerkecil->nama_satuan ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ number_format($detail->stok_sistem) }}</td>
                                    <td class="px-4 py-3 text-sm">{{ number_format($detail->stok_fisik) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        <span @class([
                                            'text-green-600' => $detail->selisih > 0,
                                            'text-red-600' => $detail->selisih < 0,
                                            'text-gray-600' => $detail->selisih == 0,
                                        ])>
                                            {{ $detail->selisih > 0 ? '+' : '' }}{{ number_format($detail->selisih) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span @class([
                                            'px-2 py-1 text-xs font-semibold rounded-full',
                                            'bg-green-100 text-green-800' => $detail->selisih > 0,
                                            'bg-red-100 text-red-800' => $detail->selisih < 0,
                                            'bg-gray-100 text-gray-800' => $detail->selisih == 0,
                                        ])>
                                            @if ($detail->selisih > 0)
                                                Kelebihan
                                            @elseif($detail->selisih < 0)
                                                Kekurangan
                                            @else
                                                Sesuai
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">{{ $detail->keterangan ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        Belum ada detail item
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-6 flex justify-between">
                    <x-button icon="o-arrow-left" class="btn-outline" :href="route('stock-opname.index')" wire:navigate>
                        Kembali
                    </x-button>

                    <div class="flex gap-2">
                        <x-button icon="o-user" class="btn-primary">
                            Dibuat oleh: {{ $opname->user->name ?? '-' }}
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openPrintWindow', (url) => {
            window.open(url, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        });
    });
</script>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('openPrintWindow', (url) => {
            window.open(url, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        });
    });
</script>
