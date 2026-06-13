<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\SeoService;

class HtmlSitemapController extends Controller
{
    public function index(SeoService $seo)
    {
        $categories = MenuItem::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['title', 'slug'])
            ->reject(fn (MenuItem $item) => $item->isBuiltInPage());

        $products = Product::storefront()
            ->orderBy('name')
            ->get(['name', 'slug']);

        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->get(['title', 'slug']);

        $vendors = Vendor::query()
            ->where('status', 'approved')
            ->orderBy('shop_name')
            ->get(['shop_name', 'slug']);

        $staticLinks = [
            ['label' => __('Home'), 'url' => route('market.home')],
            ['label' => __('Shop all'), 'url' => route('shop.search')],
            ['label' => __('Blog'), 'url' => route('blog.index')],
            ['label' => __('Offers'), 'url' => route('offers.index')],
            ['label' => __('About us'), 'url' => route('pages.about')],
            ['label' => __('Contact'), 'url' => route('pages.contact')],
            ['label' => __('Terms & conditions'), 'url' => route('legal.terms')],
            ['label' => __('Privacy policy'), 'url' => route('legal.privacy')],
        ];

        return view('market.html-sitemap', compact(
            'categories',
            'products',
            'posts',
            'vendors',
            'staticLinks',
            'seo'
        ));
    }
}
