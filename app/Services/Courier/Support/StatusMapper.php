<?php

namespace App\Services\Courier\Support;

use App\Enums\ShipmentStatus;

class StatusMapper
{
    public static function delhivery(string $statusName, ?string $statusType = null): string
    {
        $name = strtolower(trim($statusName));
        $type = strtoupper(trim((string) $statusType));

        if ($type === 'DL' && ! str_contains($name, 'rto')) {
            return ShipmentStatus::DELIVERED;
        }
        if ($type === 'RT' || str_contains($name, 'rto') || str_contains($name, 'return')) {
            return ShipmentStatus::RTO;
        }
        if ($type === 'CN' || str_contains($name, 'cancel')) {
            return ShipmentStatus::CANCELLED;
        }
        if (str_contains($name, 'out for delivery') || str_contains($name, 'ofd')) {
            return ShipmentStatus::OUT_FOR_DELIVERY;
        }
        if (str_contains($name, 'picked') || $type === 'UD') {
            return str_contains($name, 'not picked') ? ShipmentStatus::MANIFESTED : ShipmentStatus::PICKED_UP;
        }
        if (str_contains($name, 'dispatched') || str_contains($name, 'transit') || str_contains($name, 'in transit')) {
            return ShipmentStatus::IN_TRANSIT;
        }
        if (str_contains($name, 'manifest') || str_contains($name, 'scheduled')) {
            return str_contains($name, 'pickup') ? ShipmentStatus::PICKUP_SCHEDULED : ShipmentStatus::MANIFESTED;
        }
        if (str_contains($name, 'fail')) {
            return ShipmentStatus::FAILED;
        }

        return $name !== '' ? ShipmentStatus::IN_TRANSIT : ShipmentStatus::CREATED;
    }

    public static function bluedart(string $statusName, ?string $statusType = null): string
    {
        $name = strtoupper(trim($statusName.' '.$statusType));

        if (str_contains($name, 'DELIVERED') && ! str_contains($name, 'UNDELIVERED')) {
            return ShipmentStatus::DELIVERED;
        }
        if (str_contains($name, 'RTO') || str_contains($name, 'RETURN')) {
            return ShipmentStatus::RTO;
        }
        if (str_contains($name, 'CANCEL')) {
            return ShipmentStatus::CANCELLED;
        }
        if (str_contains($name, 'OUT FOR DELIVERY') || str_contains($name, 'OFD')) {
            return ShipmentStatus::OUT_FOR_DELIVERY;
        }
        if (str_contains($name, 'SHIPMENT IN TRANSIT') || str_contains($name, 'IN TRANSIT') || str_contains($name, 'TRANSIT')) {
            return ShipmentStatus::IN_TRANSIT;
        }
        if (str_contains($name, 'PICKED') || str_contains($name, 'PICKUP')) {
            return ShipmentStatus::PICKED_UP;
        }
        if (str_contains($name, 'MANIFEST')) {
            return ShipmentStatus::MANIFESTED;
        }

        return $name !== '' ? ShipmentStatus::IN_TRANSIT : ShipmentStatus::CREATED;
    }
}
