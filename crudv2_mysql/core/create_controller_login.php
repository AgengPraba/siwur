<?php
date_default_timezone_set('Asia/Jakarta');
$string = "<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
";

$string .= "\n\nclass " . $c . "  extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request \$request)
    {
        \$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => \$request->name,
            'email' => \$request->email,
            'password' => Hash::make(\$request->password),
        ]);

        return redirect()->route('login');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request \$request)
    {
        \$credentials = \$request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt(\$credentials)) {
            \$request->session()->regenerate();
            return redirect()->route('home');
        }

        return back()->withErrors([
            'email' => 'Email dan Password tidak sesuai',
        ]);
    }

    public function logout(Request \$request)
    {
        Auth::logout();
        \$request->session()->invalidate();
        \$request->session()->regenerateToken();
        return redirect()->route('awal');
    }
    
";





$string .= "\n\n}\n\n/* End of file $c */
/* Location: ./app/Http/Controllers/$c */
/* Created at " . date('Y-m-d H:i:s') . " */
/* Mohammad Irham Akbar CRUD Laravel 11 blade*/";

$hasil_controller = createFile($string, $target . "/Http/Controllers/" . $c.".php");
