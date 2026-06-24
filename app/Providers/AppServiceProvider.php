<?php

namespace App\Providers;

use App\Models\ProductVariant;
use App\Services\CartService;
use App\Services\MenuItemService;
use App\Services\ProductStorefrontService;
use App\Services\StockLedgerService;
use App\Support\SiteLogo;
use App\Support\AppUrl;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(MenuItemService::class);
        $this->app->singleton(ProductStorefrontService::class);
        $this->app->singleton(\App\Services\SeoService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        Paginator::currentPathResolver(function () {
            return AppUrl::paginatorPath();
        });

        // Laravel 8 compatibility for @selected / @checked (native in Laravel 9+).
        Blade::directive('selected', function ($expression) {
            return "<?php if({$expression}): echo 'selected'; endif; ?>";
        });
        Blade::directive('checked', function ($expression) {
            return "<?php if({$expression}): echo 'checked'; endif; ?>";
        });

        $credentialsPath = config_path('mail.credentials.php');
        if (is_readable($credentialsPath)) {
            $credentials = require $credentialsPath;
            if (! empty($credentials['password'])) {
                config([
                    'mail.mailers.smtp.password' => str_replace(' ', '', trim((string) $credentials['password'])),
                ]);
            }
            if (! empty($credentials['username'])) {
                config(['mail.mailers.smtp.username' => trim((string) $credentials['username'])]);
            }
            if (! empty($credentials['from'])) {
                config(['mail.from.address' => trim((string) $credentials['from'])]);
            }
        }

        $root = config('app.url');
        if (is_string($root) && $root !== '') {
            URL::forceRootUrl(rtrim($root, '/'));
            if (Str::startsWith($root, 'https://')) {
                URL::forceScheme('https');
            }
        }

        if (config('services.google.client_id') && ! app()->runningInConsole()) {
            app(\App\Services\GoogleAuthService::class)->syncRedirectConfig();
        }

        ProductVariant::saved(function (ProductVariant $variant) {
            if ($variant->wasRecentlyCreated || $variant->wasChanged('stock_qty')) {
                app(StockLedgerService::class)->mirrorWarehouseQty($variant, (int) $variant->stock_qty);
            }
        });

        View::composer(['layouts.market', 'layouts.admin', 'admin.auth.login'], function ($view) {
            $cart = app(CartService::class);
            $items = $cart->query()->orderByDesc('updated_at')->get();
            $subtotal = 0;
            foreach ($items as $item) {
                $subtotal += $item->variant->effectivePrice() * $item->qty;
            }

            $shippingService = app(\App\Services\ShippingService::class);
            $shippingTotals = $shippingService->totalsForSubtotal($subtotal);

            $menus = app(MenuItemService::class);

            $view->with([
                'layoutCartItems' => $items,
                'layoutCartCount' => (int) $items->sum('qty'),
                'layoutCartSubtotal' => $subtotal,
                'layoutCartShipping' => $shippingTotals['shipping_charge'],
                'layoutCartTotal' => $shippingTotals['total'],
                'layoutHeaderMenu' => $menus->headerTree(),
                'layoutFooterMenu' => $menus->footerLinks(),
                'siteLogoUrl' => SiteLogo::url(),
            ]);
        });
    }
}
