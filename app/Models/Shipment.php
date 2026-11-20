<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'order_id',
        'courier_partner',
        'courier_service',
        'awb',
        'tracking_number',
        'shipment_id',
        'status',
        'label_url',
        'manifest_url',
        'pickup_scheduled_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'shipping_cost',
        'cod_amount',
        'weight',
        'length',
        'width',
        'height',
        'request_reference',
        'api_request_reference',
        'api_response_reference',
        'failure_reason',
        'raw_response',
    ];

    protected $casts = [
        'pickup_scheduled_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'shipping_cost' => 'decimal:2',
        'cod_amount' => 'decimal:2',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(ShipmentTrackingEvent::class)->orderBy('event_time')->orderBy('id');
    }

    public function isActive(): bool
    {
        return ShipmentStatus::isActive($this->status);
    }

    public function partnerLabel(): string
    {
        $name = config('couriers.partners.'.$this->courier_partner.'.name');

        return is_string($name) && $name !== '' ? $name : (string) $this->courier_partner;
    }

    public function statusLabel(): string
    {
        return ShipmentStatus::label($this->status);
    }

    public function trackingNumber(): ?string
    {
        $awb = trim((string) ($this->awb ?: $this->tracking_number));

        return $awb !== '' ? $awb : null;
    }
}
