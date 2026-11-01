<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Penjualan & Komersial (Prefix: /penjualan)
|--------------------------------------------------------------------------
| Pelanggan, Sales Order (SO), Pengiriman, dan Invoice
*/

Route::middleware(['auth', 'permission:penjualan.view'])->group(function () {
    Route::get('/', function () {
        return view('penjualan.index');
    })->name('penjualan.index');

    Route::get('/customer', function () {
        return view('penjualan.customer.index');
    })->name('penjualan.customer.index');

    Route::get('/so', function () {
        return view('penjualan.so.index');
    })->name('penjualan.so.index');

    Route::get('/pengiriman', function () {
        return view('penjualan.pengiriman.index');
    })->name('penjualan.pengiriman.index');
});
