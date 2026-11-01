<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Eco-Passport Internal (Prefix: /passport)
|--------------------------------------------------------------------------
| Manajemen penerbitan dan penarikan paspor digital produk
*/

Route::middleware(['auth', 'permission:passport.view'])->group(function () {
    Route::get('/', function () {
        return view('passport.index');
    })->name('passport.index');
});
