<div x-data="{ mobileMenuOpen: false }">
    <div class="navbar bg-base-100 shadow-sm border-b border-base-300 sticky top-0 z-30 pr-10 pl-10">
        {{-- Brand/Logo dan Menu --}}
        <div class="navbar-start w-full">
            <div class="flex items-center space-x-4">
                {{-- Logo --}}
                <div class="lg:hidden">
                    {{-- Mobile Menu Toggle --}}
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="btn btn-ghost btn-square h-10 w-10 flex items-center justify-center"
                        :class="{ 'btn-active': mobileMenuOpen }">
                        <x-icon name="o-bars-3" class="w-6 h-6" x-show="!mobileMenuOpen" />
                        <x-icon name="o-x-mark" class="w-6 h-6" x-show="mobileMenuOpen" />
                    </button>
                </div>
                <div class="flex items-center">
                    <x-icon name="o-building-storefront" class="w-8 h-8 text-primary mr-2" />
                    <span class="text-xl font-bold text-base-content">{{ config('app.name', 'POS SCM') }}</span>
                </div>

                {{-- Desktop Menu --}}
                <ul class="hidden lg:flex menu-horizontal px-1 space-x-2">
                    @php
                        $userRole = auth()->user() && auth()->user()->akses ? auth()->user()->akses->role : null;
                    @endphp

                    {{-- Dashboard - Tampil untuk semua role --}}
                    <li
                        class="{{ Request::routeIs('home') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                        <a href="{{ route('home') }}" wire:navigate class="btn btn-ghost">
                            <x-icon name="o-squares-2x2" class="w-4 h-4" />
                            Dashboard
                        </a>
                    </li>

                    @if ($userRole === 'admin')
                        {{-- Master Data Dropdown - Hanya untuk Admin --}}
                        <li
                            class="{{ Request::routeIs('satuan.*') || Request::routeIs('jenis-barang.*') || Request::routeIs('barang.*') || Request::routeIs('supplier.*') || Request::routeIs('customer.*') || Request::routeIs('gudang.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                            <details class="dropdown">
                                <summary class="btn btn-ghost">
                                    <x-icon name="o-cog-6-tooth" class="w-4 h-4" />
                                    Master&nbsp;Data
                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                </summary>
                                <ul class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                    <li
                                        class="{{ Request::routeIs('satuan.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('satuan.index') }}" wire:navigate>
                                            <x-icon name="o-scale" class="w-4 h-4" />
                                            Satuan
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('jenis-barang.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('jenis-barang.index') }}" wire:navigate>
                                            <x-icon name="o-rectangle-stack" class="w-4 h-4" />
                                            Jenis Barang
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('barang.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('barang.index') }}" wire:navigate>
                                            <x-icon name="o-cube" class="w-4 h-4" />
                                            Barang
                                        </a>
                                    </li>

                                    <li
                                        class="{{ Request::routeIs('supplier.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('supplier.index') }}" wire:navigate>
                                            <x-icon name="o-truck" class="w-4 h-4" />
                                            Supplier
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('customer.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('customer.index') }}" wire:navigate>
                                            <x-icon name="o-users" class="w-4 h-4" />
                                            Customer
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('gudang.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('gudang.index') }}" wire:navigate>
                                            <x-icon name="o-building-office-2" class="w-4 h-4" />
                                            Gudang
                                        </a>
                                    </li>
                                </ul>
                            </details>
                        </li>
                    @endif

                    {{-- Transaksi Dropdown --}}
                    <li
                        class="{{ Request::routeIs('pembelian.*') || Request::routeIs('penjualan.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                        <details class="dropdown">
                            <summary class="btn btn-ghost">
                                <x-icon name="o-banknotes" class="w-4 h-4" />
                                Transaksi
                                <x-icon name="o-chevron-down" class="w-4 h-4" />
                            </summary>
                            <ul class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                @if ($userRole === 'admin')
                                    <li
                                        class="{{ Request::routeIs('pembelian.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('pembelian.index') }}" wire:navigate>
                                            <x-icon name="o-shopping-bag" class="w-4 h-4" />
                                            Pembelian
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('retur-pembelian.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('retur-pembelian.index') }}" wire:navigate>
                                            <x-icon name="o-arrow-uturn-down" class="w-4 h-4" />
                                            Retur Pembelian
                                        </a>
                                    </li>
                                @endif
                                <li
                                    class="{{ Request::routeIs('penjualan.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                    <a href="{{ route('penjualan.index') }}" wire:navigate>
                                        <x-icon name="o-shopping-cart" class="w-4 h-4" />
                                        Penjualan
                                    </a>
                                </li>
                                <li
                                    class="{{ Request::routeIs('retur-penjualan.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                    <a href="{{ route('retur-penjualan.index') }}" wire:navigate>
                                        <x-icon name="o-arrow-uturn-down" class="w-4 h-4" />
                                        Retur Penjualan
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>

                    @if ($userRole === 'admin')
                        {{-- Inventory Dropdown - Hanya untuk Admin --}}
                        <li
                            class="{{ Request::routeIs('gudang-stock.*') || Request::routeIs('laporan.pembayaran') || Request::routeIs('laporan.profit') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                            <details class="dropdown">
                                <summary class="btn btn-ghost">
                                    <x-icon name="o-archive-box" class="w-4 h-4" />
                                    Laporan
                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                </summary>
                                <ul class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                    <li
                                        class="{{ Request::routeIs('gudang-stock.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('gudang-stock.index') }}" wire:navigate>
                                            <x-icon name="o-squares-2x2" class="w-4 h-4" />
                                            Stock Gudang
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('laporan.pembayaran') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('laporan.pembayaran') }}" wire:navigate>
                                            <x-icon name="o-document-text" class="w-4 h-4" />
                                            Laporan Pembayaran
                                        </a>
                                    </li>
                                    <li
                                        class="{{ Request::routeIs('laporan.profit') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('laporan.profit') }}" wire:navigate>
                                            <x-icon name="o-document-text" class="w-4 h-4" />
                                            Laporan Profit
                                        </a>
                                    </li>
                                </ul>
                            </details>
                        </li>
                    @endif

                     @if ($userRole === 'admin')
                        <li
                            class="{{ Request::routeIs('stock-opname.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                            <a href="{{ route('stock-opname.index') }}" wire:navigate class="btn btn-ghost">
                                <x-icon name="o-inbox-stack" class="w-4 h-4" />
                                Stock&nbsp;Opname
                            </a>
                        </li>
                    @endif

                    @if ($userRole === 'admin')
                        {{-- Sistem Data Dropdown - Hanya untuk Admin --}}
                        <li
                            class="{{ Request::routeIs('user.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                            <details class="dropdown">
                                <summary class="btn btn-ghost">
                                    <x-icon name="o-server-stack" class="w-4 h-4" />
                                    Sistem&nbsp;Data
                                    <x-icon name="o-chevron-down" class="w-4 h-4" />
                                </summary>
                                <ul class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                                    <li
                                        class="{{ Request::routeIs('user.*') ? 'bg-green-200 dark:bg-gray-600 text-base-content rounded-lg' : '' }}">
                                        <a href="{{ route('user.index') }}" wire:navigate>
                                            <x-icon name="o-users" class="w-4 h-4" />
                                            User
                                        </a>
                                    </li>
                                </ul>
                            </details>
                        </li>
                    @endif

                </ul>
            </div>
        </div>

        {{-- Right side actions --}}
        <div class="navbar-end">
            {{-- Desktop Actions --}}
            <div class="hidden lg:flex items-center space-x-2">
                <x-theme-toggle />
                {{-- User Dropdown --}}
                @auth
                    @php
                        $user = Auth::user();
                        if ($user) {
                            $names = explode(' ', $user->name);
                            $initials = '';
                            foreach ($names as $name) {
                                $initials .= strtoupper(substr($name, 0, 1));
                            }
                            $initials; // Menampilkan contoh: "M.K"
                        } else {
                            $initials = ''; // Menampilkan contoh: "M.K"
                        }
                    @endphp
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                            <x-avatar placeholder="{{ $initials }}" class="!w-8 !h-8">
                            </x-avatar>
                        </div>
                        <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">

                            <li>
                                <x-menu-separator />
                            </li>
                            <li>
                                <a href="{{ route('logout') }}"
                                    class="text-red-600 hover:text-red-700 transition-colors">
                                    <x-icon name="o-arrow-left-on-rectangle" class="w-5 h-5" />
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                @else
                    <x-button label="Login" link="/login" class="btn btn-ghost btn-sm" />
                @endauth
            </div>

            {{-- Mobile Menu Toggle --}}
            <div class="lg:hidden flex items-center space-x-2">
                {{-- Theme Toggle --}}
                <x-theme-toggle />
                <div class="btn btn-ghost btn-square p-0">

                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                            <x-avatar placeholder="{{ $initials }}" class="!w-8 !h-8">
                            </x-avatar>
                        </div>
                        <ul tabindex="0"
                            class="dropdown-content menu bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                            <li>
                                <a href="/profile">
                                    <x-icon name="o-user" class="w-4 h-4" />
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="/settings">
                                    <x-icon name="o-cog-6-tooth" class="w-4 h-4" />
                                    Settings
                                </a>
                            </li>
                            <li>
                                <hr class="my-1">
                            </li>
                            <li>
                                <a href="{{ route('logout') }}"
                                    class="text-red-600 hover:text-red-700 transition-colors">
                                    <x-icon name="o-arrow-left-on-rectangle" class="w-5 h-5" />
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>


            </div>

        </div>
    </div>


    {{-- Mobile Menu Overlay --}}
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
        @click="mobileMenuOpen = false">
    </div>

    {{-- Mobile Menu Sidebar --}}
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed top-0 left-0 w-80 h-full bg-base-100 shadow-xl z-50 lg:hidden overflow-y-auto">

        {{-- Mobile Menu Header --}}
        <div class="p-4 border-b border-base-300">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <x-icon name="o-home" class="w-8 h-8 text-primary mr-2" />
                    <span class="text-xl font-bold">{{ config('app.name', 'App') }}</span>
                </div>
                <x-button @click="mobileMenuOpen = false" class="btn-ghost btn-square btn-sm">
                    <x-icon name="o-x-mark" class="w-5 h-5" />
                </x-button>
            </div>
        </div>

        {{-- User Info (Mobile) --}}
        @auth
            <div class="p-4 border-b border-base-300">
                <x-list-item :item="auth()->user()" value="name" sub-value="email" no-separator no-hover>
                    <x-slot:avatar>
                        <x-avatar placeholder="{{ $initials }}" class="w-6 h-6">
                        </x-avatar>
                    </x-slot:avatar>
                </x-list-item>
            </div>
        @endauth

        {{-- Mobile Menu Items --}}
        <div class="p-4">
            <x-menu activate-by-route>
                {{-- Dashboard - Tampil untuk semua role --}}
                <x-menu-item title="Dashboard" icon="o-squares-2x2" link="{{ route('home') }}"
                    @click="mobileMenuOpen = false" />

                <x-menu-separator />

                @if ($userRole === 'admin')
                    {{-- Master Data Mobile Menu - Hanya untuk Admin --}}
                    <x-menu-sub title="Master Data" icon="o-cog-6-tooth">
                        <x-menu-item title="Satuan" icon="o-scale" link="{{ route('satuan.index') }}"
                            @click="mobileMenuOpen = false" />
                        <x-menu-item title="Jenis Barang" icon="o-rectangle-stack"
                            link="{{ route('jenis-barang.index') }}" @click="mobileMenuOpen = false" />
                        <x-menu-item title="Barang" icon="o-cube" link="{{ route('barang.index') }}"
                            @click="mobileMenuOpen = false" />

                        <x-menu-item title="Supplier" icon="o-truck" link="{{ route('supplier.index') }}"
                            @click="mobileMenuOpen = false" />
                        <x-menu-item title="Customer" icon="o-users" link="{{ route('customer.index') }}"
                            @click="mobileMenuOpen = false" />
                        <x-menu-item title="Gudang" icon="o-building-office-2" link="{{ route('gudang.index') }}"
                            @click="mobileMenuOpen = false" />
                    </x-menu-sub>

                    <x-menu-separator />
                @endif

                {{-- Transaksi Mobile Menu --}}
                <x-menu-sub title="Transaksi" icon="o-banknotes">
                    @if ($userRole === 'admin')
                        <x-menu-item title="Pembelian" icon="o-shopping-bag" link="{{ route('pembelian.index') }}"
                            @click="mobileMenuOpen = false" />
                        <x-menu-item title="Retur Pembelian" icon="o-arrow-uturn-down" link="{{ route('retur-pembelian.index') }}"
                            @click="mobileMenuOpen = false" />
                    @endif
                    <x-menu-item title="Penjualan" icon="o-shopping-cart" link="{{ route('penjualan.index') }}"
                        @click="mobileMenuOpen = false" />
                    <x-menu-item title="Retur Penjualan" icon="o-arrow-uturn-down" link="{{ route('retur-penjualan.index') }}"
                        @click="mobileMenuOpen = false" />
                </x-menu-sub>

                @if ($userRole === 'admin')
                    <x-menu-separator />

                    {{-- Inventory Mobile Menu - Hanya untuk Admin --}}
                    <x-menu-sub title="Inventory" icon="o-archive-box">
                        <x-menu-item title="Stock Gudang" icon="o-squares-2x2"
                            link="{{ route('gudang-stock.index') }}" @click="mobileMenuOpen = false" />
                        <x-menu-item title="Laporan Pembayaran" icon="o-currency-dollar"
                            link="{{ route('laporan.pembayaran') }}" @click="mobileMenuOpen = false" />
                        <x-menu-item title="Laporan Profit" icon="o-currency-dollar"
                            link="{{ route('laporan.profit') }}" @click="mobileMenuOpen = false" />
                    </x-menu-sub>
                @endif

                @if ($userRole === 'admin')

                <x-menu-separator />

                {{-- Stock Opname Mobile Menu - Hanya for Admin --}}

                <x-menu-item title="Stock Opname" icon="o-squares-2x2"
                            link="{{ route('stock-opname.index') }}" @click="mobileMenuOpen = false" />

                <x-menu-separator />

                {{-- Sistem Data Mobile Menu - Hanya untuk Admin --}}
                <x-menu-sub title="Sistem Data" icon="o-server-stack">
                    <x-menu-item title="User" icon="o-users" link="{{ route('user.index') }}"
                        @click="mobileMenuOpen = false" />
                </x-menu-sub>

                @endif
            </x-menu>
        </div>
    </div>
</div>
