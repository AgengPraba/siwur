<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $m . ";

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
";

$string .= "\n\nclass Login extends Component
{ ";


    $string .= "\n\n    
    public \$email, \$password, \$isLoggedIn;

    protected \$rules = [
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ];

    public function mount()
    {
        \$this->isLoggedIn = Auth::check(); // Cek apakah pengguna sudah login
        if (\$this->isLoggedIn == true) {
            return redirect(route('home'));
        }
        \$this->email = session('email'); // Mengisi email dari session
    }


    public function login()
    {
        \$this->validate();
        if (Auth::attempt(['email' => \$this->email, 'password' => \$this->password])) {
            return redirect(route('home'));
        } else {
            Session::flash('email', \$this->email);
            Alert::toast('Email dan Password Salah', 'error');
            return \$this->redirectRoute('login', navigate: true);
        }
    }

    public function logout()
    {
        Auth::logout();
        \$this->isLoggedIn = false;
        return redirect(route('login'));
    }

    #[Layout('components.layouts.app_auth')]
    public function render()
    {
        return view('livewire.auth.login');
    }
";



$string .= "\n\n}\n\n/* End of file komponen login */
/* Location: ./app/Livewire/$m/Login.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $m."/Login.php");
