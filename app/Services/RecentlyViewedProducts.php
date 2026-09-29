<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

class RecentlyViewedProducts
{
    public const COOKIE = 'zm_recent_views';

    public const MAX_IDS = 24;

    /**
     * @return list<int>
     */
    public function idsFromRequest(?Request $request = null): array
    {
        $request = $request ?? request();
        $raw = $request->cookie(self::COOKIE);
        if ($raw === null || $raw === '') {
            return [];
        }
        $decoded = json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_filter(array_map('intval', $decoded), fn (int $id): bool => $id > 0));
    }

    public function push(Product $product): void
    {
        $ids = $this->idsFromRequest();
        $ids = array_values(array_unique(array_merge([(int) $product->id], $ids)));
        $ids = array_slice($ids, 0, self::MAX_IDS);
        Cookie::queue(
            self::COOKIE,
            json_encode($ids),
            60 * 24 * 90
        );
    }

    /**
     * Recently viewed products, most recent first (for home “Suggested for you”).
     *
     * @return Collection<int, Product>
     */
    public function productsForHome(Request $request, int $limit = 12): Collection
    {
        $ids = $this->idsFromRequest($request);
        if ($ids === []) {
            return collect();
        }

        $products = Product::query()
            ->with(['images', 'vendor', 'variants', 'flashSale', 'menuItem'])
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->get();

        return $products
            ->sortBy(fn (Product $p) => array_search($p->id, $ids, true))
            ->values()
            ->take($limit);
    }
}
