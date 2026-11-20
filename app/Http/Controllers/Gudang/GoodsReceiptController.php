<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Material;
use App\Models\Warehouse;
use App\Services\StockService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request): View
    {
        $searchColumns = ['grn_number', 'notes'];

        $receipts = GoodsReceipt::search($request->cari, $searchColumns)
            ->filter($request->only(['status', 'warehouse_id']))
            ->with(['warehouse', 'items.material'])
            ->latest('received_date')
            ->paginate(15)
            ->withQueryString();

        return view('gudang.grn.index', compact('receipts'));
    }

    public function create(): View
    {
        $warehouses = Warehouse::where('status', 'active')->get();
        $materials = Material::orderBy('name')->get();

        return view('gudang.grn.create', compact('warehouses', 'materials'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grn_number'          => ['required', 'string', 'max:30', 'unique:goods_receipts,grn_number'],
            'warehouse_id'        => ['required', 'exists:warehouses,id'],
            'received_date'       => ['required', 'date'],
            'purchase_order_id'   => ['nullable', 'integer'],
            'notes'               => ['nullable', 'string', 'max:500'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'exists:materials,id'],
            'items.*.qty_received' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes'       => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $grn = GoodsReceipt::create([
                    'grn_number'        => $validated['grn_number'],
                    'purchase_order_id' => $validated['purchase_order_id'] ?? null,
                    'warehouse_id'      => $validated['warehouse_id'],
                    'received_date'     => $validated['received_date'],
                    'status'            => 'received',
                    'notes'             => $validated['notes'] ?? null,
                    'received_by'       => auth()->id(),
                ]);

                foreach ($validated['items'] as $item) {
                    $grn->items()->create([
                        'material_id'  => $item['material_id'],
                        'qty_received' => $item['qty_received'],
                        'notes'        => $item['notes'] ?? null,
                    ]);

                    $this->stockService->addStock(
                        warehouseId: (int) $grn->warehouse_id,
                        materialId: (int) $item['material_id'],
                        productId: null,
                        quantity: (float) $item['qty_received'],
                        referenceType: 'GRN',
                        referenceId: $grn->id,
                        notes: "Penerimaan Barang via GRN #{$grn->grn_number}",
                        userId: auth()->id()
                    );
                }
            });

            return redirect()->route('gudang.grn.index')
                ->with('success', "GRN {$validated['grn_number']} berhasil disimpan dan stok gudang bertambah otomatis.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses GRN: ' . $e->getMessage());
        }
    }

    public function show(int $id): View
    {
        $receipt = GoodsReceipt::with(['warehouse', 'items.material'])->findOrFail($id);

        return view('gudang.grn.show', compact('receipt'));
    }

    public function edit(int $id): View
    {
        $receipt = GoodsReceipt::findOrFail($id);

        return view('gudang.grn.edit', compact('receipt'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $receipt = GoodsReceipt::findOrFail($id);
        $receipt->update([
            'notes' => $request->input('notes'),
        ]);

        return redirect()->route('gudang.grn.index')
            ->with('success', 'Catatan dokumen GRN berhasil diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $receipt = GoodsReceipt::with('items')->findOrFail($id);

        try {
            DB::transaction(function () use ($receipt) {
                foreach ($receipt->items as $item) {
                    $this->stockService->deductStock(
                        warehouseId: (int) $receipt->warehouse_id,
                        materialId: (int) $item->material_id,
                        productId: null,
                        quantity: (float) $item->qty_received,
                        referenceType: 'GRN_CANCEL',
                        referenceId: $receipt->id,
                        notes: "Pembatalan GRN #{$receipt->grn_number}"
                    );
                }
                $receipt->update(['status' => 'cancelled']);
            });

            return redirect()->route('gudang.grn.index')
                ->with('success', "GRN {$receipt->grn_number} berhasil dibatalkan dan penyesuaian stok dikembalikan.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan GRN: ' . $e->getMessage());
        }
    }
}
