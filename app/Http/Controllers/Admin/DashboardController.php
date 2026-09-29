<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $revenue = (float) Order::where('payment_status', 'paid')->sum('total');
        $ordersCount = Order::count();
        $pendingOrders = Order::where('status', 'pending')->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();
        $todayOrders = Order::whereDate('created_at', now()->toDateString())->count();
        $vendors = Vendor::count();
        $users = User::where('role', User::ROLE_USER)->count();
        $products = Product::count();

        $recent = Order::with('user')->latest()->take(10)->get();

        $byStatus = Order::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');

        $chartDays = collect(range(6, 0))->map(function ($i) {
            return now()->subDays($i)->format('Y-m-d');
        });
        $ordersByDayRaw = Order::query()
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');
        $ordersByDay = $chartDays->mapWithKeys(function ($day) use ($ordersByDayRaw) {
            return [$day => (int) ($ordersByDayRaw[$day] ?? 0)];
        });
        $chartMax = max(1, (int) $ordersByDay->max());

        return view('admin.dashboard', compact(
            'revenue', 'ordersCount', 'pendingOrders', 'deliveredOrders', 'todayOrders', 'vendors', 'users', 'products', 'recent', 'byStatus',
            'ordersByDay', 'chartMax', 'chartDays'
        ));
    }
}
