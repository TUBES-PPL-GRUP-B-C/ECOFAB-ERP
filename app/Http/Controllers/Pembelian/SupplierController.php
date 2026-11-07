<?php

namespace App\Http\Controllers\Pembelian;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['code', 'name', 'contact_person', 'city', 'province', 'country'];

        $suppliers = Supplier::search($request->cari, $searchColumns)
            ->filter($request->only(['status']))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('pembelian.supplier.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('pembelian.supplier.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        Supplier::create($validated);

        return redirect()
            ->route('pembelian.supplier.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier): View
    {
        $purchaseOrders = Schema::hasTable('purchase_orders')
            ? DB::table('purchase_orders')->where('supplier_id', $supplier->id)->orderByDesc('id')->get()
            : collect();

        return view('pembelian.supplier.show', [
            'supplier' => $supplier,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        return view('pembelian.supplier.edit', [
            'supplier' => $supplier,
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate($this->rules($supplier->id));

        $supplier->update($validated);

        return redirect()
            ->route('pembelian.supplier.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if (Schema::hasTable('purchase_orders') && DB::table('purchase_orders')->where('supplier_id', $supplier->id)->exists()) {
            abort(409, 'Supplier yang sudah memiliki Purchase Order tidak dapat dihapus.');
        }

        $supplier->delete();

        return redirect()
            ->route('pembelian.supplier.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }

    private function rules(?int $ignoreId = null): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($ignoreId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
