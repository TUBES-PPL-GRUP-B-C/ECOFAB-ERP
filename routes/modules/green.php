<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Faktor Emisi & Keberlanjutan (Prefix: /green)
|--------------------------------------------------------------------------
| Data referensi faktor emisi (kg CO2e) - Hak akses: Admin / emisi.manage
*/

Route::middleware(['auth', 'permission:emisi.manage'])->group(function () {
    Route::get('/faktor-emisi', function () {
        return view('green.faktor_emisi.index');
    })->name('green.faktor-emisi.index');
});
