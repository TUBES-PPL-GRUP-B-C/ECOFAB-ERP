<?php

use App\Http\Controllers\Penjualan\CustomerController;
use App\Http\Controllers\Penjualan\SalesDashboardController;
use App\Http\Controllers\Penjualan\SalesOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Penjualan & Komersial (Prefix: /penjualan)
|--------------------------------------------------------------------------
| Pelanggan (FR-19), Sales Order (FR-20), Pengiriman dan Invoice (FR-21)
*/

Route::middleware(['auth', 'permission:penjualan.view'])->group(function () {
    Route::get('/', SalesDashboardController::class)->name('penjualan.index');

    // FR-19 Kelola Customer
    Route::get('/customer', [CustomerController::class, 'index'])->name('penjualan.customer.index');
    Route::get('/customer/create', [CustomerController::class, 'create'])
        ->middleware('permission:penjualan.create')->name('penjualan.customer.create');
    Route::post('/customer', [CustomerController::class, 'store'])
        ->middleware('permission:penjualan.create')->name('penjualan.customer.store');
    Route::get('/customer/{customer}', [CustomerController::class, 'show'])->name('penjualan.customer.show');
    Route::get('/customer/{customer}/edit', [CustomerController::class, 'edit'])
        ->middleware('permission:penjualan.update')->name('penjualan.customer.edit');
    Route::put('/customer/{customer}', [CustomerController::class, 'update'])
        ->middleware('permission:penjualan.update')->name('penjualan.customer.update');
    Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])
        ->middleware('permission:penjualan.delete')->name('penjualan.customer.destroy');

    // FR-20 Kelola Sales Order (master-detail)
    Route::get('/so', [SalesOrderController::class, 'index'])->name('penjualan.so.index');
    Route::get('/so/create', [SalesOrderController::class, 'create'])
        ->middleware('permission:penjualan.create')->name('penjualan.so.create');
    Route::post('/so', [SalesOrderController::class, 'store'])
        ->middleware('permission:penjualan.create')->name('penjualan.so.store');
    Route::get('/so/{so}', [SalesOrderController::class, 'show'])->name('penjualan.so.show');
    Route::get('/so/{so}/edit', [SalesOrderController::class, 'edit'])
        ->middleware('permission:penjualan.update')->name('penjualan.so.edit');
    Route::put('/so/{so}', [SalesOrderController::class, 'update'])
        ->middleware('permission:penjualan.update')->name('penjualan.so.update');
    Route::delete('/so/{so}', [SalesOrderController::class, 'destroy'])
        ->middleware('permission:penjualan.delete')->name('penjualan.so.destroy');

    // FR-21 (Sprint 2)
    Route::get('/pengiriman', function () {
        return view('penjualan.pengiriman.index');
    })->name('penjualan.pengiriman.index');
});
