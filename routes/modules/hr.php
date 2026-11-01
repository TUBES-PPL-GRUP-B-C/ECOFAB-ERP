<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul HR & Organisasi (Prefix: /hr)
|--------------------------------------------------------------------------
| CRUD Departemen, Karyawan, dan Absensi
*/

Route::middleware(['auth', 'permission:hr.view'])->group(function () {
    Route::get('/', function () {
        return view('hr.index');
    })->name('hr.index');

    Route::get('/departemen', function () {
        return view('hr.departemen.index');
    })->name('hr.departemen.index');

    Route::get('/karyawan', function () {
        return view('hr.karyawan.index');
    })->name('hr.karyawan.index');

    Route::get('/absensi', function () {
        return view('hr.absensi.index');
    })->name('hr.absensi.index');
});
