<?php

use App\Http\Controllers\Admin\BannerAdminController;
use App\Http\Controllers\Admin\HomePromoAdminController;
use App\Http\Controllers\Admin\BlogPostAdminController;
use App\Http\Controllers\Admin\MenuItemAdminController;
use App\Http\Controllers\Admin\CouponAdminController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\ProductAdminController;
use App\Http\Controllers\Admin\InventoryDashboardController;
use App\Http\Controllers\Admin\InventoryLedgerAdminController;
use App\Http\Controllers\Admin\InventoryReportAdminController;
use App\Http\Controllers\Admin\InventoryVariantSearchController;
use App\Http\Controllers\Admin\PurchaseAdminController;
use App\Http\Controllers\Admin\SaleAdminController;
use App\Http\Controllers\Admin\SupplierAdminController;
use App\Http\Controllers\Admin\WarehouseAdminController;
use App\Http\Controllers\Admin\ReturnAdminController;
use App\Http\Controllers\Admin\SettingAdminController;
use App\Http\Controllers\Admin\ShippingSettingAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\VendorAdminController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnRequestController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\HtmlSitemapController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ProductManageController;
use App\Http\Controllers\Vendor\VendorOrderController;
use App\Http\Controllers\VendorRegisterController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\Admin\AboutPageAdminController;
use App\Http\Controllers\Admin\ContactPageAdminController;
use App\Http\Controllers\Admin\SeoAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketController::class, 'index'])->name('market.home');
Route::get('/home', function () {
    return redirect()->route('market.home');
});
Route::get('/git-test', function () {
    return 'Git deployment working';
});
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');
Route::get('/sitemap', [HtmlSitemapController::class, 'index'])->name('pages.sitemap');

Route::get('/menu/{slug}', [ShopController::class, 'menu'])->name('shop.menu');
Route::get('/category/{slug}', [ShopController::class, 'category'])->name('shop.category');
Route::get('/search', [ShopController::class, 'search'])->name('shop.search');
Route::get('/search/suggest', [ShopController::class, 'searchSuggest'])->name('shop.search.suggest');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{blogPost}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/offers', [CouponController::class, 'index'])->name('offers.index');
Route::view('/terms-and-conditions', 'market.legal.terms')->name('legal.terms');
Route::view('/privacy-policy', 'market.legal.privacy')->name('legal.privacy');
Route::view('/refund-policy', 'market.legal.refund')->name('legal.refund');
Route::view('/shipping-policy', 'market.legal.shipping')->name('legal.shipping');

Route::get('/about-us', [PageController::class, 'about'])->name('pages.about');
Route::get('/contact-us', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact-us', [PageController::class, 'contactSubmit'])->name('pages.contact.submit');

Route::get('/product/{product}', [ProductController::class, 'show'])->name('product.show');
Route::get('/store/{slug}', [ProductController::class, 'vendorShop'])->name('vendor.shop');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/vendor/join', [VendorRegisterController::class, 'create'])->name('vendor.register');
Route::post('/vendor/join', [VendorRegisterController::class, 'store'])->name('vendor.register.store');

Route::get('/login/phone', [OtpController::class, 'showPhoneForm'])->name('login.phone');
Route::post('/login/phone/send', [OtpController::class, 'sendOtp'])->name('login.phone.send');
Route::post('/login/phone/verify', [OtpController::class, 'verifyOtp'])->name('login.phone.verify');

Auth::routes();

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Email OTP verification (post-signup)
Route::get('/email/verification-sent', [EmailVerificationController::class, 'sent'])->name('verification.sent');
Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/verify/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');

Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');

Route::middleware(['auth', 'verified.email'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/validate-coupon', [CheckoutController::class, 'validateCoupon'])->name('checkout.validate-coupon');
    Route::post('/checkout/remove-coupon', [CheckoutController::class, 'removeCoupon'])->name('checkout.remove-coupon');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::post('/pay/razorpay/dummy/{order}', [PaymentController::class, 'razorpayDummyComplete'])->name('pay.razorpay.dummy');
    Route::get('/pay/razorpay/{order}', [PaymentController::class, 'razorpay'])->name('pay.razorpay');
    Route::post('/pay/razorpay/verify', [PaymentController::class, 'razorpayVerify'])->name('pay.razorpay.verify');
    Route::post('/pay/razorpay/order/{order}', [PaymentController::class, 'createRazorpayOrder'])->name('pay.razorpay.order');
    Route::post('/pay/razorpay/abandon/{order}', [PaymentController::class, 'razorpayAbandon'])->name('pay.razorpay.abandon');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/return', [ReturnRequestController::class, 'store'])->name('orders.return');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/chat/{vendor}', [ChatController::class, 'show'])->name('chat.show');
    Route::post('/chat/{vendor}', [ChatController::class, 'store'])->name('chat.store');

    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
        Route::get('/details', [AccountController::class, 'details'])->name('details');
        Route::post('/details', [AccountController::class, 'updateDetails'])->name('details.update');
        Route::get('/profile', function () {
            return redirect()->route('account.details');
        })->name('profile');
        Route::get('/refunds', [AccountController::class, 'refunds'])->name('refunds');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses.index');
        Route::post('/addresses', [AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::get('/addresses/{address}/edit', [AccountController::class, 'editAddress'])->name('addresses.edit');
        Route::patch('/addresses/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
        Route::post('/addresses/{address}/default', [AccountController::class, 'setDefaultAddress'])->name('addresses.default');
        Route::delete('/addresses/{address}', [AccountController::class, 'destroyAddress'])->name('addresses.destroy');
        Route::get('/password', [AccountController::class, 'passwordForm'])->name('password');
        Route::post('/password', [AccountController::class, 'updatePassword'])->name('password.update');
    });
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::redirect('/categories', '/admin/menu-items')->name('categories.index');
    Route::redirect('/categories/subcategories', '/admin/menu-items');

    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/vendors', [VendorAdminController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/create', [VendorAdminController::class, 'create'])->name('vendors.create');
    Route::post('/vendors', [VendorAdminController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [VendorAdminController::class, 'edit'])->name('vendors.edit');
    Route::patch('/vendors/{vendor}', [VendorAdminController::class, 'update'])->name('vendors.update');
    Route::delete('/vendors/{vendor}', [VendorAdminController::class, 'destroy'])->name('vendors.destroy');
    Route::post('/vendors/{vendor}/approve', [VendorAdminController::class, 'approve'])->name('vendors.approve');
    Route::post('/vendors/{vendor}/reject', [VendorAdminController::class, 'reject'])->name('vendors.reject');
    Route::post('/vendors/{vendor}/commission', [VendorAdminController::class, 'updateCommission'])->name('vendors.commission');

    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/role', [UserAdminController::class, 'updateRole'])->name('users.role');

    Route::get('/menu-items', [MenuItemAdminController::class, 'index'])->name('menu-items.index');
    Route::post('/menu-items', [MenuItemAdminController::class, 'store'])->name('menu-items.store');
    Route::post('/menu-items/reorder', [MenuItemAdminController::class, 'reorder'])->name('menu-items.reorder');
    Route::patch('/menu-items/{menuItem}', [MenuItemAdminController::class, 'update'])->name('menu-items.update');
    Route::delete('/menu-items/{menuItem}', [MenuItemAdminController::class, 'destroy'])->name('menu-items.destroy');

    Route::get('/products/table', [ProductAdminController::class, 'table'])->name('products.table');
    Route::get('/products', [ProductAdminController::class, 'index'])->name('products.index');
    Route::post('/products/variants/generate-matrix', [ProductAdminController::class, 'generateVariantMatrix'])->name('products.variants.generate-matrix');
    Route::get('/products/create', [ProductAdminController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductAdminController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductAdminController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [ProductAdminController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductAdminController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/{product}/toggle', [ProductAdminController::class, 'toggle'])->name('products.toggle');
    Route::post('/products/{product}/featured', [ProductAdminController::class, 'toggleFeatured'])->name('products.featured');
    Route::post('/products/{product}/stock-adjust', [ProductAdminController::class, 'adjustStock'])->name('products.stock-adjust');
    Route::get('/reports/variant-sales', [ProductAdminController::class, 'variantSalesReport'])->name('reports.variant-sales');

    Route::get('/inventory', [InventoryDashboardController::class, 'index'])->name('inventory.dashboard');
    Route::get('/inventory/product-inventory', [InventoryDashboardController::class, 'productInventory'])->name('inventory.product-inventory');
    Route::get('/inventory/api/summary', [InventoryDashboardController::class, 'apiSummary'])->name('inventory.api.summary');
    Route::get('/inventory/api/variants', InventoryVariantSearchController::class)->name('inventory.api.variants');
    Route::get('/inventory/ledger', [InventoryLedgerAdminController::class, 'index'])->name('inventory.ledger');
    Route::post('/inventory/ledger/adjust', [InventoryLedgerAdminController::class, 'adjust'])->name('inventory.ledger.adjust');
    Route::get('/inventory/export/products.csv', [InventoryDashboardController::class, 'exportProducts'])->name('inventory.export.products');
    Route::get('/inventory/export/variants.csv', [InventoryDashboardController::class, 'exportVariants'])->name('inventory.export.variants');
    Route::post('/inventory/bulk-adjust', [InventoryDashboardController::class, 'bulkAdjust'])->name('inventory.bulk-adjust');

    Route::get('/suppliers', [SupplierAdminController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierAdminController::class, 'store'])->name('suppliers.store');
    Route::get('/suppliers/{supplier}', [SupplierAdminController::class, 'show'])->name('suppliers.show');
    Route::patch('/suppliers/{supplier}', [SupplierAdminController::class, 'update'])->name('suppliers.update');

    Route::get('/purchases', [PurchaseAdminController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseAdminController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseAdminController::class, 'store'])->name('purchases.store');
    Route::get('/purchases/{purchase}', [PurchaseAdminController::class, 'show'])->name('purchases.show');

    Route::get('/sales', [SaleAdminController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SaleAdminController::class, 'create'])->name('sales.create');
    Route::post('/sales', [SaleAdminController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [SaleAdminController::class, 'show'])->name('sales.show');
    Route::post('/sales/{sale}/status', [SaleAdminController::class, 'updateStatus'])->name('sales.status');

    Route::get('/warehouses', [WarehouseAdminController::class, 'index'])->name('warehouses.index');
    Route::post('/warehouses', [WarehouseAdminController::class, 'store'])->name('warehouses.store');
    Route::get('/warehouses/{warehouse}', [WarehouseAdminController::class, 'show'])->name('warehouses.show');
    Route::get('/warehouses/{warehouse}/report', [WarehouseAdminController::class, 'report'])->name('warehouses.report');
    Route::post('/warehouses/transfer', [WarehouseAdminController::class, 'transfer'])->name('warehouses.transfer');

    Route::get('/reports/inventory', [InventoryReportAdminController::class, 'index'])->name('reports.inventory');

    Route::get('/orders', [OrderAdminController::class, 'index'])->name('orders.index');
    Route::get('/orders/export', [OrderAdminController::class, 'export'])->name('orders.export');
    Route::post('/orders/bulk-status', [OrderAdminController::class, 'bulkUpdateStatus'])->name('orders.bulk-status');
    Route::post('/orders/bulk-shipping', [OrderAdminController::class, 'bulkUpdateShipping'])->name('orders.bulk-shipping');
    Route::get('/orders/{order}', [OrderAdminController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/notes', [OrderAdminController::class, 'updateNotes'])->name('orders.notes');
    Route::get('/orders/{order}/invoice/print', [OrderAdminController::class, 'printInvoice'])->name('orders.invoice.print');
    Route::get('/orders/{order}/invoice/pdf', [OrderAdminController::class, 'downloadInvoicePdf'])->name('orders.invoice.pdf');
    Route::get('/orders/{order}/label/print', [OrderAdminController::class, 'printShippingLabel'])->name('orders.label.print');
    Route::post('/orders/{order}/status', [OrderAdminController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/payment', [OrderAdminController::class, 'updatePayment'])->name('orders.payment');

    Route::get('/returns', [ReturnAdminController::class, 'index'])->name('returns.index');
    Route::patch('/returns/{refund}', [ReturnAdminController::class, 'update'])->name('returns.update');

    Route::get('/blog-posts', [BlogPostAdminController::class, 'index'])->name('blog-posts.index');
    Route::get('/blog-posts/create', [BlogPostAdminController::class, 'create'])->name('blog-posts.create');
    Route::post('/blog-posts', [BlogPostAdminController::class, 'store'])->name('blog-posts.store');
    Route::get('/blog-posts/{blogPost}/edit', [BlogPostAdminController::class, 'edit'])->name('blog-posts.edit');
    Route::patch('/blog-posts/{blogPost}', [BlogPostAdminController::class, 'update'])->name('blog-posts.update');
    Route::delete('/blog-posts/{blogPost}', [BlogPostAdminController::class, 'destroy'])->name('blog-posts.destroy');

    Route::prefix('showcase')->name('showcase.')->group(function () {
        Route::get('/home-promo', [HomePromoAdminController::class, 'index'])->name('home-promo.index');
        Route::post('/home-promo', [HomePromoAdminController::class, 'store'])->name('home-promo.store');
        Route::patch('/home-promo/{banner}', [HomePromoAdminController::class, 'update'])->name('home-promo.update');
        Route::post('/home-promo/{banner}/toggle-active', [HomePromoAdminController::class, 'toggleActive'])->name('home-promo.toggle-active');
        Route::delete('/home-promo/{banner}', [HomePromoAdminController::class, 'destroy'])->name('home-promo.destroy');
    });

    Route::get('/banners', [BannerAdminController::class, 'index'])->name('banners.index');
    Route::post('/banners', [BannerAdminController::class, 'store'])->name('banners.store');
    Route::patch('/banners/{banner}', [BannerAdminController::class, 'update'])->name('banners.update');
    Route::post('/banners/{banner}/toggle-active', [BannerAdminController::class, 'toggleActive'])->name('banners.toggle-active');
    Route::delete('/banners/{banner}', [BannerAdminController::class, 'destroy'])->name('banners.destroy');

    Route::get('/coupons', [CouponAdminController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [CouponAdminController::class, 'store'])->name('coupons.store');
    Route::post('/coupons/validate', [CouponAdminController::class, 'validateForOrder'])->name('coupons.validate');
    Route::patch('/coupons/{coupon}', [CouponAdminController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [CouponAdminController::class, 'destroy'])->name('coupons.destroy');
    Route::post('/coupons/{coupon}/toggle', [CouponAdminController::class, 'toggle'])->name('coupons.toggle');

    Route::get('/settings', [SettingAdminController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingAdminController::class, 'update'])->name('settings.update');

    Route::get('/seo', [SeoAdminController::class, 'index'])->name('seo.index');
    Route::post('/seo', [SeoAdminController::class, 'update'])->name('seo.update');
    Route::get('/seo/report', [SeoAdminController::class, 'report'])->name('seo.report');
    Route::post('/seo/apply-fixes', [SeoAdminController::class, 'applyFixes'])->name('seo.apply-fixes');

    Route::get('/shipping-settings', [ShippingSettingAdminController::class, 'edit'])->name('shipping-settings.edit');
    Route::post('/shipping-settings', [ShippingSettingAdminController::class, 'update'])->name('shipping-settings.update');

    Route::get('/about-page', [AboutPageAdminController::class, 'edit'])->name('about-page.edit');
    Route::post('/about-page', [AboutPageAdminController::class, 'update'])->name('about-page.update');
    Route::delete('/about-page/gallery/{aboutGalleryItem}', [AboutPageAdminController::class, 'destroyGalleryItem'])->name('about-page.gallery.destroy');

    Route::get('/contact-page', [ContactPageAdminController::class, 'edit'])->name('contact-page.edit');
    Route::post('/contact-page', [ContactPageAdminController::class, 'update'])->name('contact-page.update');
    Route::post('/contact-inquiries/{contactInquiry}/read', [ContactPageAdminController::class, 'markInquiryRead'])->name('contact-inquiries.read');
});

Route::middleware(['auth', 'verified.email', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/', [VendorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [VendorOrderController::class, 'index'])->name('orders.index');

    Route::get('/products', [ProductManageController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductManageController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductManageController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductManageController::class, 'edit'])->name('products.edit');
    Route::patch('/products/{product}', [ProductManageController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductManageController::class, 'destroy'])->name('products.destroy');
    Route::post('/products/import', [ProductManageController::class, 'importCsv'])->name('products.import');
});
