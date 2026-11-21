<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['code', 'name', 'address'];

        $warehouses = Warehouse::search($request->cari, $searchColumns)
            ->filter($request->only(['status']))
            ->withCount('materialStocks')
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('gudang.gudang.index', compact('warehouses'));
    }

    public function create(): View
    {
        return view('gudang.gudang.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'     => ['required', 'string', 'max:20', 'unique:warehouses,code'],
            'name'     => ['required', 'string', 'max:100'],
            'address'  => ['nullable', 'string'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'status'   => ['required', 'in:active,inactive'],
        ], [
            'code.required' => 'Kode gudang wajib diisi.',
            'code.unique'   => 'Kode gudang sudah digunakan.',
            'name.required' => 'Nama gudang wajib diisi.',
        ]);

        Warehouse::create($validated);

        return redirect()->route('gudang.gudang.index')
            ->with('success', "Gudang '{$validated['name']}' berhasil ditambahkan.");
    }

    public function show(int $id): View
    {
        $warehouse = Warehouse::with(['materialStocks.material'])->findOrFail($id);

        return view('gudang.gudang.show', compact('warehouse'));
    }

    public function edit(int $id): View
    {
        $warehouse = Warehouse::findOrFail($id);

        return view('gudang.gudang.edit', compact('warehouse'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'code'     => ['required', 'string', 'max:20', 'unique:warehouses,code,' . $warehouse->id],
            'name'     => ['required', 'string', 'max:100'],
            'address'  => ['nullable', 'string'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'status'   => ['required', 'in:active,inactive'],
        ]);

        $warehouse->update($validated);

        return redirect()->route('gudang.gudang.index')
            ->with('success', "Data gudang '{$warehouse->name}' berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $warehouse = Warehouse::with('materialStocks')->findOrFail($id);

        if ($warehouse->materialStocks()->where('qty', '>', 0)->exists()) {
            return back()->with('error', 'Gudang tidak dapat dihapus karena masih memiliki saldo stok bahan aktif.');
        }

        $warehouse->delete();

        return redirect()->route('gudang.gudang.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}
