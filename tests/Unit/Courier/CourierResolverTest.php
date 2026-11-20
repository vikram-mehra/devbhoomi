<?php

namespace Tests\Unit\Courier;

use App\Services\Courier\Adapters\BlueDartAdapter;
use App\Services\Courier\Adapters\DelhiveryAdapter;
use App\Services\Courier\CourierResolver;
use App\Services\Courier\Exceptions\CourierNotSupportedException;
use Tests\TestCase;

class CourierResolverTest extends TestCase
{
    public function test_resolves_registered_adapters(): void
    {
        $resolver = $this->app->make(CourierResolver::class);

        $this->assertInstanceOf(DelhiveryAdapter::class, $resolver->resolve('delhivery'));
        $this->assertInstanceOf(BlueDartAdapter::class, $resolver->resolve('bluedart'));
    }

    public function test_unknown_courier_throws(): void
    {
        $this->expectException(CourierNotSupportedException::class);
        $this->expectExceptionMessage('Courier [xyz] is not configured or supported.');

        $this->app->make(CourierResolver::class)->resolve('xyz');
    }

    public function test_enabled_flag_comes_from_config(): void
    {
        config(['couriers.partners.delhivery.enabled' => true]);
        config(['couriers.partners.bluedart.enabled' => false]);

        $resolver = $this->app->make(CourierResolver::class);

        $this->assertTrue($resolver->isEnabled('delhivery'));
        $this->assertFalse($resolver->isEnabled('bluedart'));
    }

    public function test_hydrate_uses_tracking_token_when_courier_token_is_empty(): void
    {
        config([
            'couriers.partners.delhivery.api_token' => '',
            'couriers.partners.delhivery.api_url' => '',
            'tracking.delhivery.token' => 'existing-tracking-token',
            'tracking.delhivery.base_url' => 'https://staging-express.delhivery.com',
        ]);

        $hydrated = $this->app->make(CourierResolver::class)->hydrate('delhivery', config('couriers.partners.delhivery'));

        $this->assertSame('existing-tracking-token', $hydrated['api_token']);
        $this->assertSame('https://staging-express.delhivery.com', $hydrated['api_url']);
    }
}
