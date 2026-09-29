<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTrackingEvent;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class DelhiveryTrackingSyncService
{
    public function __construct(protected DelhiveryService $delhivery)
    {
    }

    /**
     * Persist a Delhivery webhook payload. Unknown AWBs are ignored (200 to Delhivery).
     *
     * @param  array<string, mixed>  $payload
     */
    public function applyWebhook(array $payload): bool
    {
        $shipment = $this->delhivery->extractShipment($payload);
        if (! $shipment) {
            Log::info('Delhivery webhook ignored: no shipment object.');

            return false;
        }

        $order = $this->findOrderForShipment($shipment);
        if (! $order) {
            Log::info('Delhivery webhook ignored: no matching order.', [
                'awb' => $shipment['AWB'] ?? null,
                'reference' => $shipment['ReferenceNo'] ?? null,
            ]);

            return false;
        }

        return $this->applyShipment($order, $shipment, 'webhook');
    }

    public function pollOrder(Order $order, bool $force = false): bool
    {
        $awb = $order->awb();
        if (! $awb) {
            return false;
        }

        if (! $force && ! $order->needsDelhiveryPoll()) {
            return false;
        }

        $order->forceFill(['delhivery_last_attempt_at' => now()])->save();

        if (! $this->delhivery->enabled()) {
            return false;
        }

        $result = $this->delhivery->fetchShipmentByWaybill($awb);
        if (! $result['ok'] || ! $result['shipment']) {
            return false;
        }

        return $this->applyShipment($order->fresh() ?: $order, $result['shipment'], 'poll');
    }

    /**
     * @return Collection<int, Order>
     */
    public function staleInTransitOrders(int $limit = 40): Collection
    {
        return Order::query()
            ->whereNotNull('tracking_id')
            ->where('tracking_id', '!=', '')
            ->whereNotIn('status', ['delivered', 'cancelled', 'returned'])
            ->where(function ($query) {
                $query->whereNull('delhivery_last_attempt_at')
                    ->orWhere('delhivery_last_attempt_at', '<=', now()->subHours(2));
            })
            ->orderBy('delhivery_last_attempt_at')
            ->limit($limit)
            ->get();
    }

    public function resetStoredTracking(Order $order): void
    {
        $order->trackingEvents()->delete();
        $order->forceFill([
            'delhivery_status' => null,
            'delhivery_status_type' => null,
            'delhivery_location' => null,
            'delhivery_origin' => null,
            'delhivery_destination' => null,
            'delhivery_expected_delivery' => null,
            'delhivery_last_synced_at' => null,
            'delhivery_last_attempt_at' => null,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $shipment
     */
    public function applyShipment(Order $order, array $shipment, string $source): bool
    {
        $status = is_array($shipment['Status'] ?? null) ? $shipment['Status'] : [];
        $statusName = trim((string) ($status['Status'] ?? ''));
        $statusType = strtoupper(trim((string) ($status['StatusType'] ?? '')));
        $location = trim((string) ($status['StatusLocation'] ?? ''));
        $awb = trim((string) ($shipment['AWB'] ?? $order->awb() ?? ''));

        $expected = $shipment['ExpectedDeliveryDate'] ?? ($shipment['PromisedDeliveryDate'] ?? null);
        $expectedAt = null;
        if ($expected) {
            try {
                $expectedAt = Carbon::parse((string) $expected);
            } catch (\Throwable $e) {
                $expectedAt = null;
            }
        }

        $order->forceFill([
            'delhivery_status' => $statusName !== '' ? $statusName : $order->delhivery_status,
            'delhivery_status_type' => $statusType !== '' ? $statusType : $order->delhivery_status_type,
            'delhivery_location' => $location !== '' ? $location : $order->delhivery_location,
            'delhivery_origin' => trim((string) ($shipment['Origin'] ?? '')) ?: $order->delhivery_origin,
            'delhivery_destination' => trim((string) ($shipment['Destination'] ?? '')) ?: $order->delhivery_destination,
            'delhivery_expected_delivery' => $expectedAt ?: $order->delhivery_expected_delivery,
            'delhivery_last_synced_at' => now(),
            'delhivery_last_attempt_at' => now(),
        ]);

        if ($awb !== '' && trim((string) $order->tracking_id) === '') {
            $order->tracking_id = $awb;
        }
        if (! $order->courier_name) {
            $order->courier_name = 'Delhivery';
        }

        $this->syncOrderStatus($order, $statusType, $statusName);
        $order->save();

        $this->storeEvents($order, $shipment, $source, $awb);

        return true;
    }

    /**
     * @param  array<string, mixed>  $shipment
     */
    protected function findOrderForShipment(array $shipment): ?Order
    {
        $awb = trim((string) ($shipment['AWB'] ?? ''));
        $reference = trim((string) ($shipment['ReferenceNo'] ?? ''));

        if ($awb !== '') {
            $order = Order::query()->where('tracking_id', $awb)->first();
            if ($order) {
                return $order;
            }
        }

        if ($reference !== '') {
            return Order::query()->where('order_number', $reference)->first();
        }

        return null;
    }

    protected function syncOrderStatus(Order $order, string $statusType, string $statusName): void
    {
        if (in_array($order->status, ['cancelled', 'returned'], true)) {
            return;
        }

        $mapped = $this->delhivery->mapStatus($statusType, $statusName);
        $rank = [
            'pending' => 1,
            'confirmed' => 2,
            'processing' => 3,
            'shipped' => 4,
            'delivered' => 5,
            'returned' => 6,
            'cancelled' => 6,
        ];

        $current = $rank[$order->status] ?? 0;
        $next = $rank[$mapped] ?? 0;
        if ($next <= $current) {
            return;
        }

        $order->status = $mapped;
        if ($mapped === 'shipped' && ! $order->shipped_at) {
            $order->shipped_at = now();
        }
        if ($mapped === 'delivered' && ! $order->delivered_at) {
            $order->delivered_at = now();
            $order->delivery_date = now()->toDateString();
        }
    }

    /**
     * @param  array<string, mixed>  $shipment
     */
    protected function storeEvents(Order $order, array $shipment, string $source, string $awb): void
    {
        $rows = [];
        foreach ($shipment['Scans'] ?? [] as $row) {
            $detail = is_array($row['ScanDetail'] ?? null) ? $row['ScanDetail'] : (is_array($row) ? $row : []);
            $title = trim((string) ($detail['Scan'] ?? ($detail['ScanType'] ?? '')));
            if ($title === '') {
                continue;
            }
            $rows[] = [
                'status' => $title,
                'status_type' => strtoupper(trim((string) ($detail['ScanType'] ?? ''))),
                'location' => trim((string) ($detail['ScannedLocation'] ?? '')),
                'instructions' => trim((string) ($detail['Instructions'] ?? '')),
                'scanned_at' => $detail['ScanDateTime'] ?? ($detail['StatusDateTime'] ?? null),
            ];
        }

        if ($rows === []) {
            $status = is_array($shipment['Status'] ?? null) ? $shipment['Status'] : [];
            $title = trim((string) ($status['Status'] ?? ''));
            if ($title !== '') {
                $rows[] = [
                    'status' => $title,
                    'status_type' => strtoupper(trim((string) ($status['StatusType'] ?? ''))),
                    'location' => trim((string) ($status['StatusLocation'] ?? '')),
                    'instructions' => trim((string) ($status['Instructions'] ?? '')),
                    'scanned_at' => $status['StatusDateTime'] ?? null,
                ];
            }
        }

        foreach ($rows as $row) {
            $scannedAt = null;
            if (! empty($row['scanned_at'])) {
                try {
                    $scannedAt = Carbon::parse((string) $row['scanned_at']);
                } catch (\Throwable $e) {
                    $scannedAt = null;
                }
            }

            $fingerprint = sha1(implode('|', [
                $order->id,
                $awb,
                $row['status'],
                $row['location'],
                $scannedAt ? $scannedAt->toIso8601String() : '',
            ]));

            OrderTrackingEvent::query()->firstOrCreate(
                [
                    'order_id' => $order->id,
                    'fingerprint' => $fingerprint,
                ],
                [
                    'awb' => $awb !== '' ? $awb : null,
                    'status' => $row['status'],
                    'status_type' => $row['status_type'] !== '' ? $row['status_type'] : null,
                    'location' => $row['location'] !== '' ? $row['location'] : null,
                    'instructions' => $row['instructions'] !== '' ? $row['instructions'] : null,
                    'scanned_at' => $scannedAt,
                    'source' => $source,
                ]
            );
        }
    }
}
