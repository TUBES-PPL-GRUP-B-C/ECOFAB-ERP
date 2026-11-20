<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Produksi\WorkOrderRequest;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkOrderController extends Controller
{
    public function __construct(private readonly WorkOrderService $service)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(WorkOrder::STATUSES))],
        ]);

        $workOrders = WorkOrder::query()
            ->with('creator')
            ->withCount(['materials', 'wasteRecords'])
            ->when(filled($filters['cari'] ?? null), function ($query) use ($filters) {
                $term = $filters['cari'];
                $query->where(function ($search) use ($term) {
                    $search->where('wo_number', 'like', "%{$term}%")
                        ->orWhere('product_name', 'like', "%{$term}%")
                        ->orWhere('notes', 'like', "%{$term}%");
                });
            })
            ->filter(['status' => $filters['status'] ?? null])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('produksi.wo.index', [
            'workOrders' => $workOrders,
            'statuses' => WorkOrder::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('produksi.wo.create', [
            'workOrder' => new WorkOrder(),
            'statuses' => WorkOrder::STATUSES,
        ]);
    }

    public function store(WorkOrderRequest $request): RedirectResponse
    {
        try {
            $workOrder = $this->service->create($request->safe()->only([
                'wo_number', 'product_name', 'target_quantity', 'start_date', 'due_date', 'notes',
            ]), $request->user());
        } catch (UniqueConstraintViolationException $exception) {
            // A concurrent request may have claimed the number after validation.
            throw ValidationException::withMessages(['wo_number' => 'Nomor Work Order sudah digunakan.']);
        }

        return redirect()->route('produksi.wo.show', $workOrder)
            ->with('success', 'Work Order berhasil ditambahkan sebagai Draft.');
    }

    public function show(WorkOrder $workOrder): View
    {
        return view('produksi.wo.show', [
            'workOrder' => $workOrder->load('creator')->loadCount(['materials', 'wasteRecords']),
            'statuses' => WorkOrder::STATUSES,
        ]);
    }

    public function edit(WorkOrder $workOrder): View
    {
        abort_unless($workOrder->isDraft(), 409, 'Hanya Work Order berstatus Draft yang dapat diubah.');

        return view('produksi.wo.edit', [
            'workOrder' => $workOrder,
            'statuses' => WorkOrder::STATUSES,
        ]);
    }

    public function update(WorkOrderRequest $request, WorkOrder $workOrder): RedirectResponse
    {
        try {
            $this->service->update($workOrder, $request->safe()->only([
                'wo_number', 'product_name', 'target_quantity', 'start_date', 'due_date', 'notes',
            ]));
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['wo_number' => 'Nomor Work Order sudah digunakan.']);
        }

        return redirect()->route('produksi.wo.show', $workOrder)
            ->with('success', 'Work Order berhasil diperbarui.');
    }

    public function destroy(WorkOrder $workOrder): RedirectResponse
    {
        $this->service->delete($workOrder);

        return redirect()->route('produksi.wo.index')
            ->with('success', 'Work Order berhasil dihapus.');
    }

    public function start(WorkOrder $workOrder): RedirectResponse
    {
        $this->service->start($workOrder);

        return redirect()->route('produksi.wo.show', $workOrder)
            ->with('success', 'Work Order berhasil dimulai.');
    }
}
