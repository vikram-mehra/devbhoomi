<?php

namespace App\Providers;

use App\Services\Courier\CourierLogger;
use App\Services\Courier\CourierManager;
use App\Services\Courier\CourierResolver;
use App\Services\Courier\Support\ShipmentRequestFactory;
use Illuminate\Support\ServiceProvider;

class CourierServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CourierResolver::class);
        $this->app->singleton(ShipmentRequestFactory::class);
        $this->app->singleton(CourierLogger::class);
        $this->app->singleton(CourierManager::class);
    }
}
