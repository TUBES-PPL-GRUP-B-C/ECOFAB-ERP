<?php

use App\Http\Controllers\Pembelian\SupplierController;
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

    Route::get('/supplier', [SupplierController::class, 'index'])->name('pembelian.supplier.index');
    Route::get('/supplier/create', [SupplierController::class, 'create'])
        ->middleware('permission:pembelian.create')->name('pembelian.supplier.create');
    Route::post('/supplier', [SupplierController::class, 'store'])
        ->middleware('permission:pembelian.create')->name('pembelian.supplier.store');
    Route::get('/supplier/{supplier}', [SupplierController::class, 'show'])->name('pembelian.supplier.show');
    Route::get('/supplier/{supplier}/edit', [SupplierController::class, 'edit'])
        ->middleware('permission:pembelian.update')->name('pembelian.supplier.edit');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])
        ->middleware('permission:pembelian.update')->name('pembelian.supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])
        ->middleware('permission:pembelian.delete')->name('pembelian.supplier.destroy');

    Route::get('/po', function () {
        return view('pembelian.po.index');
    })->name('pembelian.po.index');

    Route::get('/po/create', function () {
        return view('pembelian.po.create');
    })->name('pembelian.po.create');
});
