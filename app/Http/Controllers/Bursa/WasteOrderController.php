<?php

namespace App\Http\Controllers\Bursa;

use App\Http\Controllers\Controller;
use App\Models\WasteListing;
use App\Models\WasteOrder;
use App\Services\WasteOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WasteOrderController extends Controller
{
    public function create(Request $request): View
    {
        $data = $request->validate(['listing' => ['required', 'integer', 'min:1']]);
        $listing = WasteListing::query()->publiclyAvailable()
            ->select(['id', 'category', 'price_per_kg', 'available_weight', 'photo_path'])
            ->findOrFail($data['listing']);

        return view('public.waste_order_create', compact('listing'));
    }

    public function store(Request $request, WasteOrderService $service): RedirectResponse
    {
        $data = $request->validate([
            'waste_listing_id' => ['required', 'integer', 'min:1'],
            ...$this->rules(),
        ]);
        // Missing/nonpublic references use the same response as the public detail page.
        WasteListing::query()->publiclyAvailable()->findOrFail($data['waste_listing_id']);
        $service->create($data);

        return redirect()->route('public.bursa.order.success')->with('order_received', true);
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (! $request->session()->get('order_received')) {
            return redirect()->route('public.bursa.index');
        }

        return view('public.waste_order_success');
    }

    public function index(): View
    {
        $orders = WasteOrder::with('listing')->orderByDesc('id')->paginate(10);

        return view('bursa.order.index', compact('orders'));
    }

    public function show(WasteOrder $order): View
    {
        $order->load(['listing', 'approver']);

        return view('bursa.order.show', compact('order'));
    }

    public function edit(WasteOrder $order): View
    {
        abort_unless($order->status === 'pending', 409, 'Hanya pesanan menunggu persetujuan yang dapat diubah.');
        $order->load('listing');

        return view('bursa.order.edit', compact('order'));
    }

    public function update(Request $request, WasteOrder $order, WasteOrderService $service): RedirectResponse
    {
        $service->update($order, $request->validate($this->rules()));

        return redirect()->route('bursa.order.show', $order)->with('success', 'Pesanan berhasil diperbarui.');
    }

    public function destroy(WasteOrder $order, WasteOrderService $service): RedirectResponse
    {
        $service->cancel($order);

        return redirect()->route('bursa.order.show', $order)->with('success', 'Pesanan dibatalkan. Riwayat tetap tersimpan.');
    }

    public function approve(Request $request, WasteOrder $order, WasteOrderService $service): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $service->decide($order, $request->user(), $data['action'], $data['reason'] ?? null);

        return redirect()->route('bursa.order.show', $order)->with('success', $data['action'] === 'approve' ? 'Pesanan disetujui dan stok diperbarui.' : 'Pesanan ditolak tanpa pengurangan stok.');
    }

    private function rules(): array
    {
        return [
            'buyer_name' => ['required', 'string', 'max:100'],
            'buyer_contact' => ['required', 'string', 'max:100'],
            'requested_weight' => ['required', 'numeric', 'gt:0', 'max:999999999.999', 'decimal:0,3'],
        ];
    }
}
