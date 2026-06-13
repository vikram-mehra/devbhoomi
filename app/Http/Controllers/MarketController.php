<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\ProductStorefrontService;
use App\Services\RecentlyViewedProducts;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MarketController extends Controller
{
    public function index(Request $request, RecommendationService $rec, ProductStorefrontService $catalog)
    {
        $bannerCache = max(0, (int) config('catalog.home_cache_seconds', 600));
        $bannerTtl = $bannerCache > 0 ? $bannerCache : 1;

        $banners = $bannerCache === 0
            ? Banner::where('is_active', true)->where('placement', Banner::PLACEMENT_HOME_SLIDER)->orderBy('sort_order')->get()
            : Cache::remember('home.banners', $bannerTtl, function () {
                return Banner::where('is_active', true)->where('placement', Banner::PLACEMENT_HOME_SLIDER)->orderBy('sort_order')->get();
            });

        $promoBanners = $bannerCache === 0
            ? Banner::where('is_active', true)->where('placement', Banner::PLACEMENT_HOME_PROMO)->orderBy('sort_order')->orderBy('id')->limit(3)->get()
            : Cache::remember('home.promo_banners', $bannerTtl, function () {
                return Banner::where('is_active', true)->where('placement', Banner::PLACEMENT_HOME_PROMO)->orderBy('sort_order')->orderBy('id')->limit(3)->get();
            });

        $featured = $catalog->featuredForHome(12);
        $trending = $catalog->trendingForHome(12);
        $newProducts = $catalog->newestForHome(12);

        $vendors = Cache::remember('home.vendors', 1200, function () {
            return Vendor::where('status', 'approved')->orderByDesc('rating_avg')->take(8)->get();
        });

        $forYou = $rec->forYou(auth()->user());

        $suggestedForYou = app(RecentlyViewedProducts::class)->productsForHome($request);

        $blogPosts = Cache::remember('home.blog_posts', 600, function () {
            return BlogPost::published()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->take(3)
                ->get();
        });

        return view('market.home', compact(
            'banners', 'promoBanners', 'featured', 'trending', 'newProducts', 'vendors', 'forYou', 'suggestedForYou', 'blogPosts'
        ));
    }
}
