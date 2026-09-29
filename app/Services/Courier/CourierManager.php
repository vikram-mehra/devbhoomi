<?php

namespace App\Services\Courier;

use App\Enums\ShipmentStatus;
use App\Jobs\Courier\CreateCourierShipmentJob;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use App\Services\Courier\Contracts\CourierAdapterInterface;
use App\Services\Courier\DTOs\BulkShipmentResult;
use App\Services\Courier\DTOs\LabelResult;
use App\Services\Courier\DTOs\ShipmentRequest;
use App\Services\Courier\DTOs\ShipmentResult;
use App\Services\Courier\DTOs\TrackingResult;
use App\Services\Courier\Exceptions\CourierNotSupportedException;
use App\Services\Courier\Exceptions\ShipmentCreationException;
use App\Services\Courier\Support\ShipmentRequestFactory;
use Illuminate\Support\Facades\DB;

class CourierManager
{
    protected CourierResolver $resolver;
    protected ShipmentRequestFactory $requests;
    protected CourierLogger $logger;

    public function __construct(CourierResolver $resolver, ShipmentRequestFactory $requests, CourierLogger $logger)
    {
        $this->resolver = $resolver;
        $this->requests = $requests;
        $this->logger = $logger;
    }

    /**
     * @return array<string, string>
     */
    public function availableDrivers(): array
    {
        $out = [];
        foreach (config('couriers.partners', []) as $key => $partner) {
            if (! empty($partner['enabled'])) {
                $out[$key] = (string) ($partner['name'] ?? $key);
            }
        }

        return $out;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function partners(): array
    {
        $out = [];
        foreach (config('couriers.partners', []) as $key => $partner) {
            $out[$key] = [
                'key' => $key,
                'name' => (string) ($partner['name'] ?? $key),
                'enabled' => ! empty($partner['enabled']),
            ];
        }

        return $out;
    }

    public function driver(string $name): CourierAdapterInterface
    {
        if (! $this->resolver->isEnabled($name)) {
            throw new CourierNotSupportedException('Courier ['.$name.'] is disabled.');
        }

        return $this->resolver->resolve($name);
    }

    /**
     * @param  list<int>  $orderIds
     */
    public function createShipments(array $orderIds, string $courier, bool $allowExisting = false): BulkShipmentResult
    {
        $courier = strtolower(trim($courier));
        $this->driver($courier);

        $ids = array_values(array_unique(array_map('intval', $orderIds)));
        if ($ids === []) {
            throw new ShipmentCreationException('Select at least one order.');
        }

        $shouldQueue = config('couriers.queue') && config('queue.default') !== 'sync';
        if ($shouldQueue) {
            foreach ($ids as $id) {
                CreateCourierShipmentJob::dispatch($id, $courier, $allowExisting);
            }
            $this->logger->info('Queued shipment jobs.', ['courier' => $courier, 'count' => count($ids)]);

            return BulkShipmentResult::queued($courier, $ids);
        }

        $results = [];
        foreach ($ids as $id) {
            $results[] = $this->createShipmentForOrder($id, $courier, $allowExisting);
        }

        return new BulkShipmentResult($courier, $results);
    }

    public function createShipmentForOrder(int $orderId, string $courier, bool $allowExisting = false): ShipmentResult
    {
        $started = microtime(true);
        $order = Order::with(['items.variant.product', 'shippingAddress', 'address', 'shipments'])->find($orderId);

        if (! $order) {
            return ShipmentResult::fail($orderId, (string) $orderId, 'Order not found.', 'not_found');
        }

        try {
            $this->assertEligible($order, $allowExisting);
            $existing = $this->activeShipment($order);
            if ($existing && ! $allowExisting) {
                return ShipmentResult::ok(
                    (int) $order->id,
                    (string) $order->order_number,
                    (string) $existing->trackingNumber(),
                    [],
                    $existing->shipment_id,
                    $existing->label_url
                );
            }

            $request = $this->requests->make($order, $courier);
            $result = $this->driver($courier)->createShipment($request);
            $this->persistResult($order, $courier, $request, $result);
            $this->logger->info('Shipment processed.', [
                'courier' => $courier,
                'order_id' => $order->id,
                'awb' => $result->awb,
                'success' => $result->success,
                'ms' => (int) ((microtime(true) - $started) * 1000),
            ]);

            return $result;
        } catch (ShipmentCreationException $e) {
            $result = ShipmentResult::fail((int) $order->id, (string) $order->order_number, $e->getMessage(), 'validation');
            $this->persistResult($order, $courier, $this->safeRequest($order, $courier), $result);
            $this->logger->warning('Shipment validation failed.', ['courier' => $courier, 'order_id' => $order->id]);

            return $result;
        } catch (\Throwable $e) {
            report($e);
            $result = ShipmentResult::fail((int) $order->id, (string) $order->order_number, 'Shipment could not be created.', 'exception');
            $this->persistResult($order, $courier, $this->safeRequest($order, $courier), $result);
            $this->logger->error('Shipment exception.', ['courier' => $courier, 'order_id' => $order->id]);

            return $result;
        }
    }

    public function trackShipment(Shipment $shipment): TrackingResult
    {
        $awb = $shipment->trackingNumber();
        if (! $awb) {
            return new TrackingResult(false, null, null, null, null, 'Missing AWB.');
        }

        $result = $this->driver($shipment->courier_partner)->trackShipment($awb);
        if ($result->success && $result->status) {
            $this->applyTracking($shipment, $result);
        }

        return $result;
    }

    public function cancelShipment(Shipment $shipment): ShipmentResult
    {
        $result = $this->driver($shipment->courier_partner)->cancelShipment($shipment);
        if ($result->success) {
            $shipment->update([
                'status' => ShipmentStatus::CANCELLED,
                'cancelled_at' => now(),
            ]);
        }

        return $result;
    }

    public function generateLabel(Shipment $shipment): LabelResult
    {
        $result = $this->driver($shipment->courier_partner)->generateLabel($shipment);
        if ($result->success && $result->labelUrl) {
            $shipment->update(['label_url' => $result->labelUrl]);
        }

        return $result;
    }

    public function applyTracking(Shipment $shipment, TrackingResult $result): void
    {
        DB::transaction(function () use ($shipment, $result) {
            $shipment->update([
                'status' => $result->status ?: $shipment->status,
                'delivered_at' => $result->status === ShipmentStatus::DELIVERED ? now() : $shipment->delivered_at,
            ]);

            foreach ($result->events as $event) {
                ShipmentTrackingEvent::create([
                    'shipment_id' => $shipment->id,
                    'status' => $event['status'] ?? $result->status,
                    'courier_status' => $event['courier_status'] ?? $result->courierStatus,
                    'description' => $event['description'] ?? null,
                    'location' => $event['location'] ?? $result->location,
                    'event_time' => $event['event_time'] ?? now(),
                    'raw_response' => null,
                ]);
            }

            $order = $shipment->order;
            if ($order && $result->status === ShipmentStatus::DELIVERED && $order->status !== 'delivered') {
                $order->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'delivery_date' => now()->toDateString(),
                ]);
            }
        });
    }

    public function assertEligible(Order $order, bool $allowExisting = false): void
    {
        if (in_array($order->status, ['cancelled', 'returned'], true)) {
            throw new ShipmentCreationException('Order '.$order->order_number.' is not eligible for shipment.');
        }

        if ($order->items->isEmpty()) {
            throw new ShipmentCreationException('Order '.$order->order_number.' has no products.');
        }

        if (! $order->shippingAddress && ! $order->address) {
            throw new ShipmentCreationException('Order '.$order->order_number.' is missing a shipping address.');
        }

        $prepaid = strtolower((string) $order->payment_method) !== 'cod';
        if ($prepaid && ! in_array($order->payment_status, ['paid'], true)) {
            throw new ShipmentCreationException('Order '.$order->order_number.' is prepaid and not paid.');
        }
    }

    public function activeShipment(Order $order): ?Shipment
    {
        return $order->shipments->first(fn (Shipment $s) => $s->isActive());
    }

    protected function persistResult(Order $order, string $courier, ?ShipmentRequest $request, ShipmentResult $result): void
    {
        $reference = $request ? $request->reference : $this->requests->reference($order, $courier);

        DB::transaction(function () use ($order, $courier, $request, $result, $reference) {
            $payload = [
                'order_id' => $order->id,
                'courier_partner' => $courier,
                'awb' => $result->awb,
                'tracking_number' => $result->awb,
                'shipment_id' => $result->shipmentId,
                'status' => $result->success ? ShipmentStatus::CREATED : ShipmentStatus::FAILED,
                'label_url' => $result->labelUrl,
                'cod_amount' => $request ? $request->codAmount : null,
                'weight' => $request ? $request->package->weight : null,
                'length' => $request ? $request->package->length : null,
                'width' => $request ? $request->package->width : null,
                'height' => $request ? $request->package->height : null,
                'request_reference' => $reference,
                'api_request_reference' => $reference,
                'api_response_reference' => $result->shipmentId,
                'failure_reason' => $result->success ? null : $result->message,
                'raw_response' => $this->trimmedRaw($result->rawResponse),
            ];

            $shipment = Shipment::query()->firstOrNew(['request_reference' => $reference]);
            if ($shipment->exists && $shipment->isActive() && $result->success === false) {
                return;
            }
            $shipment->fill($payload)->save();

            if ($result->success && $result->awb) {
                $order->update([
                    'courier_name' => (string) (config('couriers.partners.'.$courier.'.name') ?: $courier),
                    'tracking_id' => $result->awb,
                    'status' => in_array($order->status, ['delivered', 'cancelled', 'returned'], true) ? $order->status : 'shipped',
                    'shipped_at' => $order->shipped_at ?: now(),
                ]);
            }
        });
    }

    protected function safeRequest(Order $order, string $courier): ?ShipmentRequest
    {
        try {
            return $this->requests->make($order, $courier);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    protected function trimmedRaw(array $raw): array
    {
        unset($raw['token'], $raw['password'], $raw['Authorization']);

        return $raw;
    }
}
