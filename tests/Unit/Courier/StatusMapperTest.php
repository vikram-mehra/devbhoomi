<?php

namespace Tests\Unit\Courier;

use App\Enums\ShipmentStatus;
use App\Services\Courier\Support\StatusMapper;
use PHPUnit\Framework\TestCase;

class StatusMapperTest extends TestCase
{
    public function test_delhivery_maps_dispatched_to_in_transit(): void
    {
        $this->assertSame(ShipmentStatus::IN_TRANSIT, StatusMapper::delhivery('Dispatched'));
    }

    public function test_delhivery_maps_delivered(): void
    {
        $this->assertSame(ShipmentStatus::DELIVERED, StatusMapper::delhivery('Delivered', 'DL'));
    }

    public function test_bluedart_maps_in_transit(): void
    {
        $this->assertSame(ShipmentStatus::IN_TRANSIT, StatusMapper::bluedart('SHIPMENT IN TRANSIT'));
    }

    public function test_bluedart_maps_delivered(): void
    {
        $this->assertSame(ShipmentStatus::DELIVERED, StatusMapper::bluedart('SHIPMENT DELIVERED'));
    }
}
