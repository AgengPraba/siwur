<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="gap-4 mb-6">
        {{-- Main Content --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mt-6">
            {{-- Content Header --}}
            <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                    <x-icon name="o-document-text" class="w-5 h-5 mr-2 text-primary" />
                    Form Stock Opname
                </h2>
            </div>
            {{-- Content --}}
            <div class="p-6">
                <form wire:submit.prevent="save" class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="xl:col-span-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <x-icon name="o-document-text" class="w-5 h-5 mr-2 text-primary" />
                            Informasi Stock Opname
                        </h3>

                        @if ($type == 'create' && count($details) > 0)
                            {{-- Info Draft --}}
                            <div
                                class="mb-3 p-2 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg">
                                <div class="flex items-center">
                                    <x-icon name="o-cloud-arrow-up"
                                        class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2" />
                                    <span class="text-xs text-blue-700 dark:text-blue-300 font-medium">Data disimpan
                                        sementara</span>
                                </div>
                            </div>
                        @endif
                        <div class="space-y-3">
                            <x-input wire:model="nomor_opname" label="Nomor Opname" readonly
                                placeholder="OPN20241201001" class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-input readonly wire:model="tanggal_opname" label="Tanggal Opname"
                                placeholder="Pilih Tanggal & Waktu" class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-select wire:model.live="gudang_id" label="Gudang" placeholder="Pilih Gudang"
                                :options="$gudang_data" option-label="nama" option-value="id"
                                class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-textarea wire:model="keterangan" label="Keterangan" placeholder="Opsional"
                                class="dark:bg-gray-700 dark:text-gray-200" />
                        </div>
                    </div>

                    {{-- Item stock opname --}}
                    {{-- Item Input & List - Compact --}}
                    <div class="xl:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <x-icon name="o-cube" class="w-5 h-5 mr-2 text-primary" />
                            Item Stock Opname
                        </h3>
                        {{-- konten --}}
                        <div>
                            <x-choices wire:model.live="detail_barang_id" :options="$this->getBarangSearch" placeholder="Cari barang"
                                single searchable clearable class="h-10" label="Barang"
                                title="Ketik nama barang atau kode untuk mencari">
                            </x-choices>
                        </div>
                        <div>
                            <x-input wire:model="detail_satuan_name" label="Satuan" placeholder="Pilih Satuan" readonly
                                class="dark:bg-gray-700 dark:text-gray-200" />
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
                            <x-input wire:model="detail_stok_sistem" label="Stok Sistem" type="number" step="1"
                                inputmode="numeric" readonly />
                            <x-input wire:model="detail_stok_fisik" label="Stok Fisik" type="number" step="1"
                                inputmode="numeric" />
                        </div>
                        <div class="flex justify-end my-6">
                            <x-button type="button" wire:click.prevent="addDetail"
                                class="btn-primary dark:bg-primary-600 dark:hover:bg-primary-700" spinner
                                icon="o-plus">Tambah</x-button>
                        </div>

                    </div>
                    <div class="xl:col-span-3">
                         <h3 class="mt-3 text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <x-icon name="o-document-text" class="w-5 h-5 mr-2 text-primary" />
                            Informasi Detail Barang
                        </h3>
                        @if (count($details) > 0)
                            <div class="mt-4">
                                <table class="w-full text-left bg-white dark:bg-gray-800 rounded-lg">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-3 py-2">#</th>
                                            <th class="px-3 py-2">Barang</th>
                                            <th class="px-3 py-2">Stok Sistem</th>
                                            <th class="px-3 py-2">Stok Fisik</th>
                                            <th class="px-3 py-2">Satuan</th>
                                            <th class="px-3 py-2">Selisih</th>
                                            <th class="px-3 py-2">Harga Satuan</th>
                                            <th class="px-3 py-2">Total Harga</th>
                                            <th class="px-3 py-2">Adjustment</th>
                                            <th class="px-3 py-2">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $d)
                                            <tr class="border-t">
                                                <td class="px-3 py-2">{{ $loop->iteration }}</td>
                                                <td class="px-3 py-2">{{ $d['barang_nama'] ?? '-' }}</td>
                                                <td class="px-3 py-2">
                                                    {{ number_format($d['stok_sistem'], 0, ',', '.') }}</td>
                                                <td class="px-3 py-2">{{ number_format($d['stok_fisik'], 0, ',', '.') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    {{ $d['satuan_nama'] ?? '-' }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    @if ($d['selisih'] > 0)
                                                        <span
                                                            class="text-green-600 font-semibold">+{{ number_format($d['selisih'], 0, ',', '.') }}</span>
                                                    @elseif($d['selisih'] < 0)
                                                        <span
                                                            class="text-red-600 font-semibold">{{ number_format($d['selisih'], 0, ',', '.') }}</span>
                                                    @else
                                                        <span class="text-gray-600">0</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="flex flex-col">
                                                        <div class="flex items-center">
                                                            <span class="mr-1">Rp</span>
                                                            <input 
                                                                type="number" 
                                                                step="1"
                                                                inputmode="numeric" 
                                                                min="0"
                                                                class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300"
                                                                value="{{ $d['harga_satuan'] ?? 0 }}"
                                                                wire:change="updateHargaSatuan({{ $loop->index }}, $event.target.value)"
                                                                placeholder="{{ $d['selisih'] > 0 ? 'Masukkan harga beli' : ($d['selisih'] < 0 ? 'Masukkan harga jual' : 'Masukkan harga') }}"
                                                            >
                                                        </div>
                                                        @if($d['selisih'] != 0)
                                                            <div class="text-xs mt-1 {{ $d['selisih'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                                {{ $d['selisih'] > 0 ? 'Harga beli untuk penambahan' : 'Harga jual untuk pengurangan' }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-3 py-2">
                                                    @if ($d['selisih'] != 0)
                                                        @php
                                                            $total = abs($d['selisih']) * ($d['harga_satuan'] ?? 0);
                                                        @endphp
                                                        <span class="font-semibold">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    @if (isset($d['adjustment_info']))
                                                        <div class="flex items-center space-x-1">
                                                            <div
                                                                class="px-2 py-1 rounded-full text-xs font-medium {{ $d['adjustment_info']['class'] }}">
                                                                <x-icon name="{{ $d['adjustment_info']['icon'] }}"
                                                                    class="w-3 h-3 inline mr-1" />
                                                                {{ $d['adjustment_info']['label'] }}
                                                            </div>
                                                        </div>
                                                        <div class="text-xs text-gray-500 mt-1">
                                                            {{ ucfirst($d['adjustment_info']['type']) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2">
                                                    <div class="flex space-x-1">
                                                        <x-button icon="o-trash"
                                                            class="btn-sm bg-red-500 hover:bg-red-600 text-white border-0 shadow-sm hover:shadow-md transition-all duration-200"
                                                            wire:click="openDeleteDetailModal({{ $loop->index }})">
                                                        </x-button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div
                                class="text-center py-4 text-gray-500 dark:text-gray-400 border border-dashed rounded-lg">
                                <x-icon name="o-shopping-cart" class="w-8 h-8 mx-auto mb-2" />
                                <p>Belum ada item ditambahkan</p>
                            </div>
                        @endif
                        <div class="mt-4 flex gap-2 justify-end">
                            <div class="flex-1">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                                    <div class="bg-blue-50 dark:bg-blue-900/50 p-3 rounded-lg">
                                        <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Total Item
                                        </div>
                                        <div class="text-lg font-bold text-blue-800 dark:text-blue-200">
                                            {{ $this->totalItems }}</div>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/50 p-3 rounded-lg">
                                        <div class="text-xs text-green-600 dark:text-green-400 font-medium">Penambahan
                                            (+)</div>
                                        <div class="text-lg font-bold text-green-800 dark:text-green-200">
                                            +{{ number_format($this->totalSelisihPlus, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900/50 p-3 rounded-lg">
                                        <div class="text-xs text-red-600 dark:text-red-400 font-medium">Pengurangan (-)
                                        </div>
                                        <div class="text-lg font-bold text-red-800 dark:text-red-200">
                                            -{{ number_format($this->totalSelisihMinus, 0, ',', '.') }}</div>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 font-medium">Net
                                            Adjustment</div>
                                        <div class="text-lg font-bold text-gray-800 dark:text-gray-200">
                                            @php $netAdjustment = $this->totalSelisihPlus - $this->totalSelisihMinus; @endphp
                                            @if ($netAdjustment > 0)
                                                <span
                                                    class="text-green-600">+{{ number_format($netAdjustment, 0, ',', '.') }}</span>
                                            @elseif($netAdjustment < 0)
                                                <span
                                                    class="text-red-600">{{ number_format($netAdjustment, 0, ',', '.') }}</span>
                                            @else
                                                <span class="text-gray-600">0</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                {{-- Ringkasan Total Nilai Harga --}}
                                @if(count($details) > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <div class="bg-green-50 dark:bg-green-900/50 p-3 rounded-lg">
                                            <div class="text-xs text-green-600 dark:text-green-400 font-medium">Total Nilai Penambahan</div>
                                            <div class="text-lg font-bold text-green-800 dark:text-green-200">
                                                @php
                                                    $totalNilaiPenambahan = collect($details)
                                                        ->where('selisih', '>', 0)
                                                        ->sum(function($item) {
                                                            return abs($item['selisih']) * ($item['harga_beli'] ?? 0);
                                                        });
                                                @endphp
                                                Rp {{ number_format($totalNilaiPenambahan, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="bg-red-50 dark:bg-red-900/50 p-3 rounded-lg">
                                            <div class="text-xs text-red-600 dark:text-red-400 font-medium">Total Nilai Pengurangan</div>
                                            <div class="text-lg font-bold text-red-800 dark:text-red-200">
                                                @php
                                                    $totalNilaiPengurangan = collect($details)
                                                        ->where('selisih', '<', 0)
                                                        ->sum(function($item) {
                                                            return abs($item['selisih']) * ($item['harga_jual'] ?? 0);
                                                        });
                                                @endphp
                                                Rp {{ number_format($totalNilaiPengurangan, 0, ',', '.') }}
                                            </div>
                                        </div>
                                        <div class="bg-blue-50 dark:bg-blue-900/50 p-3 rounded-lg">
                                            <div class="text-xs text-blue-600 dark:text-blue-400 font-medium">Net Nilai Adjustment</div>
                                            <div class="text-lg font-bold text-blue-800 dark:text-blue-200">
                                                @php $netNilaiAdjustment = $totalNilaiPenambahan - $totalNilaiPengurangan; @endphp
                                                @if ($netNilaiAdjustment > 0)
                                                    <span class="text-green-600">+Rp {{ number_format($netNilaiAdjustment, 0, ',', '.') }}</span>
                                                @elseif($netNilaiAdjustment < 0)
                                                    <span class="text-red-600">-Rp {{ number_format(abs($netNilaiAdjustment), 0, ',', '.') }}</span>
                                                @else
                                                    <span class="text-gray-600">Rp 0</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <x-button icon="o-x-mark" class="btn-outline" :href="route('stock-opname.index')"
                                wire:navigate>Batal</x-button>

                            @if ($type == 'create' && (count($details) > 0 || !empty($gudang_id) || !empty($keterangan)))
                                <x-button type="button" wire:click="startFresh" icon="o-arrow-path"
                                    class="btn-warning"
                                    wire:confirm="Yakin ingin menghapus semua data dan mulai baru?">Mulai
                                    Baru</x-button>
                            @endif

                            <x-button type="button" wire:click="saveDraft" icon="o-check"
                                class="btn-secondary">Simpan</x-button>
                        </div>
                    </div>


                </form>
            </div>
        </div>
    </div>

    <x-modal wire:model="deleteDetailModal" class="backdrop-blur-sm"
        box-class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm border border-red-200 dark:border-red-800">

        <div class="p-6">
            <!-- Modal Header -->
            <div class="flex items-center space-x-3 mb-6">
                <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full">
                    <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Konfirmasi Hapus Item</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Item akan dihapus dari daftar stock opname</p>
                </div>
            </div>

            @if ($detailToDelete)
                <!-- Item Details -->
                <div
                    class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <div class="p-2 bg-red-100 dark:bg-red-900/50 rounded-lg">
                            <x-icon name="o-cube" class="w-5 h-5 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-red-900 dark:text-red-100">
                                {{ $detailToDelete['barang_nama'] ?? 'Item' }}
                            </div>
                            <div class="text-sm text-red-700 dark:text-red-300 mt-1">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="font-medium">Stok Sistem:</span>
                                        {{ number_format($detailToDelete['stok_sistem'] ?? 0, 0, ',', '.') }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Stok Fisik:</span>
                                        {{ number_format($detailToDelete['stok_fisik'] ?? 0, 0, ',', '.') }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Selisih:</span>
                                        @if (($detailToDelete['selisih'] ?? 0) > 0)
                                            <span
                                                class="text-green-600 font-semibold">+{{ number_format($detailToDelete['selisih'], 0, ',', '.') }}</span>
                                        @elseif(($detailToDelete['selisih'] ?? 0) < 0)
                                            <span
                                                class="text-red-600 font-semibold">{{ number_format($detailToDelete['selisih'], 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-gray-600">0</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-medium">Adjustment:</span>
                                        @if (isset($detailToDelete['adjustment_info']))
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium {{ $detailToDelete['adjustment_info']['class'] }}">
                                                {{ $detailToDelete['adjustment_info']['label'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warning Message -->
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-4 mb-6">
                    <div class="flex items-start space-x-3">
                        <x-icon name="o-information-circle"
                            class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" />
                        <div class="text-sm text-yellow-800 dark:text-yellow-200">
                            <p class="font-medium mb-1">Informasi</p>
                            <p>Item akan dihapus dari daftar stock opname ini dan stok item akan dikembalikan seperti
                                semula. Data dapat ditambahkan kembali jika diperlukan.</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3">
                <x-button class="btn-outline" wire:click="$set('deleteDetailModal', false)">
                    Batal
                </x-button>
                <x-button
                    class="bg-red-600 hover:bg-red-700 text-white border-0 shadow-lg hover:shadow-xl transition-all duration-200"
                    wire:click="confirmDeleteDetail" spinner="confirmDeleteDetail">
                    <x-icon name="o-trash" class="w-4 h-4 mr-2" />
                    Hapus Item
                </x-button>
            </div>
        </div>
    </x-modal>
</div>
