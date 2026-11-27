<?php

namespace App\Http\Controllers\Passport;

use App\Http\Controllers\Controller;
use App\Models\EcoPassport;
use App\Models\WorkOrder;
use App\Services\EcoPassportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EcoPassportController extends Controller
{
    public function index(Request $request): View
    {
        $passports = EcoPassport::with('workOrder:id,wo_number,product_name')
            ->latest('id')
            ->paginate(10);

        $workOrders = collect();
        if ($request->user()->can('passport.create')) {
            $workOrders = WorkOrder::where('status', 'completed')
                ->orderByDesc('id')
                ->get(['id', 'wo_number', 'product_name']);
        }

        return view('passport.index', compact('passports', 'workOrders'));
    }

    public function store(Request $request, EcoPassportService $service): RedirectResponse
    {
        $validated = $request->validate([
            'work_order_id' => ['bail', 'required', 'integer', Rule::exists('work_orders', 'id')->where('status', 'completed')],
        ], [
            'work_order_id.required' => 'Pilih Work Order.',
            'work_order_id.integer' => 'Pilihan Work Order tidak valid.',
            'work_order_id.exists' => 'Pilih Work Order yang sudah selesai.',
        ]);

        $service->createDraft(WorkOrder::findOrFail($validated['work_order_id']));

        return redirect()->route('passport.index')->with('success', 'Draft Eco-Passport berhasil dibuat.');
    }

    public function publish(EcoPassport $passport, EcoPassportService $service): RedirectResponse
    {
        $service->publish($passport);

        return redirect()->route('passport.index')->with('success', 'Status Eco-Passport diubah menjadi Terbit.');
    }

    public function withdraw(EcoPassport $passport, EcoPassportService $service): RedirectResponse
    {
        $service->withdraw($passport);

        return redirect()->route('passport.index')->with('success', 'Eco-Passport berhasil ditarik.');
    }
}
