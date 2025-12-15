<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pb-4 mt-6">
        <!-- Product Information Card -->
        <div class="lg:col-span-1">
            <x-card title="Informasi Barang" shadow separator class="h-fit">
                <div class="space-y-4">
                    <div
                        class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-lg border border-blue-200 dark:border-gray-700">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-3">{{ $barang_data->nama_barang }}
                        </h3>

                        <div class="space-y-3">
                            <div class="flex items-center">
                                <x-icon name="o-tag" class="w-5 h-5 text-blue-600 mr-3" />
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Jenis Barang</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $barang_data->jenisBarang->nama_jenis_barang ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <x-icon name="o-scale" class="w-5 h-5 text-green-600 mr-3" />
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Satuan Terkecil</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $barang_data->satuanTerkecil->nama_satuan ?? '-' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start">
                                <x-icon name="o-document-text" class="w-5 h-5 text-purple-600 mr-3 mt-1" />
                                <div>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Keterangan</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        {{ $barang_data->keterangan ?: 'Tidak ada keterangan' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <p><strong>Dibuat:</strong> {{ $barang_data->created_at->format('d M Y H:i') }}</p>
                        <p><strong>Diperbarui:</strong> {{ $barang_data->updated_at->format('d M Y H:i') }}</p>
                    </div>
                </div>

                <x-slot:actions>
                    <x-button :href="route('barang.index')" wire:navigate class="btn-outline btn-sm w-full"
                        icon="o-arrow-left">Kembali ke Daftar Barang</x-button>
                </x-slot:actions>
            </x-card>
        </div>

        <!-- Unit Management Card -->
        <div class="lg:col-span-2">
            <x-card title="Manajemen Satuan Barang" shadow separator>
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <x-button wire:click="showAddForm" class="btn-primary btn-sm" icon="o-plus">
                            Tambah Satuan
                        </x-button>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                        <x-icon name="o-information-circle" class="w-4 h-4 mr-1" />
                        <span>Satuan terkecil tidak dapat diedit atau dihapus</span>
                    </div>
                </div>

                <!-- Units List -->
                @if (count($barangSatuanList) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @foreach ($barangSatuanList as $barangSatuan)
                            <div
                                class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex items-center">
                                        <div
                                            class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                                            {{ strtoupper(substr($barangSatuan->satuan->nama_satuan, 0, 2)) }}
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 dark:text-white">
                                                {{ $barangSatuan->satuan->nama_satuan }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $barangSatuan->satuan->keterangan }}</p>
                                        </div>
                                    </div>

                                    @if ($barangSatuan->is_satuan_terkecil === 'ya')
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                            <x-icon name="o-check-circle" class="w-3 h-3 mr-1" />
                                            Terkecil
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                        <x-icon name="o-calculator" class="w-4 h-4 mr-1" />
                                        <span>Konversi:
                                            {{ number_format($barangSatuan->konversi_satuan_terkecil, 2) }}</span>
                                    </div>

                                    <div class="flex space-x-2">
                                        @if ($barangSatuan->is_satuan_terkecil === 'ya')
                                            <div class="flex items-center text-xs text-gray-400 dark:text-gray-500"
                                                title="Satuan terkecil tidak dapat diedit atau dihapus">
                                                <x-icon name="o-lock-closed" class="w-4 h-4 mr-1" />
                                                Terlindungi
                                            </div>
                                        @else
                                            <x-button wire:click="editBarangSatuan({{ $barangSatuan->id }})"
                                                class="btn-ghost btn-xs" icon="o-pencil" title="Edit satuan" />
                                            <x-button wire:click="deleteBarangSatuan({{ $barangSatuan->id }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus satuan ini?"
                                                class="btn-ghost btn-xs text-red-600 hover:bg-red-50" icon="o-trash"
                                                title="Hapus satuan" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-icon name="o-scale" class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Satuan</h3>
                        <p class="text-gray-500 dark:text-gray-400 mb-4">Tambahkan satuan untuk barang ini agar dapat
                            digunakan dalam transaksi.</p>
                        <x-button wire:click="showAddForm" class="btn-primary" icon="o-plus">
                            Tambah Satuan Pertama
                        </x-button>
                    </div>
                @endif

                <!-- Add/Edit Form Modal -->
                @if ($showForm)
                    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                        wire:click.self="closeForm">
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $editMode ? 'Edit Satuan' : 'Tambah Satuan' }}
                                </h3>
                            </div>

                            <form wire:submit="saveBarangSatuan" class="p-6 space-y-4">
                                <!-- Info Alert -->
                                @if (!$editMode)
                                    <div
                                        class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                                        <div class="flex items-start">
                                            <x-icon name="o-information-circle"
                                                class="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                                            <div class="text-sm text-blue-800 dark:text-blue-200">
                                                <p class="font-medium mb-1">Aturan Satuan:</p>
                                                <ul class="list-disc list-inside space-y-1 text-xs">
                                                    <li>Setiap barang harus memiliki minimal satu satuan terkecil</li>
                                                    <li>Satuan terkecil digunakan sebagai referensi konversi</li>
                                                    <li>Satuan terkecil tidak dapat diedit atau dihapus</li>
                                                    @if (count($barangSatuanList) > 0)
                                                        <li class="text-green-700 dark:text-green-300">Default: "Tidak"
                                                            karena sudah ada satuan terkecil</li>
                                                    @else
                                                        <li class="text-amber-700 dark:text-amber-300">Satuan pertama
                                                            otomatis jadi satuan terkecil</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div>
                                    <x-select label="Satuan" wire:model="satuan_id" :options="$satuanOptions" option-value="id"
                                        option-label="name" placeholder="Pilih satuan..." />
                                    @if ($errors->has('satuan_id'))
                                        <span class="text-red-500 text-sm">{{ $errors->first('satuan_id') }}</span>
                                    @endif
                                </div>

                                <div>
                                    <x-input label="Konversi ke Satuan Terkecil" wire:model="konversi_satuan_terkecil"
                                        type="number" step="0.01" min="0.01"
                                        placeholder="Contoh: 12 (untuk 1 lusin = 12 butir)" />

                                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                        <p>💡 <strong>Tips:</strong> Isi dengan angka konversi, contoh:</p>
                                        <div class="ml-4 mt-1 space-y-1">
                                            <p>• 1 Lusin = 12 Butir → isi dengan: <span
                                                    class="font-mono bg-gray-100 dark:bg-gray-800 px-1 rounded">12</span>
                                            </p>
                                            <p>• 1 Karton = 360 Butir → isi dengan: <span
                                                    class="font-mono bg-gray-100 dark:bg-gray-800 px-1 rounded">360</span>
                                            </p>
                                            <p>• 1 Kodi = 20 Butir → isi dengan: <span
                                                    class="font-mono bg-gray-100 dark:bg-gray-800 px-1 rounded">20</span>
                                            </p>
                                        </div>
                                    </div>

                                    @if ($errors->has('konversi_satuan_terkecil'))
                                        <span
                                            class="text-red-500 text-sm">{{ $errors->first('konversi_satuan_terkecil') }}</span>
                                    @endif
                                </div>

                                <div>
                                    @if (count($barangSatuanList) === 0)
                                        <div
                                            class="mt-2 p-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded text-sm">
                                            <div class="flex items-center text-amber-800 dark:text-amber-200">
                                                <x-icon name="o-exclamation-triangle" class="w-4 h-4 mr-2" />
                                                <span>Satuan pertama harus ditetapkan sebagai satuan terkecil</span>
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            class="mt-2 p-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded text-sm">
                                            <div class="flex items-center text-green-800 dark:text-green-200">
                                                <x-icon name="o-check-circle" class="w-4 h-4 mr-2" />
                                                <span>Default: "Tidak" - karena sudah ada satuan terkecil</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($errors->has('is_satuan_terkecil'))
                                        <span
                                            class="text-red-500 text-sm">{{ $errors->first('is_satuan_terkecil') }}</span>
                                    @endif
                                </div>

                                <div class="flex justify-end space-x-3 pt-4">
                                    <x-button type="button" wire:click="closeForm" class="btn-ghost">
                                        Batal
                                    </x-button>
                                    <x-button type="submit" class="btn-primary" icon="o-check">
                                        {{ $editMode ? 'Perbarui' : 'Simpan' }}
                                    </x-button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Price Rules Management Section -->
    <div class="mt-6">
        <x-card title="Manajemen Aturan Harga Barang" shadow separator>
            <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <x-button wire:click="showAddAturanHargaForm" class="btn-primary btn-sm" icon="o-plus"
                        @if(count($satuanAturanOptions) === 0) disabled title="Tambahkan satuan terlebih dahulu" @endif>
                        Tambah Aturan Harga
                    </x-button>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                    <x-icon name="o-information-circle" class="w-4 h-4 mr-1" />
                    <span>Aturan harga ditentukan berdasarkan range kuantitas penjualan</span>
                </div>
            </div>

            @if(count($satuanAturanOptions) === 0)
                <div class="text-center py-8">
                    <x-icon name="o-banknotes" class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Dapat Menambah Aturan Harga</h3>
                    <p class="text-gray-500 dark:text-gray-400">Silakan tambahkan satuan barang terlebih dahulu sebelum membuat aturan harga.</p>
                </div>
            @elseif(count($aturanHargaList) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    @foreach($aturanHargaList as $aturanHarga)
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                                        {{ strtoupper(substr($aturanHarga->satuan->nama_satuan, 0, 2)) }}
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 dark:text-white">{{ $aturanHarga->satuan->nama_satuan }}</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $aturanHarga->satuan->keterangan }}</p>
                                    </div>
                                </div>
                                <div class="flex space-x-1">
                                    <x-button wire:click="editAturanHarga({{ $aturanHarga->id }})" class="btn-ghost btn-xs" icon="o-pencil" title="Edit aturan harga" />
                                    <x-button wire:click="deleteAturanHarga({{ $aturanHarga->id }})" 
                                        wire:confirm="Apakah Anda yakin ingin menghapus aturan harga ini?"
                                        class="btn-ghost btn-xs text-red-600 hover:bg-red-50" icon="o-trash" title="Hapus aturan harga" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Range Qty:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ number_format($aturanHarga->minimal_penjualan) }} 
                                        @if($aturanHarga->maksimal_penjualan)
                                            - {{ number_format($aturanHarga->maksimal_penjualan) }}
                                        @else
                                            - ∞
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Harga Jual:</span>
                                    <span class="font-bold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($aturanHarga->harga_jual, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <x-icon name="o-banknotes" class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Belum Ada Aturan Harga</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-4">Tambahkan aturan harga untuk menentukan harga jual berdasarkan kuantitas pembelian.</p>
                    <x-button wire:click="showAddAturanHargaForm" class="btn-primary" icon="o-plus">
                        Tambah Aturan Harga Pertama
                    </x-button>
                </div>
            @endif

            <!-- Add/Edit Price Rule Form Modal -->
            @if($showAturanHargaForm)
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closeAturanHargaForm">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4">
                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $editAturanHargaMode ? 'Edit Aturan Harga' : 'Tambah Aturan Harga' }}
                            </h3>
                        </div>

                        <form wire:submit="saveAturanHarga" class="p-6 space-y-4">
                            <!-- Info Alert -->
                            @if(!$editAturanHargaMode)
                                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                                    <div class="flex items-start">
                                        <x-icon name="o-information-circle" class="w-5 h-5 text-blue-600 mr-2 mt-0.5" />
                                        <div class="text-sm text-blue-800 dark:text-blue-200">
                                            <p class="font-medium mb-1">Aturan Harga:</p>
                                            <ul class="list-disc list-inside space-y-1 text-xs">
                                                <li>Range kuantitas tidak boleh tumpang tindih dengan aturan yang sudah ada</li>
                                                <li>Maksimal penjualan boleh dikosongkan (artinya tidak terbatas)</li>
                                                <li>Sistem akan otomatis memilih harga berdasarkan kuantitas saat transaksi</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div>
                                <x-select label="Satuan" wire:model="aturan_satuan_id" :options="$satuanAturanOptions" 
                                    option-value="id" option-label="name" placeholder="Pilih satuan..." />
                                @if($errors->has('aturan_satuan_id'))
                                    <span class="text-red-500 text-sm">{{ $errors->first('aturan_satuan_id') }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <x-input label="Minimal Penjualan" wire:model="minimal_penjualan" 
                                        type="number" min="1" placeholder="Contoh: 1" />
                                    @if($errors->has('minimal_penjualan'))
                                        <span class="text-red-500 text-sm">{{ $errors->first('minimal_penjualan') }}</span>
                                    @endif
                                </div>
                                <div>
                                    <x-input label="Maksimal Penjualan" wire:model="maksimal_penjualan" 
                                        type="number" min="1" placeholder="Kosongkan jika tidak terbatas" />
                                    @if($errors->has('maksimal_penjualan'))
                                        <span class="text-red-500 text-sm">{{ $errors->first('maksimal_penjualan') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <x-input label="Harga Jual" wire:model="harga_jual" 
                                    type="number" step="0.01" min="0.01" placeholder="Contoh: 25000" />
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    💡 Masukkan harga dalam format angka, contoh: 25000 untuk Rp 25.000
                                </div>
                                @if($errors->has('harga_jual'))
                                    <span class="text-red-500 text-sm">{{ $errors->first('harga_jual') }}</span>
                                @endif
                            </div>

                            <!-- Example Preview -->
                            @if($minimal_penjualan && $harga_jual)
                                <div class="p-3 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1">Preview:</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Pembelian {{ number_format($minimal_penjualan) }}
                                        @if($maksimal_penjualan)
                                            - {{ number_format($maksimal_penjualan) }}
                                        @else
                                            atau lebih
                                        @endif
                                        unit → Harga: Rp {{ number_format($harga_jual, 0, ',', '.') }} per unit
                                    </p>
                                </div>
                            @endif

                            <div class="flex justify-end space-x-3 pt-4">
                                <x-button type="button" wire:click="closeAturanHargaForm" class="btn-ghost">
                                    Batal
                                </x-button>
                                <x-button type="submit" class="btn-primary" icon="o-check">
                                    {{ $editAturanHargaMode ? 'Perbarui' : 'Simpan' }}
                                </x-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </x-card>
    </div>

    <x-back-refresh />
</div>
