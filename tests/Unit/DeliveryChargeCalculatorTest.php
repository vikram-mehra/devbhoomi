<?php

namespace Tests\Unit;

use App\Services\DeliveryChargeCalculator;
use Tests\TestCase;

class DeliveryChargeCalculatorTest extends TestCase
{
    protected DeliveryChargeCalculator $calc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calc = new DeliveryChargeCalculator();
    }

    public function test_zero_weight_is_one_base_slab(): void
    {
        $this->assertSame(1, $this->calc->slabs(0));
        $this->assertSame(60.0, $this->calc->charge(0, 60));
    }

    public function test_exact_base_weight_is_one_slab(): void
    {
        $this->assertSame(1, $this->calc->slabs(500));
        $this->assertSame(60.0, $this->calc->charge(500, 60));
    }

    public function test_weight_just_over_base_adds_a_slab(): void
    {
        $this->assertSame(2, $this->calc->slabs(501));
        $this->assertSame(120.0, $this->calc->charge(501, 60));
    }

    public function test_multiple_slabs_for_heavier_cart(): void
    {
        $this->assertSame(3, $this->calc->slabs(1200));
        $this->assertSame(180.0, $this->calc->charge(1200, 60));
    }

    public function test_additional_slab_rate_can_differ(): void
    {
        $this->assertSame(140.0, $this->calc->charge(1200, 60, 40));
    }

    public function test_custom_base_weight_slab(): void
    {
        $this->assertSame(2, $this->calc->slabs(1000, 500));
        $this->assertSame(1, $this->calc->slabs(999, 1000));
        $this->assertSame(90.0, $this->calc->charge(999, 90, null, 1000));
    }
}
