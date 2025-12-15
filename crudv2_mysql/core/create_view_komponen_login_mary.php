<?php
$string = "
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

new #[Layout('components.layouts.guest')] #[Title('Login')] class extends Component {
    use Toast;

    #[Rule('required|email')]
    public string \$email = '';

    #[Rule('required|min:6')]
    public string \$password = '';

    public bool \$remember = false;

    public function login()
    {
        \$this->validate();

        if (Auth::attempt(['email' => \$this->email, 'password' => \$this->password], \$this->remember)) {
            request()->session()->regenerate();

            \$this->success('Login berhasil!', 'Selamat datang kembali!', redirectTo: '/home');

            return;
        }

        \$this->error('Login gagal!', 'Email atau password salah.',css: 'bg-red-500 text-base-100');
    }
};
?>

<div class=\"flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8\">
    <div class=\"max-w-md w-full\">
        <div class=\"text-center\">
            <div class=\"mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100\">
                <x-icon name=\"o-lock-closed\" class=\"h-6 w-6 text-indigo-600\" />
            </div>
            <h2 class=\"mt-6 text-3xl font-extrabold\">
                Masuk ke Akun Anda
            </h2>
              
            <p class=\"mt-2 text-sm text-gray-600 dark:text-gray-300 pb-4\">
                Atau
                <a href=\"{{ route('register') }}\" wire:navigate
                    class=\"font-medium text-indigo-600 hover:text-indigo-500 transition-colors\">
                    daftar akun baru
                </a>
            </p>
        </div>
       
        <x-card>
            
            <x-form wire:submit=\"login\" class=\"space-y-2\">
                <x-input label=\"Email\" wire:model=\"email\" type=\"email\" icon=\"o-envelope\"
                    placeholder=\"masukkan@email.com\" class=\"w-full\" />

                <x-input label=\"Password\" wire:model=\"password\" type=\"password\" icon=\"o-lock-closed\"
                    placeholder=\"••••••••\" class=\"w-full\" />

                <div class=\"flex items-center justify-between\">
                    <x-checkbox label=\"Ingat saya\" wire:model=\"remember\" class=\"text-sm dark:text-gray-300\" />

                    <a href=\"/forgot-password\" class=\"text-sm text-indigo-600 hover:text-indigo-500 transition-colors\">
                        Lupa password?
                    </a>
                </div>

                <x-button type=\"submit\"
                    class=\"w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-lg transition-all duration-200 transform hover:scale-[1.02]\"
                    spinner=\"login\">
                    <x-icon name=\"o-arrow-right-on-rectangle\" class=\"w-5 h-5 mr-2\" />
                    Masuk
                </x-button>
            </x-form>
             
        </x-card>

        <div class=\"mt-6\">
            <div class=\"relative pb-4\">
                <div class=\"relative flex justify-center text-sm\">
                   <x-theme-toggle class=\"btn btn-rounded\" label='Ganti Tema' />
                 
                </div>
            </div>
             <div class=\"relative\">
                <div class=\"relative flex justify-center text-sm\">
                    <span class=\"px-2 text-gray-500 dark:text-gray-300\">Atau masuk dengan</span>
                </div>
            </div>
            <div class=\"mt-6 grid grid-cols-2 gap-3\">
                <x-button
                    class=\"w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 rounded-lg transition-colors
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600\">
                    <x-icon name=\"o-globe-alt\" class=\"w-5 h-5 mr-2\" />
                    Google
                </x-button>

                <x-button
                    class=\"w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 rounded-lg transition-colors
                           dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:bg-gray-600\">
                    <x-icon name=\"o-device-phone-mobile\" class=\"w-5 h-5 mr-2\" />
                    Facebook
                </x-button>
            </div>
        </div>
    </div>
</div>
";

// Ensure the target directory exists and has correct permissions
$targetDir = dirname("../resources/views/livewire/auth/login.blade.php");
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
    chmod($targetDir, 0777);
}

$hasil_view_form = createFile($string, "../resources/views/livewire/auth/login.blade.php");
