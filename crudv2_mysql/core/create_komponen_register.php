<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Livewire" . $backslash . "" . $m . ";

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
";

$string .= "\n\nclass Register extends Component
{ ";


    $string .= "\n\n    
    public \$name, \$email, \$password, \$password_confirmation;


    protected \$rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ];

     public function register()
    {
        \$this->validate();

        User::create([
            'name' => \$this->name,
            'email' => \$this->email,
            'password' => Hash::make(\$this->password), // Menggunakan bcrypt untuk hashing
        ]);
        Alert::toast('Berhasil Register, Silahkan Login dengan akun anda', 'success');

        return redirect(route('login'));
    }

    #[Layout('components.layouts.app_auth')]
    public function render()
    {
        return view('livewire.auth.register');
    }
";



$string .= "\n\n}\n\n/* End of file komponen register */
/* Location: ./app/Livewire/$m/Register.php */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 Livewire */";

$hasil_komponen = createFile($string, $target . "/Livewire/" . $m."/Register.php");
