<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    public const STATUS_RESERVED = 'reserved';

    public const STATUS_REDEEMED = 'redeemed';

    public const STATUS_RELEASED = 'released';

    protected $fillable = [
        'coupon_id', 'user_id', 'order_id', 'sale_id', 'guest_key', 'status',
        'discount_amount', 'reserved_at', 'redeemed_at', 'released_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'reserved_at' => 'datetime',
        'redeemed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isReserved(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isRedeemed(): bool
    {
        return $this->status === self::STATUS_REDEEMED;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_RESERVED => __('Reserved'),
            self::STATUS_REDEEMED => __('Redeemed'),
            self::STATUS_RELEASED => __('Released'),
            default => ucfirst((string) $this->status),
        };
    }
}
