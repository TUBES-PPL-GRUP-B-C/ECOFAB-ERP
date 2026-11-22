<?php

namespace App\Http\Controllers\Penjualan;

use App\Contracts\ProductStockChecker;
use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\SalesOrderRequest;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function __construct(private readonly ProductStockChecker $stockChecker)
    {
    }
    public function index(Request $request): View
    {
        $searchColumns = ['so_number', 'notes', 'customer.name', 'customer.code'];

        $salesOrders = SalesOrder::search($request->cari, $searchColumns)
            ->filter($request->only(['status', 'customer_id']))
            ->with('customer')
            ->withCount('items')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get(['id', 'code', 'name']);

        return view('penjualan.so.index', compact('salesOrders', 'customers'));
    }

    public function create(): View
    {
        return view('penjualan.so.create', [
            'customers' => Customer::orderBy('name')->get(['id', 'code', 'name']),
            'soNumber' => SalesOrder::generateNumber(),
            'stockIntegrated' => $this->stockChecker->isIntegrated(),
        ]);
    }

    public function store(SalesOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $salesOrder = DB::transaction(function () use ($validated) {
            $this->stockChecker->assertAvailable($validated['items']);

            $salesOrder = SalesOrder::create([
                'so_number' => SalesOrder::generateNumber(),
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => 0,
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($salesOrder, $validated['items']);

            return $salesOrder;
        });

        return redirect()
            ->route('penjualan.so.show', $salesOrder)
            ->with('success', "Sales Order {$salesOrder->so_number} berhasil dibuat.");
    }

    public function show(SalesOrder $so): View
    {
        $so->load(['customer', 'items', 'creator']);

        return view('penjualan.so.show', ['salesOrder' => $so]);
    }

    public function edit(SalesOrder $so): View|RedirectResponse
    {
        if ($so->hasDeliveries()) {
            return redirect()
                ->route('penjualan.so.show', $so)
                ->with('error', 'Sales Order yang sudah memiliki pengiriman tidak dapat diubah.');
        }

        if (! $so->isEditable()) {
            return redirect()
                ->route('penjualan.so.show', $so)
                ->with('error', 'Sales Order yang sudah dibatalkan tidak dapat diubah.');
        }

        $so->load('items');

        return view('penjualan.so.edit', [
            'salesOrder' => $so,
            'customers' => Customer::orderBy('name')->get(['id', 'code', 'name']),
            'stockIntegrated' => $this->stockChecker->isIntegrated(),
        ]);
    }

    public function update(SalesOrderRequest $request, SalesOrder $so): RedirectResponse
    {
        if ($so->hasDeliveries()) {
            return redirect()
                ->route('penjualan.so.show', $so)
                ->with('error', 'Sales Order yang sudah memiliki pengiriman tidak dapat diubah.');
        }

        if (! $so->isEditable()) {
            return redirect()
                ->route('penjualan.so.show', $so)
                ->with('error', 'Sales Order yang sudah dibatalkan tidak dapat diubah.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($so, $validated) {
            if ($validated['status'] !== 'cancelled') {
                $this->stockChecker->assertAvailable($validated['items']);
            }

            $so->update([
                'customer_id' => $validated['customer_id'],
                'order_date' => $validated['order_date'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $so->items()->delete();
            $this->syncItems($so, $validated['items']);
        });

        return redirect()
            ->route('penjualan.so.show', $so)
            ->with('success', "Sales Order {$so->so_number} berhasil diperbarui.");
    }

    public function destroy(SalesOrder $so): RedirectResponse
    {
        if ($so->hasDeliveries()) {
            return redirect()
                ->route('penjualan.so.index')
                ->with('error', 'Sales Order yang sudah memiliki pengiriman tidak dapat dihapus.');
        }

        if (! $so->isDeletable()) {
            return redirect()
                ->route('penjualan.so.index')
                ->with('error', 'Hanya Sales Order berstatus Draft atau Dibatalkan yang dapat dihapus.');
        }

        $number = $so->so_number;

        DB::transaction(function () use ($so) {
            $so->items()->delete();
            $so->delete();
        });

        return redirect()
            ->route('penjualan.so.index')
            ->with('success', "Sales Order {$number} berhasil dihapus.");
    }

    /**
     * Simpan item SO dan hitung subtotal + total di server (harga tidak diterima dari client).
     *
     * @param array<int, array<string, mixed>> $items
     */
    private function syncItems(SalesOrder $salesOrder, array $items): void
    {
        $total = 0.0;

        foreach ($items as $item) {
            $qty = round((float) $item['qty'], 3);
            $unitPrice = round((float) $item['unit_price'], 2);
            $subtotal = round($qty * $unitPrice, 2);
            $total += $subtotal;

            $salesOrder->items()->create([
                'product_id' => filled($item['product_id'] ?? null) ? (int) $item['product_id'] : null,
                'product_name' => $item['product_name'],
                'qty' => $qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);
        }

        $salesOrder->update(['total_amount' => round($total, 2)]);
    }
}
