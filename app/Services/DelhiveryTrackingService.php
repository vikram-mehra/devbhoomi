<?php

namespace App\Services;

/**
 * Backward-compatible wrapper around DelhiveryService.
 * New code should use DelhiveryService / DelhiveryTrackingSyncService.
 */
class DelhiveryTrackingService
{
    public function __construct(protected DelhiveryService $delhivery)
    {
    }

    public function environment(): string
    {
        return $this->delhivery->environment();
    }

    public function isTest(): bool
    {
        return $this->delhivery->isTest();
    }

    public function baseUrl(): string
    {
        return $this->delhivery->baseUrl();
    }

    public function token(): string
    {
        return $this->delhivery->token();
    }

    public function enabled(): bool
    {
        return $this->delhivery->enabled();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function trackByWaybill(string $waybill): ?array
    {
        $result = $this->delhivery->fetchShipmentByWaybill($waybill);

        return $result['ok'] ? $result['shipment'] : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function trackByReference(string $reference): ?array
    {
        $result = $this->delhivery->fetchShipmentByReference($reference);

        return $result['ok'] ? $result['shipment'] : null;
    }

    /**
     * @param  array<string, mixed>  $shipment
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function toShipmentPayload(array $shipment, array $context = []): array
    {
        $status = is_array($shipment['Status'] ?? null) ? $shipment['Status'] : [];
        $statusName = trim((string) ($status['Status'] ?? ''));
        $statusType = strtoupper(trim((string) ($status['StatusType'] ?? '')));
        $consignee = is_array($shipment['Consignee'] ?? null) ? $shipment['Consignee'] : [];

        $events = [];
        foreach ($shipment['Scans'] ?? [] as $row) {
            $detail = is_array($row['ScanDetail'] ?? null) ? $row['ScanDetail'] : (is_array($row) ? $row : []);
            $title = trim((string) ($detail['Scan'] ?? ($detail['ScanType'] ?? '')));
            if ($title === '') {
                continue;
            }
            $events[] = [
                'at' => $detail['ScanDateTime'] ?? ($detail['StatusDateTime'] ?? null),
                'title' => $title,
                'detail' => (string) ($detail['Instructions'] ?? ''),
                'location' => $detail['ScannedLocation'] ?? null,
            ];
        }

        if ($events === [] && $statusName !== '') {
            $events[] = [
                'at' => $status['StatusDateTime'] ?? ($shipment['PickUpDate'] ?? $shipment['PickedupDate'] ?? null),
                'title' => $statusName,
                'detail' => (string) ($status['Instructions'] ?? ''),
                'location' => $status['StatusLocation'] ?? null,
            ];
        }

        $destination = trim((string) ($shipment['Destination'] ?? ''));
        if ($destination === '') {
            $destination = trim(implode(', ', array_filter([
                $consignee['City'] ?? null,
                $consignee['State'] ?? null,
            ])));
        }

        return [
            'source' => 'delhivery',
            'courier_env' => $this->environment(),
            'order_number' => $context['order_number'] ?? ($shipment['ReferenceNo'] ?? ($shipment['AWB'] ?? '')),
            'status' => $this->delhivery->mapStatus($statusType, $statusName),
            'status_label' => $statusName !== '' ? $statusName : __('In transit'),
            'courier' => 'Delhivery',
            'tracking_id' => $shipment['AWB'] ?? ($context['tracking_id'] ?? null),
            'expected_delivery' => $shipment['ExpectedDeliveryDate'] ?? ($shipment['PromisedDeliveryDate'] ?? ($context['expected_delivery'] ?? null)),
            'origin' => $shipment['Origin'] ?? ($context['origin'] ?? 'Ranikhet, Uttarakhand'),
            'destination' => $destination !== '' ? $destination : ($context['destination'] ?? __('On file')),
            'items' => $context['items'] ?? [],
            'events' => $events,
            'current_index' => $events !== [] ? count($events) - 1 : 0,
        ];
    }
}
