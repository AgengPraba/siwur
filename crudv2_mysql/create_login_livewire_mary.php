<?php
require_once 'core/harviacode.php';
require_once 'core/helper.php';
require_once 'core/process.php';
$m = 'Auth';
$nama_class = strtolower($m);
// show setting
$get_setting = readJSON('core/settingjson_livewire.cfg');
$target = $get_setting->target;
//  $targetViews = $get_setting->$targetViews;
$backslash = str_replace("'", "", "'\'");
//buat folder pada App Livewire
if (!file_exists("../app/Livewire/")) {
    mkdir("../app/Livewire/", 0777, true);
    $file = '../routes/web.php';
    //buat routing
    $person = "
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    Route::get('/', function () {
        return redirect(route('login'));
    })->name('main');
    // Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    Volt::route('/login', 'auth.login')
        ->name('login');

    Volt::route('/register', 'auth.register')
        ->name('register');
});
// Authenticated routes
Route::middleware('auth')->group(function () {
    Volt::route('/home', 'home')
        ->name('home');
    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect(route('login'));
    })->name('logout');
});
";
    file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
};

if (!file_exists("../resources/views/components/layouts")) {
    mkdir("../resources/views/components/layouts", 0777, true);
}

include 'core/create_view_komponen_guest_mary.php';
include 'core/create_view_komponen_navbar_menu_mary.php';
include 'core/create_view_komponen_navbar_tanpa_menu_mary.php';
include 'core/create_view_komponen_sidebar_mary.php';
include 'core/create_view_komponen_app_mary.php';
include 'core/create_view_komponen_login_mary.php';
include 'core/create_view_komponen_register_mary.php';
include 'core/create_view_komponen_home_mary.php';

echo "<a href='index.php'>Klik Link ini untuk kembali</a>";
