<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCourierShipmentRequest;
use App\Models\Order;
use App\Models\Shipment;
use App\Services\Courier\CourierManager;
use App\Services\Courier\DTOs\BulkShipmentResult;
use App\Services\Courier\Exceptions\CourierException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CourierShipmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function store(CreateCourierShipmentRequest $request, CourierManager $manager): RedirectResponse
    {
        $summary = $manager->createShipments(
            $request->input('order_ids', []),
            (string) $request->input('courier')
        );

        $name = $manager->availableDrivers()[$summary->courier] ?? $summary->courier;

        if ($summary->queued) {
            return back()->with('status', 'Shipment creation has been queued. '.$summary->total.' orders submitted for processing using '.$name.'.')
                ->with('courier_shipment_summary', $summary->toArray());
        }

        $message = 'Shipment Creation Completed. Total: '.$summary->total.', Successful: '.$summary->successful.', Failed: '.$summary->failed.'. Courier: '.$name.'.';

        return back()
            ->with($summary->failed > 0 ? 'warning' : 'status', $message)
            ->with('courier_shipment_summary', $summary->toArray());
    }

    public function show(Order $order): View
    {
        $order->load(['shipments.trackingEvents', 'shippingAddress', 'items']);

        return view('admin.orders.shipment', compact('order'));
    }

    public function track(Order $order, CourierManager $manager): RedirectResponse
    {
        $shipment = $order->latestShipment;
        if (! $shipment) {
            return back()->with('warning', 'No shipment found for this order.');
        }

        try {
            $result = $manager->trackShipment($shipment);
        } catch (CourierException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return back()->with('status', $result->success
            ? 'Tracking updated: '.($result->status ?: 'ok')
            : ($result->message ?: 'Tracking failed.'));
    }

    public function cancel(Order $order, CourierManager $manager): RedirectResponse
    {
        $shipment = $order->latestShipment;
        if (! $shipment) {
            return back()->with('warning', 'No shipment found for this order.');
        }

        try {
            $result = $manager->cancelShipment($shipment);
        } catch (CourierException $e) {
            return back()->with('warning', $e->getMessage());
        }

        return back()->with($result->success ? 'status' : 'warning', $result->success
            ? 'Shipment cancelled.'
            : ($result->message ?: 'Cancellation failed.'));
    }

    public function retry(Order $order, CourierManager $manager): RedirectResponse
    {
        $courier = $order->latestShipment->courier_partner ?? array_key_first($manager->availableDrivers());
        if (! $courier) {
            return back()->with('warning', 'No courier is enabled.');
        }

        $result = $manager->createShipmentForOrder((int) $order->id, $courier, true);

        return back()
            ->with($result->success ? 'status' : 'warning', $result->success
                ? 'Shipment created. AWB: '.$result->awb
                : ($result->message ?: 'Retry failed.'))
            ->with('courier_shipment_summary', (new BulkShipmentResult($courier, [$result]))->toArray());
    }
}
