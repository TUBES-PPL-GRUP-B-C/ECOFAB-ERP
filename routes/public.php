<?php

use App\Http\Controllers\Bursa\PublicWasteCatalogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Publik EcoFab ERP (Tanpa Middleware Auth)
|--------------------------------------------------------------------------
| Menampilkan Katalog Bursa Limbah Sirkular dan Hasil Pindai QR Eco-Passport.
| Tidak memuat data sensitif biaya atau harga internal perusahaan (NFR-11).
*/

Route::prefix('bursa/katalog')->name('public.bursa.')->group(function () {
    Route::get('/', [PublicWasteCatalogController::class, 'index'])->name('index');
    Route::get('/{id}', [PublicWasteCatalogController::class, 'show'])->whereNumber('id')->name('show');
});

Route::prefix('passport')->name('public.passport.')->group(function () {
    Route::get('/{kode}', function ($kode) {
        return view('public.passport_scan', compact('kode'));
    })->name('show');
});
