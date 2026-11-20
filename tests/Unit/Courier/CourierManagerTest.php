<?php

namespace Tests\Unit\Courier;

use App\Services\Courier\CourierManager;
use App\Services\Courier\Exceptions\CourierNotSupportedException;
use Tests\TestCase;

class CourierManagerTest extends TestCase
{
    public function test_available_drivers_hide_disabled_partners(): void
    {
        config([
            'couriers.partners.delhivery.enabled' => true,
            'couriers.partners.delhivery.name' => 'Delhivery',
            'couriers.partners.bluedart.enabled' => false,
            'couriers.partners.bluedart.name' => 'Blue Dart',
        ]);

        $drivers = $this->app->make(CourierManager::class)->availableDrivers();

        $this->assertSame(['delhivery' => 'Delhivery'], $drivers);
        $this->assertArrayNotHasKey('bluedart', $drivers);
    }

    public function test_driver_rejects_disabled_courier(): void
    {
        config(['couriers.partners.bluedart.enabled' => false]);

        $this->expectException(CourierNotSupportedException::class);
        $this->expectExceptionMessage('Courier [bluedart] is disabled.');

        $this->app->make(CourierManager::class)->driver('bluedart');
    }
}
