<?php

namespace App\Http\Controllers\Bursa;

use App\Http\Controllers\Controller;
use App\Models\WasteListing;
use App\Models\WasteRecord;
use App\Services\WasteListingInventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WasteListingController extends Controller
{
    public function __construct(private readonly WasteListingInventory $inventory)
    {
    }

    public function index(): View
    {
        $listings = WasteListing::with('wasteRecord')->latest()->paginate(10);

        return view('bursa.listing.index', compact('listings'));
    }

    public function create(): View
    {
        $wasteRecordIds = Schema::hasTable('waste_records')
            ? WasteRecord::whereIn('status', ['available', 'listed'])->orderBy('id')->pluck('id')
            : collect();
        $wasteRecords = Schema::hasTable('waste_records') ? WasteRecord::whereIn('id', $wasteRecordIds)->get()->keyBy('id') : collect();

        return view('bursa.listing.create', compact('wasteRecordIds', 'wasteRecords'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureWasteRecordsAvailable();
        $validated = $request->validate($this->rules());
        $attributes = collect($validated)->except(['photo', 'publication_status'])->all();
        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('waste-listings', 'public');
        }
        try {
            DB::transaction(function () use ($attributes, $validated) {
                $this->lockWasteRecord($attributes);
                $listing = new WasteListing($attributes);
                $listing->status = $validated['publication_status'] ?? 'draft';
                $listing->save();
            }, 3);
        } catch (\Throwable $exception) {
            if (isset($attributes['photo_path'])) {
                Storage::disk('public')->delete($attributes['photo_path']);
            }
            throw $exception;
        }

        return redirect()->route('bursa.listing.index')->with('success', 'Listing berhasil disimpan.');
    }

    public function show(WasteListing $listing): View
    {
        $listing->load('wasteRecord');

        return view('bursa.listing.show', compact('listing'));
    }

    public function edit(WasteListing $listing): View
    {
        $wasteRecordIds = Schema::hasTable('waste_records')
            ? WasteRecord::where(fn ($query) => $query->whereIn('status', ['available', 'listed'])->orWhere('id', $listing->waste_record_id))->orderBy('id')->pluck('id')
            : collect();
        $wasteRecords = Schema::hasTable('waste_records') ? WasteRecord::whereIn('id', $wasteRecordIds)->get()->keyBy('id') : collect();

        return view('bursa.listing.edit', compact('listing', 'wasteRecordIds', 'wasteRecords'));
    }

    public function update(Request $request, WasteListing $listing): RedirectResponse
    {
        $this->ensureWasteRecordsAvailable();
        $validated = $request->validate($this->rules(true));
        $attributes = collect($validated)->except(['photo', 'publication_status'])->all();
        if ($request->hasFile('photo')) {
            $attributes['photo_path'] = $request->file('photo')->store('waste-listings', 'public');
        }
        try {
            $oldPhoto = DB::transaction(function () use ($listing, $attributes, $validated) {
                // Lock parents in a stable order before their listing, shared with order approval.
                WasteRecord::whereIn('id', [$listing->waste_record_id, $attributes['waste_record_id']])
                    ->orderBy('id')->lockForUpdate()->get();
                $locked = WasteListing::query()->lockForUpdate()->findOrFail($listing->id);
                abort_if($locked->waste_record_id !== $listing->waste_record_id, 409, 'Sumber listing berubah. Muat ulang halaman.');
                if ($locked->waste_record_id !== (int) $attributes['waste_record_id']
                    && DB::table('waste_orders')->where('waste_listing_id', $locked->id)->exists()) {
                    throw ValidationException::withMessages(['waste_record_id' => 'Sumber listing yang memiliki riwayat pesanan tidak dapat diganti.']);
                }
                $this->lockWasteRecord($attributes, $locked->id);
                $oldPhoto = $locked->photo_path;
                $locked->fill($attributes);
                $locked->status = $validated['publication_status'] ?? $locked->status;
                $locked->save();

                return $oldPhoto;
            }, 3);
        } catch (\Throwable $exception) {
            if (isset($attributes['photo_path'])) {
                Storage::disk('public')->delete($attributes['photo_path']);
            }
            throw $exception;
        }
        if (isset($attributes['photo_path']) && $this->isManagedPhoto($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return redirect()->route('bursa.listing.index')->with('success', 'Listing berhasil diperbarui.');
    }

    public function destroy(WasteListing $listing): RedirectResponse
    {
        $photoPath = DB::transaction(function () use ($listing) {
            $locked = $this->inventory->lockListing($listing->id);
            if (Schema::hasTable('waste_orders')) {
                if (! Schema::hasColumn('waste_orders', 'waste_listing_id')) {
                    throw new HttpException(503, 'Relasi order limbah belum dapat diverifikasi.');
                }
                if (DB::table('waste_orders')->where('waste_listing_id', $locked->id)->exists()) {
                    throw new HttpException(409, 'Listing yang memiliki order tidak dapat dihapus.');
                }
            }
            $locked->delete();

            return $locked->photo_path;
        }, 3);
        if ($this->isManagedPhoto($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        return redirect()->route('bursa.listing.index')->with('success', 'Listing berhasil dihapus.');
    }

    private function lockWasteRecord(array $attributes, ?int $exceptListingId = null): void
    {
        $source = WasteRecord::query()->lockForUpdate()->find($attributes['waste_record_id']);
        if ($source === null || ! in_array($source->status, ['available', 'listed'], true)) {
            throw ValidationException::withMessages(['waste_record_id' => 'Pilih catatan limbah berstatus Tersedia atau Terdaftar di Bursa.']);
        }
        $this->inventory->assertAllocation($source, (string) $attributes['available_weight'], $exceptListingId);
    }

    private function isManagedPhoto(?string $path): bool
    {
        return $path !== null && preg_match('~^waste-listings/[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|webp)$~i', $path) === 1;
    }

    private function rules(bool $updating = false): array
    {
        return [
            'waste_record_id' => ['required', 'integer', Rule::exists('waste_records', 'id')],
            'category' => ['required', 'string', 'max:255'],
            'price_per_kg' => ['required', 'numeric', 'min:0', 'max:9999999999999.99', 'decimal:0,2'],
            'available_weight' => ['required', 'numeric', $updating ? 'min:0' : 'gt:0', 'max:999999999.999', 'decimal:0,3'],
            'publication_status' => ['sometimes', 'required', Rule::in(['draft', 'published'])],
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
