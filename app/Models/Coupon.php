<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PUBLIC = 'public';

    public const TYPE_INTERNAL = 'internal';

    protected $fillable = [
        'code', 'coupon_type', 'type', 'value', 'min_cart', 'max_discount', 'starts_at', 'ends_at',
        'usage_limit', 'per_user_limit', 'used_count', 'is_active', 'first_order_only',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_cart' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'first_order_only' => 'boolean',
        'usage_limit' => 'integer',
        'per_user_limit' => 'integer',
        'used_count' => 'integer',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'coupon_product');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'coupon_menu_item');
    }

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

    public function adminStatusKey(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }
        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }
        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }
        if ($this->usage_limit !== null && (int) $this->used_count >= (int) $this->usage_limit) {
            return 'exhausted';
        }

        return 'active';
    }

    public function adminStatusLabel(): string
    {
        return match ($this->adminStatusKey()) {
            'inactive' => __('Inactive'),
            'expired' => __('Expired'),
            'scheduled' => __('Scheduled'),
            'exhausted' => __('Limit reached'),
            default => __('Active'),
        };
    }

    public function remainingUses(): ?int
    {
        if ($this->usage_limit === null) {
            return null;
        }

        $held = (int) $this->used_count + (int) ($this->reserved_count ?? 0);

        return max(0, (int) $this->usage_limit - $held);
    }

    public function hasProductRestrictions(): bool
    {
        if ($this->relationLoaded('products') && $this->relationLoaded('menuItems')) {
            return $this->products->isNotEmpty() || $this->menuItems->isNotEmpty();
        }

        if (! $this->exists) {
            return false;
        }

        return $this->products()->exists() || $this->menuItems()->exists();
    }
}
