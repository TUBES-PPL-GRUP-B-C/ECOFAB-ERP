<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request): View
    {
        $searchColumns = ['notes', 'reference_type'];

        $query = StockMovement::search($request->cari, $searchColumns)
            ->filter($request->only(['type', 'material_id']))
            ->with(['material', 'fromWarehouse', 'toWarehouse']);

        if ($warehouseId = $request->input('warehouse_id')) {
            $query->where(function ($q) use ($warehouseId) {
                $q->where('from_warehouse_id', $warehouseId)
                    ->orWhere('to_warehouse_id', $warehouseId);
            });
        }

        $movements = $query->latest('movement_date')->paginate(15)->withQueryString();
        $materials = Material::select('id', 'sku', 'name')->get();
        $warehouses = Warehouse::select('id', 'name')->get();

        return view('gudang.mutasi.index', compact('movements', 'materials', 'warehouses'));
    }

    public function create(): View
    {
        $materials = Material::all();
        $warehouses = Warehouse::where('status', 'active')->get();

        return view('gudang.mutasi.create', compact('materials', 'warehouses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $type = $request->input('type');

        $request->validate([
            'type'              => ['required', 'in:transfer,adjustment'],
            'material_id'       => ['required', 'exists:materials,id'],
            'notes'             => ['nullable', 'string', 'max:500'],
            'from_warehouse_id' => ['required_if:type,transfer', 'nullable', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id'   => ['required_if:type,transfer', 'nullable', 'exists:warehouses,id'],
            'qty'               => ['required_if:type,transfer', 'nullable', 'numeric', 'min:0.01'],
            'warehouse_id'      => ['required_if:type,adjustment', 'nullable', 'exists:warehouses,id'],
            'actual_qty'        => ['required_if:type,adjustment', 'nullable', 'numeric', 'min:0'],
        ]);

        try {
            if ($type === 'transfer') {
                $this->stockService->transferStock(
                    fromWarehouseId: (int) $request->input('from_warehouse_id'),
                    toWarehouseId: (int) $request->input('to_warehouse_id'),
                    materialId: (int) $request->input('material_id'),
                    productId: null,
                    quantity: (float) $request->input('qty'),
                    notes: $request->input('notes'),
                    userId: auth()->id()
                );
            } else {
                $this->stockService->adjustStock(
                    warehouseId: (int) $request->input('warehouse_id'),
                    materialId: (int) $request->input('material_id'),
                    actualQuantity: (float) $request->input('actual_qty'),
                    notes: $request->input('notes'),
                    userId: auth()->id()
                );
            }

            return redirect()->route('gudang.mutasi.index')
                ->with('success', "Mutasi stok ({$type}) berhasil diproses.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(int $id): View
    {
        $movement = StockMovement::findOrFail($id);

        return view('gudang.mutasi.edit', compact('movement'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $movement = StockMovement::findOrFail($id);
        $movement->update([
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('gudang.mutasi.index')
            ->with('success', 'Catatan mutasi stok berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $movement = StockMovement::findOrFail($id);
        $movement->update([
            'notes' => '[DIBATALKAN] ' . ($movement->notes ?? ''),
        ]);

        return redirect()->route('gudang.mutasi.index')
            ->with('success', 'Mutasi stok ditandai dibatalkan (riwayat audit tetap disimpan).');
    }
}
