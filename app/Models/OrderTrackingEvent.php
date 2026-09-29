<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTrackingEvent extends Model
{
    protected $fillable = [
        'order_id',
        'awb',
        'status',
        'status_type',
        'location',
        'instructions',
        'scanned_at',
        'source',
        'fingerprint',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
