<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <x-card>
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Tambah Retur Pembelian</h3>
                <x-button icon="o-arrow-left" :href="route('retur-pembelian.index')" wire:navigate>
                    Kembali
                </x-button>
            </div>

            <!-- Form Header -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <x-input wire:model="nomor_retur" label="Nomor Retur" readonly class="bg-gray-50" />
                </div>
                <div>
                    <x-input wire:model="tanggal_retur" label="Tanggal Retur" type="datetime" icon="o-calendar" />
                </div>
            </div>

            <!-- Pembelian Selection -->
            <div class="grid grid-cols-1 gap-6 mb-6">
                <div>
                    <label class="label">
                        <span class="label-text">Pilih Pembelian <span class="text-red-500">*</span></span>
                    </label>    
                    @if ($selectedPembelian)
                        <div class="flex gap-2">
                            <x-input value="{{ $selectedPembelian->nomor_pembelian }}" label="" readonly
                                class="bg-gray-50 flex-1" />
                            <x-button icon="o-magnifying-glass" wire:click="$set('showPembelianModal', true)"
                                class="btn-outline btn-md">
                                Ubah
                            </x-button>
                        </div>
                    @else
                        <x-button icon="o-magnifying-glass" wire:click="$set('showPembelianModal', true)"
                            class="btn-outline w-full justify-start">
                            Pilih Pembelian...
                        </x-button>
                    @endif
                    @error('pembelian_id')
                        <div class="label">
                            <span class="label-text-alt text-red-500">{{ $message }}</span>
                        </div>
                    @enderror
                </div>
            </div>

            <!-- Supplier Info -->
            @if ($supplier)
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h4 class="font-semibold mb-2">Informasi Supplier</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Supplier</p>
                            <p class="font-medium">{{ $supplier->nama_supplier }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">No. HP</p>
                            <p class="font-medium">{{ $supplier->no_hp ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Items Table -->
            @if (!empty($details))
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold">Item yang akan diretur</h4>
                        <div class="flex gap-2">
                            <x-button wire:click="setAllReturQty" class="btn-outline btn-xs" icon="o-check-circle">
                                Pilih Semua
                            </x-button>
                            <x-button wire:click="clearAllReturQty" class="btn-outline btn-xs" icon="o-x-circle">
                                Reset Semua
                            </x-button>
                        </div>
                    </div>
                    <div class="overflow-x-auto border rounded-lg">
                        <table class="table table-zebra w-full">
                            <thead class="bg-base-200">
                                <tr>
                                    <th class="w-8">No</th>
                                    <th>Nama Barang</th>
                                    <th class="w-20">Satuan</th>
                                    <th class="w-24">Qty Beli</th>
                                    <th class="w-24">Qty Tersedia</th>
                                    <th class="w-32">Qty Retur</th>
                                    <th class="w-24">Harga</th>
                                    <th class="w-32">Total</th>
                                    <th class="w-40">Alasan Retur</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($details as $index => $detail)
                                    <tr class="{{ $detail['qty_retur'] > 0 ? 'bg-green-50' : '' }}">
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td class="font-medium">{{ $detail['nama_barang'] }}</td>
                                        <td class="text-center">{{ $detail['satuan'] }}</td>
                                        <td class="text-center">{{ number_format($detail['qty_beli'], 0) }}</td>
                                        <td class="text-center text-blue-600 font-semibold">
                                            {{ number_format($detail['qty_tersedia'] ?? $detail['qty_beli'], 0) }}
                                        </td>
                                        <td>
                                            <x-input wire:model.live="details.{{ $index }}.qty_retur"
                                                type="number" step="1" min="0"
                                                max="{{ $detail['qty_tersedia'] ?? $detail['qty_beli'] }}"
                                                class="input-sm w-20 text-center" />
                                        </td>
                                        <td class="text-right">{{ number_format($detail['harga_beli'], 0) }}</td>
                                        <td class="text-right font-semibold text-green-600">
                                            {{ number_format($detail['total'], 0) }}
                                        </td>
                                        <td>
                                            <x-select wire:model.live="details.{{ $index }}.alasan_retur"
                                                :options="$alasanReturOptions" option-label="name" option-value="value"
                                                placeholder="Pilih alasan" class="select-sm" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-base-200">
                                <tr class="font-bold">
                                    <td colspan="7" class="text-right">Total Retur:</td>
                                    <td class="text-right text-lg text-primary">
                                        Rp {{ number_format(collect($details)->sum('total'), 0) }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Summary Info -->
                    <div class="mt-4 flex justify-between text-sm text-gray-600">
                        <span>
                            Total item yang akan diretur:
                            <strong class="text-green-600">{{ collect($details)->where('qty_retur', '>', 0)->count() }}
                                item</strong>
                        </span>
                        <span>
                            Total quantity:
                            <strong
                                class="text-blue-600">{{ number_format(collect($details)->sum(fn($item) => (int)$item['qty_retur']), 0) }}</strong>
                        </span>
                    </div>
                </div>
            @else
                <div class="mb-6">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                        <x-icon name="o-shopping-cart" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 mb-2">Belum ada item yang dipilih</p>
                        <p class="text-sm text-gray-400">Pilih pembelian terlebih dahulu untuk memuat item</p>
                    </div>
                </div>
            @endif

            <!-- Keterangan -->
            <div class="mb-6">
                <x-textarea wire:model="keterangan" label="Keterangan" rows="3"
                    placeholder="Masukkan keterangan tambahan (opsional)" />
            </div>

            <!-- Actions -->
            <div class="flex justify-between items-center">
                <div class="flex gap-2">
                    <x-button variant="outline" :href="route('retur-pembelian.index')" wire:navigate icon="o-arrow-left">
                        Kembali
                    </x-button>
                </div>

                <div class="flex gap-2">
                    <x-button wire:click="save" class="btn-primary" icon="o-check" :disabled="empty($details) || collect($details)->sum(fn($item) => (int)$item['qty_retur']) <= 0">
                        <span wire:loading.remove wire:target="save">
                            Simpan Retur
                        </span>
                        <span wire:loading wire:target="save">
                            Menyimpan...
                        </span>
                    </x-button>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Modal Pilih Pembelian -->
    <x-modal wire:model="showPembelianModal" title="Pilih Pembelian" class="backdrop-blur max-w">
        <div class="p-4">
            <!-- Search -->
            <div class="mb-4">
                <x-input wire:model.live.debounce.300ms="searchPembelian"
                    placeholder="Cari nomor pembelian atau nama supplier..." icon="o-magnifying-glass" clearable />
            </div>

            <!-- Pembelian List -->
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @if (count($availablePembelians) > 0)
                    @foreach ($availablePembelians as $pembelian)
                        <div wire:click="selectPembelian({{ $pembelian['id'] }})"
                            class="p-4 border rounded-lg cursor-pointer hover:bg-blue-50 hover:border-blue-300 transition-all duration-200">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <p class="font-semibold text-gray-900">{{ $pembelian['nomor_pembelian'] }}</p>
                                        <span
                                            class="badge badge-sm badge-success">{{ ucfirst($pembelian['status']) }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-1">
                                        <x-icon name="o-building-storefront" class="w-4 h-4 inline mr-1" />
                                        {{ $pembelian['supplier']['nama_supplier'] ?? 'Supplier tidak ditemukan' }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <x-icon name="o-calendar" class="w-4 h-4 inline mr-1" />
                                        {{ date('d/m/Y', strtotime($pembelian['tanggal_pembelian'])) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-primary">
                                        Rp {{ number_format($pembelian['total_harga'], 0) }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ count($pembelian['pembelian_details'] ?? []) }} item
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-12 text-gray-500">
                        <x-icon name="o-document-magnifying-glass" class="w-16 h-16 mx-auto mb-4 text-gray-300" />
                        @if ($searchPembelian)
                            <p class="text-lg mb-2">Tidak ada pembelian yang ditemukan</p>
                            <p class="text-sm">Coba gunakan kata kunci yang berbeda</p>
                        @else
                            <p class="text-lg mb-2">Tidak ada pembelian yang tersedia untuk diretur</p>
                            <p class="text-sm">Pastikan ada pembelian yang sudah selesai dan belum ada barang yang dijual dari pembelian tersebut.</p>
                        @endif
                    </div>
                @endif
            </div>

            @if (count($availablePembelians) > 0)
                <div class="mt-4 text-sm text-gray-500 text-center">
                    Klik pada pembelian untuk memilih
                </div>
            @endif
        </div>

        <x-slot:actions>
            <x-button variant="outline" wire:click="$set('showPembelianModal', false)" icon="o-x-mark">
                Tutup
            </x-button>
        </x-slot:actions>
    </x-modal>
</div>
