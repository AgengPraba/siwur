<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;

new #[Layout('components.layouts.guest')] #[Title('Login')] class extends Component {
    use Toast;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:6')]
    public string $password = '';

    public bool $remember = false;

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->intended('/home');
        }
    }

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            request()->session()->regenerate();
            $this->success('Login berhasil!', redirectTo: route('home'));
        } else {
            $this->error('Email atau password salah.');
        }
    }
};

?>

<div
    class="min-h-screen flex items-center justify-center">
    <!-- Enhanced animated background elements -->
    <div class="absolute inset-0 z-0 overflow-hidden">
        <!-- Primary floating orbs with business theme -->
        <div
            class="absolute -top-32 -left-32 w-96 h-96 bg-gradient-to-r from-emerald-300 to-teal-300 dark:from-emerald-800 dark:to-teal-800 rounded-full mix-blend-multiply dark:mix-blend-overlay opacity-20 animate-float-slow filter blur-xl">
        </div>
        <div
            class="absolute top-1/3 -right-32 w-80 h-80 bg-gradient-to-r from-blue-300 to-indigo-300 dark:from-blue-800 dark:to-indigo-800 rounded-full mix-blend-multiply dark:mix-blend-overlay opacity-25 animate-float-medium filter blur-xl">
        </div>
        <div
            class="absolute -bottom-32 left-1/4 w-72 h-72 bg-gradient-to-r from-green-300 to-emerald-300 dark:from-green-800 dark:to-emerald-800 rounded-full mix-blend-multiply dark:mix-blend-overlay opacity-20 animate-float-fast filter blur-xl">
        </div>

        <!-- Business-themed smaller orbs -->
        <div
            class="absolute top-1/4 left-1/3 w-32 h-32 bg-gradient-to-r from-amber-300 to-orange-300 dark:from-amber-700 dark:to-orange-700 rounded-full mix-blend-multiply dark:mix-blend-overlay opacity-30 animate-pulse-slow filter blur-lg">
        </div>
        <div
            class="absolute bottom-1/4 right-1/3 w-24 h-24 bg-gradient-to-r from-cyan-300 to-blue-300 dark:from-cyan-700 dark:to-blue-700 rounded-full mix-blend-multiply dark:mix-blend-overlay opacity-25 animate-bounce-slow filter blur-lg">
        </div>

        <!-- Floating business particles -->
        <div
            class="absolute top-1/2 left-1/2 w-2 h-2 bg-emerald-400 dark:bg-emerald-300 rounded-full opacity-60 animate-float-particle-1">
        </div>
        <div
            class="absolute top-1/3 left-2/3 w-1 h-1 bg-blue-400 dark:bg-blue-300 rounded-full opacity-50 animate-float-particle-2">
        </div>
        <div
            class="absolute bottom-1/3 left-1/4 w-1.5 h-1.5 bg-teal-400 dark:bg-teal-300 rounded-full opacity-40 animate-float-particle-3">
        </div>
    </div>

    <!-- Business mesh gradient overlay -->
    <div
        class="absolute inset-0 bg-gradient-to-tr from-transparent via-emerald-50/10 to-blue-100/20 dark:from-transparent dark:via-gray-900/20 dark:to-emerald-900/30 z-0">
    </div>

    <div class="max-w-4xl w-full relative z-10 px-4 sm:px-6 lg:px-8">
        <div class="relative">
            <!-- Enhanced SIWUR Login Form Card -->
            <x-card
                class="relative backdrop-blur-xl bg-white/90 dark:bg-gray-800/90 shadow-2xl rounded-3xl border border-white/20 dark:border-gray-700/50 overflow-hidden transition-all duration-500 hover:shadow-emerald-500/20 hover:shadow-3xl group">
                <!-- Animated top border with business colors -->
                <div
                    class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-green-600 animate-gradient-x">
                </div>

                <!-- Enhanced glassmorphism overlay -->
                <div
                    class="absolute inset-0 bg-gradient-to-br from-white/10 via-transparent to-emerald-500/5 dark:from-gray-700/10 dark:to-emerald-500/5 rounded-3xl">
                </div>

                <!-- Business-themed floating elements -->
                <div class="absolute top-4 right-4 w-2 h-2 bg-emerald-400 rounded-full opacity-60 animate-pulse"></div>
                <div class="absolute bottom-6 left-6 w-1 h-1 bg-teal-400 rounded-full opacity-40 animate-bounce"></div>

                <div class="relative z-10 p-8">
                    <!-- Enhanced SIWUR Logo and Header Inside Card -->
                    <div class="flex flex-col lg:flex-row items-center lg:items-start gap-8">
                        <!-- Logo Section -->
                        <div class="flex-shrink-0 text-center lg:text-left">
                            <div
                                class="relative mx-auto lg:mx-0 h-24 w-24 flex items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-500 via-teal-600 to-green-700 shadow-2xl transform hover:scale-110 hover:rotate-6 transition-all duration-500 cursor-pointer group">
                                <!-- Enhanced glow effect -->
                                <div
                                    class="absolute inset-0 rounded-3xl bg-gradient-to-br from-emerald-400 to-green-600 opacity-0 group-hover:opacity-75 blur-xl transition-all duration-500">
                                </div>
                                <!-- Inner shadow -->
                                <div
                                    class="absolute inset-1 rounded-2xl bg-gradient-to-br from-emerald-600 to-green-700 shadow-inner">
                                </div>
                                <!-- SIWUR Icon - Business/Store themed -->
                                <div class="relative z-10 flex flex-col items-center">
                                    <x-icon name="o-building-storefront"
                                        class="h-8 w-8 text-white group-hover:scale-110 transition-transform duration-300" />
                                    <span class="text-xs font-bold text-white mt-1 tracking-wider">SIWUR</span>
                                </div>
                                <!-- Floating ring -->
                                <div class="absolute inset-0 rounded-3xl border-2 border-emerald-300/30 animate-ping"></div>
                            </div>

                            <div class="mt-4 space-y-2">
                                <h1 class="text-2xl lg:text-3xl font-bold text-gray-800 dark:text-white tracking-tight">
                                    <span class="bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                                        SIWUR
                                    </span>
                                </h1>
                                <p class="text-sm lg:text-base text-gray-600 dark:text-gray-300 font-semibold">
                                    Sistem Wirausaha Terpadu
                                </p>
                                <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                                    Kelola Usaha Multi-Toko dengan Mudah & Aman
                                </p>

                                <!-- Business features highlight -->
                                <div class="flex flex-wrap justify-center lg:justify-start gap-3 mt-3">
                                    <div class="flex items-center space-x-1 text-xs text-emerald-600 dark:text-emerald-400">
                                        <x-icon name="o-shopping-bag" class="w-3 h-3" />
                                        <span>Penjualan</span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-xs text-blue-600 dark:text-blue-400">
                                        <x-icon name="o-truck" class="w-3 h-3" />
                                        <span>Pembelian</span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-xs text-teal-600 dark:text-teal-400">
                                        <x-icon name="o-building-office" class="w-3 h-3" />
                                        <span>Multi-Toko</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Login Section -->
                        <div class="flex-1 space-y-6">
                            <!-- Welcome Message -->
                            <div class="text-center lg:text-left space-y-1">
                                <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
                                    Selamat Datang
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Masuk ke akun Anda untuk melanjutkan
                                </p>
                            </div>

                            <!-- Manual Login Form -->
                            <form wire:submit="login" class="space-y-4">
                                <x-input 
                                    wire:model="email" 
                                    label="Email" 
                                    placeholder="Masukkan email Anda"
                                    icon="o-envelope"
                                    type="email" />

                                <x-input 
                                    wire:model="password" 
                                    label="Password" 
                                    placeholder="Masukkan password"
                                    icon="o-lock-closed"
                                    type="password" />

                                <div class="flex items-center justify-between">
                                    <x-checkbox wire:model="remember" label="Ingat saya" />
                                </div>

                                <x-button 
                                    type="submit" 
                                    class="btn-primary w-full" 
                                    icon="o-arrow-right-on-rectangle"
                                    spinner="login">
                                    Masuk
                                </x-button>
                            </form>

                            <!-- Divider -->
                            <div class="relative">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-200 dark:border-gray-600"></div>
                                </div>
                                <div class="relative flex justify-center text-sm">
                                    <span class="px-4 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">atau</span>
                                </div>
                            </div>

                            <!-- Google Login Button -->
                            <a href="{{ route('login.google.redirect') }}"
                                class="w-full flex items-center justify-center px-6 py-3 bg-white dark:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-md hover:shadow-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-300 group">
                                <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                                    <path fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05"
                                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                                </svg>
                                <span class="font-medium">Masuk dengan Google</span>
                            </a>

                            <!-- Quick Features Info -->
                            <div class="grid grid-cols-2 gap-2 mt-4">
                                <div class="text-center p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                                    <x-icon name="o-chart-bar" class="w-5 h-5 text-emerald-600 mx-auto mb-1" />
                                    <p class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">Laporan Real-time</p>
                                </div>
                                <div class="text-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                                    <x-icon name="o-building-storefront" class="w-5 h-5 text-blue-600 mx-auto mb-1" />
                                    <p class="text-xs text-blue-700 dark:text-blue-300 font-medium">Multi Store</p>
                                </div>
                                <div class="text-center p-2 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                                    <x-icon name="o-cube" class="w-5 h-5 text-purple-600 mx-auto mb-1" />
                                    <p class="text-xs text-purple-700 dark:text-purple-300 font-medium">Stok Otomatis</p>
                                </div>
                                <div class="text-center p-2 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                                    <x-icon name="o-device-phone-mobile" class="w-5 h-5 text-orange-600 mx-auto mb-1" />
                                    <p class="text-xs text-orange-700 dark:text-orange-300 font-medium">Mobile Ready</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Simplified Footer -->
            <div class="mt-4 text-center space-y-4">
                <div class="flex justify-center">
                    <x-theme-toggle
                        class="btn btn-rounded bg-white/90 dark:bg-gray-700/90 shadow-lg hover:shadow-xl transition-all duration-500 backdrop-blur-md border border-gray-200/50 dark:border-gray-600/50 hover:scale-105"
                        label='Ganti Tema' />
                </div>

                <!-- System Status -->
                <div class="flex justify-center space-x-4 text-xs text-gray-400 dark:text-gray-500">
                    <div class="flex items-center space-x-1">
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        <span>Online</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <x-icon name="o-shield-check" class="w-3 h-3 text-emerald-500" />
                        <span>Secure</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <x-icon name="o-cloud" class="w-3 h-3 text-blue-500" />
                        <span>Cloud</span>
                    </div>
                </div>

                <!-- Copyright -->
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <p>© {{ date('Y') }} <span class="font-bold text-emerald-600 dark:text-emerald-400">SIWUR</span>
                        v3.0</p>
                </div>
            </div>
        </div>
    </div>
</div>
