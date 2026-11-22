<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Penjualan\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $searchColumns = ['code', 'name', 'phone', 'email', 'address'];

        $customers = Customer::search($request->cari, $searchColumns)
            ->withCount('salesOrders')
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('penjualan.customer.index', compact('customers'));
    }

    public function create(): View
    {
        return view('penjualan.customer.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return redirect()
            ->route('penjualan.customer.index')
            ->with('success', 'Customer berhasil ditambahkan.');
    }

    public function show(Customer $customer): View
    {
        $salesOrders = $customer->salesOrders()
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        return view('penjualan.customer.show', compact('customer', 'salesOrders'));
    }

    public function edit(Customer $customer): View
    {
        return view('penjualan.customer.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()
            ->route('penjualan.customer.index')
            ->with('success', 'Customer berhasil diperbarui.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->salesOrders()->exists()) {
            return redirect()
                ->route('penjualan.customer.index')
                ->with('error', "Customer {$customer->name} sudah memiliki Sales Order sehingga tidak dapat dihapus.");
        }

        $customer->delete();

        return redirect()
            ->route('penjualan.customer.index')
            ->with('success', 'Customer berhasil dihapus.');
    }
}
