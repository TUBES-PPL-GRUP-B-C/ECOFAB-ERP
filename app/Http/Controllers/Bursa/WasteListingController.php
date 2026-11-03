<?php

namespace App\Http\Controllers\Bursa;

use App\Http\Controllers\Controller;
use App\Models\WasteListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WasteListingController extends Controller
{
    public function index(): View
    {
        $listings = WasteListing::latest()->paginate(10);

        return view('bursa.listing.index', compact('listings'));
    }

    public function create(): View
    {
        $wasteRecordIds = Schema::hasTable('waste_records')
            ? DB::table('waste_records')->orderBy('id')->pluck('id')
            : collect();

        return view('bursa.listing.create', compact('wasteRecordIds'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureWasteRecordsAvailable();
        $validated = $request->validate($this->rules());
        $attributes = collect($validated)->except('photo')->all();

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('waste-listings', 'public');
        }

        try {
            WasteListing::create($attributes);
        } catch (\Throwable $exception) {
            if (isset($attributes['photo_path'])) {
                Storage::disk('public')->delete($attributes['photo_path']);
            }
            throw $exception;
        }

        return redirect()->route('bursa.listing.index')->with('success', 'Listing berhasil disimpan sebagai draft.');
    }

    public function show(WasteListing $listing): View
    {
        abort(501, 'Halaman detail listing belum tersedia pada irisan awal FR-23.');
    }

    public function edit(WasteListing $listing): View
    {
        abort(501, 'Form edit listing belum tersedia pada irisan awal FR-23.');
    }

    public function update(Request $request, WasteListing $listing): RedirectResponse
    {
        $this->ensureWasteRecordsAvailable();
        $validated = $request->validate($this->rules());
        $attributes = collect($validated)->except('photo')->all();
        $oldPhoto = $listing->photo_path;

        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('waste-listings', 'public');
        }

        try {
            $listing->update($attributes);
        } catch (\Throwable $exception) {
            if (isset($attributes['photo_path'])) {
                Storage::disk('public')->delete($attributes['photo_path']);
            }
            throw $exception;
        }

        if (isset($attributes['photo_path']) && $oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()->route('bursa.listing.index')->with('success', 'Listing berhasil diperbarui.');
    }

    public function destroy(WasteListing $listing): RedirectResponse
    {
        // FR-25 owns waste_orders; keep the listing while orders reference it.
        if (Schema::hasTable('waste_orders') && DB::table('waste_orders')->where('waste_listing_id', $listing->id)->exists()) {
            abort(409, 'Listing yang memiliki order tidak dapat dihapus.');
        }

        $listing->delete();

        if ($listing->photo_path) {
            Storage::disk('public')->delete($listing->photo_path);
        }

        return redirect()->route('bursa.listing.index')->with('success', 'Listing berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'waste_record_id' => ['required', 'integer', Rule::exists('waste_records', 'id')],
            'category' => ['required', 'string', 'max:255'],
            'price_per_kg' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'available_weight' => ['required', 'numeric', 'gt:0', 'max:999999999.999', 'decimal:0,3'],
            'photo' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function ensureWasteRecordsAvailable(): void
    {
        if (! Schema::hasTable('waste_records')) {
            throw new HttpException(503, 'Data limbah produksi FR-22 belum tersedia.');
        }
    }
}
