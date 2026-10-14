<?php

namespace Tests\Unit\Coupon;

use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CouponDiscountTest extends TestCase
{
    protected CouponService $coupons;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coupons = new CouponService();
    }

    public function test_percent_discount_is_capped_by_max_discount(): void
    {
        $coupon = new Coupon([
            'type' => 'percent',
            'value' => 50,
            'max_discount' => 80,
        ]);

        $this->assertSame(80.0, $this->coupons->calculateDiscount($coupon, 1000));
    }

    public function test_discount_never_exceeds_eligible_amount(): void
    {
        $percent = new Coupon(['type' => 'percent', 'value' => 100]);
        $fixed = new Coupon(['type' => 'fixed', 'value' => 500]);

        $this->assertSame(40.0, $this->coupons->calculateDiscount($percent, 40));
        $this->assertSame(40.0, $this->coupons->calculateDiscount($fixed, 40));
    }

    public function test_inactive_coupon_is_rejected(): void
    {
        $coupon = new Coupon([
            'is_active' => false,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'min_cart' => 0,
        ]);

        $this->expectException(ValidationException::class);
        $this->coupons->assertCouponUsable($coupon, 200);
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $coupon = new Coupon([
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->subDay(),
            'min_cart' => 0,
        ]);

        try {
            $this->coupons->assertCouponUsable($coupon, 200);
            $this->fail('Expected expiry validation to fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('expired', strtolower(collect($e->errors())->flatten()->first()));
        }
    }

    public function test_minimum_order_amount_is_enforced(): void
    {
        $coupon = new Coupon([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'min_cart' => 500,
        ]);

        try {
            $this->coupons->assertCouponUsable($coupon, 100);
            $this->fail('Expected minimum order validation to fail.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('at least', strtolower(collect($e->errors())->flatten()->first()));
        }
    }

    public function test_global_limit_uses_used_count_on_unsaved_coupon(): void
    {
        $coupon = new Coupon([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'min_cart' => 0,
            'usage_limit' => 1,
            'used_count' => 1,
        ]);

        $this->expectException(ValidationException::class);
        $this->coupons->assertCouponUsable($coupon, 200);
    }

    public function test_applying_does_not_change_used_count(): void
    {
        $coupon = new Coupon([
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDay(),
            'min_cart' => 0,
            'type' => 'fixed',
            'value' => 50,
            'used_count' => 0,
        ]);

        $this->coupons->assertCouponUsable($coupon, 200);
        $this->assertSame(0, (int) $coupon->used_count);
        $this->assertSame(50.0, $this->coupons->calculateDiscount($coupon, 200));
    }
}
