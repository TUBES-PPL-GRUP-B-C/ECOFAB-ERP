<?php

use App\Http\Controllers\Gudang\GoodsReceiptController;
use App\Http\Controllers\Gudang\MaterialController;
use App\Http\Controllers\Gudang\StockMovementController;
use App\Http\Controllers\Gudang\WarehouseController;
use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Modul Inventaris & Gudang (Prefix: /gudang)
|--------------------------------------------------------------------------
| Menangani FR-08 (GRN), FR-09 (Gudang), FR-10 (Material), FR-11 (Mutasi Stok).
*/

Route::middleware(['auth', 'permission:gudang.view'])->group(function () {
    // Ringkasan Modul Gudang
    Route::get('/', function () {
        $totalWarehouses = Warehouse::count();
        $totalMaterials = Material::count();
        $lowStockCount = Material::all()->filter(fn ($m) => $m->is_low_stock)->count();
        $recentMovements = StockMovement::with(['material', 'fromWarehouse', 'toWarehouse'])->latest()->limit(5)->get();
        $recentGrn = GoodsReceipt::with('warehouse')->latest()->limit(5)->get();

        return view('gudang.index', compact('totalWarehouses', 'totalMaterials', 'lowStockCount', 'recentMovements', 'recentGrn'));
    })->name('gudang.index');

    // FR-09: Kelola Gudang
    Route::resource('gudang', WarehouseController::class)->names('gudang.gudang');

    // FR-10: Kelola Material
    Route::resource('material', MaterialController::class)->names('gudang.material');

    // FR-11: Kelola Mutasi Stok
    Route::resource('mutasi', StockMovementController::class)->names('gudang.mutasi');

    // FR-08: Kelola Penerimaan Barang (GRN)
    Route::resource('grn', GoodsReceiptController::class)->names('gudang.grn');
});
