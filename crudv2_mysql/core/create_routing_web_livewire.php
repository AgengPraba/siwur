<?php
if ($jenis_tabel == 'datatables') {
    $person = "
    Route::group(['middleware' => 'auth'], function () {
    //route $nama_class
        Route::get('/$nama_class', App\Livewire$backslash$nama_komponen\Index::class)->name('$nama_class.index');
        Route::get('/$nama_class/edit/{id}', App\Livewire$backslash$nama_komponen\Edit::class)->name('$nama_class.edit');
        Route::get('/$nama_class/show/{id}', App\Livewire$backslash$nama_komponen\Show::class)->name('$nama_class.show');
        Route::get('/$nama_class/create', App\Livewire$backslash$nama_komponen\Create::class)->name('$nama_class.create');
        //datatables $nama_class
        Route::get('/$nama_class/data', [App\Http\Controllers$backslash$c::class, 'data_json'])->name('$nama_class.data');
        });";
} else {
    $person = " //route $nama_class
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/$nama_class', App\Livewire$backslash$nama_komponen\Index::class)->name('$nama_class.index');
        Route::get('/$nama_class/edit/{id}', App\Livewire$backslash$nama_komponen\Edit::class)->name('$nama_class.edit');
        Route::get('/$nama_class/show/{id}', App\Livewire$backslash$nama_komponen\Show::class)->name('$nama_class.show');
        Route::get('/$nama_class/create', App\Livewire$backslash$nama_komponen\Create::class)->name('$nama_class.create');
        });";
}
