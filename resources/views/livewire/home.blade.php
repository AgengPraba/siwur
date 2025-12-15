<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use App\Models\Penjualan;
use App\Models\Pembelian;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Barang;
use App\Models\GudangStock;
use App\Models\PenjualanDetail;
use App\Models\Akses;
use App\Models\Toko;
use App\Models\Satuan;
use App\Models\JenisBarang;
use App\Models\BarangSatuan;
use App\Models\Gudang;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mary\Traits\Toast;

new #[Layout('components.layouts.app')] #[Title('Dashboard')] class extends Component {
    use Toast;

    // Status toko dan akses user
    public $hasStoreAccess = false;
    public $userRole = '';
    public $currentToko = null;
    public $showCreateStoreForm = false;

    // Form data untuk membuat toko
    public $nama_toko = '';
    public $alamat_toko = '';
    public $logo_toko = '';
    public $import_template_data = true; // Default true untuk import data template

    // Dashboard data
    public $todaySales = 0;
    public $todayPurchases = 0;
    public $monthlySales = 0;
    public $monthlyPurchases = 0;
    public $totalCustomers = 0;
    public $totalSuppliers = 0;
    public $totalProducts = 0;
    public $lowStockItems = 0;
    public $pendingSales = 0;
    public $pendingPurchases = 0;

    // Khusus untuk dashboard kasir
    public $recentSales = [];
    public $topProducts = [];
    public $dailySalesCount = 0;
    public $monthlySalesCount = 0;
    public $averageTransactionValue = 0;

    public function mount()
    {
        $this->checkUserStoreAccess();

        if ($this->hasStoreAccess) {
            $this->loadDashboardData();
        }
    }

    /**
     * Cek apakah user sudah memiliki akses toko
     */
    public function checkUserStoreAccess()
    {
        $userId = Auth::id();

        // Cek apakah user memiliki akses di tabel akses
        $akses = Akses::where('user_id', $userId)->with('toko')->first();

        if ($akses) {
            $this->hasStoreAccess = true;
            $this->userRole = $akses->role;
            $this->currentToko = $akses->toko;
        } else {
            $this->hasStoreAccess = false;
            $this->showCreateStoreForm = true;
        }
    }

    /**
     * Method untuk membuat toko baru
     */
    public function createStore()
    {
        // Validasi input
        $this->validate(
            [
                'nama_toko' => 'required|string|max:255',
                'alamat_toko' => 'nullable|string|max:500',
            ],
            [
                'nama_toko.required' => 'Nama toko wajib diisi',
                'nama_toko.max' => 'Nama toko maksimal 255 karakter',
                'alamat_toko.max' => 'Alamat toko maksimal 500 karakter',
            ],
        );

        try {
            DB::beginTransaction();

            $userId = Auth::id();

            // Buat toko baru
            $toko = Toko::create([
                'nama_toko' => $this->nama_toko,
                'alamat_toko' => $this->alamat_toko,
                'logo_toko' => $this->logo_toko,
                'user_id' => $userId,
            ]);

            // Buat akses sebagai admin untuk user yang membuat toko
            Akses::create([
                'user_id' => $userId,
                'toko_id' => $toko->id,
                'role' => 'admin',
            ]);

            // Assign admin role menggunakan Spatie
            $user = Auth::user();
            if (!$user->hasAnyRole(['admin', 'kasir', 'staff_gudang', 'akuntan'])) {
                $user->assignRole('admin');
            }

            // Import data template jika dipilih
            if ($this->import_template_data) {
                $this->importTemplateData($toko->id);
            }

            // Buat data dummy customer dan supplier
            $this->createDummyData($toko->id);

            DB::commit();

            // Simpan status import sebelum reset
            $wasImported = $this->import_template_data;

            // Reset form
            $this->reset(['nama_toko', 'alamat_toko', 'logo_toko', 'import_template_data', 'showCreateStoreForm']);

            // Refresh status akses
            $this->checkUserStoreAccess();

            // Load dashboard data
            if ($this->hasStoreAccess) {
                $this->loadDashboardData();
            }

            $successMessage = 'Toko berhasil dibuat! Anda sekarang memiliki akses sebagai admin.';
            if ($wasImported) {
                $successMessage .= ' Data template (satuan, jenis barang, dan produk) telah diimport.';
            }
            $successMessage .= ' Data dummy gudang utama, customer dan supplier telah ditambahkan.';

            $this->success($successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal membuat toko: ' . $e->getMessage());
        }

        // Refresh page after store creation
        $this->dispatch('refresh-navigation');
        return redirect()->to('/');
    }

    /**
     * Method untuk membatalkan pembuatan toko
     */
    public function cancelCreateStore()
    {
        $this->reset(['nama_toko', 'alamat_toko', 'logo_toko', 'import_template_data']);
        $this->showCreateStoreForm = false;
    }

    /**
     * Method untuk import data template ke toko baru
     */
    private function importTemplateData($tokoId)
    {
        // 1. Import template_satuan ke satuan
        $templateSatuan = DB::table('template_satuan')->get();
        $satuanMapping = [];

        foreach ($templateSatuan as $template) {
            $satuan = Satuan::create([
                'nama_satuan' => $template->nama_satuan,
                'keterangan' => $template->keterangan,
                'toko_id' => $tokoId,
            ]);
            $satuanMapping[$template->id] = $satuan->id;
        }

        // 2. Import template_jenis_barang ke jenis_barang
        $templateJenisBarang = DB::table('template_jenis_barang')->get();
        $jenisBarangMapping = [];

        foreach ($templateJenisBarang as $template) {
            $jenisBarang = JenisBarang::create([
                'nama_jenis_barang' => $template->nama_jenis_barang,
                'keterangan' => $template->keterangan,
                'toko_id' => $tokoId,
            ]);
            $jenisBarangMapping[$template->id] = $jenisBarang->id;
        }

        // 3. Import template_barang ke barang
        $templateBarang = DB::table('template_barang')->get();
        $barangMapping = [];

        foreach ($templateBarang as $template) {
            // Pastikan jenis_barang_id dan satuan_terkecil_id sudah ada di mapping
            if (isset($jenisBarangMapping[$template->jenis_barang_id]) && isset($satuanMapping[$template->satuan_terkecil_id])) {
                $barang = Barang::create([
                    'kode_barang' => $template->kode_barang,
                    'nama_barang' => $template->nama_barang,
                    'keterangan' => $template->keterangan,
                    'jenis_barang_id' => $jenisBarangMapping[$template->jenis_barang_id],
                    'satuan_terkecil_id' => $satuanMapping[$template->satuan_terkecil_id],
                    'toko_id' => $tokoId,
                ]);
                $barangMapping[$template->id] = $barang->id;
            }
        }

        // 4. Import template_barang_satuan ke barang_satuan
        $templateBarangSatuan = DB::table('template_barang_satuan')->get();

        foreach ($templateBarangSatuan as $template) {
            // Pastikan barang_id dan satuan_id sudah ada di mapping
            if (isset($barangMapping[$template->barang_id]) && isset($satuanMapping[$template->satuan_id])) {
                BarangSatuan::create([
                    'barang_id' => $barangMapping[$template->barang_id],
                    'satuan_id' => $satuanMapping[$template->satuan_id],
                    'konversi_satuan_terkecil' => $template->konversi_satuan_terkecil,
                    'is_satuan_terkecil' => $template->is_satuan_terkecil,
                ]);
            }
        }
    }

    /**
     * Method untuk membuat data dummy customer, supplier, dan gudang
     */
    private function createDummyData($tokoId)
    {
        // Buat gudang utama
        Gudang::create([
            'nama_gudang' => 'Gudang Utama',
            'keterangan' => 'Gudang utama untuk penyimpanan barang',
            'toko_id' => $tokoId,
        ]);

        // Buat customer umum
        Customer::create([
            'nama_customer' => 'Customer Umum',
            'alamat' => 'Alamat tidak diketahui',
            'no_hp' => '-',
            'email' => null,
            'keterangan' => 'Customer default untuk transaksi umum',
            'is_opname' => false,
            'toko_id' => $tokoId,
        ]);
        
        // Buat customer opname
        Customer::create([
            'nama_customer' => 'Customer Opname',
            'alamat' => 'Alamat tidak diketahui',
            'no_hp' => '-',
            'email' => null,
            'keterangan' => 'Customer default untuk transaksi opname',
            'is_opname' => true,
            'toko_id' => $tokoId,
        ]);

        // Buat supplier umum
        Supplier::create([
            'nama_supplier' => 'Supplier Umum',
            'alamat' => 'Alamat tidak diketahui',
            'no_hp' => '-',
            'email' => null,
            'keterangan' => 'Supplier default untuk pembelian umum',
            'is_opname' => false,
            'toko_id' => $tokoId,
        ]);
        
        // Buat supplier opname
        Supplier::create([
            'nama_supplier' => 'Supplier Opname',
            'alamat' => 'Alamat tidak diketahui',
            'no_hp' => '-',
            'email' => null,
            'keterangan' => 'Supplier default untuk pembelian opname',
            'is_opname' => true,
            'toko_id' => $tokoId,
        ]);
    }

    public function loadDashboardData()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $tokoId = $this->currentToko->id;

        // Sales data
        $this->todaySales = Penjualan::where('toko_id', $tokoId)->whereDate('tanggal_penjualan', $today)->sum('total_harga');
        $this->monthlySales = Penjualan::where('toko_id', $tokoId)
            ->whereBetween('tanggal_penjualan', [$startOfMonth, $endOfMonth])
            ->sum('total_harga');
        $this->pendingSales = Penjualan::where('toko_id', $tokoId)->where('status', 'belum_bayar')->count();

        // Data khusus untuk kasir
        if ($this->userRole === 'kasir') {
            // Jumlah transaksi hari ini dan bulan ini
            $this->dailySalesCount = Penjualan::where('toko_id', $tokoId)->whereDate('tanggal_penjualan', $today)->count();
            $this->monthlySalesCount = Penjualan::where('toko_id', $tokoId)
                ->whereBetween('tanggal_penjualan', [$startOfMonth, $endOfMonth])
                ->count();

            // Rata-rata nilai transaksi
            if ($this->monthlySalesCount > 0) {
                $this->averageTransactionValue = round($this->monthlySales / $this->monthlySalesCount);
            }

            // Transaksi terbaru
            $this->recentSales = Penjualan::with(['customer', 'user'])
                ->where('toko_id', $tokoId)
                ->orderBy('tanggal_penjualan', 'desc')
                ->limit(5)
                ->get();

            // Produk terlaris
            $this->topProducts = PenjualanDetail::with('barang')
                ->select('barang_id', DB::raw('SUM(jumlah) as total_sold'))
                ->whereHas('penjualan', function ($query) use ($startOfMonth, $endOfMonth, $tokoId) {
                    $query->where('toko_id', $tokoId)->whereBetween('tanggal_penjualan', [$startOfMonth, $endOfMonth]);
                })
                ->groupBy('barang_id')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();
        }

        // Data untuk admin
        if ($this->userRole === 'admin') {
            // Purchase data
            $this->todayPurchases = Pembelian::where('toko_id', $tokoId)->whereDate('tanggal_pembelian', $today)->sum('total_harga');
            $this->monthlyPurchases = Pembelian::where('toko_id', $tokoId)
                ->whereBetween('tanggal_pembelian', [$startOfMonth, $endOfMonth])
                ->sum('total_harga');
            $this->pendingPurchases = Pembelian::where('toko_id', $tokoId)->where('status', 'belum_bayar')->count();

            // General counts
            $this->totalCustomers = Customer::where('toko_id', $tokoId)->where('is_opname', false)->count();
            $this->totalSuppliers = Supplier::where('toko_id', $tokoId)->where('is_opname', false)->count();
            $this->totalProducts = Barang::where('toko_id', $tokoId)->count();
            $this->lowStockItems = GudangStock::whereHas('gudang', function ($query) use ($tokoId) {
                $query->where('toko_id', $tokoId);
            })
                ->where('jumlah', '<=', 10)
                ->count();
        }
    }
}; ?>

<div>
    @if ($showCreateStoreForm)
        <!-- FORM PEMBUATAN TOKO -->
        <x-header title="Selamat Datang!" subtitle="Anda belum memiliki toko. Silakan buat toko terlebih dahulu."
            separator>
            <x-slot:middle class="!justify-end">
                <x-badge value="{{ date('d M Y') }}" class="badge-primary dark:badge-primary" />
            </x-slot:middle>
        </x-header>

        <div class="max-w-2xl mx-auto">
            <x-card title="Buat Toko Baru" subtitle="Lengkapi informasi toko Anda" shadow separator
                class="border-l-4 border-l-blue-500 dark:border-l-blue-400 bg-white dark:bg-gray-800">
                <x-form wire:submit="createStore">
                    <div class="grid grid-cols-1 gap-4">
                        <x-input label="Nama Toko" wire:model="nama_toko" placeholder="Masukkan nama toko" required
                            icon="o-building-storefront" class="input-bordered" />

                        <x-textarea label="Alamat Toko" wire:model="alamat_toko"
                            placeholder="Masukkan alamat lengkap toko (opsional)" rows="3"
                            class="textarea-bordered" />

                        <x-input label="Logo Toko (URL)" wire:model="logo_toko"
                            placeholder="https://example.com/logo.png (opsional)" icon="o-photo"
                            class="input-bordered" />

                        <div class="form-control">
                            <label class="label cursor-pointer">
                                <span class="label-text font-medium">
                                    <x-icon name="o-document-duplicate" class="w-4 h-4 inline mr-2" />
                                    Import Data Template
                                </span>
                                <input type="checkbox" wire:model="import_template_data"
                                    class="checkbox checkbox-primary" />
                            </label>
                            <div class="label">
                                <span class="label-text-alt text-gray-500">
                                    Otomatis import data satuan, jenis barang, dan produk template ke toko baru
                                </span>
                            </div>
                        </div>
                    </div>

                    <x-slot:actions>
                        <x-button label="Batal" wire:click="cancelCreateStore" class="btn-ghost" />
                        <x-button label="Buat Toko" type="submit" icon="o-check" class="btn-primary"
                            spinner="createStore" />
                    </x-slot:actions>
                </x-form>
            </x-card>
        </div>
    @else
        <!-- DASHBOARD NORMAL -->
        <x-header title="Dashboard {{ $currentToko ? $currentToko->nama_toko : 'POS SCM' }}"
            subtitle="Selamat datang di sistem Point of Sale & Supply Chain Management" separator progress-indicator>
            <x-slot:middle class="!justify-end">
                <x-badge value="{{ date('d M Y') }}" class="badge-primary dark:badge-primary" />
                @if ($currentToko)
                    <x-badge value="{{ ucfirst($userRole) }}" class="badge-secondary dark:badge-secondary ml-2" />
                @endif
            </x-slot:middle>
            <x-slot:actions>
                <x-button label="Refresh" icon="o-arrow-path" class="btn-primary btn-sm dark:btn-primary"
                    wire:click="loadDashboardData" />
            </x-slot:actions>
        </x-header>

        @if ($userRole === 'admin')
            <!-- DASHBOARD ADMIN -->
            <!-- Sales & Purchase Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-card
                    class="bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Penjualan Hari Ini</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                        </div>
                        <x-icon name="o-banknotes" class="w-12 h-12 text-blue-200" />
                    </div>
                </x-card>

                <x-card
                    class="bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Penjualan Bulan Ini</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($monthlySales, 0, ',', '.') }}</p>
                        </div>
                        <x-icon name="o-chart-bar" class="w-12 h-12 text-green-200" />
                    </div>
                </x-card>

                <x-card
                    class="bg-gradient-to-br from-orange-500 to-orange-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Pembelian Hari Ini</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($todayPurchases, 0, ',', '.') }}</p>
                        </div>
                        <x-icon name="o-shopping-cart" class="w-12 h-12 text-orange-200" />
                    </div>
                </x-card>

                <x-card
                    class="bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Pembelian Bulan Ini</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($monthlyPurchases, 0, ',', '.') }}</p>
                        </div>
                        <x-icon name="o-truck" class="w-12 h-12 text-purple-200" />
                    </div>
                </x-card>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-stat title="Total Customer" description="Jumlah pelanggan"
                    value="{{ number_format($totalCustomers) }}" icon="o-users" color="text-blue-600 dark:text-blue-400"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow" />

                <x-stat title="Total Supplier" description="Jumlah pemasok" value="{{ number_format($totalSuppliers) }}"
                    icon="o-building-office" color="text-green-600 dark:text-green-400"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow" />

                <x-stat title="Total Produk" description="Jumlah barang" value="{{ number_format($totalProducts) }}"
                    icon="o-cube" color="text-indigo-600 dark:text-indigo-400"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow" />

                <x-stat title="Stok Menipis" description="Barang perlu restock"
                    value="{{ number_format($lowStockItems) }}" icon="o-exclamation-triangle"
                    color="text-red-600 dark:text-red-400"
                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md dark:hover:shadow-gray-700/50 transition-shadow" />
            </div>

            <!-- Alerts & Quick Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <x-card title="Perhatian Khusus" subtitle="Item yang memerlukan tindakan" shadow separator
                    class="border-l-4 border-l-amber-500 dark:border-l-amber-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="space-y-4">
                        @if ($pendingSales > 0)
                            <div
                                class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                <div class="flex items-center space-x-3">
                                    <x-icon name="o-clock" class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                    <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Penjualan
                                        Belum Dibayar</span>
                                </div>
                                <x-badge value="{{ $pendingSales }}" class="badge-warning dark:badge-warning" />
                            </div>
                        @endif

                        @if ($pendingPurchases > 0)
                            <div
                                class="flex items-center justify-between p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg border border-orange-200 dark:border-orange-800">
                                <div class="flex items-center space-x-3">
                                    <x-icon name="o-credit-card"
                                        class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                                    <span class="text-sm font-medium text-orange-800 dark:text-orange-200">Pembelian
                                        Belum Dibayar</span>
                                </div>
                                <x-badge value="{{ $pendingPurchases }}" class="badge-warning dark:badge-warning" />
                            </div>
                        @endif

                        @if ($lowStockItems > 0)
                            <div
                                class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                <div class="flex items-center space-x-3">
                                    <x-icon name="o-exclamation-triangle"
                                        class="w-5 h-5 text-red-600 dark:text-red-400" />
                                    <span class="text-sm font-medium text-red-800 dark:text-red-200">Stok Barang
                                        Menipis</span>
                                </div>
                                <x-badge value="{{ $lowStockItems }}" class="badge-error dark:badge-error" />
                            </div>
                        @endif

                        @if ($pendingSales == 0 && $pendingPurchases == 0 && $lowStockItems == 0)
                            <div class="flex items-center justify-center p-6 text-gray-500 dark:text-gray-400">
                                <div class="text-center">
                                    <x-icon name="o-check-circle"
                                        class="w-12 h-12 text-green-500 dark:text-green-400 mx-auto mb-2" />
                                    <p class="text-sm">Semua dalam kondisi baik!</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>

                <x-card title="Aksi Cepat" subtitle="Menu favorit untuk akses cepat" shadow separator
                    class="border-l-4 border-l-blue-500 dark:border-l-blue-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="grid grid-cols-2 gap-3">
                        <a href="/penjualan" class="block">
                            <div
                                class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-shopping-bag"
                                        class="w-8 h-8 text-blue-600 dark:text-blue-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Penjualan</p>
                                </div>
                            </div>
                        </a>

                        <a href="/pembelian" class="block">
                            <div
                                class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-truck"
                                        class="w-8 h-8 text-green-600 dark:text-green-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Pembelian</p>
                                </div>
                            </div>
                        </a>

                        <a href="/barang" class="block">
                            <div
                                class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-cube"
                                        class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Produk</p>
                                </div>
                            </div>
                        </a>

                        <a href="/gudang-stock" class="block">
                            <div
                                class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-building-storefront"
                                        class="w-8 h-8 text-purple-600 dark:text-purple-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-purple-800 dark:text-purple-200">Stok Gudang</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </x-card>
            </div>

            <!-- Recent Activity Summary -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <x-card title="Ringkasan Keuangan" subtitle="Performa finansial bulan ini" shadow separator
                    class="border-l-4 border-l-emerald-500 dark:border-l-emerald-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="space-y-4">
                        <div
                            class="flex justify-between items-center p-3 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                            <span class="text-sm font-medium text-emerald-800 dark:text-emerald-200">Total
                                Penjualan</span>
                            <span class="text-lg font-bold text-emerald-600 dark:text-emerald-400">Rp
                                {{ number_format($monthlySales, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <span class="text-sm font-medium text-red-800 dark:text-red-200">Total Pembelian</span>
                            <span class="text-lg font-bold text-red-600 dark:text-red-400">Rp
                                {{ number_format($monthlyPurchases, 0, ',', '.') }}</span>
                        </div>
                        <div
                            class="flex justify-between items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-t-2 border-blue-200 dark:border-blue-700">
                            <span class="text-sm font-medium text-blue-800 dark:text-blue-200">Keuntungan Kotor</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp
                                {{ number_format($monthlySales - $monthlyPurchases, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </x-card>

                <x-card title="Status Sistem" subtitle="Informasi sistem dan database" shadow separator
                    class="border-l-4 border-l-teal-500 dark:border-l-teal-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sistem Aktif</span>
                            </div>
                            <x-badge value="Online" class="badge-success dark:badge-success" />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Database</span>
                            </div>
                            <x-badge value="Connected" class="badge-success dark:badge-success" />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Update</span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ date('H:i:s') }}</span>
                        </div>
                    </div>
                </x-card>
            </div>
            <!-- END DASHBOARD ADMIN -->
        @elseif($userRole === 'kasir')
            <!-- DASHBOARD KASIR -->
            <!-- Sales Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <x-card
                    class="bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Penjualan Hari Ini</p>
                            <p class="text-2xl font-bold">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                        </div>
                        <x-icon name="o-banknotes" class="w-12 h-12 text-blue-200" />
                    </div>
                </x-card>

                <x-card
                    class="bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Transaksi Hari Ini</p>
                            <p class="text-2xl font-bold">{{ number_format($dailySalesCount) }}</p>
                        </div>
                        <x-icon name="o-receipt-percent" class="w-12 h-12 text-green-200" />
                    </div>
                </x-card>

                <x-card
                    class="bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Transaksi Bulan Ini</p>
                            <p class="text-2xl font-bold">{{ number_format($monthlySalesCount) }}</p>
                        </div>
                        <x-icon name="o-chart-bar" class="w-12 h-12 text-purple-200" />
                    </div>
                </x-card>

                <x-card
                    class="bg-gradient-to-br from-amber-500 to-amber-600 text-white shadow-lg dark:shadow-gray-800/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-amber-100 text-sm font-medium">Rata-rata Transaksi</p>
                            <p class="text-2xl font-bold">Rp
                                {{ number_format($averageTransactionValue, 0, ',', '.') }}</p>
                        </div>
                        <x-icon name="o-currency-dollar" class="w-12 h-12 text-amber-200" />
                    </div>
                </x-card>
            </div>

            <!-- Transaksi Terbaru & Produk Terlaris -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Transaksi Terbaru -->
                <x-card title="Transaksi Terbaru" subtitle="5 transaksi penjualan terakhir" shadow separator
                    class="border-l-4 border-l-blue-500 dark:border-l-blue-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="overflow-x-auto">
                        <table class="table table-zebra table-sm">
                            <thead>
                                <tr>
                                    <th>No. Transaksi</th>
                                    <th>Tanggal</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td>
                                            <a href="{{ route('penjualan.show', $sale->id) }}"
                                                class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                                {{ $sale->nomor_penjualan }}
                                            </a>
                                        </td>
                                        <td>{{ $sale->tanggal_penjualan->format('d/m/Y') }}</td>
                                        <td>{{ $sale->customer->nama_customer }}</td>
                                        <td>Rp {{ number_format($sale->total_harga, 0, ',', '.') }}</td>
                                        <td>
                                            @if ($sale->status === 'lunas')
                                                <x-badge value="Lunas" class="badge-success dark:badge-success" />
                                            @else
                                                <x-badge value="Belum Bayar"
                                                    class="badge-warning dark:badge-warning" />
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">
                                            Belum ada transaksi penjualan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-right">
                        <x-button label="Lihat Semua" link="{{ route('penjualan.index') }}"
                            class="btn-sm btn-ghost" />
                    </div>
                </x-card>

                <!-- Produk Terlaris -->
                <x-card title="Produk Terlaris" subtitle="5 produk dengan penjualan tertinggi bulan ini" shadow
                    separator
                    class="border-l-4 border-l-emerald-500 dark:border-l-emerald-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="space-y-4">
                        @forelse($topProducts as $index => $product)
                            <div
                                class="flex items-center p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div
                                    class="flex-shrink-0 w-10 h-10 flex items-center justify-center bg-{{ ['emerald', 'blue', 'purple', 'amber', 'indigo'][$index % 5] }}-100 dark:bg-{{ ['emerald', 'blue', 'purple', 'amber', 'indigo'][$index % 5] }}-900/30 text-{{ ['emerald', 'blue', 'purple', 'amber', 'indigo'][$index % 5] }}-600 dark:text-{{ ['emerald', 'blue', 'purple', 'amber', 'indigo'][$index % 5] }}-400 rounded-full mr-4">
                                    <span class="font-bold">{{ $index + 1 }}</span>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                        {{ $product->barang->nama_barang }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $product->barang->kode_barang }}</p>
                                </div>
                                <div class="flex-shrink-0 text-right">
                                    <span
                                        class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ number_format($product->total_sold, 0) }}</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">terjual</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center p-6 text-gray-500 dark:text-gray-400">
                                <div class="text-center">
                                    <x-icon name="o-shopping-bag"
                                        class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                                    <p class="text-sm">Belum ada data penjualan produk</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                    <div class="mt-4 text-right">
                        <x-button label="Lihat Semua Produk" link="{{ route('barang.index') }}"
                            class="btn-sm btn-danger" />
                    </div>
                </x-card>
            </div>

            <!-- Aksi Cepat & Status Sistem -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Aksi Cepat -->
                <x-card title="Aksi Cepat" subtitle="Menu favorit untuk akses cepat" shadow separator
                    class="border-l-4 border-l-blue-500 dark:border-l-blue-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('penjualan.create') }}" class="block">
                            <div
                                class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-plus-circle"
                                        class="w-8 h-8 text-blue-600 dark:text-blue-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">Transaksi Baru</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('penjualan.index') }}" class="block">
                            <div
                                class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-list-bullet"
                                        class="w-8 h-8 text-green-600 dark:text-green-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-green-800 dark:text-green-200">Daftar Transaksi
                                    </p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('customer.index') }}" class="block">
                            <div
                                class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-users"
                                        class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-indigo-800 dark:text-indigo-200">Customer</p>
                                </div>
                            </div>
                        </a>

                        <a href="{{ route('barang.index') }}" class="block">
                            <div
                                class="p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors group">
                                <div class="text-center">
                                    <x-icon name="o-cube"
                                        class="w-8 h-8 text-purple-600 dark:text-purple-400 mx-auto mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-purple-800 dark:text-purple-200">Produk</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </x-card>

                <!-- Status Sistem -->
                <x-card title="Status Sistem" subtitle="Informasi sistem dan database" shadow separator
                    class="border-l-4 border-l-teal-500 dark:border-l-teal-400 bg-white dark:bg-gray-800 dark:shadow-gray-700/50">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sistem Aktif</span>
                            </div>
                            <x-badge value="Online" class="badge-success dark:badge-success" />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Database</span>
                            </div>
                            <x-badge value="Connected" class="badge-success dark:badge-success" />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Last Update</span>
                            </div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ date('H:i:s') }}</span>
                        </div>

                        @if ($pendingSales > 0)
                            <div
                                class="flex items-center justify-between p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                                <div class="flex items-center space-x-3">
                                    <x-icon name="o-clock" class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                    <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Penjualan
                                        Belum Dibayar</span>
                                </div>
                                <x-badge value="{{ $pendingSales }}" class="badge-warning dark:badge-warning" />
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
            <!-- END DASHBOARD KASIR -->
        @endif
    @endif
</div>
