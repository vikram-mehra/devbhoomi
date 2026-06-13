<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:'.(\App\Models\User::ROLE_VENDOR)]);
    }

    public function index()
    {
        $vendor = auth()->user()->vendor;
        if (! $vendor) {
            return redirect()->route('login')->with('error', 'No vendor profile.');
        }

        if ($vendor->status !== 'approved') {
            return view('vendor.pending', compact('vendor'))->with('vendorPendingBanner', true);
        }

        $productCount = $vendor->products()->count();
        $lowStock = $vendor->products()->whereHas('variants', fn ($q) => $q->where('stock_qty', '<', 5))->count();

        $orderItems = OrderItem::where('vendor_id', $vendor->id)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'paid'));

        $gross = (clone $orderItems)->sum('line_total');
        $commission = (clone $orderItems)->sum('commission_amount');
        $net = $gross - $commission;

        $recentItems = OrderItem::with(['order.user', 'variant.product'])
            ->where('vendor_id', $vendor->id)
            ->latest()
            ->take(12)
            ->get();

        $chatUserIds = \App\Models\ChatMessage::where('vendor_id', $vendor->id)
            ->distinct()
            ->orderByDesc('id')
            ->limit(50)
            ->pluck('user_id')
            ->unique()
            ->take(8)
            ->values();

        return view('vendor.dashboard', compact(
            'vendor', 'productCount', 'lowStock', 'gross', 'commission', 'net', 'recentItems', 'chatUserIds'
        ));
    }
}
