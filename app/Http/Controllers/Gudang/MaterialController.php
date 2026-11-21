<?php

namespace App\Http\Controllers\Gudang;

use App\Http\Controllers\Controller;
use App\Models\EmissionFactor;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaterialController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['sku', 'name', 'category'];

        $query = Material::search($request->cari, $searchColumns)
            ->filter($request->only(['category']))
            ->with(['emissionFactor', 'stocks.warehouse']);

        $materials = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = Material::select('category')->distinct()->pluck('category');

        $lowStockCount = Material::all()->filter(fn ($m) => $m->is_low_stock)->count();

        return view('gudang.material.index', compact('materials', 'categories', 'lowStockCount'));
    }

    public function create(): View
    {
        $emissionFactors = EmissionFactor::all();

        return view('gudang.material.create', compact('emissionFactors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku'                => ['required', 'string', 'max:30', 'unique:materials,sku'],
            'name'               => ['required', 'string', 'max:150'],
            'category'           => ['required', 'string', 'max:50'],
            'unit'               => ['required', 'string', 'max:20'],
            'min_stock'          => ['required', 'numeric', 'min:0'],
            'unit_cost'          => ['required', 'numeric', 'min:0'],
            'emission_factor_id' => ['nullable', 'exists:emission_factors,id'],
        ], [
            'sku.required'       => 'SKU material wajib diisi.',
            'sku.unique'         => 'SKU material sudah digunakan.',
            'name.required'      => 'Nama material wajib diisi.',
            'unit.required'      => 'Satuan ukuran wajib diisi.',
            'min_stock.required' => 'Batas stok minimum wajib diisi.',
            'unit_cost.required' => 'Biaya satuan standar wajib diisi.',
        ]);

        Material::create($validated);

        return redirect()->route('gudang.material.index')
            ->with('success', "Material '{$validated['name']}' berhasil ditambahkan.");
    }

    public function show(int $id): View
    {
        $material = Material::with(['emissionFactor', 'stocks.warehouse', 'movements' => function ($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);

        return view('gudang.material.show', compact('material'));
    }

    public function edit(int $id): View
    {
        $material = Material::findOrFail($id);
        $emissionFactors = EmissionFactor::all();

        return view('gudang.material.edit', compact('material', 'emissionFactors'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'sku'                => ['required', 'string', 'max:30', 'unique:materials,sku,' . $material->id],
            'name'               => ['required', 'string', 'max:150'],
            'category'           => ['required', 'string', 'max:50'],
            'unit'               => ['required', 'string', 'max:20'],
            'min_stock'          => ['required', 'numeric', 'min:0'],
            'unit_cost'          => ['required', 'numeric', 'min:0'],
            'emission_factor_id' => ['nullable', 'exists:emission_factors,id'],
        ]);

        $material->update($validated);

        return redirect()->route('gudang.material.index')
            ->with('success', "Data material '{$material->name}' berhasil diperbarui.");
    }

    public function destroy(int $id): RedirectResponse
    {
        $material = Material::with('stocks')->findOrFail($id);

        if ($material->total_stock > 0) {
            return back()->with('error', 'Material tidak dapat dihapus karena masih memiliki saldo stok aktif.');
        }

        $material->delete();

        return redirect()->route('gudang.material.index')
            ->with('success', 'Material berhasil dihapus.');
    }
}
