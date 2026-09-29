<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnModel;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);
        $request->validate(['reason' => 'required|string|max:2000']);

        ReturnModel::firstOrCreate(
            ['order_id' => $order->id],
            ['user_id' => auth()->id(), 'reason' => $request->reason, 'status' => ReturnModel::STATUS_PENDING]
        );

        return back()->with('status', __('Return request submitted.'));
    }
}
