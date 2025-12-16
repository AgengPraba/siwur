<div class="dark:bg-gray-900 min-h-screen flex flex-col overflow-hidden" style="touch-action: manipulation;">
    <!-- Responsive Header -->
    <div class="bg-white dark:bg-gray-800 shadow-sm py-2 px-2 sm:px-4">
        <!-- Mobile Header -->
        <div class="flex flex-col space-y-2 sm:hidden">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <a href="{{ route('penjualan.index') }}"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                        wire:navigate>
                        <x-icon name="o-arrow-left" class="w-5 h-5" />
                    </a>
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">
                        {{ $type == 'create' ? 'Tambah' : 'Edit' }} Penjualan</h1>
                </div>
                <div class="text-xs bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded-full">
                    <span class="font-medium">{{ $nomor_penjualan }}</span>
                </div>
            </div>
            <div class="flex flex-col space-y-2">
                <div class="text-xs text-gray-600 dark:text-gray-300 text-center">
                    <span
                        class="font-medium">{{ \Carbon\Carbon::parse($tanggal_penjualan)->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span>
                </div>
                <x-select wire:model="customer_id" :options="$this->customer_data" placeholder="Pilih Customer" icon="o-user"
                    searchable class="dark:bg-gray-700 dark:text-white w-full" />
                <x-input wire:model="kembalian" label="Kembalian" type="number" step="100" min="0"
                    placeholder="Masukkan kembalian jika ada" icon="o-banknotes"
                    class="dark:bg-gray-700 dark:text-white w-full" />
            </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden sm:flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <a href="{{ route('penjualan.index') }}"
                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300" wire:navigate>
                    <x-icon name="o-arrow-left" class="w-5 h-5" />
                </a>
                <h1 class="text-lg font-bold text-gray-800 dark:text-white">{{ $type == 'create' ? 'Tambah' : 'Edit' }}
                    Penjualan KU</h1>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <span
                        class="font-medium">{{ \Carbon\Carbon::parse($tanggal_penjualan)->locale('id')->isoFormat('D MMM YYYY HH:mm') }}</span>
                </div>
                <x-select wire:model="customer_id" :options="$this->customer_data" placeholder="Pilih Customer" icon="o-user"
                    searchable class="dark:bg-gray-700 dark:text-white w-56" />
                <div class="text-sm bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                    <span class="font-medium">{{ $nomor_penjualan }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kasir Info - Only show in create mode -->
    {{-- @if ($type === 'create')
        <div class="bg-white dark:bg-gray-800 shadow-sm py-2 px-2 sm:px-4 border-b dark:border-gray-700">
            @if ($modal_kasir_hari_ini)
                <!-- Modal Kasir Info -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                            <x-icon name="o-banknotes" class="w-5 h-5" />
                            <span class="font-medium text-sm">Modal Kasir Hari Ini:</span>
                        </div>
                        <div class="text-lg font-bold text-gray-800 dark:text-white">
                            Rp {{ number_format($modal_kasir_hari_ini->modal, 0, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            ({{ \Carbon\Carbon::parse($modal_kasir_hari_ini->tanggal)->locale('id')->isoFormat('D MMM YYYY') }})
                        </div>
                    </div>
                    <button wire:click="showModalKasirForm"
                        class="text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-3 py-1 rounded-full hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors">
                        Edit Modal
                    </button>
                </div>
                @else
                <!-- No Modal Kasir Alert -->
                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <x-icon name="o-exclamation-triangle"
                                class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                            <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Modal kasir hari ini belum diatur
                            </span>
                        </div>
                        <button wire:click="showModalKasirForm"
                            class="text-xs bg-yellow-600 text-white px-3 py-1 rounded-full hover:bg-yellow-700 transition-colors">
                            Atur Modal
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endif --}}

    <!-- Modal Kasir Form -->
    {{-- @if ($show_modal_kasir_form)
        <div class="fixed inset-0 bg-black bg-opacity-75 backdrop-blur-sm flex items-center justify-center z-50"
            x-data="{ show: true }" x-show="show" x-transition @click="$wire.cancelModalKasir()">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-2xl p-6 w-full max-w-md mx-4" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                        {{ $modal_kasir_hari_ini ? 'Edit' : 'Atur' }} Modal Kasir Hari Ini
                    </h3>
                    <button wire:click="cancelModalKasir"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-icon name="o-x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal
                        </label>
                        <div
                            class="text-sm text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 px-3 py-2 rounded-lg">
                            {{ \Carbon\Carbon::today()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                        </div>
                    </div>

                    <div>
                        <x-input wire:model="modal_kasir_jumlah" label="Jumlah Modal Kasir"
                            placeholder="Masukkan jumlah modal kasir" type="number" step="0.01" min="0"
                            icon="o-banknotes" class="dark:bg-gray-700 dark:text-white" />
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button wire:click="cancelModalKasir"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        Batal
                    </button>
                    <button wire:click="saveModalKasir"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    @endif --}}

    <!-- Keyboard Shortcuts -->



    <!-- Keyboard Shortcuts Info - Hidden on mobile -->
    <div
        class="hidden md:block bg-white dark:bg-gray-800 rounded-lg shadow-sm p-2 mx-3 mt-3 mb-0 text-xs text-gray-600 dark:text-gray-400">
        <div class="flex flex-wrap gap-2 justify-center">
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F1</kbd> Scan Barcode</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F2</kbd> Cari Item</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">Ctrl</kbd> + <kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F2</kbd> Tambah Item</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F3</kbd> Pembayaran Cash</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F4</kbd> Pembayaran Transfer</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F5</kbd> Lunasi</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F6</kbd> Simpan</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F7</kbd> Bayar & Simpan</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">F8</kbd> Cetak</span>
            <span><kbd class="px-1 py-0.5 bg-gray-100 dark:bg-gray-700 rounded">Esc</kbd> Clear/Kembali</span>
        </div>
    </div>

    <!-- Main Content Area - Responsive Layout -->
    <div x-data="{ expandItems: false, showSidebar: false }" class="flex-1 flex flex-col lg:grid lg:grid-cols-1"
        :class="{ 'lg:grid-cols-3 xl:grid-cols-4': !expandItems, 'lg:grid-cols-1': expandItems }"
        style="gap: 0.75rem; padding: 0.75rem; min-height: 0; overflow: hidden;">

        <!-- Mobile Responsive Meta Viewport Helper -->
        <style>
            @media (max-width: 640px) {
                .mobile-scroll {
                    -webkit-overflow-scrolling: touch;
                    overscroll-behavior: contain;
                }

                .mobile-input {
                    font-size: 16px !important;
                    /* Prevent zoom on iOS */
                }

                /* Improve touch targets */
                button,
                .btn {
                    min-height: 44px;
                    min-width: 44px;
                }

                /* Prevent horizontal scroll */
                body {
                    overflow-x: hidden;
                }

                /* Better spacing for mobile */
                .mobile-spacing {
                    padding: 0.5rem;
                }
            }

            /* General responsive improvements */
            .responsive-table {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            /* Ensure proper touch behavior */
            * {
                -webkit-tap-highlight-color: transparent;
            }
        </style>
        <!-- Main Content - Form & Items -->
        <div :class="{ 'lg:col-span-2 xl:col-span-3': !expandItems, 'lg:col-span-1': expandItems }"
            class="flex flex-col space-y-3 overflow-visible relative flex-1">
            <!-- Mobile Sidebar Toggle -->
            <button @click="showSidebar = !showSidebar"
                class="lg:hidden fixed bottom-4 right-4 z-50 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none">
                <svg x-show="!showSidebar" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M9 11h.01M9 8h.01M12 8h.01M15 8h.01M12 11h.01M15 11h.01M12 14h.01M15 14h.01M12 17h.01M15 17h.01">
                    </path>
                </svg>
                <svg x-show="showSidebar" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <!-- Desktop Toggle Expand Button -->
            <button @click="expandItems = !expandItems"
                class="hidden lg:block absolute top-2 right-2 z-10 bg-white dark:bg-gray-700 p-1 rounded-full shadow-md text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 focus:outline-none">
                <svg x-show="!expandItems" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5">
                    </path>
                </svg>
                <svg x-show="expandItems" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>

            <!-- Add Item Form - Compact Design -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-visible">
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
                            <div class="flex items-center text-slate-600 dark:text-slate-300 text-xs mr-10">
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
                                    data-shortcut-target="scan-barcode"
                                    placeholder="Scan atau ketik kode barcode di sini..." icon="o-qr-code"
                                    class="dark:bg-gray-700 dark:text-white h-12 font-mono text-lg border-2 border-blue-300 dark:border-blue-600 focus:border-blue-500 dark:focus:border-blue-400 transition-colors pr-10 pl-3"
                                    autocomplete="off" autofocus />
                                <!-- Clear button -->
                                @if ($barcode_search)
                                    <button wire:click="clearBarcodeForm" type="button"
                                        class="absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                        title="Clear (Esc)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
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
                                        <circle class="opacity-25" cx="12" cy="12" r="10"`
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                            <div class="hidden sm:block text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
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

                    <!-- Mobile Layout -->
                    <div class="block sm:hidden space-y-3">
                        <!-- Barang Search -->
                        <div class="relative" data-shortcut-target="item-search">   
                            <x-choices  wire:model.live="detail_barang_id" :options="$this->getBarangSearch" debounce="300ms"
                                placeholder="🔍 Cari nama barang atau kode..." single searchable class="h-12 mobile-input w-full">
                            </x-choices>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ketik untuk mencari barang</div>
                        </div>

                        <!-- Row 1: Satuan & Gudang -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <x-select wire:model.live="detail_satuan_id" :options="$satuan_data"
                                    placeholder="Pilih Satuan"
                                    class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unit pengukuran</div>
                            </div>
                            <div class="relative">
                                <x-select wire:model.live="detail_gudang_id" :options="$this->gudang_data"
                                    placeholder="Pilih Gudang"
                                    class="dark:bg-gray-700 dark:text-white h-12 mobile-input" :disabled="!$detail_satuan_id" />
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    @if (!$detail_satuan_id)
                                        Pilih satuan dulu
                                    @else
                                        Lokasi stok barang
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Harga & Jumlah -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <x-input wire:model.live="detail_harga_satuan" type="number" step="10"
                                    min="0" placeholder="Harga Jual (Rp)"
                                    class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Harga per satuan</div>
                            </div>
                            <div class="relative">
                                <x-input wire:model.live.debounce.500ms="detail_jumlah" type="number" step="1" placeholder="Qty"
                                    min="0" max="{{ $detail_jumlah_tersedia }}"
                                    class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    @if ($detail_jumlah_tersedia > 0)
                                        Tersedia: {{ number_format($detail_jumlah_tersedia - $detail_jumlah, 0) }}
                                    @else
                                        Masukkan jumlah
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Diskon & Biaya Lain -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="relative">
                                <x-input wire:model="detail_diskon" type="number" step="10" min="0"
                                    placeholder="Diskon per unit (Rp)"
                                    class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                                <div class="text-xs text-red-500 dark:text-red-400 mt-1">Potongan harga</div>
                            </div>
                            <div class="relative">
                                <x-input wire:model="detail_biaya_lain" type="number" step="10" min="0"
                                    placeholder="Biaya Tambahan (Rp)"
                                    class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                                <div class="text-xs text-blue-500 dark:text-blue-400 mt-1">Biaya ekstra</div>
                            </div>
                        </div>

                        <!-- Add Button -->
                        <div class="relative">
                            <x-button wire:click="addDetail" icon="o-plus" label="Tambah Item"
                                data-shortcut-action="add-item"
                                class="btn-primary dark:bg-blue-600 dark:hover:bg-blue-700 h-12 w-full justify-center"
                                :disabled="!($detail_harga_satuan > 0 && $detail_jumlah > 0)" />
                            @if (!($detail_harga_satuan > 0 && $detail_jumlah > 0))
                                <div class="text-xs text-orange-500 dark:text-orange-400 mt-1 text-center">
                                    Lengkapi harga dan jumlah untuk menambah item
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Desktop Layout -->
                    <div class="hidden sm:block">
                        <div class="grid grid-cols-12 gap-2">
                            <!-- Barang Search -->
                            <div class="col-span-12 md:col-span-4" data-shortcut-target="item-search">
                                <x-choices wire:model.live="detail_barang_id" :options="$this->getBarangSearch" debounce="300ms"
                                    placeholder="🔍 Cari barang (F2)" single searchable
                                    class="h-10 w-full" title="Ketik nama barang atau kode untuk mencari">
                                </x-choices>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ketik untuk mencari barang
                                </div>
                            </div>

                            <!-- Compact Controls Row -->
                            <div class="col-span-12 md:col-span-8 grid grid-cols-12 gap-2">
                                <!-- Satuan -->
                                <div class="col-span-3">
                                    <x-select wire:model.live="detail_satuan_id" :options="$satuan_data"
                                        placeholder="Pilih Satuan" class="dark:bg-gray-700 dark:text-white h-10"
                                        title="Pilih unit pengukuran barang" />
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Unit pengukuran</div>
                                </div>

                                <!-- Gudang -->
                                <div class="col-span-3">
                                    <x-select wire:model.live="detail_gudang_id" :options="$this->gudang_data"
                                        placeholder="Pilih Gudang" class="dark:bg-gray-700 dark:text-white h-10"
                                        :disabled="!$detail_satuan_id" :title="!$detail_satuan_id
                                            ? 'Pilih satuan terlebih dahulu'
                                            : 'Pilih lokasi stok barang'" />
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        @if (!$detail_satuan_id)
                                            Pilih satuan dulu
                                        @else
                                            Lokasi stok barang
                                        @endif
                                    </div>
                                </div>

                                <!-- Harga -->
                                <div class="col-span-2">
                                    <x-input wire:model.live="detail_harga_satuan" type="number" step="10"
                                        min="0" placeholder="Harga Jual (Rp)"
                                        class="dark:bg-gray-700 dark:text-white h-10" title="Harga jual per satuan" />
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Harga per satuan</div>
                                </div>

                                <!-- Jumlah -->
                                <div class="col-span-2">
                                    <div class="relative">
                                        <div class="flex">
                                            <button type="button" 
                                                wire:click="decreaseQuantity"
                                                class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-l-md border border-r-0 border-gray-300 dark:border-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
                                                :disabled="$wire.detail_jumlah <= 0">
                                                <x-icon name="o-minus" class="w-4 h-4" />
                                            </button>
                                            <x-input wire:model.live="detail_jumlah" type="number" step="1" min="0"
                                                placeholder="Qty" max="{{ $detail_jumlah_tersedia }}"
                                                class="dark:bg-gray-700 dark:text-white h-10 rounded-none border-x-0 text-center" 
                                                :title="$detail_jumlah > 0
                                                    ? 'Jumlah barang (Tersedia: ' . number_format($detail_jumlah_tersedia, 0) . ')'
                                                    : 'Jumlah barang'" />
                                            <button type="button" 
                                                wire:click="increaseQuantity"
                                                class="px-3 py-2 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
                                                :disabled="$wire.detail_jumlah >= {{ $detail_jumlah_tersedia }}">
                                                <x-icon name="o-plus" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        @if ($detail_jumlah_tersedia > 0)
                                            <span class="flex items-center justify-between">
                                                <span>Tersedia: {{ number_format($detail_jumlah_tersedia - $detail_jumlah, 0) }}</span>
                                            </span>
                                        @else
                                            Masukkan jumlah
                                        @endif
                                    </div>
                                </div>

                                <!-- Add Button -->
                                <div class="col-span-2">
                                    <x-button wire:click="addDetail" icon="o-plus"
                                        data-shortcut-action="add-item"
                                        class="btn-primary dark:bg-blue-600 dark:hover:bg-blue-700 h-10 w-full justify-center"
                                        :disabled="!($detail_harga_satuan > 0 && $detail_jumlah > 0)"
                                        title="{{ $detail_harga_satuan > 0 && $detail_jumlah > 0 ? 'Klik untuk menambah item ke daftar' : 'Lengkapi harga dan jumlah terlebih dahulu' }}" />
                                </div>
                            </div>

                            <!-- Row for Diskon & Biaya Lain -->
                            <div class="col-span-12 md:col-span-8 md:col-start-5 grid grid-cols-12 gap-2">
                                <!-- Diskon -->
                                <div class="col-span-5">
                                    <x-input wire:model="detail_diskon" type="number" step="1" min="0"
                                        placeholder="Diskon per unit (Rp)"
                                        class="dark:bg-gray-700 dark:text-white h-10"
                                        title="Potongan harga per satuan barang" />
                                    <div class="text-xs text-red-500 dark:text-red-400 mt-1">Potongan harga</div>
                                </div>

                                <!-- Biaya Lain -->
                                <div class="col-span-5">
                                    <x-input wire:model="detail_biaya_lain" type="number" step="1"
                                        min="0" placeholder="Biaya Tambahan (Rp)"
                                        class="dark:bg-gray-700 dark:text-white h-10"
                                        title="Biaya tambahan per satuan (misal: ongkir, asuransi, dll)" />
                                    <div class="text-xs text-blue-500 dark:text-blue-400 mt-1">Biaya ekstra</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items List - Compact Table -->
            <div x-data="{ itemFilter: '' }"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="px-4 py-2 border-b dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                        <h3 class="font-medium text-gray-800 dark:text-white">Detail Item Penjualan</h3>
                        <div
                            class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-2">
                            <x-input x-model="itemFilter" placeholder="🔍  Cari item..."
                                class="dark:bg-gray-700 dark:text-white h-8 w-full sm:w-40 text-sm"
                                title="Ketik nama barang untuk memfilter daftar item" />
                            <x-input wire:model="keterangan" placeholder="💬 Catatan penjualan (opsional)"
                                class="dark:bg-gray-700 dark:text-white h-8 w-full sm:w-64 text-sm"
                                title="Tambahkan catatan atau keterangan untuk transaksi ini" />
                        </div>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto mobile-scroll" style="max-height: calc(100vh - 400px);">
                    @if (count($details) > 0)
                        <!-- Mobile Card Layout -->
                        <div class="block sm:hidden space-y-2 p-3">
                            @foreach ($details as $index => $detail)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 space-y-2"
                                    wire:key="detail-mobile-{{ $index }}-{{ $detail['barang_id'] ?? 'unknown' }}"
                                    x-show="itemFilter === '' || '{{ strtolower($detail['nama_barang'] ?? '') }}'.includes(itemFilter.toLowerCase())">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                                                {{ $detail['barang_nama'] }}</h4>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $detail['gudang_nama'] }} • Batch: {{ $detail['nomor_pembelian'] }}
                                            </p>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button wire:click="editDetail({{ $index }})"
                                                class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                                title="Edit item">
                                                <x-icon name="o-pencil" class="w-4 h-4" />
                                            </button>
                                            <button wire:click="removeDetail({{ $index }})"
                                                class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                wire:confirm="Hapus item ini?" title="Hapus item">
                                                <x-icon name="o-trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 text-sm">
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Harga:</span>
                                            <p class="font-medium text-blue-600 dark:text-blue-400">Rp
                                                {{ number_format($detail['harga_satuan'], 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Jumlah:</span>
                                            <div class="flex items-center space-x-1">
                                                <input type="number" value="{{ $detail['jumlah'] }}"
                                                    wire:input.debounce.500ms="updateDetailQuantity({{ $index }}, $event.target.value)"
                                                    wire:key="qty-mobile-{{ $index }}-{{ $detail['barang_id'] }}"
                                                    class="w-16 px-2 py-1 text-sm text-center border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    step="1" min="1" placeholder="Qty"
                                                    title="Ubah jumlah item (min: 1)">
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $detail['satuan_nama'] }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Subtotal:</span>
                                            <p class="font-bold text-green-600 dark:text-green-400">Rp
                                                {{ number_format((float) $detail['subtotal'], 0, ',', '.') }}</p>
                                        </div>
                                        <div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">Profit:</span>
                                            <p class="font-medium text-purple-600 dark:text-purple-400">
                                                {{ number_format((float) $detail['profit'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2 text-xs">
                                        <span class="text-red-500">Diskon:
                                            -{{ number_format((float) ($detail['diskon'] ?? 0), 0, ',', '.') }}</span>
                                        <span class="text-blue-500">Biaya Lain:
                                            +{{ number_format((float) ($detail['biaya_lain'] ?? 0), 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Desktop Table Layout -->
                        <table class="hidden sm:table min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800 sticky z-0 top-0">
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
                                        class="px-3 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">
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
                                                    <span class="text-red-500">Diskon:
                                                        -{{ number_format((float) ($detail['diskon'] ?? 0), 0, ',', '.') }}</span>
                                                    <span class="text-blue-500">Biaya:
                                                        +{{ number_format((float) ($detail['biaya_lain'] ?? 0), 0, ',', '.') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <div class="flex items-center justify-center space-x-1">
                                                <input type="number" value="{{ $detail['jumlah'] }}"
                                                    wire:input.debounce.500ms="updateDetailQuantity({{ $index }}, $event.target.value)"
                                                    wire:key="qty-desktop-{{ $index }}-{{ $detail['barang_id'] }}"
                                                    class="w-16 px-2 py-1 text-sm text-center border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                                    step="1" min="1" placeholder="Qty"
                                                    title="Ubah jumlah item (min: 1)">
                                                <span
                                                    class="text-xs text-gray-500 dark:text-gray-400">{{ $detail['satuan_nama'] }}</span>
                                            </div>
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
                                            <div class="flex justify-center space-x-1">
                                                <button wire:click="editDetail({{ $index }})"
                                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                                    title="Edit item">
                                                    <x-icon name="o-pencil" class="w-4 h-4" />
                                                </button>
                                                <button wire:click="removeDetail({{ $index }})"
                                                    class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                                    wire:confirm="Hapus item ini?" title="Hapus item">
                                                    <x-icon name="o-trash" class="w-4 h-4" />
                                                </button>
                                            </div>
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

        <!-- Mobile Sidebar Overlay -->
        <div x-show="showSidebar" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-40"
            @click="showSidebar = false"></div>

        <!-- Sidebar Content -->
        <div :class="{
            'lg:col-span-1 xl:col-span-1': !expandItems,
            'hidden lg:flex lg:flex-col': expandItems,
            'fixed inset-y-0 right-0 w-80 bg-white dark:bg-gray-900 z-50 transform transition-transform duration-300 ease-in-out': true,
            'translate-x-0': showSidebar,
            'translate-x-full lg:translate-x-0': !showSidebar
        }"
            class="flex flex-col space-y-3 overflow-hidden lg:relative lg:inset-auto lg:w-auto lg:transform-none lg:transition-none">

            <!-- Mobile Sidebar Header -->
            <div class="lg:hidden flex items-center justify-between p-4 border-b dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Ringkasan & Pembayaran</h2>
                <button @click="showSidebar = false"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-3 lg:p-0 space-y-3">
                <!-- Sales Summary - Compact Design -->
                {{-- <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden"
                    x-show="!show_modal_kasir_form">
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
                </div> --}}

                <!-- Payment Form & List - Compact Design -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex-1 flex flex-col">
                    <div class="flex items-center justify-between px-4 py-2 border-b dark:border-gray-700">
                        <h3 class="font-medium text-gray-800 dark:text-white">Pembayaran</h3>
                        @if ($sisa_pembayaran > 0)
                            <x-button wire:click="quickFillPayment" icon="o-bolt" label="Lunasi"
                                data-shortcut-action="fill-full-payment"
                                class="btn-xs bg-yellow-500 hover:bg-yellow-600 text-white" />
                        @endif
                    </div>

                    <div class="p-3 space-y-3">
                        <!-- Payment Form -->
                        <!-- Mobile Payment Form -->
                        <div class="block sm:hidden space-y-3">
                            <x-select wire:model.live="payment_jenis" :options="[
                                ['id' => 'cash', 'name' => 'Cash'],
                                ['id' => 'transfer', 'name' => 'Transfer'],
                                ['id' => 'check', 'name' => 'Cek'],
                                ['id' => 'other', 'name' => 'Lainnya'],
                            ]"
                                placeholder="Jenis Pembayaran"
                                data-shortcut-target="payment-type"
                                class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                            <x-input wire:model="payment_jumlah" type="number" step="1" min="0"
                                placeholder="Jumlah" data-shortcut-target="payment-amount"
                                class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                            <x-input wire:model="payment_keterangan" placeholder="Keterangan pembayaran"
                                class="dark:bg-gray-700 dark:text-white h-12 mobile-input" />
                            <x-button wire:click="addPayment" icon="o-plus" label="Tambah Pembayaran"
                                data-shortcut-action="add-payment"
                                class="btn-primary dark:bg-blue-600 dark:hover:bg-blue-700 h-12 w-full justify-center" />
                        </div>

                        <!-- Desktop Payment Form -->
                        <div class="hidden sm:block">
                            <div class="grid grid-cols-12 gap-2">
                                <!-- Payment Type -->
                                <div class="col-span-5">
                                    <x-select wire:model.live="payment_jenis" :options="[
                                        ['id' => 'cash', 'name' => 'Cash'],
                                        ['id' => 'transfer', 'name' => 'Transfer'],
                                        ['id' => 'check', 'name' => 'Cek'],
                                        ['id' => 'other', 'name' => 'Lainnya'],
                                    ]" placeholder="Jenis"
                                        data-shortcut-target="payment-type"
                                        class="dark:bg-gray-700 dark:text-white h-10" />
                                </div>

                                <!-- Payment Amount -->
                                <div class="col-span-5">
                                    <x-input wire:model="payment_jumlah" type="number" step="1"
                                        min="0" placeholder="Jumlah"
                                        data-shortcut-target="payment-amount"
                                        class="dark:bg-gray-700 dark:text-white h-10" />
                                </div>

                                <!-- Add Button -->
                                <div class="col-span-2">
                                    <x-button wire:click="addPayment" icon="o-plus"
                                        data-shortcut-action="add-payment"
                                        class="btn-primary dark:bg-blue-600 dark:hover:bg-blue-700 h-10 w-full justify-center" />
                                </div>

                                <!-- Payment Notes -->
                                <div class="col-span-12">
                                    <x-input wire:model="payment_keterangan" placeholder="Keterangan pembayaran"
                                        class="dark:bg-gray-700 dark:text-white h-10" />
                                </div>
                            </div>
                        </div>

                        @if ($payment_jenis === 'cash')
                            @php
                                $cashDenominations = [1000, 2000, 5000, 10000, 20000, 50000, 100000];

                                $baseDue = (int) ceil(max($sisa_pembayaran ?? 0, 0));
                                if ($baseDue === 0 && isset($total_harga)) {
                                    $baseDue = (int) ceil((float) $total_harga);
                                }

                                $cashSuggestions = [];

                                if ($baseDue > 0) {
                                    $cashSuggestions[] = $baseDue;

                                    $suggestionSteps = [500, 1000, 5000, 10000, 20000, 50000, 100000];
                                    foreach ($suggestionSteps as $step) {
                                        $candidate = (int) ceil($baseDue / $step) * $step;
                                        $cashSuggestions[] = $candidate;
                                    }

                                    if ($baseDue >= 200000) {
                                        $altRounded = (int) ceil(($baseDue + 5000) / 10000) * 10000;
                                        if ($altRounded > $baseDue) {
                                            $cashSuggestions[] = $altRounded;
                                        }
                                    }

                                    $finalCap = (int) ceil($baseDue / 100000) * 100000;
                                    if ($finalCap > $baseDue) {
                                        $cashSuggestions[] = $finalCap;
                                    }

                                    $allowedMax = max($finalCap, $baseDue + 100000);

                                    $cashSuggestions = array_values(
                                        array_unique(
                                            array_filter($cashSuggestions, function ($value) use (
                                                $baseDue,
                                                $allowedMax,
                                            ) {
                                                return $value >= $baseDue && $value <= $allowedMax;
                                            }),
                                        ),
                                    );

                                    sort($cashSuggestions);

                                    if (count($cashSuggestions) > 5) {
                                        $cashSuggestions = array_slice($cashSuggestions, 0, 5);
                                    }
                                }

                                $currentPaymentValue = (int) ($payment_jumlah ?? 0);
                            @endphp

                            <div
                                class="space-y-3 rounded-lg border border-dashed border-gray-200 bg-gray-50 p-3 dark:border-gray-700/60 dark:bg-gray-900/40">
                                <div class="space-y-1">
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Pecahan Tunai
                                    </p>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                        @foreach ($cashDenominations as $nominal)
                                            @php
                                                $isActive = $currentPaymentValue === (int) $nominal;
                                            @endphp
                                            <button type="button"
                                                wire:click="$set('payment_jumlah', {{ $nominal }})"
                                                data-shortcut-cash="{{ $nominal }}"
                                                class="w-full h-15 rounded-lg px-2 py-1 text-xs font-semibold transition {{ $isActive ? 'bg-blue-600 text-white shadow-sm dark:bg-blue-500' : 'border border-gray-200 bg-white text-gray-700 hover:border-blue-300 hover:text-blue-600 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-blue-500 dark:hover:text-blue-100' }}">
                                                {{ 'Rp ' . number_format($nominal, 0, ',', '.') }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                @if (!empty($cashSuggestions))
                                    <div class="space-y-1">
                                        <div
                                            class="flex items-center justify-between text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                            <span>Saran Cepat</span>
                                        </div>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            @foreach ($cashSuggestions as $nominal)
                                                @php
                                                    $isActiveSuggestion = $currentPaymentValue === (int) $nominal;
                                                @endphp
                                                <button type="button"
                                                    wire:click="$set('payment_jumlah', {{ $nominal }})"
                                                    data-shortcut-suggest-index="{{ $loop->iteration }}"
                                                    data-suggest-amount="{{ $nominal }}"
                                                    class="w-full h-15 rounded-lg px-2 py-1 text-xs font-semibold transition {{ $isActiveSuggestion ? 'bg-emerald-600 text-white shadow-sm dark:bg-emerald-500' : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-700/60 dark:bg-emerald-900/30 dark:text-emerald-200 dark:hover:border-emerald-500' }}">
                                                    {{ 'Rp ' . number_format($nominal, 0, ',', '.') }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

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
                            <!-- Mobile Main Action Buttons -->
                            <div class="block sm:hidden space-y-2">
                                <x-button wire:click="addPaymentAndSave" icon="o-check-circle" label="Bayar & Simpan"
                                    data-shortcut-action="pay-and-save"
                                    class="btn-success w-full dark:bg-green-600 dark:hover:bg-green-700 h-12"
                                    spinner />
                                <x-button wire:click="store" icon="o-paper-airplane" label="Simpan Transaksi"
                                    data-shortcut-action="save-transaction"
                                    class="btn-primary w-full dark:bg-blue-600 dark:hover:bg-blue-700 h-12" spinner />
                            </div>


                            <!-- Desktop Main Action Buttons -->
                            <div class="hidden sm:block space-y-2">
                                <x-button wire:click="addPaymentAndSave" icon="o-check-circle"
                                    label="Bayar & Simpan (F7)"
                                    data-shortcut-action="pay-and-save"
                                    class="btn-success w-full dark:bg-green-600 dark:hover:bg-green-700 h-10"
                                    spinner />
                                <x-button wire:click="store" icon="o-paper-airplane" label="Simpan Transaksi (F6)"
                                    data-shortcut-action="save-transaction"
                                    class="btn-primary w-full dark:bg-blue-600 dark:hover:bg-blue-700 h-10" spinner />
                            </div>
                        @else
                            <!-- Mobile Main Action Buttons -->
                            <div class="block sm:hidden space-y-2">
                                <button wire:click="addPaymentAndUpdate" type="button"
                                    class="btn btn-success w-full dark:bg-green-600 dark:hover:bg-green-700 h-12 flex items-center justify-center gap-2"
                                    wire:loading.attr="disabled">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Bayar & Update</span>
                                    <span wire:loading wire:target="addPaymentAndUpdate">...</span>
                                </button>
                                <button wire:click="update" type="button"
                                    class="btn btn-warning w-full dark:bg-yellow-600 dark:hover:bg-yellow-700 h-12 flex items-center justify-center gap-2"
                                    wire:loading.attr="disabled">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Update Transaksi</span>
                                    <span wire:loading wire:target="update">...</span>
                                </button>
                            </div>

                            <!-- Desktop Main Action Buttons -->
                            <div class="hidden sm:block space-y-2">
                                <button wire:click="addPaymentAndUpdate" type="button"
                                    class="btn btn-success w-full dark:bg-green-600 dark:hover:bg-green-700 h-10 flex items-center justify-center gap-2"
                                    wire:loading.attr="disabled">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Bayar & Update (F10)</span>
                                    <span wire:loading wire:target="addPaymentAndUpdate">...</span>
                                </button>
                                <button wire:click="update" type="button"
                                    class="btn btn-warning w-full dark:bg-yellow-600 dark:hover:bg-yellow-700 h-10 flex items-center justify-center gap-2"
                                    wire:loading.attr="disabled">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    <span>Update Transaksi (F9)</span>
                                    <span wire:loading wire:target="update">...</span>
                                </button>
                            </div>
                        @endif

                        <!-- Mobile Action Buttons -->
                        <div class="block sm:hidden space-y-2">
                            <x-button icon="o-arrow-left" :href="route('penjualan.index')" label="Kembali"
                                class="btn-outline dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-700 h-12 w-full"
                                wire:navigate />
                            {{-- <x-button icon="o-printer" label="Cetak" wire:click="printReceipt"
                            class="btn-info dark:bg-blue-600 dark:hover:bg-blue-700 h-12 w-full" /> --}}
                        </div>

                        <!-- Desktop Action Buttons -->
                        <div class="hidden sm:block">
                            <div class="grid grid-cols-1 gap-2">
                                <x-button icon="o-arrow-left" :href="route('penjualan.index')" label="Kembali (Esc)"
                                    class="btn-outline dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-700 h-10"
                                    wire:navigate />
                                {{-- <x-button icon="o-printer" label="Cetak (F8)" wire:click="printReceipt"
                                class="btn-info dark:bg-blue-600 dark:hover:bg-blue-700 h-10" /> --}}
                            </div>
                        </div>
                        @if ($this->type === 'create')
                        <x-button icon="o-trash" label="Hapus Data Temp" wire:click="clearTempData"
                            class="btn-outline btn-error w-full dark:border-red-500 dark:text-red-500 dark:hover:bg-red-500 dark:hover:text-white h-12 sm:h-10" />
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <x-back-refresh />
    </div>

    @script
        <script>
            
            (function () {
                if (window.__POS_SHORTCUTS_INITIALIZED__) {
                    return;
                }
                window.__POS_SHORTCUTS_INITIALIZED__ = true;

                const PENJUALAN_INDEX_URL = @js(route('penjualan.index'));

                const ACTION_EVENT_MAP = {
                    'add-item': 'pos:add-item',
                    'fill-full-payment': 'pos:fill-full-payment',
                    'add-payment': 'pos:add-payment',
                    'save-transaction': 'pos:save-transaction',
                    'pay-and-save': 'pos:pay-and-save',
                    'clear-barcode': 'pos:clear-barcode',
                    'auto-settle-print-redirect': 'pos:auto-settle-print-redirect',
                };

                const focusableSelector = 'input:not([type="hidden"]):not([disabled]):not([readonly]), textarea:not([disabled]):not([readonly]), select:not([disabled]):not([readonly]), [contenteditable="true"], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

                const onReady = (callback) => {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', callback, { once: true });
                    } else {
                        callback();
                    }
                };

                const isVisible = (element) => {
                    if (!element) {
                        return false;
                    }
                    const styles = window.getComputedStyle(element);
                    if (styles.visibility === 'hidden' || styles.display === 'none') {
                        return false;
                    }
                    return element.offsetParent !== null || element.getClientRects().length > 0;
                };

                const findFirstFocusable = (root) => {
                    if (!root) {
                        return null;
                    }

                    const candidates = [];
                    if (root.matches && root.matches(focusableSelector)) {
                        candidates.push(root);
                    }

                    if (root.querySelectorAll) {
                        candidates.push(...root.querySelectorAll(focusableSelector));
                    }

                    for (const candidate of candidates) {
                        if (isVisible(candidate)) {
                            return candidate;
                        }
                    }

                    return null;
                };

                const focusShortcutTarget = (name, select = true) => {
                    const nodes = document.querySelectorAll(`[data-shortcut-target="${name}"]`);
                    for (const node of nodes) {
                        const focusable = findFirstFocusable(node);
                        if (!focusable) {
                            const choicesContainer = node.classList?.contains('choices')
                                ? node
                                : node.querySelector?.('.choices');
                            if (choicesContainer && isVisible(choicesContainer)) {
                                const trigger = choicesContainer.querySelector?.('.choices__inner') ?? choicesContainer;
                                if (trigger && typeof trigger.dispatchEvent === 'function') {
                                    trigger.dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
                                    trigger.dispatchEvent(new MouseEvent('click', { bubbles: true }));
                                }

                                const searchInput = choicesContainer.querySelector?.('.choices__input--cloned');
                                if (searchInput && typeof searchInput.focus === 'function') {
                                    // focus inside next tick to allow Choices to open
                                    setTimeout(() => {
                                        searchInput.focus({ preventScroll: true });
                                        if (select && typeof searchInput.select === 'function') {
                                            searchInput.select();
                                        }
                                    }, 0);
                                    return true;
                                }

                                if (typeof choicesContainer.focus === 'function') {
                                    choicesContainer.focus({ preventScroll: true });
                                    return true;
                                }
                            }
                            continue;
                        }
                        if (typeof focusable.focus === 'function') {
                            focusable.focus({ preventScroll: true });
                        }
                        if (select && typeof focusable.select === 'function') {
                            focusable.select();
                        }
                        return true;
                    }
                    return false;
                };

                const focusBarcodeInput = () => focusShortcutTarget('scan-barcode');

                const dispatchLivewire = (eventName, payload) => {
                    if (!eventName) {
                        return false;
                    }
                    if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                        payload === undefined
                            ? window.Livewire.dispatch(eventName)
                            : window.Livewire.dispatch(eventName, payload);
                        return true;
                    }
                    if (window.Livewire && typeof window.Livewire.emit === 'function') {
                        payload === undefined
                            ? window.Livewire.emit(eventName)
                            : window.Livewire.emit(eventName, payload);
                        return true;
                    }
                    if (window.livewire && typeof window.livewire.emit === 'function') {
                        payload === undefined
                            ? window.livewire.emit(eventName)
                            : window.livewire.emit(eventName, payload);
                        return true;
                    }
                    return false;
                };

                const triggerAction = (actionName, payload) => {
                    const button = document.querySelector(`[data-shortcut-action="${actionName}"]`);
                    if (button && !button.disabled) {
                        button.click();
                        return true;
                    }

                    const eventName = ACTION_EVENT_MAP[actionName];
                    if (!eventName) {
                        return false;
                    }

                    return dispatchLivewire(eventName, payload);
                };

                const clearBarcodeInput = () => {
                    const barcode = document.querySelector('[data-shortcut-target="scan-barcode"]');
                    if (barcode) {
                        barcode.value = '';
                        barcode.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    triggerAction('clear-barcode');
                    focusBarcodeInput();
                };

                const handleEscape = () => {
                    const barcode = document.querySelector('[data-shortcut-target="scan-barcode"]');
                    if (barcode && barcode.value && barcode.value.trim() !== '') {
                        clearBarcodeInput();
                        return true;
                    }

                    window.location.href = PENJUALAN_INDEX_URL;
                    return true;
                };

                const setPaymentType = (type) => {
                    let updated = false;
                    const nodes = document.querySelectorAll('[data-shortcut-target="payment-type"]');
                    nodes.forEach((node) => {
                        const selectEl = node.tagName === 'SELECT' ? node : node.querySelector?.('select');
                        if (!selectEl) {
                            return;
                        }
                        if (selectEl.value === type) {
                            updated = true;
                            return;
                        }
                        selectEl.value = type;
                        selectEl.dispatchEvent(new Event('input', { bubbles: true }));
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                        updated = true;
                    });

                    const dispatched = dispatchLivewire('pos:select-payment-type', { type });
                    focusShortcutTarget('payment-type', false);
                    focusShortcutTarget('payment-amount');
                    return updated || dispatched;
                };

                const handleQuickCashShortcut = (amount) => {
                    const cashButton = document.querySelector(`[data-shortcut-cash="${amount}"]`);
                    if (cashButton && !cashButton.disabled) {
                        cashButton.click();
                        focusShortcutTarget('payment-amount');
                        return true;
                    }

                    const dispatched = dispatchLivewire('pos:set-quick-cash', { amount });
                    if (dispatched) {
                        focusShortcutTarget('payment-amount');
                    }
                    return dispatched;
                };

                const handleSuggestedPaymentShortcut = (index) => {
                    const suggestButton = document.querySelector(`[data-shortcut-suggest-index="${index}"]`);
                    if (!suggestButton) {
                        return false;
                    }

                    if (!suggestButton.disabled) {
                        suggestButton.click();
                        focusShortcutTarget('payment-amount');
                        return true;
                    }

                    const amountAttr = suggestButton.getAttribute('data-suggest-amount');
                    const amount = amountAttr ? parseFloat(amountAttr) : NaN;
                    if (!Number.isFinite(amount)) {
                        return false;
                    }

                    const dispatched = dispatchLivewire('pos:set-suggested-payment', { amount });
                    if (dispatched) {
                        focusShortcutTarget('payment-amount');
                    }
                    return dispatched;
                };

                const getDigitFromEvent = (event) => {
                    if (!event.code || !event.code.startsWith('Digit')) {
                        return null;
                    }
                    return event.code.slice(5);
                };

                const KEY_ACTIONS = {
                    F1: () => {
                        focusBarcodeInput();
                        return true;
                    },
                    F2: () => {
                        focusShortcutTarget('item-search', true);
                        return true;
                    },
                    F3: () => setPaymentType('cash'),
                    F4: () => setPaymentType('transfer'),
                    F5: () => {
                        const handled = triggerAction('fill-full-payment');
                        focusShortcutTarget('payment-amount');
                        return handled !== false;
                    },
                    F6: () => triggerAction('save-transaction'),
                    F7: () => triggerAction('pay-and-save'),
                    F8: () => triggerAction('auto-settle-print-redirect'),
                    Escape: () => handleEscape(),
                };

                const QUICK_CASH_MAP = {
                    '1': 1000,
                    '2': 2000,
                    '3': 5000,
                    '4': 10000,
                    '5': 20000,
                    '6': 50000,
                    '7': 100000,
                };

                const handleCtrlShortcuts = (event) => {
                    const isCtrlLike = event.ctrlKey || event.metaKey;
                    if (!isCtrlLike || event.altKey) {
                        return false;
                    }

                    if (!event.shiftKey && event.key === 'F2') {
                        return triggerAction('add-item') !== false;
                    }

                    if (!event.shiftKey && event.key === 'F5') {
                        const handled = triggerAction('add-payment');
                        if (handled !== false) {
                            focusShortcutTarget('payment-amount');
                            return true;
                        }
                        return false;
                    }

                    const digit = getDigitFromEvent(event);
                    if (digit && QUICK_CASH_MAP[digit]) {
                        return handleQuickCashShortcut(QUICK_CASH_MAP[digit]);
                    }

                    return false;
                };

                const handleShiftShortcuts = (event) => {
                    if (!event.shiftKey || event.altKey || event.ctrlKey || event.metaKey) {
                        return false;
                    }

                    const digit = getDigitFromEvent(event);
                    if (!digit) {
                        return false;
                    }

                    return handleSuggestedPaymentShortcut(parseInt(digit, 10));
                };

                document.addEventListener('keydown', (event) => {
                    if (event.repeat) {
                        return;
                    }

                    if (handleCtrlShortcuts(event)) {
                        event.preventDefault();
                        return;
                    }

                    if (handleShiftShortcuts(event)) {
                        event.preventDefault();
                        return;
                    }

                    if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) {
                        return;
                    }

                    const handler = KEY_ACTIONS[event.key];
                    if (!handler) {
                        return;
                    }

                    const handled = handler(event);
                    if (handled !== false) {
                        event.preventDefault();
                    }
                });

                document.addEventListener('keydown', (event) => {
                    if (!event.target || !event.target.matches('[data-shortcut-target="scan-barcode"]')) {
                        return;
                    }

                    if (event.key === 'Enter') {
                        event.preventDefault();
                        setTimeout(() => {
                            if (event.target && typeof event.target.select === 'function') {
                                event.target.select();
                            }
                        }, 50);
                    }

                    if (event.key === 'Tab' && !event.shiftKey) {
                        const moved = focusShortcutTarget('item-search');
                        if (moved) {
                            event.preventDefault();
                        }
                    }
                });

                onReady(() => {
                    focusBarcodeInput();
                });

                window.addEventListener('refocus-barcode', () => {
                    setTimeout(() => focusBarcodeInput(), 0);
                });

                if (window.Livewire && typeof window.Livewire.on === 'function') {
                    window.Livewire.on('refocus-barcode', () => {
                        setTimeout(() => focusBarcodeInput(), 0);
                    });
                }
            })();

            if (!window.__POS_SHORTCUTS_WIRE_GUARD__) {
                window.__POS_SHORTCUTS_WIRE_GUARD__ = true;

                // Auto-calculate subtotal when harga_satuan atau jumlah changes
                $wire.on('detail-updated', () => {
                    console.log('Detail updated');
                });

                // Handle totals update event
                $wire.on('totals-updated', (data) => {
                    console.log('Totals updated:', data);
                });

                // Handle Livewire errors gracefully
                document.addEventListener('livewire:exception', (event) => {
                    console.error('Livewire error:', event.detail);

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
            }
        </script>
    @endscript
</div>
