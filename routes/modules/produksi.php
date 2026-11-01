<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Manufaktur Sirkular / Produksi (Prefix: /produksi)
|--------------------------------------------------------------------------
| Mesin, Produk Jadi, Green BoM, Work Order, dan Limbah Produksi
*/

Route::middleware(['auth', 'permission:produksi.view'])->group(function () {
    Route::get('/', function () {
        return view('produksi.index');
    })->name('produksi.index');

    Route::get('/mesin', function () {
        return view('produksi.mesin.index');
    })->name('produksi.mesin.index');

    Route::get('/produk', function () {
        return view('produksi.produk.index');
    })->name('produksi.produk.index');

    Route::get('/bom', function () {
        return view('produksi.bom.index');
    })->name('produksi.bom.index');

    Route::get('/wo', function () {
        return view('produksi.wo.index');
    })->name('produksi.wo.index');

    Route::get('/limbah', function () {
        return view('produksi.limbah.index');
    })->name('produksi.limbah.index');
});
