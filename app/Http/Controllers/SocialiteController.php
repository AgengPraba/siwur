<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Akses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class SocialiteController extends Controller
{
    public function redirectGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle()
    {
        $login = Socialite::driver('google')->user();

        $user = User::where(
            ['email' => $login->email],
        )->first();
        
        if ($user) {
            // Pastikan user sudah punya role Spatie
            if (!$user->hasAnyRole(['admin', 'kasir', 'staff_gudang', 'akuntan'])) {
                // Sync dari tabel akses jika ada
                if ($user->akses && $user->akses->role) {
                    $user->syncRoles([$user->akses->role]);
                }
            }
            
            Auth::login($user, true);
            Session::regenerate();
            return redirect(route('home'));
        } else {
            // Create new user (tanpa auto-create toko)
            // User akan diarahkan ke home dan melihat form pembuatan toko
            $newUser = User::create([
                'name' => $login->name,
                'email' => $login->email,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(16)),
            ]);

            Auth::login($newUser);
            Session::regenerate();
            
            // Redirect ke home - form pembuatan toko akan muncul
            // karena user belum punya akses toko
            return redirect(route('home'));
        }
    }
}
