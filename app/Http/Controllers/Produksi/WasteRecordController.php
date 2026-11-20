<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produksi\WasteRecordRequest;
use App\Models\WasteRecord;
use App\Models\WorkOrder;
use App\Services\WasteRecordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WasteRecordController extends Controller
{
    public function __construct(private readonly WasteRecordService $service)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(WasteRecord::STATUSES))],
        ]);

        $wasteRecords = WasteRecord::query()
            ->with(['workOrder', 'creator'])
            ->withCount('wasteListings')
            ->when(filled($filters['cari'] ?? null), function ($query) use ($filters) {
                $term = $filters['cari'];
                $query->where(function ($search) use ($term) {
                    $search->where('waste_type', 'like', "%{$term}%")
                        ->orWhere('condition', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%")
                        ->orWhereHas('workOrder', fn ($wo) => $wo->where('wo_number', 'like', "%{$term}%"));
                });
            })
            ->filter(['status' => $filters['status'] ?? null])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('produksi.limbah.index', [
            'wasteRecords' => $wasteRecords,
            'statuses' => WasteRecord::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('produksi.limbah.create', $this->formData(new WasteRecord([
            'condition' => 'Bagus',
            'status' => 'available',
        ])));
    }

    public function store(WasteRecordRequest $request): RedirectResponse
    {
        $wasteRecord = $this->service->create($request->validated(), $request->user());

        return redirect()->route('produksi.limbah.show', $wasteRecord)
            ->with('success', 'Limbah produksi berhasil dicatat.');
    }

    public function show(WasteRecord $wasteRecord): View
    {
        return view('produksi.limbah.show', [
            'wasteRecord' => $wasteRecord->load(['workOrder', 'creator'])->loadCount('wasteListings'),
            'statuses' => WasteRecord::STATUSES,
        ]);
    }

    public function edit(WasteRecord $wasteRecord): View
    {
        $this->service->assertManageable($wasteRecord);

        return view('produksi.limbah.edit', $this->formData($wasteRecord));
    }

    public function update(WasteRecordRequest $request, WasteRecord $wasteRecord): RedirectResponse
    {
        $this->service->update($wasteRecord, $request->validated());

        return redirect()->route('produksi.limbah.show', $wasteRecord)
            ->with('success', 'Limbah produksi berhasil diperbarui.');
    }

    public function destroy(WasteRecord $wasteRecord): RedirectResponse
    {
        $this->service->delete($wasteRecord);

        return redirect()->route('produksi.limbah.index')
            ->with('success', 'Limbah produksi berhasil dihapus.');
    }

    private function formData(WasteRecord $wasteRecord): array
    {
        return [
            'wasteRecord' => $wasteRecord->exists ? $wasteRecord->loadCount('wasteListings') : $wasteRecord,
            'workOrders' => WorkOrder::query()->where('status', 'completed')->latest('id')->get(),
            'statuses' => WasteRecord::STATUSES,
        ];
    }
}
