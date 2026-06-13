<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class MenuSubmenuAndProductSeeder extends Seeder
{
    public function run(): void
    {
        $collections = MenuItem::updateOrCreate(
            ['parent_id' => null, 'title' => 'Collections'],
            [
                'url' => '/search',
                'sort_order' => 6,
                'is_active' => true,
                'is_mega' => false,
                'mega_use_categories' => false,
            ]
        );

        MenuItem::updateOrCreate(
            ['parent_id' => $collections->id, 'title' => 'Men Fashion'],
            ['url' => '/search?q=men', 'sort_order' => 1, 'is_active' => true]
        );
        MenuItem::updateOrCreate(
            ['parent_id' => $collections->id, 'title' => 'Women Fashion'],
            ['url' => '/search?q=women', 'sort_order' => 2, 'is_active' => true]
        );
        MenuItem::updateOrCreate(
            ['parent_id' => $collections->id, 'title' => 'Footwear'],
            ['url' => '/search?q=shoes', 'sort_order' => 3, 'is_active' => true]
        );

        $vendor = Vendor::query()->first();
        $categories = Category::query()->where('is_active', true)->orderBy('id')->pluck('id')->values();

        if ($vendor && $categories->isNotEmpty()) {
            $products = [
                [
                    'slug' => 'alluringstyle-casual-shirt',
                    'name' => 'Alluringstyle Casual Shirt',
                    'sku' => 'ALS-CSH-001',
                    'short_description' => 'Comfort fit shirt for daily style.',
                    'description' => 'Breathable cotton blend shirt designed for everyday comfort and style.',
                    'base_price' => 1299,
                    'compare_price' => 1899,
                    'rating_avg' => 4.4,
                    'rating_count' => 12,
                    'sales_count' => 45,
                    'is_featured' => true,
                    'size' => 'M',
                    'color' => 'Navy',
                ],
                [
                    'slug' => 'alluringstyle-denim-jacket',
                    'name' => 'Alluringstyle Denim Jacket',
                    'sku' => 'ALS-DJK-002',
                    'short_description' => 'Classic denim jacket for all seasons.',
                    'description' => 'Durable stretch denim jacket with relaxed fit and contrast stitching.',
                    'base_price' => 2299,
                    'compare_price' => 2999,
                    'rating_avg' => 4.5,
                    'rating_count' => 18,
                    'sales_count' => 62,
                    'is_featured' => true,
                    'size' => 'L',
                    'color' => 'Blue',
                ],
                [
                    'slug' => 'alluringstyle-athleisure-joggers',
                    'name' => 'Alluringstyle Athleisure Joggers',
                    'sku' => 'ALS-JGR-003',
                    'short_description' => 'Soft joggers for gym and casual wear.',
                    'description' => 'Moisture-friendly fabric joggers with tapered fit and zip pocket.',
                    'base_price' => 1499,
                    'compare_price' => 2099,
                    'rating_avg' => 4.3,
                    'rating_count' => 9,
                    'sales_count' => 28,
                    'is_featured' => false,
                    'size' => 'M',
                    'color' => 'Charcoal',
                ],
                [
                    'slug' => 'alluringstyle-summer-kurta-set',
                    'name' => 'Alluringstyle Summer Kurta Set',
                    'sku' => 'ALS-KRT-004',
                    'short_description' => 'Lightweight kurta set for festive comfort.',
                    'description' => 'Elegant printed kurta set with breathable lining and soft feel.',
                    'base_price' => 1899,
                    'compare_price' => 2599,
                    'rating_avg' => 4.6,
                    'rating_count' => 22,
                    'sales_count' => 74,
                    'is_featured' => true,
                    'size' => 'S',
                    'color' => 'Maroon',
                ],
                [
                    'slug' => 'alluringstyle-urban-sneakers',
                    'name' => 'Alluringstyle Urban Sneakers',
                    'sku' => 'ALS-SNK-005',
                    'short_description' => 'Daily wear sneakers with cushioned sole.',
                    'description' => 'Lightweight urban sneakers with anti-slip outsole and padded collar.',
                    'base_price' => 2199,
                    'compare_price' => 2899,
                    'rating_avg' => 4.2,
                    'rating_count' => 14,
                    'sales_count' => 39,
                    'is_featured' => false,
                    'size' => '42',
                    'color' => 'White',
                ],
                [
                    'slug' => 'alluringstyle-wireless-earbuds',
                    'name' => 'Alluringstyle Wireless Earbuds',
                    'sku' => 'ALS-EBD-006',
                    'short_description' => 'Crystal clear sound with long battery life.',
                    'description' => 'True wireless earbuds with quick charge support and deep bass output.',
                    'base_price' => 1799,
                    'compare_price' => 2499,
                    'rating_avg' => 4.1,
                    'rating_count' => 16,
                    'sales_count' => 51,
                    'is_featured' => false,
                    'size' => null,
                    'color' => 'Black',
                ],
            ];

            foreach ($products as $index => $row) {
                $categoryId = (int) $categories[$index % $categories->count()];
                $product = Product::updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'vendor_id' => $vendor->id,
                        'category_id' => $categoryId,
                        'name' => $row['name'],
                        'sku' => $row['sku'],
                        'short_description' => $row['short_description'],
                        'description' => $row['description'],
                        'base_price' => $row['base_price'],
                        'compare_price' => $row['compare_price'],
                        'rating_avg' => $row['rating_avg'],
                        'rating_count' => $row['rating_count'],
                        'sales_count' => $row['sales_count'],
                        'is_active' => true,
                        'is_featured' => $row['is_featured'],
                        'meta_title' => $row['name'],
                    ]
                );

                ProductImage::updateOrCreate(
                    ['product_id' => $product->id, 'sort_order' => 0],
                    ['path' => 'https://picsum.photos/seed/'.$row['slug'].'/800/1000']
                );

                ProductVariant::updateOrCreate(
                    ['product_id' => $product->id, 'sku' => $row['sku']],
                    ['size' => $row['size'], 'color' => $row['color'], 'price' => $row['base_price'], 'stock_qty' => 40]
                );
            }
        }

        app(\App\Services\MenuItemService::class)->flushCache();
        Cache::forget('home.categories.v2');
        Cache::forget('home.featured');
        Cache::forget('home.trending');
        Cache::forget('home.newest');
    }
}
