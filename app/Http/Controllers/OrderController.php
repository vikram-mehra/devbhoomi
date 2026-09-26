<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnModel;
use App\Services\OrderTrackingService;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $orders = Order::with('items')
            ->where('user_id', auth()->id())
            ->visibleInAccount()
            ->latest()
            ->paginate(15);

        return view('market.orders', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $order->load(['items.variant.product', 'address', 'trackingEvents']);
        $returnRequest = ReturnModel::query()->where('order_id', $order->id)->first();
        $shipment = app(OrderTrackingService::class)->shipmentForAuthorizedOrder($order);

        return view('market.order-show', compact('order', 'returnRequest', 'shipment'));
    }
}
