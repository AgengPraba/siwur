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
                    Form Pembelian
                </h2>
            </div>

            {{-- Content --}}
            <div class="p-6">
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    {{-- Header Information - Compact --}}
                    <div class="xl:col-span-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <x-icon name="o-document-text" class="w-5 h-5 mr-2 text-primary" />
                            Informasi Pembelian
                        </h3>
                        @if ($type == 'create' && (count($details) > 0 || count($payments) > 0))
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
                            <x-input wire:model="nomor_pembelian" readonly label="Nomor Pembelian"
                                placeholder="PB20241201001" class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-datetime readonly wire:model="tanggal_pembelian" label="Tanggal Pembelian"
                                placeholder="Pilih Tanggal & Waktu" type="datetime-local"
                                class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-select wire:model.live="supplier_id" label="Supplier" placeholder="Pilih Supplier"
                                :options="$supplier_data" option-label="nama" option-value="id"
                                class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-textarea wire:model="keterangan" label="Keterangan" placeholder="Opsional"
                                class="dark:bg-gray-700 dark:text-gray-200" />
                        </div>
                    </div>

                    {{-- Item Input & List - Compact --}}
                    <div class="xl:col-span-2">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                            <x-icon name="o-cube" class="w-5 h-5 mr-2 text-primary" />
                            Item Pembelian
                        </h3>

                        {{-- Barcode Scanner --}}
                        <div class="mb-4">
                            <div class="relative">
                                <x-input wire:model.live="barcode_input" placeholder="Scan Barcode (Ctrl+B)"
                                    class="pr-10 dark:bg-gray-700 dark:text-gray-200"
                                    wire:keydown.enter="scanBarcode" />
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">

                                </div>
                            </div>
                            @if ($barcode_message)
                                <div
                                    class="mt-1 text-xs {{ $barcode_message_type === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $barcode_message }}
                                </div>
                            @endif
                        </div>

                        {{-- Item Search & Input --}}
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
                            <div>
                                <x-choices wire:model.live="detail_barang_id" label="Barang"
                                    placeholder="Cari barang..." :options="$barang_searchable" option-label="nama" option-value="id"
                                    single searchable class="dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div>
                                <x-select wire:model.live="detail_satuan_id" label="Satuan" placeholder="Pilih Satuan"
                                    :options="$satuan_data" option-label="nama" option-value="id"
                                    class="dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            <div>
                                <x-select wire:model.live="detail_gudang_id" label="Gudang" placeholder="Pilih Gudang"
                                    :options="$gudang_data" option-label="nama" option-value="id"
                                    class="dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 mb-4">
                            <x-input wire:model="detail_jumlah" label="Jumlah" type="number" min="1"
                                step="1" class="dark:bg-gray-700 dark:text-gray-200" id="jumlah-input" />
                            <x-input wire:model="detail_harga_satuan" label="Harga Beli" type="number" min="0"
                                step="1" class="dark:bg-gray-700 dark:text-gray-200" id="harga-satuan-input" />
                            <x-input wire:model="detail_rencana_harga_jual" label="Rencana Harga Jual" type="number"
                                min="0" step="1" class="dark:bg-gray-700 dark:text-gray-200" />
                            <x-input wire:model="detail_diskon" label="Diskon / Potongan Harga" type="number"
                                min="0" step="1" class="dark:bg-gray-700 dark:text-gray-200">
                                <x-slot:prepend>
                                    <x-select wire:model="diskon_tipe" :options="$detail_diskon_tipe" option-label="label"
                                        option-value="value" placeholder="Pilih" class="join-item bg-base-200" style="width: 70px;"/>
                                </x-slot:prepend>
                            </x-input>

                        </div>

                        <div class="flex justify-end mb-6">
                            <x-button wire:click="addDetail" icon="o-plus" label="Tambah Item"
                                class="btn-primary dark:bg-primary-600 dark:hover:bg-primary-700" spinner />
                        </div>

                        {{-- Item List --}}
                        @if (count($details) > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                    <thead
                                        class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th class="px-4 py-2">No</th>
                                            <th class="px-4 py-2">Barang</th>
                                            <th class="px-4 py-2">Jumlah</th>
                                            <th class="px-4 py-2">Harga Beli</th>
                                            <th class="px-4 py-2">Rencana Harga Jual</th>
                                            <th class="px-4 py-2">Diskon</th>
                                            <th class="px-4 py-2">Subtotal</th>
                                            <th class="px-4 py-2">Total</th>
                                            <th class="px-4 py-2">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($details as $index => $detail)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <td class="px-4 py-2">{{ $index + 1 }}</td>
                                                <td class="px-4 py-2">
                                                    <div class="font-medium text-gray-900 dark:text-white">
                                                        {{ $detail['barang_nama'] }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $detail['satuan_nama'] }}</div>
                                                </td>
                                                <td class="px-4 py-2 font-medium">
                                                    {{ number_format($detail['jumlah'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-2 font-mono">
                                                    {{ number_format($detail['harga_satuan'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-2 font-mono">
                                                    {{ number_format($detail['rencana_harga_jual'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-2 font-mono">
                                                    {{ number_format($detail['diskon'], 0, ',', '.') }}</td>
                                                <td
                                                    class="px-4 py-2 font-mono font-medium text-gray-900 dark:text-white">
                                                    {{ number_format($detail['subtotal'], 0, ',', '.') }}
                                                </td>
                                                 <td
                                                    class="px-4 py-2 font-mono font-medium text-gray-900 dark:text-white">
                                                    {{ number_format($detail['total_harga'], 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    <div class="flex space-x-1">
                                                        <x-button wire:click="editDetail({{ $index }})"
                                                            icon="o-pencil" class="btn-warning btn-sm" />
                                                        <x-button wire:click="removeDetail({{ $index }})"
                                                            icon="o-trash"
                                                            wire:confirm="Yakin ingin menghapus item ini?"
                                                            class="btn-error btn-sm" />
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-semibold text-gray-900 dark:text-white">
                                            <td class="px-4 py-2 text-base" colspan="7">Total</td>
                                            <td class="px-4 py-2 text-base font-mono">
                                                {{ number_format($total_harga, 0, ',', '.') }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div
                                class="text-center py-4 text-gray-500 dark:text-gray-400 border border-dashed rounded-lg">
                                <x-icon name="o-shopping-cart" class="w-8 h-8 mx-auto mb-2" />
                                <p>Belum ada item ditambahkan</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Payment and Summary Section --}}
                <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                        <x-icon name="o-credit-card" class="w-5 h-5 mr-2 text-primary" />
                        Pembayaran & Total
                    </h3>

                    <div class="space-y-4">
                        {{-- Summary Total - Compact --}}
                        <div
                            class="bg-gradient-to-r from-primary/10 to-primary/5 dark:from-primary/20 dark:to-primary/10 rounded-lg p-4 border border-primary/20">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center">
                                        <x-icon name="o-calculator" class="w-5 h-5 text-white" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-lg text-primary dark:text-primary-400">Total
                                            Pembelian</h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">{{ count($details) }} item
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-mono text-2xl font-black text-primary dark:text-primary-400">
                                        Rp {{ number_format($total_harga, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Summary - Compact Grid --}}
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total Bayar</p>
                                <p class="font-mono font-semibold text-success dark:text-success-400 text-sm">
                                    Rp {{ number_format($total_payment, 0, ',', '.') }}
                                </p>
                            </div>
                            @if ($this->total_kembalian > 0)
                                <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg text-center">
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Kembalian</p>
                                    <p class="font-mono font-semibold text-green-600 dark:text-green-400 text-sm">
                                        Rp {{ number_format($this->total_kembalian, 0, ',', '.') }}
                                    </p>
                                </div>
                            @endif
                            <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Sisa Bayar</p>
                                <p
                                    class="font-mono font-semibold {{ $sisa_pembayaran > 0 ? 'text-warning dark:text-warning-400' : 'text-success dark:text-success-400' }} text-sm">
                                    Rp {{ number_format($sisa_pembayaran, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg text-center">
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Status</p>
                                @if ($status == 'belum_bayar')
                                    <div class="badge badge-error badge-sm gap-1">
                                        <x-icon name="o-x-circle" class="w-3 h-3" />
                                        Belum Bayar
                                    </div>
                                @elseif($status == 'belum_lunas')
                                    <div class="badge badge-warning badge-sm gap-1">
                                        <x-icon name="o-clock" class="w-3 h-3" />
                                        Belum Lunas
                                    </div>
                                @else
                                    <div class="badge badge-success badge-sm gap-1">
                                        <x-icon name="o-check-circle" class="w-3 h-3" />
                                        Lunas
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Payment Form - Compact --}}
                        <div
                            class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg border border-gray-300 dark:border-gray-600">
                            <h4 class="font-semibold mb-3 text-gray-700 dark:text-gray-300 flex items-center">
                                <x-icon name="o-credit-card"
                                    class="w-4 h-4 mr-2 text-primary dark:text-primary-400" />
                                Form Pembayaran
                            </h4>
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">
                                <x-select wire:model="payment_jenis" label="Jenis" :options="[
                                    ['id' => 'cash', 'name' => 'Cash'],
                                    ['id' => 'transfer', 'name' => 'Transfer'],
                                    ['id' => 'check', 'name' => 'Check'],
                                    ['id' => 'other', 'name' => 'Lainnya'],
                                ]"
                                    placeholder="Pilih Jenis" class="dark:bg-gray-700 dark:text-gray-200" />
                                <x-input wire:model="payment_jumlah" label="Jumlah" type="number" step="0.01"
                                    min="0" class="dark:bg-gray-700 dark:text-gray-200" />
                            </div>
                            @if ($sisa_pembayaran > 0)
                                <x-button wire:click="quickFillPayment" icon="o-bolt"
                                    label="Isi Sisa ({{ number_format($sisa_pembayaran, 0, ',', '.') }})"
                                    class="btn-outline btn-secondary btn-sm w-full mb-3 dark:border-secondary-400 dark:text-secondary-400" />
                            @endif
                            @if ($payment_kembalian > 0)
                                <div
                                    class="bg-green-50 dark:bg-green-900/20 p-2 rounded border border-green-200 dark:border-green-700 mb-3">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="text-xs text-green-600 dark:text-green-400 font-semibold">Kembalian:</span>
                                        <span class="font-mono font-bold text-green-600 dark:text-green-400">
                                            Rp {{ number_format($payment_kembalian, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                            <x-input wire:model="payment_keterangan" label="Keterangan" placeholder="Opsional"
                                class="dark:bg-gray-700 dark:text-gray-200 mb-3" />

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2">
                                @if ($type == 'create')
                                    <x-button wire:click="addPaymentAndSave" icon="o-check" label="Bayar & Simpan"
                                        class="btn-primary w-full dark:bg-primary-600 dark:hover:bg-primary-700"
                                        spinner :disabled="count($details) == 0" />
                                    <x-button wire:click="addPaymentAndSave(true)" icon="o-document-check"
                                        label="Simpan Saja"
                                        class="btn-accent w-full dark:bg-accent-600 dark:hover:bg-accent-700" spinner
                                        :disabled="count($details) == 0" />
                                @else
                                    <x-button wire:click="addPaymentAndUpdate" icon="o-arrow-path"
                                        label="Bayar & Update"
                                        class="btn-primary w-full dark:bg-primary-600 dark:hover:bg-primary-700"
                                        spinner :disabled="count($details) == 0" />
                                    <x-button wire:click="addPaymentAndUpdate(true)" icon="o-document-arrow-up"
                                        label="Update Saja"
                                        class="btn-accent w-full dark:bg-accent-600 dark:hover:bg-accent-700" spinner
                                        :disabled="count($details) == 0" />
                                @endif
                            </div>
                        </div>

                        {{-- Payment List - Compact --}}
                        @if (count($payments) > 0)
                            <div class="mt-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h5 class="font-medium text-gray-700 dark:text-gray-300 text-sm">Daftar Pembayaran
                                    </h5>
                                    <div class="badge badge-info badge-sm">{{ count($payments) }}</div>
                                </div>
                                <div class="space-y-2">
                                    @foreach ($payments as $index => $payment)
                                        <div
                                            class="flex items-center justify-between p-2 bg-base-100 dark:bg-gray-700 rounded border dark:border-gray-600">
                                            <div class="flex items-center gap-2 flex-1">
                                                <div
                                                    class="w-6 h-6 rounded bg-primary/10 dark:bg-primary/20 flex items-center justify-center">
                                                    @if ($payment['jenis_pembayaran'] == 'cash')
                                                        <x-icon name="o-banknotes"
                                                            class="w-3 h-3 text-primary dark:text-primary-400" />
                                                    @elseif($payment['jenis_pembayaran'] == 'transfer')
                                                        <x-icon name="o-credit-card"
                                                            class="w-3 h-3 text-primary dark:text-primary-400" />
                                                    @elseif($payment['jenis_pembayaran'] == 'check')
                                                        <x-icon name="o-document-check"
                                                            class="w-3 h-3 text-primary dark:text-primary-400" />
                                                    @else
                                                        <x-icon name="o-currency-dollar"
                                                            class="w-3 h-3 text-primary dark:text-primary-400" />
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <div class="font-medium text-sm dark:text-gray-200">
                                                        {{ ucfirst($payment['jenis_pembayaran']) }}</div>
                                                    @if ($payment['keterangan'])
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                                            {{ $payment['keterangan'] }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-right mr-2">
                                                <div class="font-mono font-semibold text-sm dark:text-gray-200">
                                                    Rp {{ number_format($payment['jumlah'], 0, ',', '.') }}
                                                </div>
                                                @if (($payment['kembalian'] ?? 0) > 0)
                                                    <div class="text-xs text-green-600 dark:text-green-400">
                                                        Kembalian: Rp
                                                        {{ number_format($payment['kembalian'], 0, ',', '.') }}
                                                    </div>
                                                @endif
                                            </div>
                                            <x-button wire:click="removePayment({{ $index }})"
                                                wire:confirm="Yakin ingin menghapus pembayaran ini?" icon="o-trash"
                                                class="btn-error btn-xs" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons - Compact --}}
                    <div class="bg-base-200 dark:bg-gray-700 p-3 rounded-lg border dark:border-gray-600 mt-6">
                        <div class="flex justify-between items-center">
                            <div class="text-sm">
                                @if (count($details) == 0)
                                    <div class="flex items-center text-warning dark:text-warning-400">
                                        <x-icon name="o-exclamation-triangle" class="w-4 h-4 mr-1" />
                                        <span>Tambahkan minimal 1 item</span>
                                    </div>
                                @else
                                    <div class="flex items-center text-success dark:text-success-400">
                                        <x-icon name="o-check-circle" class="w-4 h-4 mr-1" />
                                        <span>{{ count($details) }} item siap</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                @if ($type == 'create' && (count($details) > 0 || count($payments) > 0))
                                    <x-button wire:click="clearTempData"
                                        wire:confirm="Yakin ingin menghapus semua data sementara?" icon="o-trash"
                                        label="Hapus Data" class="btn-warning btn-sm dark:btn-warning" />
                                @endif
                                <x-button :href="route('pembelian.index')" icon="o-arrow-left" label="Kembali"
                                    class="btn-outline btn-sm dark:border-gray-400 dark:text-gray-300" wire:navigate />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />

    {{-- JavaScript untuk Barcode Scanner --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto focus ke barcode input saat halaman dimuat
            const barcodeInput = document.querySelector('input[wire\\:model\\.live="barcode_input"]');
            if (barcodeInput) {
                barcodeInput.focus();
            }

            // Keyboard shortcut: Ctrl+B untuk focus ke barcode scanner
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'b') {
                    e.preventDefault();
                    if (barcodeInput) {
                        barcodeInput.focus();
                        barcodeInput.select();
                    }
                }
            });

            // Auto-clear barcode message setelah 5 detik
            window.addEventListener('barcode-scanned', function() {
                setTimeout(function() {
                    Livewire.dispatch('clearBarcodeMessage');
                }, 5000);
            });

            // Focus ke field harga satuan setelah scan barcode
            window.addEventListener('focus-harga-satuan', function(event) {
                setTimeout(function() {
                    const hargaInput = document.getElementById(event.detail.id);
                    if (hargaInput) {

                        hargaInput.focus();
                        hargaInput.select();
                    }
                }, 100);
            });

            // Focus ke field jumlah setelah scan barcode
            window.addEventListener('focus-jumlah', function(event) {
                setTimeout(function() {
                    const jumlahInput = document.getElementById(event.detail.id);
                    if (jumlahInput) {
                        jumlahInput.focus();
                        jumlahInput.select();
                    }
                }, 100);
            });
        });
    </script>

    {{-- Styling tambahan untuk barcode scanner --}}
    <style>
        /* Animasi untuk barcode input saat focus */
        input[wire\:model\\.live="barcode_input"]:focus {
            animation: pulse-border 2s infinite;
        }

        @keyframes pulse-border {
            0% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }

            70% {
                box-shadow: 0 0 0 4px rgba(59, 130, 246, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }
    </style>
</div>
