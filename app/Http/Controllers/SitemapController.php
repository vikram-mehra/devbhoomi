<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Response;

class SitemapController extends Controller
{
    public function index()
    {
        $entries = collect();

        foreach (config('seo.sitemap.static_pages', []) as $page) {
            try {
                $entries->push([
                    'loc' => route($page['route']),
                    'lastmod' => now()->toAtomString(),
                    'changefreq' => $page['changefreq'] ?? 'weekly',
                    'priority' => $page['priority'] ?? '0.5',
                ]);
            } catch (\Throwable $e) {
                // Skip missing routes
            }
        }

        BlogPost::published()->get(['slug', 'updated_at'])->each(function (BlogPost $post) use ($entries) {
            $entries->push([
                'loc' => route('blog.show', $post->slug),
                'lastmod' => optional($post->updated_at)->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ]);
        });

        MenuItem::where('is_active', true)
            ->whereNotNull('slug')
            ->get(['slug', 'updated_at'])
            ->each(function (MenuItem $menuItem) use ($entries) {
                if ($menuItem->isBuiltInPage()) {
                    return;
                }
                $entries->push([
                    'loc' => route('shop.menu', $menuItem->slug),
                    'lastmod' => optional($menuItem->updated_at)->toAtomString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ]);
            });

        Product::storefront()->get(['slug', 'updated_at'])->each(function (Product $product) use ($entries) {
            $entries->push([
                'loc' => route('product.show', $product->slug),
                'lastmod' => optional($product->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ]);
        });

        Vendor::where('status', 'approved')->get(['slug', 'updated_at'])->each(function (Vendor $vendor) use ($entries) {
            $entries->push([
                'loc' => route('vendor.shop', $vendor->slug),
                'lastmod' => optional($vendor->updated_at)->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ]);
        });

        $unique = $entries->unique('loc')->values();

        return Response::view('sitemap-xml', ['entries' => $unique])
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
