<?php

use App\Http\Controllers\Bursa\WasteListingController;
use App\Http\Controllers\Bursa\WasteOrderController;
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

    Route::get('/listing', [WasteListingController::class, 'index'])->name('bursa.listing.index');

    Route::get('/listing/create', [WasteListingController::class, 'create'])
        ->middleware('permission:bursa.create')->name('bursa.listing.create');
    Route::post('/listing', [WasteListingController::class, 'store'])
        ->middleware('permission:bursa.create')->name('bursa.listing.store');
    Route::get('/listing/{listing}', [WasteListingController::class, 'show'])->name('bursa.listing.show');
    Route::get('/listing/{listing}/edit', [WasteListingController::class, 'edit'])
        ->middleware('permission:bursa.update')->name('bursa.listing.edit');
    Route::put('/listing/{listing}', [WasteListingController::class, 'update'])
        ->middleware('permission:bursa.update')->name('bursa.listing.update');
    Route::delete('/listing/{listing}', [WasteListingController::class, 'destroy'])
        ->middleware('permission:bursa.delete')->name('bursa.listing.destroy');

    Route::get('/order', [WasteOrderController::class, 'index'])->name('bursa.order.index');
    Route::get('/order/{order}', [WasteOrderController::class, 'show'])->whereNumber('order')->name('bursa.order.show');
    Route::get('/order/{order}/edit', [WasteOrderController::class, 'edit'])
        ->whereNumber('order')->middleware('permission:bursa.update')->name('bursa.order.edit');
    Route::put('/order/{order}', [WasteOrderController::class, 'update'])
        ->whereNumber('order')->middleware('permission:bursa.update')->name('bursa.order.update');
    Route::delete('/order/{order}', [WasteOrderController::class, 'destroy'])
        ->whereNumber('order')->middleware('permission:bursa.delete')->name('bursa.order.destroy');
    Route::post('/order/{order}/approve', [WasteOrderController::class, 'approve'])
        ->whereNumber('order')->middleware('permission:order-limbah.approve')->name('bursa.order.approve');
});
