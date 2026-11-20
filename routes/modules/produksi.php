<?php

use App\Http\Controllers\Produksi\WorkOrderController;
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

    Route::get('/wo', [WorkOrderController::class, 'index'])->name('produksi.wo.index');
    Route::get('/wo/create', [WorkOrderController::class, 'create'])
        ->middleware('permission:produksi.create')->name('produksi.wo.create');
    Route::post('/wo', [WorkOrderController::class, 'store'])
        ->middleware('permission:produksi.create')->name('produksi.wo.store');
    Route::get('/wo/{workOrder}', [WorkOrderController::class, 'show'])->name('produksi.wo.show');
    Route::get('/wo/{workOrder}/edit', [WorkOrderController::class, 'edit'])
        ->middleware('permission:produksi.update')->name('produksi.wo.edit');
    Route::put('/wo/{workOrder}', [WorkOrderController::class, 'update'])
        ->middleware('permission:produksi.update')->name('produksi.wo.update');
    Route::delete('/wo/{workOrder}', [WorkOrderController::class, 'destroy'])
        ->middleware('permission:produksi.delete')->name('produksi.wo.destroy');
    Route::post('/wo/{workOrder}/mulai', [WorkOrderController::class, 'start'])
        ->middleware('permission:wo.start')->name('produksi.wo.start');

    Route::get('/limbah', function () {
        return view('produksi.limbah.index');
    })->name('produksi.limbah.index');
});
