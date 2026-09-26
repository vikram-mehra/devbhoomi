<?php

namespace App\Http\Controllers;

use App\Services\OrderTrackingService;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function show(OrderTrackingService $tracking)
    {
        return view('market.track-order', [
            'dummyEnabled' => $tracking->dummyEnabled(),
            'dummy' => $tracking->dummyCredentials(),
            'courier' => $tracking->courierMeta(),
            'shipment' => session('tracking_shipment'),
        ]);
    }

    public function lookup(Request $request, OrderTrackingService $tracking)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:40'],
            'contact' => ['required', 'string', 'max:80'],
        ]);

        $shipment = $tracking->lookup($data['order_number'], $data['contact']);

        if (! $shipment) {
            return back()
                ->withInput()
                ->withErrors(['order_number' => __('We could not find an order matching those details.')]);
        }

        return redirect()
            ->route('orders.track')
            ->withInput()
            ->with('tracking_shipment', $shipment);
    }
}
