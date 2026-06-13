<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;

class VendorOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:'.(\App\Models\User::ROLE_VENDOR)]);
    }

    public function index()
    {
        $vendor = auth()->user()->vendor;
        abort_unless($vendor && $vendor->status === 'approved', 403);

        $orderIds = OrderItem::where('vendor_id', $vendor->id)->pluck('order_id')->unique();
        $orders = Order::with(['user', 'items' => fn ($q) => $q->where('vendor_id', $vendor->id)])
            ->whereIn('id', $orderIds)
            ->latest()
            ->paginate(20);

        return view('vendor.orders', compact('orders', 'vendor'));
    }
}
