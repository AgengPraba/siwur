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
if (!file_exists("../app/Livewire/" . $m)) {
    mkdir("../app/Livewire/" . $m, 0777, true);
    $file = '../routes/web.php';
    //buat routing
    $person = "
    use Illuminate\Support\Facades\Auth;
    //routing auth livewire
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', App\Livewire\Auth\Register::class)->name('register');
        Route::get('/login', App\Livewire\Auth\Login::class)->name('login');
        Route::get('/', App\Livewire\Auth\Login::class)->name('awal');
    });
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/home', App\Livewire\Home::class)->name('home');
        Route::post('/logout', function () {
        Auth::logout(); // Mengeluarkan pengguna
        return redirect('/login'); // Redirect ke halaman login
    })->name('logout');
    });";
    file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
};

if (!file_exists("../resources/views/livewire/" . $nama_class)) {
    mkdir("../resources/views/livewire/" . $nama_class, 0777, true);
}
if (!file_exists("../resources/views/components/layouts")) {
    mkdir("../resources/views/components/layouts", 0777, true);
}

include 'core/create_view_komponen_app.php';
include 'core/create_view_komponen_app_auth.php';
include 'core/create_komponen_login.php';
include 'core/create_view_komponen_login.php';
include 'core/create_komponen_register.php';
include 'core/create_view_komponen_register.php';
include 'core/create_komponen_home.php';
include 'core/create_view_komponen_home.php';

echo "<a href='index.php'>Klik Link ini untuk kembali</a>";
