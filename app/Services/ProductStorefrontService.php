<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductStorefrontService
{
    public const CACHE_FEATURED = 'home.featured';

    public const CACHE_TRENDING = 'home.trending';

    public const CACHE_NEWEST = 'home.newest';

    /** @var list<string> */
    private const CACHE_KEYS = [
        self::CACHE_FEATURED,
        self::CACHE_TRENDING,
        self::CACHE_NEWEST,
    ];

    public function homeCacheSeconds(): int
    {
        return max(0, (int) config('catalog.home_cache_seconds', 0));
    }

    public function shouldCache(): bool
    {
        return $this->homeCacheSeconds() > 0;
    }

    public function flushHomeCaches(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }

        foreach (['rec.guest'] as $guestKey) {
            Cache::forget($guestKey);
        }
    }

    /**
     * Active products from approved vendors (featured first, then newest).
     */
    public function featuredForHome(int $limit = 12): Collection
    {
        return $this->remember(self::CACHE_FEATURED, function () use ($limit) {
            return $this->storefrontQuery()
                ->orderByDesc('is_featured')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });
    }

    public function newestForHome(int $limit = 12): Collection
    {
        return $this->remember(self::CACHE_NEWEST, function () use ($limit) {
            return $this->storefrontQuery()
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });
    }

    public function trendingForHome(int $limit = 12): Collection
    {
        return $this->remember(self::CACHE_TRENDING, function () use ($limit) {
            return $this->storefrontQuery()
                ->orderByDesc('sales_count')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
        });
    }

    protected function storefrontQuery(): Builder
    {
        return Product::query()
            ->with(['images', 'vendor', 'variants', 'flashSale', 'menuItem'])
            ->where('is_active', true)
            ->whereHas('vendor', fn ($q) => $q->where('status', 'approved'));
    }

    /**
     * @param  callable(): Collection  $loader
     */
    protected function remember(string $key, callable $loader): Collection
    {
        if (! $this->shouldCache()) {
            return $loader();
        }

        return Cache::remember($key, $this->homeCacheSeconds(), $loader);
    }
}
