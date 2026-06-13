<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\RecentlyViewedProducts;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function show(string $slug)
    {
        $product = Product::with([
            'vendor',
            'menuItem',
            'images',
            'variants' => fn ($q) => $q->where('status', ProductVariant::STATUS_ACTIVE)
                ->orderBy('id')
                ->with(['galleryImages']),
            'flashSale',
            'reviews.user',
        ])
            ->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $related = $this->relatedProductsForPdp($product);

        $recentPurchaseFeed = $this->buildRecentPurchaseFeed($product);

        app(RecentlyViewedProducts::class)->push($product);

        return view('market.product', compact('product', 'related', 'recentPurchaseFeed'));
    }

    /**
     * Same category first; if empty (null category, solo SKU, etc.) fill from same vendor, then store-wide.
     */
    protected function relatedProductsForPdp(Product $product, int $limit = 6): EloquentCollection
    {
        $with = ['images', 'variants', 'flashSale', 'vendor', 'menuItem'];
        $related = new EloquentCollection;
        $excludeIds = [$product->id];

        if ($product->menu_item_id) {
            $fromCategory = Product::query()
                ->with($with)
                ->where('is_active', true)
                ->where('menu_item_id', $product->menu_item_id)
                ->whereKeyNot($product->id)
                ->orderByDesc('sales_count')
                ->orderByDesc('id')
                ->limit($limit)
                ->get();
            $related = $related->merge($fromCategory);
            $excludeIds = array_merge($excludeIds, $fromCategory->modelKeys());
        }

        if ($related->count() < $limit) {
            $need = $limit - $related->count();
            $fromVendor = Product::query()
                ->with($with)
                ->where('is_active', true)
                ->where('vendor_id', $product->vendor_id)
                ->whereKeyNot($excludeIds)
                ->orderByDesc('sales_count')
                ->orderByDesc('id')
                ->limit($need)
                ->get();
            $related = $related->merge($fromVendor);
            $excludeIds = array_merge($excludeIds, $fromVendor->modelKeys());
        }

        if ($related->count() < $limit) {
            $need = $limit - $related->count();
            $fallback = Product::query()
                ->with($with)
                ->where('is_active', true)
                ->whereKeyNot($excludeIds)
                ->orderByDesc('sales_count')
                ->orderByDesc('id')
                ->limit($need)
                ->get();
            $related = $related->merge($fallback);
        }

        return (new EloquentCollection($related->unique('id')->values()->all()))->take($limit);
    }

    /**
     * Social-proof toast on PDP: recent paid line items, with catalog fallback when few orders exist.
     *
     * @return array<int, array{name: string, image: string, url: string, ago: string}>
     */
    protected function buildRecentPurchaseFeed(Product $currentProduct): array
    {
        $feed = [];
        $seen = [];

        $orderItems = OrderItem::query()
            ->select('order_items.*')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->orderByDesc('orders.created_at')
            ->with(['variant.product.images', 'order'])
            ->limit(30)
            ->get();

        foreach ($orderItems as $item) {
            $product = $item->variant?->product;
            $key = $product ? 'p:'.$product->id : 'n:'.Str::lower($item->product_name);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $row = $this->recentPurchaseRowFromOrderItem($item);
            if ($row !== null) {
                $feed[] = $row;
            }
            if (count($feed) >= 14) {
                break;
            }
        }

        $need = max(5, 8 - count($feed));
        if ($need > 0) {
            $fallback = Product::query()
                ->where('is_active', true)
                ->with('images')
                ->where('id', '!=', $currentProduct->id)
                ->inRandomOrder()
                ->limit($need)
                ->get();

            foreach ($fallback as $p) {
                $key = 'p:'.$p->id;
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $urls = $p->cardImageUrls();
                $feed[] = [
                    'name' => $p->name,
                    'image' => $urls[0] ?? $p->namedPlaceholderUrl(false),
                    'url' => route('product.show', $p),
                    'ago' => $this->randomRecentPurchaseAgo(),
                ];
            }
        }

        if (count($feed) < 1) {
            $urls = $currentProduct->cardImageUrls();
            $feed[] = [
                'name' => $currentProduct->name,
                'image' => $urls[0] ?? $currentProduct->namedPlaceholderUrl(false),
                'url' => route('product.show', $currentProduct),
                'ago' => $this->randomRecentPurchaseAgo(),
            ];
        }

        shuffle($feed);

        return array_slice($feed, 0, min(12, count($feed)));
    }

    /**
     * @return array{name: string, image: string, url: string, ago: string}|null
     */
    protected function recentPurchaseRowFromOrderItem(OrderItem $item): ?array
    {
        $product = $item->variant?->product;
        $name = $item->product_name;

        if ($product && $product->is_active) {
            $name = $product->name;
            $urls = $product->cardImageUrls();
            $image = $urls[0] ?? $product->namedPlaceholderUrl(false);
            $url = route('product.show', $product);
        } else {
            $image = 'https://placehold.co/96x96/14b8a6/ffffff?text='.rawurlencode(Str::limit($name, 6));
            $url = route('shop.search', ['q' => Str::limit($name, 40)]);
        }

        $at = $item->order?->created_at ?? $item->created_at;
        $ago = $at instanceof Carbon ? $at->diffForHumans() : __('Recently');

        return [
            'name' => $name,
            'image' => $image,
            'url' => $url,
            'ago' => $ago,
        ];
    }

    protected function randomRecentPurchaseAgo(): string
    {
        $m = random_int(3, 55);

        return Carbon::now()->subMinutes($m)->diffForHumans();
    }

    public function vendorShop(string $slug)
    {
        $vendor = \App\Models\Vendor::where('slug', $slug)->where('status', 'approved')->firstOrFail();
        $products = Product::with(['images', 'variants', 'flashSale', 'menuItem', 'vendor'])
            ->where('vendor_id', $vendor->id)->where('is_active', true)
            ->orderByDesc('id')->paginate(20);

        return view('market.vendor-shop', compact('vendor', 'products'));
    }
}
