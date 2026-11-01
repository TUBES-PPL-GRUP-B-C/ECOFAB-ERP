<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Pengadaan & Pembelian (Prefix: /pembelian)
|--------------------------------------------------------------------------
| CRUD Supplier dan Purchase Order (PO)
*/

Route::middleware(['auth', 'permission:pembelian.view'])->group(function () {
    Route::get('/', function () {
        return view('pembelian.index');
    })->name('pembelian.index');

    Route::get('/supplier', function () {
        return view('pembelian.supplier.index');
    })->name('pembelian.supplier.index');

    Route::get('/po', function () {
        return view('pembelian.po.index');
    })->name('pembelian.po.index');

    Route::get('/po/create', function () {
        return view('pembelian.po.create');
    })->name('pembelian.po.create');
});
