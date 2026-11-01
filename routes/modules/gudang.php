<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Inventaris & Gudang (Prefix: /gudang)
|--------------------------------------------------------------------------
| CRUD Gudang, Material Sirkular, Mutasi Stok, dan GRN
*/

Route::middleware(['auth', 'permission:gudang.view'])->group(function () {
    Route::get('/', function () {
        return view('gudang.index');
    })->name('gudang.index');

    Route::get('/gudang', function () {
        return view('gudang.gudang.index');
    })->name('gudang.gudang.index');

    Route::get('/material', function () {
        return view('gudang.material.index');
    })->name('gudang.material.index');

    Route::get('/mutasi', function () {
        return view('gudang.mutasi.index');
    })->name('gudang.mutasi.index');

    Route::get('/grn', function () {
        return view('gudang.grn.index');
    })->name('gudang.grn.index');
});
