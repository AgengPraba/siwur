<?php
$person = "
                    //route untuk tabel $nama_class
                    // uncoment jika belum ada Route
                    // use Illuminate\Support\Facades\Route;
                    // use Modules". $backslash .$modul_name."\Http\Controllers$backslash$c;
                    // Route::prefix('set_sendiri')->group(function () {
                    Route::controller($c::class)->group(function () {
                        Route::get('$nama_class', 'index')->name('$nama_class.index');
                        Route::get('$nama_class/data', 'data_json')->name('$nama_class.data');
                        Route::get('$nama_class/create', 'create')->name('$nama_class.create');
                        Route::post('$nama_class/store', 'store')->name('$nama_class.store');
                        Route::get('$nama_class/edit/{id}', 'edit')->name('$nama_class.edit');
                        Route::put('$nama_class/update/{id}', 'update')->name('$nama_class.update');
                        Route::delete('$nama_class/delete', 'delete')->name('$nama_class.delete');
                        Route::get('$nama_class/cari', 'cari')->name('$nama_class.cari');
                        Route::get('$nama_class/show/{id}', 'show')->name('$nama_class.show');
                    });
                    // });
                    ";