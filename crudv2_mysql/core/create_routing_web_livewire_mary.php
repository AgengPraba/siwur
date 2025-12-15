<?php
$person = " 
 //route $nama_class
 Route::group(['middleware' => 'auth'], function () {
     Route::get('/$nama_view_komponen', App\Livewire$backslash$nama_komponen\Index::class)->name('$nama_view_komponen.index');
     Route::get('/$nama_view_komponen/create', App\Livewire$backslash$nama_komponen\Form::class)->name('$nama_view_komponen.create');
     Route::get('/$nama_view_komponen/{id}/edit', App\Livewire$backslash$nama_komponen\Form::class)->name('$nama_view_komponen.edit');
     Route::get('/$nama_view_komponen/{id}', App\Livewire$backslash$nama_komponen\Show::class)->name('$nama_view_komponen.show');
     
     });";
