<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ShippingSetting extends Model
{
    protected $fillable = [
        'free_shipping_amount',
        'shipping_charge',
    ];

    protected $casts = [
        'free_shipping_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
    ];

    public static function current(): self
    {
        return Cache::remember('shipping_settings.current', 3600, function () {
            return static::query()->firstOrCreate([], [
                'free_shipping_amount' => 500,
                'shipping_charge' => 50,
            ]);
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('shipping_settings.current');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }
}
