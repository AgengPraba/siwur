<div class="dark:bg-gray-900 h-screen flex flex-col overflow-hidden">
    <!-- Compact Header with Breadcrumbs -->
    <div class="bg-white dark:bg-gray-800 shadow-sm py-2 px-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <a href="{{ route('penjualan.index') }}"
                class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" wire:navigate>
                <x-icon name="o-arrow-left" class="w-5 h-5" />
            </a>
            <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ $type == 'create' ? 'Tambah' : 'Edit' }}
                Penjualan</h1>
        </div>
        <div class="flex items-center space-x-3">
            <div class="text-sm text-gray-600 dark:text-gray-300">
                <span
                    class="font-medium">{{ \Carbon\Carbon::parse($tanggal_penjualan)->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span>
            </div>
            <x-select wire:model="customer_id" :options="$this->customer_data" placeholder="Pilih Customer" icon="o-user" searchable
                class="dark:bg-gray-700 dark:text-white w-56" />
            <div class="text-sm bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                <span class="font-medium">{{ $nomor_penjualan }}</span>
            </div>
        </div>
    </div>

    <!-- Keyboard Shortcuts -->
    <script>
        document.addEventListener('livewire:initialized', function() {
            // Auto-focus on barcode input when page loads
            setTimeout(function() {
                const barcodeInput = document.querySelector('[wire\\:model="barcode_search"]');
                if (barcodeInput) {
                    barcodeInput.focus();
                    barcodeInput.select();
                }
            }, 100);

            // Handle Enter key on barcode input for quick scanning
            document.addEventListener('keydown', function(e) {
                if (e.target.matches('[wire\\:model="barcode_search"]')) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        // The wire:model.live will handle the barcode search automatically
                        // Just clear the input after a short delay to prepare for next scan
                        setTimeout(function() {
                            e.target.select();
                        }, 50);
                    }
                    // Handle Tab key to move to next field
                    if (e.key === 'Tab' && !e.shiftKey) {
                        const nextInput = document.querySelector('[wire\\:model="detail_barang_id"]');
                        if (nextInput) {
                            e.preventDefault();
                            nextInput.focus();
                        }
                    }
                }
            });

            // Listen for refocus-barcode event
            window.addEventListener('refocus-barcode', function() {
                setTimeout(function() {
                    const barcodeInput = document.querySelector('[wire\\:model="barcode_search"]');
                    if (barcodeInput) {
                        barcodeInput.focus();
                        barcodeInput.select();
                    }
                }, 200);
            });

            // Listen for Livewire events
            document.addEventListener('livewire:init', () => {
                Livewire.on('refocus-barcode', () => {
                    setTimeout(() => {
                        const barcodeInput = document.querySelector(
                            '[wire\\:model="barcode_search"]');
                        if (barcodeInput) {
                            barcodeInput.focus();
                            barcodeInput.select();
                        }
                    }, 100);
                });
            });

            document.addEventListener('keydown', function(e) {
                // Prevent shortcuts when typing in input fields
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName ===
                    'SELECT') {
                    // Allow F9 and F10 even in input fields
                    if (e.key !== 'F9' && e.key !== 'F10') {
                        return;
                    }
                }

                // F9 - Save Transaction
                if (e.key === 'F9') {
                    e.preventDefault();
                    @this.store();
                }

                // F10 - Pay & Save
                if (e.key === 'F10') {
                    e.preventDefault();
                    @this.addPaymentAndSave();
                }

                // F1 - Focus on barcode scanner
                if (e.key === 'F1') {
                    e.preventDefault();
                    document.querySelector('[wire\\:model="barcode_search"]').focus();
                }

                // F2 - Focus on item search
                if (e.key === 'F2') {
                    e.preventDefault();
                    const itemSearch = document.querySelector('[wire\\:model="detail_barang_id"]');
                    if (itemSearch) {
                        itemSearch.focus();
                    }
                }

                // F3 - Focus on payment amount
                if (e.key === 'F3') {
                    e.preventDefault();
                    document.querySelector('[wire\\:model="payment_jumlah"]').focus();
                }

                // F4 - Quick fill payment
                if (e.key === 'F4') {
                    e.preventDefault();
                    @this.quickFillPayment();
                }

                // F8 - Print receipt
                if (e.key === 'F8') {
                    e.preventDefault();
                    @this.printReceipt();
                }

                // Escape - Clear form or back to list
                if (e.key === 'Escape') {
                    e.preventDefault();
                    // If barcode input has value, clear it first
                    const barcodeInput = document.querySelector('[wire\\:model="barcode_search"]');
                    if (barcodeInput && barcodeInput.value) {
                        @this.clearBarcodeForm();
                    } else {
                        // Otherwise go back to list
                        window.location.href = '{{ route('penjualan.index') }}';
                    }
                }
            });
        });
    </script>

    <!-- Keyboard Shortcuts Info -->
    <div
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-2 mx-3 mt-3 mb-0 text-xs text-gray-600 dark:text-gray-400 flex flex-wrap gap-2 justify-center">
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F1</kbd> Scan Barcode</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F2</kbd> Cari Item</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F3</kbd> Jumlah Bayar</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F4</kbd> Lunasi</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F8</kbd> Cetak</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F9</kbd> Simpan</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F10</kbd> Bayar & Simpan</span>
        <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">Esc</kbd> Clear/Kembali</span>
    </div>

    <!-- Main Content Area with Fixed Height -->
    <div x-data="{ expandItems: false }" class="flex-1 grid grid-cols-1"
        :class="{ 'lg:grid-cols-3 xl:grid-cols-4': !expandItems, 'grid-cols-1': expandItems }"
        style="gap: 0.75rem; padding: 0.75rem; min-height: 0; overflow: hidden;">
        <!-- Left Column - Form & Items -->
        <div :class="{ 'lg:col-span-2 xl:col-span-3': !expandItems, 'col-span-1': expandItems }"
            class="flex flex-col space-y-3 overflow-hidden relative">
            <!-- Toggle Expand Button -->
            <button @click="expandItems = !expandItems"
                class="absolute top-2 right-2 z-10 bg-white dark:bg-gray-700 p-1 rounded-full shadow-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none">
                <svg x-show="!expandItems" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5">
                    </path>
                </svg>
                <svg x-show="expandItems" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <!-- Add Item Form - Compact Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2 border-b dark:border-gray-700">
                    <h3 class="font-medium text-gray-800 dark:text-white">Tambah Item</h3>
                    <div class="flex items-center space-x-2">
                        @if ($detail_jumlah_tersedia > 0)
                            <div class="flex items-center text-green-600 dark:text-green-400 text-xs">
                                <x-icon name="o-cube" class="w-3 h-3 mr-1" />
                                <span>{{ $detail_jumlah_tersedia }}</span>
                            </div>
                        @elseif ($detail_jumlah_tersedia <= 0 && $detail_gudang_id)
                            <div class="flex items-center text-red-500 text-xs">
                                <x-icon name="o-cube" class="w-3 h-3 mr-1" />
                                <span>Stok Kosong</span>
                            </div>
                        @endif

                        @if ($detail_harga_beli > 0)
                            <div class="flex items-center text-slate-600 dark:text-slate-300 text-xs">
                                <x-icon name="o-banknotes" class="w-3 h-3 mr-1" />
                                <span>Beli: {{ number_format((float) $detail_harga_beli, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-3">
                    <!-- Barcode Scanner Input -->
                    <div class="mb-3">
                        <div class="flex items-center space-x-2">
                            <div class="flex-1 relative">
                                <x-input wire:model.live.debounce.500ms="barcode_search"
                                    placeholder="Scan atau ketik kode barcode di sini..." icon="o-qr-code"
                                    class="dark:bg-gray-700 dark:text-white h-12 font-mono text-lg border-2 border-blue-300 dark:border-blue-600 focus:border-blue-500 dark:focus:border-blue-400 transition-colors pr-10"
                                    autocomplete="off" autofocus />
                                <!-- Clear button -->
                                @if ($barcode_search)
                                    <button wire:click="clearBarcodeForm" type="button"
                                        class="absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                        title="Clear (Esc)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                @endif
                                <!-- Loading indicator -->
                                <div wire:loading wire:target="updatedBarcodeSearch"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2">
                                    <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                <div
                                    class="bg-gradient-to-r from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800 px-3 py-2 rounded-lg border border-blue-300 dark:border-blue-600">
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z">
                                            </path>
                                        </svg>
                                        <span class="font-medium text-blue-700 dark:text-blue-300">Barcode
                                            Scanner</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-2">
                        <!-- Barang Search - Larger width -->
                        <div class="col-span-12 md:col-span-4">
                            <x-choices wire:model.live="detail_barang_id" :options="$this->getBarangSearch" debounce="300ms"
                                placeholder="Cari barang (F2)" single searchable class="h-10">
                            </x-choices>
                        </div>

                        <!-- Compact Controls Row -->
                        <div class="col-span-12 md:col-span-8 grid grid-cols-12 gap-2">
                            <!-- Satuan -->
                            <div class="col-span-3">
                                <x-select wire:model.live="detail_satuan_id" :options="$satuan_data" placeholder="Satuan"
                                    class="dark:bg-gray-700 dark:text-white h-10" />
                            </div>

                            <!-- Gudang -->
                            <div class="col-span-3">
                                <x-select wire:model.live="detail_gudang_id" :options="$this->gudang_data" placeholder="Gudang"
                                    class="dark:bg-gray-700 dark:text-white h-10" :disabled="!$detail_satuan_id" />
                            </div>

                            <!-- Harga -->
                            <div class="col-span-2">
                                <x-input wire:model.live="detail_harga_satuan" type="number" step="0.01"
                                    placeholder="Harga" class="dark:bg-gray-700 dark:text-white h-10" />
                            </div>

                            <!-- Jumlah -->
                            <div class="col-span-2">
                                <x-input wire:model="detail_jumlah" type="number" step="0.01"
                                    placeholder="Jumlah" max="{{ $detail_jumlah_tersedia }}"
                                    class="dark:bg-gray-700 dark:text-white h-10" />
                            </div>

                            <!-- Add Button -->
                            <div class="col-span-2">
                                <x-button wire:click="addDetail" icon="o-plus"
                                    class="btn-primary dark:bg-blue-600 dark:hover:bg-blue-700 h-10 w-full justify-center"
                                    :disabled="!($detail_harga_satuan > 0 && $detail_jumlah > 0)" />
                            </div>
                        </div>

                        <!-- Optional Row for Diskon & Biaya Lain -->
                        @if ($detail_gudang_id)
                            <div class="col-span-12 md:col-span-8 md:col-start-5 grid grid-cols-12 gap-2">
                                <!-- Diskon -->
                                <div class="col-span-5">
                                    <x-input wire:model="detail_diskon" type="number" step="0.01"
                                        placeholder="Diskon" class="dark:bg-gray-700 dark:text-white h-10" />
                                </div>

                                <!-- Biaya Lain -->
                                <div class="col-span-5">
                                    <x-input wire:model="detail_biaya_lain" type="number" step="0.01"
                                        placeholder="Biaya Lain" class="dark:bg-gray-700 dark:text-white h-10" />
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Items List - Compact Table -->
            <div x-data="{ itemFilter: '' }"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="flex items-center justify-between px-4 py-2 border-b dark:border-gray-700">
                    <h3 class="font-medium text-gray-800 dark:text-white">Detail Item Penjualan</h3>
                    <div class="flex items-center space-x-2">
                        <x-input x-model="itemFilter" placeholder="Filter item..."
                            class="dark:bg-gray-700 dark:text-white h-8 w-40 text-sm" />
                        <x-input wire:model="keterangan" placeholder="Keterangan"
                            class="dark:bg-gray-700 dark:text-white h-8 w-64 text-sm" />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto" style="max-height: calc(100vh - 350px);">
                    @if (count($details) > 0)
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Barang</th>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Harga</th>
                                    <th
                                        class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Jumlah</th>
                                    <th
                                        class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Subtotal</th>
                                    <th
                                        class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-10">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($details as $index => $detail)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                        wire:key="detail-{{ $index }}-{{ $detail['barang_id'] ?? 'unknown' }}"
                                        x-show="itemFilter === '' || '{{ strtolower($detail['nama_barang'] ?? '') }}'.includes(itemFilter.toLowerCase())">
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-sm font-medium text-gray-900 dark:text-white">{{ $detail['barang_nama'] }}</span>
                                                <div
                                                    class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                    <span class="mr-2">{{ $detail['gudang_nama'] }}</span>
                                                    <span>Batch: {{ $detail['nomor_pembelian'] }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">Rp
                                                    {{ number_format($detail['harga_satuan'], 0, ',', '.') }}</span>
                                                <div class="flex items-center text-xs space-x-1">
                                                    <span class="text-gray-500 dark:text-gray-400">Beli:
                                                        {{ number_format($detail['harga_beli'], 0, ',', '.') }}</span>
                                                    @if (($detail['diskon'] ?? 0) > 0)
                                                        <span
                                                            class="text-red-500">-{{ number_format((float) ($detail['diskon'] ?? 0), 0, ',', '.') }}</span>
                                                    @endif
                                                    @if (($detail['biaya_lain'] ?? 0) > 0)
                                                        <span
                                                            class="text-blue-500">+{{ number_format((float) ($detail['biaya_lain'] ?? 0), 0, ',', '.') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span
                                                class="text-sm text-gray-900 dark:text-white">{{ $detail['jumlah'] }}
                                                {{ $detail['satuan_nama'] }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <div class="flex flex-col items-end">
                                                <span class="text-sm font-bold text-green-600 dark:text-green-400">Rp
                                                    {{ number_format((float) $detail['subtotal'], 0, ',', '.') }}</span>
                                                <span class="text-xs text-purple-600 dark:text-purple-400">Profit:
                                                    {{ number_format((float) $detail['profit'], 0, ',', '.') }}</span>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <button wire:click="removeDetail({{ $index }})"
                                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                wire:confirm="Hapus item ini?">
                                                <x-icon name="o-trash" class="w-4 h-4" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                                <!-- No Results Message -->

                            </tbody>
                        </table>
                    @else
                        <div class="flex items-center justify-center h-32 text-gray-500 dark:text-gray-400">
                            <div class="text-center">
                                <x-icon name="o-shopping-bag"
                                    class="w-8 h-8 mx-auto mb-2 text-gray-300 dark:text-gray-600" />
                                <p class="text-sm">Belum ada item yang ditambahkan</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Summary, Payment & Actions -->
        <div :class="{ 'lg:col-span-1 xl:col-span-1': !expandItems, 'hidden lg:flex lg:flex-col': expandItems }"
            class="flex flex-col space-y-3 overflow-hidden">
            <!-- Sales Summary - Compact Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-4 py-2 border-b dark:border-gray-700">
                    <h3 class="font-medium text-gray-800 dark:text-white">Ringkasan</h3>
                    <x-badge value="{{ ucfirst(str_replace('_', ' ', $status)) }}"
                        class="text-xs {{ $status === 'lunas' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : ($status === 'belum_lunas' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300') }}" />
                </div>

                <div class="p-3 space-y-2">
                    <!-- Main Totals -->
                    <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <span class="text-sm text-gray-600 dark:text-gray-300">Total Penjualan</span>
                        <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp
                            {{ number_format(round((float) $total_harga / 100) * 100, 0, ',', '.') }}</span>
                    </div>

                    <!-- Secondary Totals -->
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Subtotal</span>
                            <span
                                class="font-medium">{{ number_format($subtotal_sebelum_diskon, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Diskon</span>
                            <span
                                class="font-medium text-red-500">-{{ number_format(collect($details)->sum('diskon'), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total Bayar</span>
                            <span
                                class="font-medium text-blue-600 dark:text-blue-400">{{ number_format((float) $total_payment, 0, ',', '.') }}</span>
                        </div>

                        @if ($total_kembalian < 0)
                            <div
                                class="flex justify-between items-center p-2 {{ $sisa_pembayaran > 0 ? 'bg-red-50 dark:bg-red-900/30' : 'bg-green-50 dark:bg-green-900/30' }} rounded-lg">
                                <span
                                    class="text-xs {{ $sisa_pembayaran > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">Sisa</span>
                                <span
                                    class="font-medium {{ $sisa_pembayaran > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    {{ number_format((float) abs($sisa_pembayaran), 0, ',', '.') }}
                                </span>
                            </div>
                        @elseif ($total_kembalian > 0)
                            <div
                                class="flex justify-between items-center p-2 bg-green-50 dark:bg-green-900/30 rounded-lg">
                                <span class="text-xs text-green-600 dark:text-green-400">Kembalian</span>
                                <span
                                    class="font-medium text-green-600 dark:text-green-400">{{ number_format((float) $total_kembalian, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Form & List - Compact Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="flex items-center justify-between px-4 py-2 border-b dark:border-gray-700">
                    <h3 class="font-medium text-gray-800 dark:text-white">Pembayaran</h3>
                    @if ($sisa_pembayaran > 0)
                        <x-button wire:click="quickFillPayment" icon="o-bolt" label="Lunasi"
                            class="btn-xs bg-yellow-500 hover:bg-yellow-600 text-white" />
                    @endif
                </div>

                <div class="p-3 space-y-3">
                    <!-- Payment Form -->
                    <div class="grid grid-cols-12 gap-2">
                        <!-- Payment Type -->
                        <div class="col-span-5">
                            <x-select wire:model="payment_jenis" :options="[
                                ['id' => 'cash', 'name' => 'Cash'],
                                ['id' => 'transfer', 'name' => 'Transfer'],
                                ['id' => 'check', 'name' => 'Cek'],
                                ['id' => 'other', 'name' => 'Lainnya'],
                            ]" placeholder="Jenis"
                                class="dark:bg-gray-700 dark:text-white h-10" />
                        </div>

                        <!-- Payment Amount -->
                        <div class="col-span-5">
                            <x-input wire:model="payment_jumlah" type="number" step="0.01" placeholder="Jumlah"
                                class="dark:bg-gray-700 dark:text-white h-10" />
                        </div>

                        <!-- Add Button -->
                        <div class="col-span-2">
                            <x-button wire:click="addPayment" icon="o-plus"
                                class="btn-primary dark:bg-blue-600 dark:hover:bg-blue-700 h-10 w-full justify-center" />
                        </div>

                        <!-- Payment Notes -->
                        <div class="col-span-12">
                            <x-input wire:model="payment_keterangan" placeholder="Keterangan pembayaran"
                                class="dark:bg-gray-700 dark:text-white h-10" />
                        </div>
                    </div>

                    <!-- Payment List -->
                    <div class="border-t dark:border-gray-700 pt-2">
                        <div class="overflow-y-auto" style="max-height: 150px;">
                            @if (count($payments) > 0)
                                <div class="space-y-2">
                                    @foreach ($payments as $index => $payment)
                                        <div class="flex justify-between items-center p-2 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm"
                                            wire:key="payment-{{ $index }}-{{ $payment['jenis_pembayaran'] ?? 'unknown' }}">
                                            <div>
                                                <div class="flex items-center gap-1">
                                                    <span
                                                        class="font-medium text-gray-800 dark:text-white">{{ ucfirst($payment['jenis_pembayaran']) }}</span>
                                                    <span
                                                        class="font-bold text-green-600 dark:text-green-400">{{ number_format((float) $payment['jumlah'], 0, ',', '.') }}</span>
                                                </div>
                                                @if ($payment['keterangan'])
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400">{{ $payment['keterangan'] }}</span>
                                                @endif
                                            </div>
                                            <button wire:click="removePayment({{ $index }})"
                                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                wire:confirm="Hapus pembayaran ini?">
                                                <x-icon name="o-trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-3 text-gray-500 dark:text-gray-400">
                                    <p class="text-xs">Belum ada pembayaran</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons - Compact Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
                <div class="p-3 space-y-2">
                    @if ($type == 'create')
                        <x-button wire:click="addPaymentAndSave" icon="o-check-circle" label="Bayar & Simpan (F10)"
                            class="btn-success w-full dark:bg-green-600 dark:hover:bg-green-700 h-10" spinner />
                        <x-button wire:click="store" icon="o-paper-airplane" label="Simpan Transaksi (F9)"
                            class="btn-primary w-full dark:bg-blue-600 dark:hover:bg-blue-700 h-10" spinner />
                    @else
                        <x-button wire:click="addPaymentAndUpdate" icon="o-check-circle" label="Bayar & Update (F10)"
                            class="btn-success w-full dark:bg-green-600 dark:hover:bg-green-700 h-10" spinner />
                        <x-button wire:click="update" icon="o-arrow-path" label="Update Transaksi (F9)"
                            class="btn-warning w-full dark:bg-yellow-600 dark:hover:bg-yellow-700 h-10" spinner />
                    @endif

                    <div class="grid grid-cols-2 gap-2">
                        <x-button icon="o-arrow-left" :href="route('penjualan.index')" label="Kembali (Esc)"
                            class="btn-outline dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-700 h-10"
                            wire:navigate />
                        <x-button icon="o-printer" label="Cetak (F8)" wire:click="printReceipt"
                            class="btn-info dark:bg-blue-600 dark:hover:bg-blue-700 h-10" />
                    </div>
                    <x-button icon="o-trash" label="Hapus Data Temp" wire:click="clearTempData"
                        class="btn-outline btn-error w-full dark:border-red-500 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white h-10" />
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />
</div>

@script
    <script>
        // Auto-calculate subtotal when harga_satuan or jumlah changes
        $wire.on('detail-updated', () => {
            // This will be triggered when detail calculations are updated
            console.log('Detail updated');
        });

        // Handle totals update event
        $wire.on('totals-updated', (data) => {
            console.log('Totals updated:', data);
        });

        // Handle Livewire errors gracefully
        document.addEventListener('livewire:exception', (event) => {
            console.error('Livewire error:', event.detail);

            // Show user-friendly error message
            if (event.detail.message && event.detail.message.includes('corrupt data')) {
                alert('Terjadi kesalahan saat memproses data. Silakan refresh halaman dan coba lagi.');
            }
        });

        // Prevent rapid form submissions
        let isSubmitting = false;
        document.addEventListener('livewire:start', () => {
            if (isSubmitting) {
                return false;
            }
            isSubmitting = true;
            setTimeout(() => {
                isSubmitting = false;
            }, 1000);
        });
    </script>
@endscript
