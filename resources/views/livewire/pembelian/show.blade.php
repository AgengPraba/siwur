<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="space-y-6 pb-4 mt-6">
        <!-- Header Information -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">
            <!-- Status Card -->
            <x-card class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-500 text-white mr-4">
                        <x-icon name="o-shopping-cart" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Pembelian</p>
                        <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                            @if ($pembelian_data->status == 'pending')
                                <x-badge value="PENDING" class="badge-warning" />
                            @elseif($pembelian_data->status == 'approved')
                                <x-badge value="APPROVED" class="badge-success" />
                            @elseif($pembelian_data->status == 'rejected')
                                <x-badge value="REJECTED" class="badge-error" />
                            @else
                                <x-badge value="{{ strtoupper($pembelian_data->status) }}" class="badge-info" />
                            @endif
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Total Harga Card -->
            <x-card class="bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-500 text-white mr-4">
                        <x-icon name="o-currency-dollar" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Harga</p>
                        <p class="text-lg font-bold text-green-600 dark:text-green-400">
                            Rp {{ number_format($pembelian_data->total_harga, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Detail Items Card -->
            <x-card class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-500 text-white mr-4">
                        <x-icon name="o-squares-2x2" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Item</p>
                        <p class="text-lg font-bold text-purple-600 dark:text-purple-400">
                            {{ $pembelian_details->count() }} Items
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Pembayaran Card -->
            <x-card class="bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-500 text-white mr-4">
                        <x-icon name="o-credit-card" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pembayaran</p>
                        <p class="text-lg font-bold text-orange-600 dark:text-orange-400">
                            {{ $pembelian_payments->count() }} Transaksi
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Kembalian Card -->
            <x-card class="bg-gradient-to-r from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-500 text-white mr-4">
                        <x-icon name="o-arrow-uturn-left" class="w-6 h-6" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Kembalian</p>
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">
                            Rp {{ number_format($total_kembalian, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </x-card>
        </div>

        <!-- Main Information -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Informasi Pembelian -->
            <div class="lg:col-span-2">
                <x-card title="Informasi Pembelian" shadow separator>
                    <x-slot:menu>
                        <x-icon name="o-document-text" class="w-5 h-5" />
                    </x-slot:menu>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor
                                    Pembelian</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $pembelian_data->nomor_pembelian }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal
                                    Pembelian</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($pembelian_data->tanggal_pembelian)->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 font-semibold">
                                    {{ $pembelian_data->supplier->nama_supplier }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">User
                                    Input</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $pembelian_data->user->name }}</p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Diskon</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">Rp
                                    {{ number_format($pembelian_data->total_diskon, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Biaya
                                    Lain</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">Rp
                                    {{ number_format($pembelian_data->total_biaya_lain, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300">Keterangan</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ $pembelian_data->keterangan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dibuat
                                    Pada</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                    {{ \Carbon\Carbon::parse($pembelian_data->created_at)->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @if ($pembelian_data->informasi_tambahan)
                        <div
                            class="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 dark:bg-yellow-900/20 dark:border-yellow-500">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <x-icon name="o-exclamation-triangle" class="w-5 h-5 text-yellow-400" />
                                </div>
                                <div class="ml-3">
                                    <label
                                        class="block text-sm font-medium text-yellow-800 dark:text-yellow-200">Informasi
                                        Tambahan</label>
                                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-100">
                                        {{ $pembelian_data->informasi_tambahan }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </x-card>
            </div>

            <!-- Summary Card -->
            <div>
                <x-card title="Ringkasan Pembelian" shadow separator>
                    <x-slot:menu>
                        <x-icon name="o-calculator" class="w-5 h-5" />
                    </x-slot:menu>

                    <div class="space-y-4">
                        @php
                            $subtotal = $pembelian_details->sum('subtotal');
                            $total_paid = $pembelian_payments->sum('jumlah');
                            $remaining = $pembelian_data->total_harga - $total_paid;
                        @endphp

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Subtotal</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">Rp
                                {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Diskon</span>
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">-Rp
                                {{ number_format($pembelian_data->total_diskon, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Biaya Lain</span>
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">+Rp
                                {{ number_format($pembelian_data->total_biaya_lain, 0, ',', '.') }}</span>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900 dark:text-gray-100">Total Harga</span>
                            <span class="text-base font-bold text-green-600 dark:text-green-400">Rp
                                {{ number_format($pembelian_data->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Terbayar</span>
                            <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">Rp
                                {{ number_format($total_paid, 0, ',', '.') }}</span>
                        </div>

                        @if ($remaining >= 0)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sisa</span>
                            <span
                                class="text-sm font-semibold {{ $remaining > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </span>
                        </div>
                        @endif

                        <hr class="border-gray-200 dark:border-gray-700">

                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Kembalian</span>
                            <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                Rp {{ number_format($total_kembalian, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Detail Barang -->
        <x-card title="Detail Barang" shadow separator>
            <x-slot:menu>
                <x-icon name="o-cube" class="w-5 h-5" />
            </x-slot:menu>

            @if ($pembelian_details->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Nama Barang</th>
                                <th scope="col" class="px-6 py-3">Satuan</th>
                                <th scope="col" class="px-6 py-3">Gudang</th>
                                <th scope="col" class="px-6 py-3">Harga Satuan</th>
                                <th scope="col" class="px-6 py-3">Jumlah</th>
                                <th scope="col" class="px-6 py-3">Diskon</th>
                                <th scope="col" class="px-6 py-3">Biaya Lain</th>
                                <th scope="col" class="px-6 py-3">Rencana Harga Jual</th>
                                <th scope="col" class="px-6 py-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pembelian_details as $index => $detail)
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $index + 1 }}</td>
                                    <td class="px-6 py-4">{{ $detail->barang->nama_barang }}</td>
                                    <td class="px-6 py-4">{{ $detail->satuan->nama_satuan }}</td>
                                    <td class="px-6 py-4">{{ $detail->gudang->nama_gudang }}</td>
                                    <td class="px-6 py-4">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">{{ number_format($detail->jumlah, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 text-red-600 dark:text-red-400">
                                        Rp {{ number_format($detail->diskon, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($detail->biaya_lain, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4">Rp
                                        {{ number_format($detail->rencana_harga_jual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="o-cube" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Belum ada detail barang</p>
                </div>
            @endif
        </x-card>

        <!-- Riwayat Pembayaran -->
        <x-card title="Riwayat Pembayaran" shadow separator>
            <x-slot:menu>
                <x-icon name="o-credit-card" class="w-5 h-5" />
            </x-slot:menu>

            @if ($pembelian_payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Tanggal</th>
                                <th scope="col" class="px-6 py-3">Jenis Pembayaran</th>
                                <th scope="col" class="px-6 py-3">Jumlah</th>
                                <th scope="col" class="px-6 py-3">Kembalian</th>
                                <th scope="col" class="px-6 py-3">User</th>
                                <th scope="col" class="px-6 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pembelian_payments as $index => $payment)
                                <tr
                                    class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                                        {{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        {{ \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <x-badge value="{{ strtoupper($payment->jenis_pembayaran) }}"
                                            class="badge-info" />
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($payment->jumlah, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-red-600 dark:text-red-400">
                                        @if($payment->kembalian > 0)
                                            Rp {{ number_format($payment->kembalian, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $payment->user->name }}</td>
                                    <td class="px-6 py-4">{{ $payment->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <x-icon name="o-credit-card" class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                    <p class="text-gray-500 dark:text-gray-400">Belum ada pembayaran</p>
                </div>
            @endif
        </x-card>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <x-button :href="route('pembelian.index')" wire:navigate class="btn-neutral w-full sm:w-auto" icon="o-arrow-left">
                Kembali ke Daftar
            </x-button>

            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <x-button :href="route('pembelian.edit', ['id' => $pembelian_data->id])" wire:navigate class="btn-primary w-full sm:w-auto" icon="o-pencil">
                    Edit Pembelian
                </x-button>
                <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <x-button wire:click="printPurchase" class="btn-success w-full sm:w-auto" icon="o-printer">
                        Cetak Pembelian
                    </x-button>
                    <x-button wire:click="printPayment" class="btn-info w-full sm:w-auto" icon="o-credit-card">
                        Cetak Pembayaran
                    </x-button>
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />
</div>
