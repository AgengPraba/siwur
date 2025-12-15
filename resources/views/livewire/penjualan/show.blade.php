<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 pb-4 mt-6">

        <!-- Kolom Kiri -->
        <div class="space-y-6">
            <!-- Header Card -->
            <x-card title="Detail Penjualan" shadow separator>
                <x-slot:menu>
                    <x-button wire:click="refreshData" size="sm" class="btn-ghost"
                        icon="{{ $isRefreshing ? 'o-arrow-path animate-spin' : 'o-arrow-path' }}" tooltip="Refresh Data"
                        :disabled="$isRefreshing">
                        {{ $isRefreshing ? 'Refreshing...' : 'Refresh' }}
                    </x-button>
                </x-slot:menu>
                <!-- Status Pembayaran -->
                @if ($this->persentasePembayaran > 0)
                    @php
                        $colorMap = [
                            'error' => 'red',
                            'warning' => 'yellow', 
                            'success' => 'green'
                        ];
                        $color = $colorMap[$this->progressColor] ?? 'gray';
                    @endphp
                    <div
                        class="mb-4 p-3 rounded-lg bg-{{ $color }}-50 dark:bg-{{ $color }}-900/20 border border-{{ $color }}-200 dark:border-{{ $color }}-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-icon name="o-banknotes" class="w-5 h-5 text-{{ $color }}-500" />
                                <span
                                    class="font-medium text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                    Status Pembayaran: {{ $penjualan->status_label }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="text-sm font-medium text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                    {{ $this->persentasePembayaran }}% Terbayar
                                </span>
                                <x-button wire:click="refreshPembayaran" size="xs" class="btn-ghost"
                                    icon="o-arrow-path">
                                    <span wire:loading.remove wire:target="refreshPembayaran">Refresh</span>
                                    <span wire:loading wire:target="refreshPembayaran">Refreshing...</span>
                                </x-button>
                            </div>
                        </div>
                        <div
                            class="w-full bg-{{ $color }}-200 dark:bg-{{ $color }}-700 rounded-full h-2 mt-2">
                            <div class="bg-{{ $color }}-500 h-2 rounded-full"
                                style="width: {{ $this->persentasePembayaran }}%"></div>
                        </div>
                        <div class="flex justify-between text-sm mt-2">
                            <span
                                class="text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                Total Dibayar: Rp {{ number_format($this->totalPembayaran, 0, ',', '.') }}
                            </span>
                            @if ($this->sisaPembayaran > 0)
                                <span
                                    class="text-{{ $color }}-700 dark:text-{{ $color }}-300">
                                    Sisa: Rp {{ number_format($this->sisaPembayaran, 0, ',', '.') }}
                                </span>
                            @elseif ($this->sisaPembayaran < 0)
                                <span class="text-yellow-700 dark:text-yellow-300">
                                    Kembalian: Rp {{ number_format(abs($this->sisaPembayaran), 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                        @if ($this->totalKembalian > 0)
                            <div class="flex justify-between text-sm mt-1">
                                <span class="text-orange-700 dark:text-orange-300 font-medium">
                                    Total Kembalian:
                                </span>
                                <span class="text-orange-700 dark:text-orange-300 font-medium">
                                    {{ $this->formattedTotalKembalian }}
                                </span>
                            </div>
                        @endif
                    </div>
                @endif
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Informasi Penjualan -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $penjualan->nomor_penjualan }}
                            </h3>
                            <div class="flex items-center gap-2">
                                <x-badge :value="$penjualan->status_label" :class="$penjualan->status_badge_class" />
                                @if ($this->persentasePembayaran > 0)
                                    <div
                                        class="text-xs text-{{ $this->progressColor }}-600 dark:text-{{ $this->progressColor }}-400 font-medium">
                                        {{ $this->persentasePembayaran }}% Terbayar
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Tanggal:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $penjualan->formatted_tanggal }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Customer:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $penjualan->customer->nama_customer }}
                                </span>
                            </div>

                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Dibuat oleh:</span>
                                <span class="text-sm text-gray-900 dark:text-gray-100">
                                    {{ $penjualan->user->name }}
                                </span>
                            </div>

                            @if ($penjualan->keterangan)
                                <div class="flex justify-between items-start pt-2">
                                    <span
                                        class="text-sm font-medium text-gray-600 dark:text-gray-400">Keterangan:</span>
                                    <span class="text-sm text-gray-900 dark:text-gray-100 text-right max-w-64">
                                        {{ $penjualan->keterangan }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Summary Singkat -->
                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Ringkasan Transaksi</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal Barang:</span>
                                <span class="font-medium">Rp
                                    {{ number_format($penjualanDetails->sum('subtotal'), 0, ',', '.') }}</span>
                            </div>
                            @if ($penjualan->total_diskon > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Total Diskon:</span>
                                    <span class="text-red-600">-{{ $penjualan->formatted_total_diskon }}</span>
                                </div>
                            @endif
                            @if ($penjualan->total_biaya_lain > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Total Biaya Lain:</span>
                                    <span class="text-green-600">+{{ $penjualan->formatted_total_biaya_lain }}</span>
                                </div>
                            @endif
                            <hr class="my-2 border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between font-semibold text-lg">
                                <span>Total Akhir (Pembulatan):</span>
                                <span
                                    class="text-blue-600 dark:text-blue-400">{{ $penjualan->formatted_total_harga }}</span>
                            </div>
                            @if ($this->totalKembalian > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Total Kembalian:</span>
                                    <span
                                        class="text-orange-600 dark:text-orange-400 font-medium">{{ $this->formattedTotalKembalian }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Informasi Kembalian (ditampilkan ketika ada kembalian) -->
            @if ($this->totalKembalian > 0)
                <x-card title="Informasi Kembalian" shadow separator>
                    <div
                        class="bg-gradient-to-r from-orange-50 to-yellow-50 dark:from-orange-900/20 dark:to-yellow-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="p-3 bg-orange-100 dark:bg-orange-900/30 rounded-full">
                                <x-icon name="o-currency-dollar" class="w-6 h-6 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-orange-800 dark:text-orange-200">
                                    Total Kembalian yang Diberikan
                                </h3>
                                <p class="text-sm text-orange-700 dark:text-orange-300">
                                    Kembalian dari pembayaran yang melebihi total tagihan
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div
                                class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-orange-200 dark:border-orange-700">
                                <div class="text-3xl font-bold text-orange-600 dark:text-orange-400 mb-2">
                                    {{ $this->formattedTotalKembalian }}
                                </div>
                                <div class="text-sm font-medium text-orange-700 dark:text-orange-300">
                                    Total Kembalian
                                </div>
                            </div>

                            <div
                                class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-blue-200 dark:border-blue-700">
                                <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                                    {{ $this->formattedTotalPembayaran }}
                                </div>
                                <div class="text-sm font-medium text-blue-700 dark:text-blue-300">
                                    Total Dibayar
                                </div>
                            </div>

                            <div
                                class="text-center p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="text-3xl font-bold text-gray-600 dark:text-gray-400 mb-2">
                                    {{ $penjualan->formatted_total_harga }}
                                </div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total Tagihan
                                </div>
                            </div>
                        </div>

                        <!-- Detail Kembalian per Pembayaran -->
                        @if ($pembayaranList->where('kembalian', '>', 0)->count() > 0)
                            <div class="mt-6">
                                <h4 class="text-md font-semibold text-orange-800 dark:text-orange-200 mb-3">
                                    Detail Kembalian per Pembayaran
                                </h4>
                                <div class="space-y-2">
                                    @foreach ($pembayaranList->where('kembalian', '>', 0) as $pembayaran)
                                        <div
                                            class="flex justify-between items-center p-3 bg-white dark:bg-gray-800 rounded border border-orange-100 dark:border-orange-800">
                                            <div class="flex items-center gap-3">
                                                <x-badge
                                                    value="{{ ucfirst(str_replace('_', ' ', $pembayaran->jenis_pembayaran)) }}"
                                                    class="badge-primary" />
                                                <span class="text-sm text-gray-600 dark:text-gray-400">
                                                    {{ $pembayaran->created_at->format('d M Y, H:i') }}
                                                </span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Pembayaran:</div>
                                                <div class="font-semibold text-green-600 dark:text-green-400">
                                                    Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600 dark:text-gray-400">Kembalian:</div>
                                                <div class="font-semibold text-orange-600 dark:text-orange-400">
                                                    Rp {{ number_format($pembayaran->kembalian, 0, ',', '.') }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif

            <!-- Detail Barang -->
            <x-card title="Detail Barang" shadow separator>

                <div wire:loading wire:target="refreshData"
                    class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                    <div class="flex items-center space-x-2">
                        <x-loading class="loading-spinner" />
                        <span class="text-sm text-gray-600">Memuat data...</span>
                    </div>
                </div>
                @foreach ($this->purchaseInfo as $purchase)
                    <div class="mb-6 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div
                            class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h4 class="font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $purchase['nomor_pembelian'] }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Supplier: {{ $purchase['supplier'] }} |
                                        Tanggal:
                                        {{ \Carbon\Carbon::parse($purchase['tanggal_pembelian'])->format('d M Y') }}
                                    </p>
                                </div>

                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                                        <th class="text-left py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Barang</th>
                                        <th class="text-left py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Satuan</th>
                                        <th class="text-left py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Gudang</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Harga Beli</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Harga Jual</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Jumlah</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Subtotal Beli</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Subtotal Jual</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Profit</th>
                                        <th class="text-right py-2 px-4 font-medium text-gray-700 dark:text-gray-300">
                                            Margin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchase['items'] as $item)
                                        <tr
                                            class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800">
                                            <td class="py-2 px-4 font-medium text-gray-900 dark:text-gray-100">
                                                {{ $item['barang_nama'] }}
                                            </td>
                                            <td class="py-2 px-4 text-gray-700 dark:text-gray-300">
                                                {{ $item['satuan_nama'] }}
                                            </td>
                                            <td class="py-2 px-4 text-gray-700 dark:text-gray-300">
                                                {{ $item['gudang_nama'] }}
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-700 dark:text-gray-300">
                                                {{ $item['formatted_harga_beli'] }}
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-700 dark:text-gray-300">
                                                {{ $item['formatted_harga_jual'] }}
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-700 dark:text-gray-300">
                                                {{ number_format($item['jumlah'], 2) }}
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-700 dark:text-gray-300">
                                                {{ $item['formatted_subtotal_beli'] }}
                                            </td>
                                            <td class="py-2 px-4 text-right text-gray-700 dark:text-gray-300">
                                                {{ $item['formatted_subtotal_jual'] }}
                                            </td>
                                            <td
                                                class="py-2 px-4 text-right font-medium text-green-600 dark:text-green-400">
                                                {{ $item['formatted_profit'] }}
                                            </td>
                                            <td
                                                class="py-2 px-4 text-right font-medium text-green-600 dark:text-green-400">
                                                {{ $item['margin_profit'] }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr
                                        class="bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                        <td colspan="6"
                                            class="py-2 px-4 text-right font-semibold text-gray-700 dark:text-gray-300">
                                            Total(Pembulatan):
                                        </td>
                                        <td
                                            class="py-2 px-4 text-right font-semibold text-gray-700 dark:text-gray-300">
                                            Rp
                                            {{ number_format($purchase['items']->sum('subtotal_beli'), 0, ',', '.') }}
                                        </td>
                                        <td
                                            class="py-2 px-4 text-right font-semibold text-gray-700 dark:text-gray-300">
                                            Rp
                                            {{ number_format($purchase['items']->sum('subtotal_jual'), 0, ',', '.') }}
                                        </td>
                                        <td
                                            class="py-2 px-4 text-right font-semibold text-green-600 dark:text-green-400">
                                            Rp {{ number_format($purchase['items']->sum('profit'), 0, ',', '.') }}
                                        </td>
                                        <td
                                            class="py-2 px-4 text-right font-semibold text-green-600 dark:text-green-400">
                                            {{ $purchase['items']->sum('subtotal_beli') > 0
                                                ? round(($purchase['items']->sum('profit') / $purchase['items']->sum('subtotal_beli')) * 100, 2)
                                                : 0 }}%
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
                @if ($penjualanDetails->count() > 0)
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Informasi Batch Pembelian Per Barang
                    </h3>
                    <div class="overflow-x-auto">
                        <div class="space-y-4">
                            @foreach ($penjualanDetails as $index => $detail)
                                <div
                                    class="bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm text-gray-500">{{ $index + 1 }}</span>
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ $detail->barang->nama_barang }}
                                            </h4>

                                            @if ($detail->barang->keterangan)
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $detail->barang->keterangan }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ number_format($detail->jumlah, 2) }} {{ $detail->satuan->nama_satuan }}
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="flex items-center gap-1">
                                            <x-icon name="o-building-office-2" class="w-4 h-4" />
                                            {{ $detail->gudang->nama_gudang }}
                                        </div>

                                        @if ($detail->pembelianDetail && $detail->pembelianDetail->pembelian)
                                            <div class="flex items-center gap-1">
                                                <x-icon name="o-queue-list" class="w-4 h-4" />
                                                {{ $detail->pembelianDetail->pembelian->nomor_pembelian }}
                                                ({{ \Carbon\Carbon::parse($detail->pembelianDetail->pembelian->tanggal_pembelian)->format('d/m/Y') }})
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <x-icon name="o-cube" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>Tidak ada detail barang</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Kolom Kanan -->
        <div class="space-y-6">

            <!-- Informasi Pembelian (ditampilkan ketika pembayaran melebihi total harga) -->
            @if ($this->paymentExceedsTotal && $this->purchaseInfo->count() > 0)
                <x-card title="Informasi Detail Penjualan" shadow separator>
                    @if ($this->sisaPembayaran < 0)
                        <div
                            class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="o-currency-dollar" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                                <span class="font-medium text-blue-800 dark:text-blue-200">
                                    Pembayaran melebihi total tagihan. Berikut adalah informasi kembalian:
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-blue-700 dark:text-blue-300">Total Pembayaran:</span>
                                    <span
                                        class="font-medium text-blue-800 dark:text-blue-200 ml-2">{{ $this->formattedTotalPembayaran }}</span>
                                </div>
                                <div>
                                    <span class="text-blue-700 dark:text-blue-300">Total Tagihan:</span>
                                    <span
                                        class="font-medium text-blue-800 dark:text-blue-200 ml-2">{{ $penjualan->formatted_total_harga }}</span>
                                </div>
                                <div>
                                    <span class="text-blue-700 dark:text-blue-300">Total Kembalian:</span>
                                    <span
                                        class="font-medium text-red-600 dark:text-red-400 ml-2">{{ $this->formattedSisaPembayaran }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div
                        class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="text-yellow-700 dark:text-yellow-300">Total Biaya Pembelian:</span>
                                <span
                                    class="font-medium text-yellow-800 dark:text-yellow-200 ml-2">{{ $this->formattedTotalPurchaseCost }}</span>
                            </div>
                            <div>
                                <span class="text-yellow-700 dark:text-yellow-300">Total Profit:</span>
                                <span
                                    class="font-medium text-green-600 dark:text-green-400 ml-2">{{ $this->formattedTotalProfit }}</span>
                            </div>
                            <div>
                                <span class="text-yellow-700 dark:text-yellow-300">Margin Profit:</span>
                                <span class="font-medium text-green-600 dark:text-green-400 ml-2">
                                    {{ $this->totalPurchaseCost > 0 ? round(($this->totalProfit / $this->totalPurchaseCost) * 100, 2) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>


                </x-card>
            @endif
            <!-- Riwayat Pembayaran -->
            <x-card title="Riwayat Pembayaran" shadow separator>
                @if ($pembayaranList->count() > 0)
                    <div class="space-y-4">
                        @foreach ($pembayaranList as $pembayaran)
                            <div
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <x-badge
                                                value="{{ ucfirst(str_replace('_', ' ', $pembayaran->jenis_pembayaran)) }}"
                                                class="badge-primary" />
                                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $pembayaran->created_at->format('d M Y, H:i') }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <div class="text-lg font-semibold text-green-600 dark:text-green-400">
                                                Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}
                                            </div>
                                            @if ($pembayaran->kembalian > 0)
                                                <div
                                                    class="text-sm font-medium px-2 py-1 bg-orange-100 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300 rounded">
                                                    Kembalian: Rp
                                                    {{ number_format($pembayaran->kembalian, 0, ',', '.') }}
                                                </div>
                                            @endif
                                        </div>
                                        @if ($pembayaran->keterangan)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                                {{ $pembayaran->keterangan }}
                                            </p>
                                        @endif
                                        <div class="flex items-center gap-2">
                                            <x-icon name="o-user" class="w-4 h-4 text-gray-400" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $pembayaran->user->name }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex gap-2">
                                        <x-button wire:click="lihatDetailPembayaran({{ $pembayaran->id }})"
                                            size="sm" class="btn-info" icon="o-eye">
                                            Detail
                                        </x-button>
                                        <x-button wire:click="cetakPembayaran({{ $pembayaran->id }})" size="sm"
                                            class="btn-secondary" icon="o-printer">
                                            Cetak
                                        </x-button>
                                        <x-button wire:click="konfirmasiHapusPembayaran({{ $pembayaran->id }})"
                                            size="sm" class="btn-error" icon="o-trash">
                                            Hapus
                                        </x-button>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <!-- Summary Pembayaran -->
                        <div
                            class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mt-4">
                            <div
                                class="grid grid-cols-1 md:grid-cols-{{ $this->totalKembalian > 0 ? '4' : '3' }} gap-4 text-center">
                                <div>
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($this->totalPembayaran, 0, ',', '.') }}
                                    </div>
                                    <div class="text-sm text-blue-700 dark:text-blue-300">Total Dibayar</div>
                                </div>
                                @if ($this->totalKembalian > 0)
                                    <div>
                                        <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                            {{ $this->formattedTotalKembalian }}
                                        </div>
                                        <div class="text-sm text-orange-700 dark:text-orange-300">Total Kembalian</div>
                                    </div>
                                @endif
                                <div>
                                    <div
                                        class="text-2xl font-bold {{ $this->sisaPembayaran > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        Rp {{ number_format(abs($this->sisaPembayaran), 0, ',', '.') }}
                                    </div>
                                    <div
                                        class="text-sm {{ $this->sisaPembayaran > 0 ? 'text-red-700 dark:text-red-300' : 'text-green-700 dark:text-green-300' }}">
                                        {{ $this->sisaPembayaran > 0 ? 'Sisa Tagihan' : ($this->sisaPembayaran < 0 ? 'Kelebihan Bayar' : 'Lunas') }}
                                    </div>
                                    @if ($this->sisaPembayaran < 0 && $this->purchaseInfo->count() > 0)
                                        
                                    @endif
                                </div>
                                <div>
                                    <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">
                                        Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Tagihan</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <x-icon name="o-credit-card" class="w-12 h-12 mx-auto mb-2 opacity-50" />
                        <p>Belum ada pembayaran</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6">
        <div class="flex flex-wrap gap-3 justify-end">
            <x-button :href="route('penjualan.index')" wire:navigate class="btn-error text-white" icon="o-arrow-left">
                Kembali ke Daftar
            </x-button>
            <x-button :href="route('penjualan.edit', ['id' => $penjualan->id])" wire:navigate class="btn-warning text-white" icon="o-pencil">
                Edit Penjualan
            </x-button>
            <x-button wire:click="cetakInvoice" class="btn-primary text-white" icon="o-printer">
                Cetak Invoice
            </x-button>
        </div>
    </div>

    <!-- Form Tambah Pembayaran Baru -->
    @if ($this->sisaPembayaran > 0)
        <x-card title="Tambah Pembayaran Baru" shadow separator class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <x-select label="Jenis Pembayaran" wire:model="jenis_pembayaran.live"
                        placeholder="Pilih jenis pembayaran" :options="[
                            ['id' => 'cash', 'name' => 'Cash'],
                            ['id' => 'transfer', 'name' => 'Transfer'],
                            ['id' => 'check', 'name' => 'Cek'],
                            ['id' => 'other', 'name' => 'Lainnya'],
                        ]" />
                </div>
                <div>
                    <x-input label="Jumlah Pembayaran" wire:model="jumlah" type="number" min="1"
                        placeholder="Masukkan jumlah pembayaran" />
                </div>
                <div>
                    <x-input label="Keterangan (Opsional)" wire:model="keterangan"
                        placeholder="Masukkan keterangan pembayaran" />
                </div>
                <div class="flex items-end">
                    <x-button wire:click="tambahPembayaran" class="btn-primary text-white w-full" icon="o-plus">
                        Tambah Pembayaran
                    </x-button>
                </div>
            </div>

            <!-- Progress Bar Pembayaran -->
            <div class="mt-6">
                <div class="flex justify-between text-sm mb-1">
                    <span>Proses Pembayaran</span>
                    <span>{{ $this->persentasePembayaran }}%</span>
                </div>
                
                @php
                    $colorMap = [
                        'error' => 'red',
                        'warning' => 'yellow', 
                        'success' => 'green'
                    ];
                    $progressBarColor = $colorMap[$this->progressColor] ?? 'gray';
                @endphp
                
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                    <div class="bg-{{ $progressBarColor }}-500 h-2.5 rounded-full"
                        style="width: {{ $this->persentasePembayaran }}%"></div>
                </div>
            </div>

            <!-- Tombol Pembayaran Cepat -->
            <div class="mt-6 flex flex-wrap gap-2">
                <x-button wire:click="bayarLunas" class="btn-success text-white" icon="o-check">
                    Bayar Lunas
                </x-button>
                <x-button wire:click="bayarSebagian(25)" class="btn-info text-white" icon="o-banknotes">
                    Bayar 25%
                </x-button>
                <x-button wire:click="bayarSebagian(50)" class="btn-info text-white" icon="o-banknotes">
                    Bayar 50%
                </x-button>
                <x-button wire:click="bayarSebagian(75)" class="btn-info text-white" icon="o-banknotes">
                    Bayar 75%
                </x-button>
                <x-button wire:click="refreshPembayaran" class="btn-secondary" icon="o-arrow-path">
                    Refresh Data
                </x-button>
            </div>
        </x-card>
    @endif

    <!-- Modal Konfirmasi Hapus Pembayaran -->
    <x-modal wire:model="showDeleteModal">
        <x-card title="Konfirmasi Hapus Pembayaran" class="mx-auto max-w-lg">
            <p class="mb-4 text-gray-700 dark:text-gray-300">
                Apakah Anda yakin ingin menghapus pembayaran ini? Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="flex justify-end gap-3">
                <x-button wire:click="batalHapusPembayaran" class="btn-secondary">
                    Batal
                </x-button>
                <x-button wire:click="hapusPembayaran" class="btn-error text-white">
                    Hapus Pembayaran
                </x-button>
            </div>
        </x-card>
    </x-modal>

    <!-- Modal Detail Pembayaran -->
    <x-modal wire:model="showDetailModal">
        <x-card title="Detail Pembayaran" class="mx-auto max-w-lg">
            @if ($selectedPembayaranDetail)
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">ID Pembayaran:</span>
                        <span class="text-gray-900 dark:text-gray-100">#{{ $selectedPembayaranDetail->id }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Tanggal Pembayaran:</span>
                        <span
                            class="text-gray-900 dark:text-gray-100">{{ $selectedPembayaranDetail->created_at->format('d M Y, H:i') }}</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Jenis Pembayaran:</span>
                        <x-badge
                            value="{{ ucfirst(str_replace('_', ' ', $selectedPembayaranDetail->jenis_pembayaran)) }}"
                            class="badge-primary" />
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Jumlah:</span>
                        <span class="text-lg font-semibold text-green-600 dark:text-green-400">
                            Rp {{ number_format($selectedPembayaranDetail->jumlah, 0, ',', '.') }}
                        </span>
                    </div>

                    @if ($selectedPembayaranDetail->kembalian > 0)
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Kembalian:</span>
                            <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">
                                Rp {{ number_format($selectedPembayaranDetail->kembalian, 0, ',', '.') }}
                            </span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center">
                        <span class="font-medium text-gray-700 dark:text-gray-300">Petugas:</span>
                        <span
                            class="text-gray-900 dark:text-gray-100">{{ $selectedPembayaranDetail->user->name }}</span>
                    </div>

                    @if ($selectedPembayaranDetail->keterangan)
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3 mt-3">
                            <span class="font-medium text-gray-700 dark:text-gray-300 block mb-2">Keterangan:</span>
                            <p class="text-gray-900 dark:text-gray-100 bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                                {{ $selectedPembayaranDetail->keterangan }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="flex justify-between mt-6">
                    <x-button wire:click="konfirmasiHapusPembayaran({{ $selectedPembayaranDetail?->id }})"
                        class="btn-error text-white" icon="o-trash">
                        Hapus
                    </x-button>
                    <div class="flex gap-3">
                        <x-button wire:click="cetakPembayaran({{ $selectedPembayaranDetail?->id }})"
                            class="btn-primary text-white" icon="o-printer">
                            Cetak Bukti
                        </x-button>
                        <x-button wire:click="tutupDetailPembayaran" class="btn-secondary">
                            Tutup
                        </x-button>
                    </div>
                </div>
            @else
                <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <p>Data pembayaran tidak ditemukan</p>
                </div>
            @endif
        </x-card>
    </x-modal>

    <x-back-refresh />
</div>
