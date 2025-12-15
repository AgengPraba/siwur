
<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Mary\Traits\Toast;

new #[Layout('components.layouts.guest')] #[Title('Register')] class extends Component {
    use Toast;

    #[Rule('required|string|max:255|min:2')]
    public string $name = '';

    #[Rule('required|email:rfc,dns|unique:users,email|max:255')]
    public string $email = '';

    #[Rule('required|confirmed')]
    public string $password = '';

    #[Rule('required|same:password')]
    public string $password_confirmation = '';

    // Loading state
    public bool $loading = false;

    protected array $rules = [
        'name' => 'required|min:2|max:255',
        'email' => 'required|email|unique:users,email', // Pastikan tabel 'users' sesuai
        'password' => 'required|min:8', // Hapus 'confirmed', gunakan 'same' di bawah
        'password_confirmation' => 'required|same:password|min:8', // Pastikan sama dengan password
    ];

    // Mendefinisikan pesan kustom untuk validasi
    protected array $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'name.min' => 'Nama minimal 2 karakter.',
        'name.max' => 'Nama maksimal 255 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
    ];

    public function register()
    {
        // Validasi data
        $validated = $this->validate();
        try {
            // Buat user baru
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(), // Langsung verify email (opsional)
            ]);

            // Login user setelah registrasi
            Auth::login($user);

            // Regenerate session untuk keamanan
            request()->session()->regenerate();
            // Reset form
            $this->reset(['name', 'email', 'password', 'password_confirmation']);
            // Toast success dan redirect
            $this->success('Registrasi berhasil!', 'Selamat datang di aplikasi kami, ' . $user->name . '!', redirectTo: '/home');
        } catch (\Exception $e) {
            // Log error untuk debugging
            \Log::error('Registration error: ' . $e->getMessage());
            // Tampilkan error ke user
            $this->error('Registrasi gagal!', $e->getMessage() . 'Terjadi kesalahan saat membuat akun. Silakan coba lagi.');
        }
    }

    public function mount()
    {
        // Redirect jika user sudah login
        if (Auth::check()) {
            return redirect(route('home'));
        }
    }
}; ?>

<div class="flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-emerald-100">
                <x-icon name="o-user-plus" class="h-6 w-6 text-emerald-600" />
            </div>
            <h2 class="mt-6 text-3xl font-extrabold">
                Buat Akun Baru
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Sudah punya akun?
                <a href="/login" wire:navigate
                    class="font-medium text-emerald-600 hover:text-emerald-500 transition-colors">
                    masuk di sini
                </a>
            </p>
        </div>

        <!-- Form -->
        <x-card>
            <x-form wire:submit="register">
                <!-- Name -->
                <x-input label="Nama Lengkap" wire:model="name" icon="o-user" placeholder="Masukkan nama lengkap"
                    class="w-full" />

                <!-- Email -->
                <x-input label="Email" wire:model="email" type="email" icon="o-envelope"
                    placeholder="masukkan@email.com" class="w-full" />

                <!-- Password -->
                <div class="space-y-2">
                    <x-input label="Password" wire:model.live="password" type="password" icon="o-lock-closed"
                        placeholder="••••••••" class="w-full" />

                    <!-- Password Strength Indicator -->
                    @if (strlen($password) > 0)
                        {{-- <div class="text-xs space-y-1">
                            <div class="flex items-center space-x-1">
                                <div class="flex space-x-1">
                                    <div
                                        class="h-1 w-6 rounded {{ strlen($password) >= 8 ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    </div>
                                    <div
                                        class="h-1 w-6 rounded {{ preg_match('/[A-Z]/', $password) ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    </div>
                                    <div
                                        class="h-1 w-6 rounded {{ preg_match('/[a-z]/', $password) ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    </div>
                                    <div
                                        class="h-1 w-6 rounded {{ preg_match('/[0-9]/', $password) ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    </div>
                                    <div
                                        class="h-1 w-6 rounded {{ preg_match('/[^A-Za-z0-9]/', $password) ? 'bg-emerald-500' : 'bg-gray-300' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="text-gray-500">
                                Password harus mengandung: 8+ karakter, huruf besar, huruf kecil, angka, dan simbol
                            </div>
                        </div> --}}
                    @endif
                </div>

                <!-- Confirm Password -->
                <x-input label="Konfirmasi Password" wire:model="password_confirmation" type="password"
                    icon="o-lock-closed" placeholder="••••••••" class="w-full" />



                <!-- Submit Button -->
                <x-button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 text-white font-semibold py-3 rounded-lg transition-all duration-200 transform hover:scale-[1.02] disabled:transform-none"
                    :disabled="$loading" spinner="register">
                    @if ($loading)
                        <x-icon name="o-arrow-path" class="w-5 h-5 mr-2 animate-spin" />
                        Mendaftar...
                    @else
                        <x-icon name="o-user-plus" class="w-5 h-5 mr-2" />
                        Daftar Sekarang
                    @endif
                </x-button>
            </x-form>
        </x-card>
        <div class="relative pb-4">
            <div class="relative flex justify-center text-sm">
                <x-theme-toggle class="btn btn-rounded" label='Ganti Tema' />

            </div>
        </div>


    </div>
</div>

