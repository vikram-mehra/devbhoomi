<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    public const TYPE_PUBLIC = 'public';

    public const TYPE_INTERNAL = 'internal';

    protected $fillable = [
        'code', 'coupon_type', 'type', 'value', 'min_cart', 'max_discount', 'starts_at', 'ends_at',
        'usage_limit', 'used_count', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_cart' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('coupon_type', self::TYPE_PUBLIC);
    }

    public function scopeInternal(Builder $query): Builder
    {
        return $query->where('coupon_type', self::TYPE_INTERNAL);
    }

    public function scopeActiveValid(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            });
    }

    public function isPublic(): bool
    {
        return $this->coupon_type === self::TYPE_PUBLIC;
    }

    public function isInternal(): bool
    {
        return $this->coupon_type === self::TYPE_INTERNAL;
    }

    public function discountLabel(): string
    {
        if ($this->type === 'percent') {
            return rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.').'% off';
        }

        return '₹'.number_format((float) $this->value, 2).' off';
    }
}
