<?php

namespace App\Http\Controllers\Penjualan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SalesOrder;
use Illuminate\View\View;

class SalesDashboardController extends Controller
{
    public function __invoke(): View
    {
        $totalCustomers = Customer::count();
        $totalSalesOrders = SalesOrder::count();
        $confirmedSalesOrders = SalesOrder::where('status', 'confirmed')->count();
        $totalSalesValue = (float) SalesOrder::where('status', '!=', 'cancelled')->sum('total_amount');
        $recentSalesOrders = SalesOrder::with('customer')
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('penjualan.index', compact(
            'totalCustomers',
            'totalSalesOrders',
            'confirmedSalesOrders',
            'totalSalesValue',
            'recentSalesOrders',
        ));
    }
}
