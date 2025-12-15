
<div class="dark:bg-gray-900 dark:text-white min-h-screen">
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header />

    <div class="container mx-auto px-4 py-6">
        <div class="max-w-6xl mx-auto">
            <!-- Header Section -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 mb-6">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-t-xl p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl font-bold text-white">{{ strtoupper(substr($supplier_data->nama_supplier, 0, 2)) }}</span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-white">{{ $supplier_data->nama_supplier }}</h1>
                                <p class="text-green-100 dark:text-green-200 mt-1">ID Supplier: #{{ $supplier_data->id }}</p>
                                <div class="flex items-center mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white">
                                        <span class="w-2 h-2 bg-green-300 rounded-full mr-2"></span>
                                        Aktif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex gap-3">
                            <x-button 
                                :href="route('supplier.edit', $supplier_data->id)" 
                                wire:navigate 
                                class="bg-white/10 border-white/20 text-white hover:bg-white/20 transition-all duration-200"
                                icon="o-pencil"
                                label="Edit Supplier"
                            />
                            <x-button 
                                :href="route('supplier.index')" 
                                wire:navigate 
                                class="bg-white text-green-600 hover:bg-green-50 transition-all duration-200"
                                icon="o-arrow-left"
                                label="Kembali"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                                <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Informasi Supplier
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Nama Supplier -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2 block">Nama Supplier</label>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $supplier_data->nama_supplier }}</span>
                                    </div>
                                </div>

                                <!-- Tanggal Bergabung -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2 block">Tanggal Bergabung</label>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-gray-900 dark:text-gray-100">{{ $supplier_data->created_at->format('d F Y') }}</span>
                                    </div>
                                </div>

                                <!-- Alamat -->
                                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2 block">Alamat Lengkap</label>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <p class="text-gray-900 dark:text-gray-100 leading-relaxed">{{ $supplier_data->alamat }}</p>
                                    </div>
                                </div>

                                <!-- Keterangan -->
                                @if($supplier_data->keterangan)
                                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2 block">Keterangan</label>
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <p class="text-gray-900 dark:text-gray-100 leading-relaxed">{{ $supplier_data->keterangan }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6 flex items-center">
                                <svg class="w-6 h-6 mr-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                Informasi Kontak
                            </h2>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Telepon -->
                                @if($supplier_data->no_hp)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2 block">Nomor Telepon</label>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        <a href="tel:{{ $supplier_data->no_hp }}" class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 font-medium transition-colors">{{ $supplier_data->no_hp }}</a>
                                    </div>
                                </div>
                                @endif

                                <!-- Email -->
                                @if($supplier_data->email)
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <label class="text-sm font-medium text-gray-600 dark:text-gray-300 mb-2 block">Email</label>
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        <a href="mailto:{{ $supplier_data->email }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium transition-colors">{{ $supplier_data->email }}</a>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Quick Contact Actions -->
                            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Kontak Cepat</h4>
                                <div class="flex flex-wrap gap-3">
                                    @if($supplier_data->no_hp)
                                    <a href="tel:{{ $supplier_data->no_hp }}" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        Telepon
                                    </a>
                                    @endif
                                    @if($supplier_data->email)
                                    <a href="mailto:{{ $supplier_data->email }}" class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                        Email
                                    </a>
                                    @endif
                                    @if($supplier_data->no_hp)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $supplier_data->no_hp) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        WhatsApp
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- System Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Informasi Sistem
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dibuat Tanggal</label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-medium">{{ $supplier_data->created_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terakhir Diubah</label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-medium">{{ $supplier_data->updated_at->format('d M Y, H:i') }}</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Durasi Kerjasama</label>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-medium">{{ $supplier_data->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Statistik Transaksi
                            </h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-blue-50 rounded-lg border border-blue-100">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Total Pembelian</span>
                                    </div>
                                    <span class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $supplier_data->pembelian->count() }}</span>
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Total Nilai</span>
                                    </div>
                                    <span class="text-lg font-bold text-green-600 dark:text-green-400">
                                        Rp {{ number_format($supplier_data->pembelian->sum('total_harga'), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Aksi Cepat
                            </h3>
                            
                            <div class="space-y-3">
                                <a href="{{ route('supplier.edit', $supplier_data->id) }}" 
                                   wire:navigate 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-medium rounded-lg transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Supplier
                                </a>
                                
                                <button 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-medium rounded-lg cursor-not-allowed"
                                    disabled>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                    </svg>
                                    Cetak Profil
                                </button>
                                
                                <button 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-medium rounded-lg cursor-not-allowed"
                                    disabled
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    Duplikasi Data
                                </button>

                                <button 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 font-medium rounded-lg cursor-not-allowed"
                                    disabled>
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                                    </svg>
                                    Buat Pembelian
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-back-refresh />
</div>