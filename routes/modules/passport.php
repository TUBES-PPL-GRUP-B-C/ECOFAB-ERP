<?php

use App\Http\Controllers\Passport\EcoPassportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Eco-Passport Internal (Prefix: /passport)
|--------------------------------------------------------------------------
| Manajemen paspor digital produk
*/

Route::middleware(['auth', 'permission:passport.view'])->group(function () {
    Route::get('/', [EcoPassportController::class, 'index'])->name('passport.index');
    Route::post('/', [EcoPassportController::class, 'store'])
        ->middleware('permission:passport.create')->name('passport.store');
});
