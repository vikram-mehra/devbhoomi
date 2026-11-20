<?php

namespace App\Services;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OrderTrackingService
{
    public function __construct(protected DelhiveryService $delhivery)
    {
    }

    /**
     * @return array{name: string, env: string, is_test: bool, configured: bool}
     */
    public function courierMeta(): array
    {
        return [
            'name' => 'Delhivery',
            'env' => $this->delhivery->environment(),
            'is_test' => $this->delhivery->isTest(),
            'configured' => $this->delhivery->enabled(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function lookup(string $orderNumber, string $contact): ?array
    {
        $orderNumber = trim($orderNumber);
        $contact = trim($contact);

        if ($orderNumber === '' || $contact === '') {
            return null;
        }

        $order = $this->findAccessibleOrder($orderNumber, $contact);
        if (! $order) {
            return null;
        }

        return $this->shipmentFromStoredOrder($order);
    }

    /**
     * Build a tracking payload for an order the current user already owns.
     *
     * @return array<string, mixed>
     */
    public function shipmentForAuthorizedOrder(Order $order): array
    {
        $order->loadMissing(['items', 'address', 'shippingAddress', 'user', 'trackingEvents']);

        return $this->shipmentFromStoredOrder($order);
    }

    protected function findAccessibleOrder(string $orderNumber, string $contact): ?Order
    {
        $order = Order::query()
            ->with(['items', 'address', 'shippingAddress', 'user', 'trackingEvents'])
            ->where('order_number', $orderNumber)
            ->visibleInAccount()
            ->first();

        if (! $order || ! $this->canAccessOrder($order, $contact)) {
            return null;
        }

        return $order;
    }

    protected function canAccessOrder(Order $order, string $contact): bool
    {
        if (auth()->check()) {
            return (int) $order->user_id === (int) auth()->id();
        }

        $needles = array_filter([
            $this->normalizeContact((string) $order->customer_email),
            $this->normalizeContact((string) ($order->user?->email ?? '')),
            $this->normalizeContact((string) $order->customer_phone),
            $this->normalizeContact((string) ($order->shippingAddress?->phone ?? '')),
            $this->normalizeContact((string) ($order->address?->phone ?? '')),
        ]);

        return in_array($this->normalizeContact($contact), $needles, true);
    }

    /**
     * Customer-facing payload from our database only. Never calls Delhivery.
     *
     * @return array<string, mixed>
     */
    protected function shipmentFromStoredOrder(Order $order): array
    {
        $events = $order->trackingEvents
            ->sortBy(fn ($event) => optional($event->scanned_at)->timestamp ?? $event->id)
            ->values();

        if ($events->isNotEmpty()) {
            $destination = $order->delhivery_destination ?: trim(implode(', ', array_filter([
                $order->address?->city ?: $order->shippingAddress?->city,
                $order->address?->state ?: $order->shippingAddress?->state,
            ])));

            $mapped = $events->map(fn ($event) => [
                'at' => $event->scanned_at,
                'title' => $event->status,
                'detail' => $event->instructions ?: '',
                'location' => $event->location,
            ])->all();

            return $this->formatShipment([
                'source' => 'database',
                'courier_env' => $this->delhivery->environment(),
                'courier_track_url' => $order->publicCourierTrackUrl(),
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->delhivery_status ?: Order::statusLabel($order->status),
                'courier' => $order->courier_name ?: 'Delhivery',
                'tracking_id' => $order->awb(),
                'expected_delivery' => $order->delhivery_expected_delivery ?: $order->delivery_date,
                'origin' => $order->delhivery_origin ?: 'Ranikhet, Uttarakhand',
                'destination' => $destination !== '' ? $destination : __('On file'),
                'location' => $order->delhivery_location,
                'last_updated' => $order->delhivery_last_synced_at ?: $events->last()->scanned_at,
                'items' => $order->items->map(fn ($item) => [
                    'name' => $item->product_name,
                    'qty' => (int) $item->qty,
                ])->all(),
                'events' => $mapped,
                'current_index' => count($mapped) - 1,
            ]);
        }

        $fallback = $this->shipmentFromOrder($order);
        $fallback['location'] = $order->delhivery_location;
        $fallback['last_updated'] = $order->delhivery_last_synced_at;
        if ($order->delhivery_status) {
            $fallback['status_label'] = $order->delhivery_status;
        }

        return $fallback;
    }

    /**
     * @return array<string, mixed>
     */
    protected function shipmentFromOrder(Order $order): array
    {
        $keys = Order::fulfillmentTimelineKeys();
        $current = array_search($order->status, $keys, true);
        if ($current === false) {
            $current = 0;
        }

        $events = [];
        foreach ($keys as $idx => $key) {
            $at = match ($key) {
                'pending' => $order->created_at,
                'confirmed' => $order->confirmed_at ?: ($idx <= $current ? $order->created_at : null),
                'processing' => $idx <= $current ? ($order->confirmed_at ?: $order->created_at) : null,
                'shipped' => $order->shipped_at,
                'delivered' => $order->delivered_at,
                default => null,
            };

            $events[] = [
                'at' => $at,
                'title' => Order::statusLabel($key),
                'detail' => $this->orderEventDetail($key, $order),
                'location' => $key === 'shipped' || $key === 'delivered'
                    ? ($order->address?->city ?: $order->shippingAddress?->city)
                    : 'Ranikhet, Uttarakhand',
            ];
        }

        if ($order->status === 'cancelled') {
            $events = [[
                'at' => $order->updated_at,
                'title' => __('Order cancelled'),
                'detail' => __('This order is no longer in transit.'),
                'location' => null,
            ]];
            $current = 0;
        }

        $destination = trim(implode(', ', array_filter([
            $order->address?->city ?: $order->shippingAddress?->city,
            $order->address?->state ?: $order->shippingAddress?->state,
        ])));

        return $this->formatShipment([
            'source' => 'order',
            'courier_track_url' => $order->publicCourierTrackUrl(),
            'order_number' => $order->order_number,
            'status' => $order->status,
            'courier' => $order->courier_name ?: __('Standard delivery'),
            'tracking_id' => $order->tracking_id,
            'expected_delivery' => $order->delivery_date,
            'origin' => 'Ranikhet, Uttarakhand',
            'destination' => $destination !== '' ? $destination : __('On file'),
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product_name,
                'qty' => (int) $item->qty,
            ])->all(),
            'events' => $events,
            'current_index' => $current,
        ]);
    }

    protected function orderEventDetail(string $key, Order $order): string
    {
        return match ($key) {
            'pending' => __('Order received and awaiting confirmation.'),
            'confirmed' => __('Payment and order details confirmed.'),
            'processing' => __('Items are being packed at our facility.'),
            'shipped' => $order->tracking_id
                ? __('Handed to :courier. AWB :awb', ['courier' => $order->courier_name ?: __('courier'), 'awb' => $order->tracking_id])
                : __('Handed to the courier partner.'),
            'delivered' => __('Marked delivered. Enjoy your Himalayan staples.'),
            default => '',
        };
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    protected function formatShipment(array $raw): array
    {
        $events = [];
        $current = (int) ($raw['current_index'] ?? 0);
        foreach ($raw['events'] as $idx => $event) {
            $at = $event['at'] ?? null;
            $events[] = [
                'at' => $at instanceof CarbonInterface ? $at : ($at ? Carbon::parse($at) : null),
                'title' => $event['title'],
                'detail' => $event['detail'] ?? '',
                'location' => $event['location'] ?? null,
                'done' => $idx < $current,
                'current' => $idx === $current,
                'pending' => $idx > $current,
            ];
        }

        $expected = $raw['expected_delivery'] ?? null;

        return [
            'source' => $raw['source'],
            'courier_env' => $raw['courier_env'] ?? null,
            'order_number' => $raw['order_number'],
            'status' => $raw['status'],
            'status_label' => $raw['status_label'] ?? Order::statusLabel($raw['status']),
            'courier' => $raw['courier'],
            'tracking_id' => $raw['tracking_id'],
            'courier_track_url' => $raw['courier_track_url'] ?? null,
            'expected_delivery' => $expected instanceof CarbonInterface ? $expected : ($expected ? Carbon::parse($expected) : null),
            'origin' => $raw['origin'],
            'destination' => $raw['destination'],
            'location' => $raw['location'] ?? null,
            'last_updated' => $this->asCarbon($raw['last_updated'] ?? null),
            'items' => $raw['items'],
            'events' => $events,
        ];
    }

    protected function asCarbon($value): ?Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        return $value ? Carbon::parse($value) : null;
    }

    protected function normalizeContact(string $value): string
    {
        $value = Str::lower(trim($value));
        if (str_contains($value, '@')) {
            return $value;
        }

        return preg_replace('/\D+/', '', $value) ?: $value;
    }
}
