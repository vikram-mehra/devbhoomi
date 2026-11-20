<?php

namespace App\Enums;

class ShipmentStatus
{
    public const CREATED = 'created';
    public const MANIFESTED = 'manifested';
    public const PICKUP_SCHEDULED = 'pickup_scheduled';
    public const PICKED_UP = 'picked_up';
    public const IN_TRANSIT = 'in_transit';
    public const OUT_FOR_DELIVERY = 'out_for_delivery';
    public const DELIVERED = 'delivered';
    public const CANCELLED = 'cancelled';
    public const RTO = 'rto';
    public const FAILED = 'failed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return [
            self::CREATED,
            self::MANIFESTED,
            self::PICKUP_SCHEDULED,
            self::PICKED_UP,
            self::IN_TRANSIT,
            self::OUT_FOR_DELIVERY,
            self::DELIVERED,
            self::CANCELLED,
            self::RTO,
            self::FAILED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function inactive(): array
    {
        return [self::CANCELLED, self::FAILED];
    }

    public static function isActive(?string $status): bool
    {
        return $status !== null && $status !== '' && ! in_array($status, self::inactive(), true);
    }

    public static function label(?string $status): string
    {
        $labels = [
            self::CREATED => 'Created',
            self::MANIFESTED => 'Manifested',
            self::PICKUP_SCHEDULED => 'Pickup scheduled',
            self::PICKED_UP => 'Picked up',
            self::IN_TRANSIT => 'In transit',
            self::OUT_FOR_DELIVERY => 'Out for delivery',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
            self::RTO => 'RTO',
            self::FAILED => 'Failed',
        ];

        return $labels[$status] ?? (string) $status;
    }
}
