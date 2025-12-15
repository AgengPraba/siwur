<?php
require_once 'core/harviacode.php';
require_once 'core/helper.php';
require_once 'core/process.php';
$m = 'Auth';
$c = $m . 'Controller';
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
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\HomeController;
    //routing auth blade
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/', [AuthController::class, 'showLoginForm'])->name('awal');
    });
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');
        Route::post('/logout', function () {
        Auth::logout(); // Mengeluarkan pengguna
        return redirect('/login'); // Redirect ke halaman login
    })->name('logout');
    });";
    file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
};

if (!file_exists("../resources/views/" . $nama_class)) {
    mkdir("../resources/views/" . $nama_class, 0777, true);
}
if (!file_exists("../resources/views/layouts")) {
    mkdir("../resources/views/layouts", 0777, true);
}

include 'core/create_view_app_blade.php';
include 'core/create_view_app_auth_blade.php';
include 'core/create_controller_login.php';
include 'core/create_view_login.php';
include 'core/create_view_register.php';
include 'core/create_controller_home.php';
include 'core/create_view_home.php';

echo "<a href='index.php'>Klik Link ini untuk kembali</a>";
