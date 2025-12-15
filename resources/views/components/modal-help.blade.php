@once
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endonce

<div x-data="{ open: false }" class="fixed bottom-4 right-4 z-50" x-on:show-shortcut-help.window="open = true">
    <div x-show="open" x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black/50 p-4"
        @keydown.escape.window="open = false"
        @click.self="open = false">

        <div class="w-full max-w-3xl rounded-xl bg-white shadow-xl dark:bg-gray-800">
            {{-- Header --}}
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Daftar Shortcut Keyboard</h2>
                <button type="button"
                    class="text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300"
                    @click="open = false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Scrollable Table Content --}}
            <div class="max-h-[calc(100vh-200px)] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-100 dark:bg-gray-700">
                        <tr class="text-left text-gray-700 dark:text-gray-200">
                            <th class="w-1/3 px-6 py-3 text-xs font-semibold uppercase tracking-wide">Shortcut</th>
                            <th class="w-2/3 px-6 py-3 text-xs font-semibold uppercase tracking-wide">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        {{-- Shortcut Global --}}
                        <tr class="bg-blue-50 dark:bg-blue-900/20">
                            <td colspan="2" class="px-6 py-2 text-xs font-bold uppercase tracking-wide text-blue-700 dark:text-blue-300">
                                Shortcut Global
                            </td>
                        </tr>
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                <kbd class="shortcut">Ctrl + .</kbd>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                Buka bantuan shortcut
                            </td>
                        </tr>
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                <kbd class="shortcut">Esc</kbd>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                Tutup bantuan shortcut
                            </td>
                        </tr>
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                <kbd class="shortcut">Ctrl + D</kbd>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                Tambah transaksi penjualan (kasir)
                            </td>
                        </tr>
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                <kbd class="shortcut">Ctrl + H</kbd>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                History transaksi penjualan (hari ini)
                            </td>
                        </tr>
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                <kbd class="shortcut">Ctrl + B</kbd>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                History transaksi penjualan (hari ini & belum bayar)
                            </td>
                        </tr>
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                <kbd class="shortcut">Ctrl + K</kbd>
                            </td>
                            <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                Fokus ke kolom pencarian
                            </td>
                        </tr>

                        {{-- Shortcut Kasir (hanya saat create penjualan) --}}
                        @if (request()->routeIs('penjualan.create'))
                            {{-- Kasir --}}
                            <tr class="bg-green-50 dark:bg-green-900/20">
                                <td colspan="2" class="px-6 py-2 text-xs font-bold uppercase tracking-wide text-green-700 dark:text-green-300">
                                    Shortcut Kasir
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F1</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Fokus ke scan barcode
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F2</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Fokus ke cari barang
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">Ctrl + F2</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Tambah barang ke transaksi
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F3</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Set jenis pembayaran ke Cash
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F4</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Set jenis pembayaran ke Transfer
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F5</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Lunasi otomatis
                                </td>
                            </tr>

                            {{-- Quick Cash --}}
                            <tr class="bg-purple-50 dark:bg-purple-900/20">
                                <td colspan="2" class="px-6 py-2 text-xs font-bold uppercase tracking-wide text-purple-700 dark:text-purple-300">
                                    Pecahan Cash (Quick Payment)
                                </td>
                            </tr>
                            @foreach ([1=>'1.000', 2=>'2.000', 3=>'5.000', 4=>'10.000', 5=>'20.000', 6=>'50.000', 7=>'100.000'] as $key => $value)
                                <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                    <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                        <kbd class="shortcut">Ctrl + {{ $key }}</kbd>
                                    </td>
                                    <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                        Isi pembayaran Rp {{ $value }}
                                    </td>
                                </tr>
                            @endforeach

                            {{-- Suggest Payment --}}
                            <tr class="bg-orange-50 dark:bg-orange-900/20">
                                <td colspan="2" class="px-6 py-2 text-xs font-bold uppercase tracking-wide text-orange-700 dark:text-orange-300">
                                    Suggest Payment
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">Shift + 1..9</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Pilih opsi pembayaran yang disarankan (urutan ke-N)
                                </td>
                            </tr>

                            {{-- Aksi Transaksi --}}
                            <tr class="bg-red-50 dark:bg-red-900/20">
                                <td colspan="2" class="px-6 py-2 text-xs font-bold uppercase tracking-wide text-red-700 dark:text-red-300">
                                    Aksi Transaksi
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">Ctrl + F5</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Tambah pembayaran
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F6</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Simpan transaksi
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F7</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Bayar dan simpan
                                </td>
                            </tr>
                            <tr class="transition hover:bg-gray-50 dark:hover:bg-gray-750">
                                <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                    <kbd class="shortcut">F8</kbd>
                                </td>
                                <td class="px-6 py-3 text-gray-700 dark:text-gray-200">
                                    Simpan, bayar otomatis, cetak & redirect ke tambah penjualan
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            <div class="border-t border-gray-200 px-6 py-3 text-center dark:border-gray-700">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Tekan <kbd class="shortcut">Esc</kbd> atau klik di luar untuk menutup
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Utility CSS --}}
<style>
    kbd.shortcut {
        @apply rounded border border-gray-300 bg-gray-50 px-2 py-0.5 text-xs dark:border-gray-600 dark:bg-gray-800;
    }
</style>
