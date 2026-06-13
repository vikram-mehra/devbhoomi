<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\FlashSale;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Setting;
use App\Models\ShippingSetting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        Setting::setValue('default_commission_percent', '12');
        ShippingSetting::query()->updateOrCreate([], [
            'free_shipping_amount' => 500,
            'shipping_charge' => 50,
        ]);

        User::create([
            'name' => 'Super Admin',
            'email' => 'alluringstyle@gmail.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_ADMIN,
            'account_status' => User::ACCOUNT_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $vendorUser = User::create([
            'name' => 'Devbhoomi Naturals',
            'email' => 'info@devbhoominaturals.com',
            'password' => Hash::make('password'),
            'role' => User::ROLE_VENDOR,
            'phone' => '9876543210',
            'account_status' => User::ACCOUNT_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Devbhoomi Naturals',
            'slug' => 'devbhoomi-naturals',
            'status' => 'approved',
            'commission_percent' => 12,
            'city' => 'Mumbai',
            'state' => 'MH',
            'rating_avg' => 4.6,
            'rating_count' => 2400,
        ]);

        User::create([
            'name' => 'Happy Customer',
            'email' => 'customer@alluringstyle.test',
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'wallet_balance' => 200,
            'account_status' => User::ACCOUNT_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $fashion = Category::create([
            'parent_id' => null,
            'name' => 'Fashion',
            'slug' => 'fashion',
            'sort_order' => 1,
            'is_active' => true,
            'meta_title' => 'Fashion — alluringstyle',
        ]);

        $electronics = Category::create([
            'parent_id' => null,
            'name' => 'Electronics',
            'slug' => 'electronics',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Category::create([
            'parent_id' => $fashion->id,
            'name' => 'Kurtas',
            'slug' => 'kurtas',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $phoneCat = Category::create([
            'parent_id' => $electronics->id,
            'name' => 'Mobiles',
            'slug' => 'mobiles',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $names = [
            ['n' => 'AuraFit Smartwatch Pro', 'p' => 2499, 'c' => 3299],
            ['n' => 'Breeze Wireless Buds', 'p' => 899, 'c' => 1499],
            ['n' => 'PixelGlow Phone Case', 'p' => 299, 'c' => 499],
            ['n' => 'Velvet Kurta Set', 'p' => 799, 'c' => 1299],
            ['n' => 'Urban Sneakers', 'p' => 1499, 'c' => 2199],
            ['n' => 'Nano Power Bank 20K', 'p' => 1199, 'c' => 1799],
        ];

        foreach ($names as $i => $row) {
            $slug = Str::slug($row['n']).'-'.$i;
            $p = Product::create([
                'vendor_id' => $vendor->id,
                'category_id' => $i < 3 ? $phoneCat->id : $fashion->id,
                'name' => $row['n'],
                'slug' => $slug,
                'sku' => 'ZM-'.$i,
                'short_description' => 'Premium quality. Fast delivery. Trusted seller on Alluringstyle.',
                'description' => "Fabric: Quality materials chosen for everyday use.\n\nSize & Fit: Regular fit. Check the size guide before you buy.\n\nMaterial & Care: Follow care instructions on the label. Hand-wash where recommended.",
                'base_price' => $row['p'],
                'compare_price' => $row['c'],
                'rating_avg' => 4.2 + ($i % 3) * 0.2,
                'rating_count' => 50 + $i * 30,
                'sales_count' => 100 + $i * 50,
                'is_active' => true,
                'is_featured' => $i < 4,
                'meta_title' => $row['n'].' — Buy online',
            ]);

            ProductImage::create([
                'product_id' => $p->id,
                'path' => 'https://placehold.co/600x600/14b8a6/ffffff?text='.rawurlencode(Str::limit($row['n'], 16)),
                'sort_order' => 0,
            ]);

            ProductVariant::create([
                'product_id' => $p->id,
                'sku' => 'ZM-'.$i.'-A',
                'size' => $i % 2 ? 'M' : null,
                'color' => ['Black', 'White', 'Blue'][$i % 3],
                'price' => null,
                'stock_qty' => 40,
            ]);

            if ($i === 0) {
                FlashSale::create([
                    'product_id' => $p->id,
                    'sale_price' => 1999,
                    'starts_at' => now()->subHour(),
                    'ends_at' => now()->addDays(1),
                    'is_active' => true,
                ]);
            }
        }

        $customer = User::where('email', 'customer@alluringstyle.test')->first();
        $firstProduct = Product::first();
        if ($customer && $firstProduct) {
            Review::create([
                'product_id' => $firstProduct->id,
                'user_id' => $customer->id,
                'order_id' => null,
                'rating' => 5,
                'title' => 'Love it!',
                'body' => 'Great value and quick delivery.',
                'is_approved' => true,
            ]);
        }

        Banner::create([
            'title' => 'Mega style sale',
            'image' => 'https://picsum.photos/seed/banner1/1400/420',
            'link' => '#deals',
            'placement' => 'home_slider',
            'sort_order' => 0,
            'is_active' => true,
        ]);

        Banner::create([
            'title' => 'Gadget week',
            'image' => 'https://picsum.photos/seed/banner2/1400/420',
            'link' => '#trending',
            'placement' => 'home_slider',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->call(HeaderMenuSeeder::class);

        Coupon::create([
            'code' => 'WELCOME10',
            'coupon_type' => 'public',
            'type' => 'percent',
            'value' => 10,
            'min_cart' => 499,
            'max_discount' => 150,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonths(3),
            'usage_limit' => 5000,
            'used_count' => 0,
            'is_active' => true,
        ]);

        Coupon::create([
            'code' => 'STAFF50',
            'coupon_type' => 'internal',
            'type' => 'percent',
            'value' => 50,
            'min_cart' => 0,
            'max_discount' => 500,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
            'usage_limit' => 100,
            'used_count' => 0,
            'is_active' => true,
        ]);

        $this->call(BlogPostSeeder::class);
        $this->call(OrderManagementSeeder::class);
    }
}
