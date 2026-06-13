<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function normalizeCode(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }
        $s = trim($raw);
        if ($s === '') {
            return '';
        }

        return strtoupper(trim(ltrim($s, '#')));
    }

    /**
     * @return array{coupon: Coupon, discount: float}
     */
    public function resolveForCart(string $rawCode, float $subtotal): array
    {
        $normalized = $this->normalizeCode($rawCode);
        if ($normalized === '') {
            throw ValidationException::withMessages([
                'coupon_code' => __('Please enter a coupon code.'),
            ]);
        }

        $coupon = Coupon::query()
            ->whereRaw('UPPER(code) = ?', [$normalized])
            ->first();

        if (! $coupon) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon code is not valid.'),
            ]);
        }

        $this->assertCouponUsable($coupon, $subtotal);

        return [
            'coupon' => $coupon,
            'discount' => $this->calculateDiscount($coupon, $subtotal),
        ];
    }

    public function assertCouponUsable(Coupon $coupon, float $subtotal): void
    {
        if (! $coupon->is_active) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon is no longer active.'),
            ]);
        }

        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon is not valid yet.'),
            ]);
        }

        if ($coupon->ends_at && $coupon->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon has expired.'),
            ]);
        }

        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw ValidationException::withMessages([
                'coupon_code' => __('This coupon has reached its usage limit.'),
            ]);
        }

        if ((float) $coupon->min_cart > $subtotal) {
            throw ValidationException::withMessages([
                'coupon_code' => __('Your cart must be at least ₹:amount to use this coupon.', [
                    'amount' => number_format((float) $coupon->min_cart, 0),
                ]),
            ]);
        }
    }

    public function calculateDiscount(Coupon $coupon, float $subtotal): float
    {
        if ($coupon->type === 'percent') {
            $discount = round($subtotal * ((float) $coupon->value / 100), 2);
            if ($coupon->max_discount) {
                $discount = min($discount, (float) $coupon->max_discount);
            }

            return $discount;
        }

        return round(min((float) $coupon->value, $subtotal), 2);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicPayload(Coupon $coupon): array
    {
        return [
            'code' => $coupon->code,
            'coupon_type' => $coupon->coupon_type,
            'discount_type' => $coupon->type,
            'value' => (float) $coupon->value,
            'discount_label' => $coupon->discountLabel(),
            'min_cart' => (float) $coupon->min_cart,
            'max_discount' => $coupon->max_discount ? (float) $coupon->max_discount : null,
            'starts_at' => optional($coupon->starts_at)?->toIso8601String(),
            'ends_at' => optional($coupon->ends_at)?->toIso8601String(),
            'usage_limit' => $coupon->usage_limit,
            'used_count' => $coupon->used_count,
        ];
    }

    public function markUsed(?string $couponCode): void
    {
        if (! $couponCode) {
            return;
        }

        Coupon::query()->where('code', $couponCode)->increment('used_count');
    }

    public function releaseUsage(?string $couponCode): void
    {
        if (! $couponCode) {
            return;
        }

        Coupon::query()
            ->where('code', $couponCode)
            ->where('used_count', '>', 0)
            ->decrement('used_count');
    }
}
