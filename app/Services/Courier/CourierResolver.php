<?php

namespace App\Services\Courier;

use App\Services\Courier\Contracts\CourierAdapterInterface;
use App\Services\Courier\Exceptions\CourierNotSupportedException;

class CourierResolver
{
    public function resolve(string $driver): CourierAdapterInterface
    {
        $key = strtolower(trim($driver));
        $partners = config('couriers.partners', []);

        if (! isset($partners[$key]) || ! is_array($partners[$key])) {
            throw new CourierNotSupportedException('Courier ['.$driver.'] is not configured or supported.');
        }

        $config = $this->hydrate($key, $partners[$key]);
        $adapterClass = $config['adapter'] ?? null;

        if (! is_string($adapterClass) || ! class_exists($adapterClass)) {
            throw new CourierNotSupportedException('Courier ['.$driver.'] is not configured or supported.');
        }

        $adapter = app()->make($adapterClass, ['config' => $config]);

        if (! $adapter instanceof CourierAdapterInterface) {
            throw new CourierNotSupportedException('Courier ['.$driver.'] adapter is invalid.');
        }

        return $adapter;
    }

    public function isConfigured(string $driver): bool
    {
        return isset(config('couriers.partners', [])[strtolower(trim($driver))]);
    }

    public function isEnabled(string $driver): bool
    {
        $key = strtolower(trim($driver));
        $config = config('couriers.partners.'.$key);

        return is_array($config) && ! empty($config['enabled']);
    }

    /**
     * Empty .env keys must not wipe tokens/URLs already used by tracking.
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function hydrate(string $key, array $config): array
    {
        if ($key === 'delhivery') {
            $config['api_token'] = trim((string) (
                ($config['api_token'] ?? '')
                ?: config('tracking.delhivery.token')
                ?: config('services.delhivery.token')
            ));
            $config['api_url'] = rtrim((string) (
                ($config['api_url'] ?? '')
                ?: config('tracking.delhivery.base_url')
                ?: config('services.delhivery.base_url')
                ?: 'https://staging-express.delhivery.com'
            ), '/');
            $config['warehouse'] = trim((string) (
                ($config['warehouse'] ?? '')
                ?: env('DELHIVERY_WAREHOUSE')
                ?: env('COURIER_PICKUP_NAME')
            ));
            $config['client'] = trim((string) ($config['client'] ?? ''));
        }

        return $config;
    }
}
