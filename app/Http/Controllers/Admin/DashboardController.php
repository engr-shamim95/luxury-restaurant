<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display admin overview dashboard with key metrics and recent orders.
     */
    public function index(): View
    {
        $totalSales = (float) Order::where('order_status', '!=', Order::STATUS_CANCELLED)
            ->sum('total');

        $todaySales = (float) Order::whereDate('created_at', today())
            ->where('order_status', '!=', Order::STATUS_CANCELLED)
            ->sum('total');

        $totalOrders = Order::count();
        $pendingOrders = Order::whereIn('order_status', [Order::STATUS_NEW, Order::STATUS_PREPARING])->count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();

        $recentOrders = Order::with('items')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSales',
            'todaySales',
            'totalOrders',
            'pendingOrders',
            'totalProducts',
            'totalCategories',
            'recentOrders'
        ));
    }
}
