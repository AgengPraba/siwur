<?php

use App\Http\Controllers\SocialiteController;
use Livewire\Volt\Volt;

Volt::route('/', 'users.index');

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('login'));
})->name('main');

Route::group(['middleware' => 'guest'], function () {
    Route::get('/auth/google', [SocialiteController::class, 'redirectGoogle'])->name('login.google.redirect');
    Route::get('/auth/google/callback', [SocialiteController::class, 'callbackGoogle'])->name('login.google.callback');
});
// Guest routes (not authenticated)
Route::middleware('guest')->group(function () {
    Volt::route('/login', 'auth.login')
        ->name('login');

    // Volt::route('/register', 'auth.register')
    //     ->name('register');
});
// Authenticated routes
Route::middleware('auth')->group(function () {
    Volt::route('/home', 'home')
        ->name('home');
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect(route('login'));
    })->name('logout');
});

Route::group(['middleware' => 'auth'], function () {
    Volt::route('laporan-pembayaran', 'laporan-pembayaran')
        ->name('laporan.pembayaran');
    Volt::route('laporan-profit', 'laporan-profit')
        ->name('laporan.profit');
    Route::get('/laporan-profit/print', [\App\Http\Controllers\PrintController::class, 'printLaporanProfit'])
        ->name('laporan-profit.print');
    Route::get('/laporan-profit/print', [\App\Http\Controllers\PrintController::class, 'printLaporanProfit'])
        ->name('laporan-profit.print');
});


//route satuan
Route::group(['middleware' => 'auth'], function () {
    Route::get('/satuan', App\Livewire\Satuan\Index::class)->name('satuan.index');
    Route::get('/satuan/create', App\Livewire\Satuan\Form::class)->name('satuan.create');
    Route::get('/satuan/{id}/edit', App\Livewire\Satuan\Form::class)->name('satuan.edit');
    Route::get('/satuan/{id}', App\Livewire\Satuan\Show::class)->name('satuan.show');
});

//route supplier
Route::group(['middleware' => 'auth'], function () {
    Route::get('/supplier', App\Livewire\Supplier\Index::class)->name('supplier.index');
    Route::get('/supplier/create', App\Livewire\Supplier\Form::class)->name('supplier.create');
    Route::get('/supplier/{id}/edit', App\Livewire\Supplier\Form::class)->name('supplier.edit');
    Route::get('/supplier/{id}', App\Livewire\Supplier\Show::class)->name('supplier.show');
});
//route penjualan
Route::group(['middleware' => 'auth'], function () {
    // Print routes
    Route::get('/penjualan/print/invoice/{id}', [\App\Http\Controllers\PrintController::class, 'printInvoice'])->name('penjualan.print.invoice');
    Route::get('/penjualan/print/pembayaran/{id}', [\App\Http\Controllers\PrintController::class, 'printPembayaran'])->name('penjualan.print.pembayaran');

    // Standard routes
    Route::get('/penjualan', App\Livewire\Penjualan\Index::class)->name('penjualan.index');
    Route::get('/penjualan/create', App\Livewire\Penjualan\Form::class)->name('penjualan.create');
    Route::get('/penjualan/{id}/edit', App\Livewire\Penjualan\Form::class)->name('penjualan.edit');
    Route::get('/penjualan/{id}', App\Livewire\Penjualan\Show::class)->name('penjualan.show');
});
//route penjualan_detail
Route::group(['middleware' => 'auth'], function () {
    Route::get('/penjualan-detail', App\Livewire\PenjualanDetail\Index::class)->name('penjualan-detail.index');
    Route::get('/penjualan-detail/create', App\Livewire\PenjualanDetail\Form::class)->name('penjualan-detail.create');
    Route::get('/penjualan-detail/{id}/edit', App\Livewire\PenjualanDetail\Form::class)->name('penjualan-detail.edit');
    Route::get('/penjualan-detail/{id}', App\Livewire\PenjualanDetail\Show::class)->name('penjualan-detail.show');
});
//route pembelian
Route::group(['middleware' => 'auth'], function () {
    Route::get('/pembelian', App\Livewire\Pembelian\Index::class)->name('pembelian.index');
    Route::get('/pembelian/create', App\Livewire\Pembelian\Form::class)->name('pembelian.create');
    Route::get('/pembelian/{id}/edit', App\Livewire\Pembelian\Form::class)->name('pembelian.edit');
    Route::get('/pembelian/{id}', App\Livewire\Pembelian\Show::class)->name('pembelian.show');
    Route::get('/pembelian/{id}/print', [\App\Http\Controllers\PrintController::class, 'printPurchase'])->name('pembelian.print');
    Route::get('/pembelian/{id}/print-payment', [\App\Http\Controllers\PrintController::class, 'printPayment'])->name('pembelian.print.payment');
});
//route pembelian_detail
Route::group(['middleware' => 'auth'], function () {
    Route::get('/pembelian-detail', App\Livewire\PembelianDetail\Index::class)->name('pembelian-detail.index');
    Route::get('/pembelian-detail/create', App\Livewire\PembelianDetail\Form::class)->name('pembelian-detail.create');
    Route::get('/pembelian-detail/{id}/edit', App\Livewire\PembelianDetail\Form::class)->name('pembelian-detail.edit');
    Route::get('/pembelian-detail/{id}', App\Livewire\PembelianDetail\Show::class)->name('pembelian-detail.show');
});
//route jenis_barang
Route::group(['middleware' => 'auth'], function () {
    Route::get('/jenis-barang', App\Livewire\JenisBarang\Index::class)->name('jenis-barang.index');
    Route::get('/jenis-barang/create', App\Livewire\JenisBarang\Form::class)->name('jenis-barang.create');
    Route::get('/jenis-barang/{id}/edit', App\Livewire\JenisBarang\Form::class)->name('jenis-barang.edit');
    Route::get('/jenis-barang/{id}', App\Livewire\JenisBarang\Show::class)->name('jenis-barang.show');
});
//route gudang
Route::group(['middleware' => 'auth'], function () {
    Route::get('/gudang', App\Livewire\Gudang\Index::class)->name('gudang.index');
    Route::get('/gudang/create', App\Livewire\Gudang\Form::class)->name('gudang.create');
    Route::get('/gudang/{id}/edit', App\Livewire\Gudang\Form::class)->name('gudang.edit');
    Route::get('/gudang/{id}', App\Livewire\Gudang\Show::class)->name('gudang.show');
});
//route gudang_stock
Route::group(['middleware' => 'auth'], function () {
    Route::get('/gudang-stock', App\Livewire\GudangStock\Index::class)->name('gudang-stock.index');
    Route::get('/gudang-stock/create', App\Livewire\GudangStock\Form::class)->name('gudang-stock.create');
    Route::get('/gudang-stock/{id}/edit', App\Livewire\GudangStock\Form::class)->name('gudang-stock.edit');
    Route::get('/gudang-stock/{id}', App\Livewire\GudangStock\Show::class)->name('gudang-stock.show');
});
//route customer
Route::group(['middleware' => 'auth'], function () {
    Route::get('/customer', App\Livewire\Customer\Index::class)->name('customer.index');
    Route::get('/customer/create', App\Livewire\Customer\Form::class)->name('customer.create');
    Route::get('/customer/{id}/edit', App\Livewire\Customer\Form::class)->name('customer.edit');
    Route::get('/customer/{id}', App\Livewire\Customer\Show::class)->name('customer.show');
});
//route barang
Route::group(['middleware' => 'auth'], function () {
    Route::get('/barang', App\Livewire\Barang\Index::class)->name('barang.index');
    Route::get('/barang/create', App\Livewire\Barang\Form::class)->name('barang.create');
    Route::get('/barang/{id}/edit', App\Livewire\Barang\Form::class)->name('barang.edit');
    Route::get('/barang/{id}', App\Livewire\Barang\Show::class)->name('barang.show');
});
//route barang_satuan
Route::group(['middleware' => 'auth'], function () {
    Route::get('/barang-satuan', App\Livewire\BarangSatuan\Index::class)->name('barang-satuan.index');
    Route::get('/barang-satuan/create', App\Livewire\BarangSatuan\Form::class)->name('barang-satuan.create');
    Route::get('/barang-satuan/{id}/edit', App\Livewire\BarangSatuan\Form::class)->name('barang-satuan.edit');
    Route::get('/barang-satuan/{id}', App\Livewire\BarangSatuan\Show::class)->name('barang-satuan.show');
});
//route transaksi_gudang_stock
Route::group(['middleware' => 'auth'], function () {
    Route::get('/transaksi-gudang-stock', App\Livewire\TransaksiGudangStock\Index::class)->name('transaksi-gudang-stock.index');
    Route::get('/transaksi-gudang-stock/create', App\Livewire\TransaksiGudangStock\Form::class)->name('transaksi-gudang-stock.create');
    Route::get('/transaksi-gudang-stock/{id}/edit', App\Livewire\TransaksiGudangStock\Form::class)->name('transaksi-gudang-stock.edit');
    Route::get('/transaksi-gudang-stock/{id}', App\Livewire\TransaksiGudangStock\Show::class)->name('transaksi-gudang-stock.show');
});

// route stock opname
Route::group(['middleware' => 'auth'], function () {
    Route::get('/stock-opname', App\Livewire\StockOpname\Index::class)->name('stock-opname.index');
    Route::get('/stock-opname/create', App\Livewire\StockOpname\Form::class)->name('stock-opname.create');
    Route::get('/stock-opname/{id}', App\Livewire\StockOpname\Show::class)->name('stock-opname.show');
    Route::get('/stock-opname/{id}/print', [\App\Http\Controllers\PrintController::class, 'printOpname'])
        ->name('stock-opname.print');
});

// route retur pembelian
Route::group(['middleware' => 'auth'], function () {
    Route::get('/retur-pembelian', App\Livewire\ReturPembelian\Index::class)->name('retur-pembelian.index');
    Route::get('/retur-pembelian/create', App\Livewire\ReturPembelian\Form::class)->name('retur-pembelian.create');
    Route::get('/retur-pembelian/form', App\Livewire\ReturPembelian\Form::class)->name('retur-pembelian.form');
    Route::get('/retur-pembelian/{id}', App\Livewire\ReturPembelian\Show::class)->name('retur-pembelian.show');
    Route::get('/retur-pembelian/{id}/print', [\App\Http\Controllers\PrintController::class, 'printReturPembelian'])
        ->name('retur-pembelian.print');
});

// route retur penjualan
Route::group(['middleware' => 'auth'], function () {
    Route::get('/retur-penjualan', App\Livewire\ReturPenjualan\Index::class)->name('retur-penjualan.index');
    Route::get('/retur-penjualan/create', App\Livewire\ReturPenjualan\Form::class)->name('retur-penjualan.create');
    Route::get('/retur-penjualan/form', App\Livewire\ReturPenjualan\Form::class)->name('retur-penjualan.form');
    Route::get('/retur-penjualan/{id}', App\Livewire\ReturPenjualan\Show::class)->name('retur-penjualan.show');
    Route::get('/retur-penjualan/{id}/print', [\App\Http\Controllers\PrintController::class, 'printReturPenjualan'])
        ->name('retur-penjualan.print');
    
    // Chatbot route - requires authentication
    Route::post('/chatbot/ask', [\App\Http\Controllers\ChatbotController::class, 'ask'])->name('chatbot.ask');
    
    // Debug route to test user akses
    Route::get('/debug/user-akses', function() {
        $user = Auth::user();
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'has_akses' => $user->akses ? true : false,
            'akses' => $user->akses,
            'toko_id' => $user->akses ? $user->akses->toko_id : null
        ]);
    })->name('debug.user-akses');
});

// route user management (admin only)
Route::group(['middleware' => ['auth', 'role:admin']], function () {
    Route::get('/user', App\Livewire\User\Index::class)->name('user.index');
    Route::get('/user/create', App\Livewire\User\Form::class)->name('user.create');
    Route::get('/user/{id}/edit', App\Livewire\User\Form::class)->name('user.edit');
    Route::get('/user/{id}', App\Livewire\User\Show::class)->name('user.show');
});