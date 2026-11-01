<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Bursa Limbah Internal (Prefix: /bursa)
|--------------------------------------------------------------------------
| Listing limbah & manajemen order pesanan limbah
*/

Route::middleware(['auth', 'permission:bursa.view'])->group(function () {
    Route::get('/', function () {
        return view('bursa.index');
    })->name('bursa.index');

    Route::get('/listing', function () {
        return view('bursa.listing.index');
    })->name('bursa.listing.index');

    Route::get('/order', function () {
        return view('bursa.order.index');
    })->name('bursa.order.index');
});
