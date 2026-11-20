<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use App\Support\MenuItemTree;
use App\Support\OptimizedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShopController extends Controller
{
    public function products(Request $request)
    {
        $menuItem = MenuItem::query()
            ->active()
            ->where(function ($q) {
                $q->where('slug', 'our-products')
                    ->orWhere('title', 'Our Products');
            })
            ->orderByRaw('CASE WHEN parent_id IS NULL OR parent_id = 0 THEN 0 ELSE 1 END')
            ->orderBy('sort_order')
            ->first();

        if (! $menuItem) {
            return redirect()->route('shop.search');
        }

        return $this->renderMenuListing($menuItem, $request);
    }

    public function menu(string $slug, Request $request)
    {
        $builtInRoute = MenuItem::builtInPageRouteName($slug);
        if ($builtInRoute !== null) {
            return redirect()->route($builtInRoute, 301);
        }

        if ($slug === 'our-products') {
            return redirect()->route('shop.products', $request->query(), 301);
        }

        $menuItem = MenuItem::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return $this->renderMenuListing($menuItem, $request);
    }

    /** @deprecated Old /menu/{slug} URLs */
    public function legacyMenu(string $slug, Request $request)
    {
        $builtInRoute = MenuItem::builtInPageRouteName($slug);
        if ($builtInRoute !== null) {
            return redirect()->route($builtInRoute, 301);
        }

        if ($slug === 'our-products') {
            return redirect()->route('shop.products', $request->query(), 301);
        }

        return redirect()->route('shop.menu', array_merge(['slug' => $slug], $request->query()), 301);
    }

    protected function renderMenuListing(MenuItem $menuItem, Request $request)
    {
        $q = Product::with(['images', 'vendor', 'variants', 'flashSale', 'menuItem'])
            ->storefront()
            ->whereIn('menu_item_id', MenuItemTree::subtreeIds($menuItem->id));

        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%');
            });
        }

        $facetBase = Product::query()->storefront()
            ->whereIn('menu_item_id', MenuItemTree::subtreeIds($menuItem->id));
        if ($request->filled('q')) {
            $term = $request->q;
            $facetBase->where(function ($qq) use ($term) {
                $qq->where('name', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%');
            });
        }
        $priceCeil = $this->computePriceCeilFromQuery($facetBase);

        $this->applyListingFilters($request, $q, $priceCeil);

        $sort = $request->get('sort', 'popular');
        if ($sort === 'price_asc') {
            $q->orderBy('base_price');
        } elseif ($sort === 'price_desc') {
            $q->orderByDesc('base_price');
        } elseif ($sort === 'newest') {
            $q->orderByDesc('id');
        } else {
            $q->orderByDesc('sales_count');
        }

        $products = $q->paginate(20)->withQueryString();

        $facets = $this->buildFacets($facetBase);

        return view('market.menu', compact('menuItem', 'products', 'facets'));
    }

    /** @deprecated Redirect old category URLs */
    public function category(string $slug)
    {
        $item = MenuItem::where('slug', $slug)->where('is_active', true)->first();
        if ($item) {
            return redirect($item->resolvedUrl(), 301);
        }

        return redirect()->route('shop.search');
    }

    public function search(Request $request)
    {
        $q = Product::with(['images', 'vendor', 'variants', 'flashSale', 'menuItem'])
            ->storefront();

        $this->applySearchScope($request, $q);

        $facetBase = Product::query()->storefront();
        $this->applySearchScope($request, $facetBase);
        $priceCeil = $this->computePriceCeilFromQuery($facetBase);

        $this->applyListingFilters($request, $q, $priceCeil);

        $sort = $request->get('sort', 'popular');
        if ($sort === 'price_asc') {
            $q->orderBy('base_price');
        } elseif ($sort === 'price_desc') {
            $q->orderByDesc('base_price');
        } elseif ($sort === 'newest') {
            $q->orderByDesc('id');
        } else {
            $q->orderByDesc('sales_count');
        }

        $products = $q->paginate(24)->withQueryString();

        $menuCountsBase = Product::where('is_active', true);
        $this->applySearchScopeExceptMenu($request, $menuCountsBase);
        $this->applyListingFilters($request, $menuCountsBase, $priceCeil);

        $facets = $this->buildFacets($facetBase, $menuCountsBase);

        return view('market.search', compact('products', 'facets'));
    }

    public function searchSuggest(Request $request)
    {
        $term = trim((string) $request->input('q', ''));
        $empty = fn (?string $q = null) => response()->json([
            'products' => [],
            'suggestions' => [],
            'view_all_url' => $q ? route('shop.search', ['q' => $q]) : route('shop.search'),
        ]);

        if (mb_strlen($term) < 2) {
            return $empty();
        }
        if (mb_strlen($term) > 80) {
            $term = mb_substr($term, 0, 80);
        }

        $escaped = addcslashes($term, '%_\\');
        $pattern = '%'.$escaped.'%';
        $prefix = $escaped.'%';

        $products = Product::query()
            ->storefront()
            ->with(['images', 'flashSale'])
            ->where(function ($q) use ($pattern) {
                $q->where('name', 'like', $pattern)
                    ->orWhere('description', 'like', $pattern)
                    ->orWhere('brand', 'like', $pattern);
            })
            ->orderByRaw('CASE WHEN name LIKE ? THEN 0 ELSE 1 END', [$prefix])
            ->orderByDesc('sales_count')
            ->limit(10)
            ->get();

        $payload = $products->map(function (Product $p) {
            $price = $p->effectivePrice();
            $compare = $p->compare_price ? (float) $p->compare_price : 0;
            if ($compare <= $price) {
                $base = (float) $p->base_price;
                $compare = $base > $price ? $base : 0;
            }
            $pctOff = ($compare > $price)
                ? (int) round(100 - ($price / $compare) * 100)
                : 0;
            $image = $p->cardImageUrls()[0] ?? '';

            return [
                'name' => $p->name,
                'url' => route('product.show', $p),
                'image' => OptimizedImage::url($image, 96),
                'price' => $price,
                'price_text' => '₹'.number_format($price, 2),
                'compare_price' => $pctOff > 0 ? $compare : null,
                'compare_text' => $pctOff > 0 ? '₹'.number_format($compare, 2) : null,
                'percent_off' => $pctOff,
            ];
        })->values()->all();

        return response()->json([
            'products' => $payload,
            'suggestions' => array_map(static fn (array $row) => [
                'text' => $row['name'],
                'url' => $row['url'],
            ], $payload),
            'view_all_url' => route('shop.search', ['q' => $term]),
        ]);
    }

    protected function applySearchScopeExceptMenu(Request $request, $q): void
    {
        if ($request->filled('q')) {
            $term = $request->q;
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', '%'.$term.'%')
                    ->orWhere('description', 'like', '%'.$term.'%');
            });
        }
    }

    protected function applySearchScope(Request $request, $q): void
    {
        $this->applySearchScopeExceptMenu($request, $q);

        $slugs = $this->normalizeMenuSlugList($request->input('menu'));
        if ($slugs === []) {
            $slugs = $this->normalizeMenuSlugList($request->input('categories'));
        }
        if ($slugs !== []) {
            $this->applyMenuSlugFilter($q, $slugs);
        }
    }

    /**
     * @param  mixed  $raw
     * @return list<string>
     */
    protected function normalizeMenuSlugList($raw): array
    {
        if ($raw === null || $raw === '' || $raw === []) {
            return [];
        }
        $list = is_array($raw) ? $raw : [$raw];
        $out = [];
        foreach ($list as $item) {
            if ($item === null || $item === '') {
                continue;
            }
            $s = strtolower(trim((string) $item));
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    protected function applyMenuSlugFilter($q, array $slugs): void
    {
        if ($slugs === []) {
            return;
        }

        $matched = MenuItem::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->where(function ($qq) use ($slugs) {
                foreach ($slugs as $s) {
                    $qq->orWhereRaw('LOWER(TRIM(slug)) = ?', [$s]);
                }
            })
            ->get(['id']);

        if ($matched->isEmpty()) {
            $q->whereRaw('1 = 0');

            return;
        }

        $byParent = MenuItemTree::byParentKey();

        $allIds = [];
        foreach ($matched as $item) {
            $allIds = array_merge($allIds, MenuItemTree::subtreeIds((int) $item->id, $byParent));
        }
        $allIds = array_values(array_unique($allIds));
        if ($allIds !== []) {
            $q->whereIn('menu_item_id', $allIds);
        } else {
            $q->whereRaw('1 = 0');
        }
    }

    protected function applyListingFilters(Request $request, $q, ?int $priceCeil = null): void
    {
        if ($request->filled('min') && (float) $request->min > 0) {
            $q->where('base_price', '>=', (float) $request->min);
        }
        if ($request->filled('max')) {
            $maxReq = (float) $request->max;
            if ($priceCeil === null || $maxReq < $priceCeil) {
                $q->where('base_price', '<=', $maxReq);
            }
        }

        $rating = $request->input('rating_min', $request->input('rating'));
        if ($rating !== null && $rating !== '' && is_numeric($rating) && (float) $rating > 0) {
            $q->whereRaw('COALESCE(rating_avg, 0) >= ?', [(float) $rating]);
        }

        $brandInput = $request->input('brand');
        if ($brandInput !== null && $brandInput !== '' && $brandInput !== []) {
            $ids = array_values(array_unique(array_filter(array_map('intval', Arr::wrap($brandInput)))));
            if ($ids !== []) {
                $q->whereIn('vendor_id', $ids);
            }
        }

        $colorInput = $request->input('color');
        if ($colorInput !== null && $colorInput !== '' && $colorInput !== []) {
            $colors = array_values(array_unique(array_filter(Arr::wrap($colorInput), function ($c) {
                return $c !== null && $c !== '';
            })));
            if ($colors !== []) {
                $q->whereHas('variants', function ($vq) use ($colors) {
                    $vq->whereIn('color', $colors);
                });
            }
        }

        $discountMin = $request->input('discount_min');
        if ($discountMin !== null && $discountMin !== '') {
            $allowedDiscounts = [10, 20, 30, 40, 50, 60, 70, 80];
            $d = (int) $discountMin;
            if (in_array($d, $allowedDiscounts, true)) {
                $q->whereRaw(
                    'compare_price IS NOT NULL AND compare_price > 0 AND base_price < compare_price AND ((compare_price - base_price) / compare_price * 100) >= ?',
                    [(float) $d]
                );
            }
        }
    }

    protected function computePriceCeilFromQuery($query): int
    {
        $maxPrice = (float) ((clone $query)->max('base_price') ?: 0);

        return (int) max(500, min(500000, ceil($maxPrice / 100) * 100 ?: 5000));
    }

    protected function buildFacets($facetBaseQuery, $menuCountsBaseQuery = null): array
    {
        $menuCountBase = $menuCountsBaseQuery ?? $facetBaseQuery;

        $productIds = (clone $facetBaseQuery)->pluck('id');
        $vendorIds = (clone $facetBaseQuery)->distinct()->pluck('vendor_id')->filter()->values();

        $brandCounts = (clone $facetBaseQuery)
            ->reorder()
            ->selectRaw('vendor_id, COUNT(*) as facet_count')
            ->groupBy('vendor_id')
            ->pluck('facet_count', 'vendor_id');

        $brands = $vendorIds->isEmpty()
            ? collect()
            : Vendor::whereIn('id', $vendorIds)->orderBy('shop_name')->get(['id', 'shop_name']);

        $colors = $productIds->isEmpty()
            ? collect()
            : ProductVariant::query()
                ->whereIn('product_id', $productIds)
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->distinct()
                ->orderBy('color')
                ->pluck('color')
                ->unique()
                ->values();

        $priceCeil = $this->computePriceCeilFromQuery($facetBaseQuery);

        $menuRoots = MenuItem::query()
            ->roots()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderBy('sort_order')
            ->get(['id', 'title', 'slug']);

        $byParent = MenuItemTree::byParentKey();

        $menuCounts = [];
        foreach ($menuRoots as $root) {
            $menuIds = MenuItemTree::subtreeIds($root->id, $byParent);
            $menuCounts[$root->slug] = (clone $menuCountBase)->whereIn('menu_item_id', $menuIds)->count();
        }

        return [
            'brands' => $brands,
            'brand_counts' => $brandCounts,
            'colors' => $colors,
            'menu_counts' => $menuCounts,
            'category_counts' => $menuCounts,
            'price_ceil' => $priceCeil,
            'menus' => $menuRoots,
            'categories' => $menuRoots,
        ];
    }
}
