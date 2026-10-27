<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductShowcaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.driver', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('database.connections.sqlite.prefix', '');
        config()->set('database.connections.sqlite.foreign_key_constraints', false);
        config()->set('app.url', 'http://localhost');

        DB::purge();
        DB::reconnect();
        $this->createSchema();
    }

    public function test_showcase_uses_dynamic_product_slug_and_links_to_other_showcases(): void
    {
        $vendorId = $this->makeApprovedVendor();
        $this->makeProduct($vendorId, 'showcase-tea', 'Showcase Tea', 'A fragrant tea description.');
        $this->makeProduct($vendorId, 'showcase-rice', 'Showcase Rice', 'A mountain rice description.');

        $this->get('/showcase/showcase-tea')
            ->assertOk()
            ->assertSee('Showcase Tea')
            ->assertSee('A fragrant tea description.')
            ->assertSee('₹125.00')
            ->assertSee('https://example.test/showcase-tea.jpg')
            ->assertSee('/showcase/showcase-rice')
            ->assertSee(__('Other Products'))
            ->assertSee(__('View Details'))
            ->assertSee('pro-page-hero', false)
            ->assertSee('showcase-card-image-backdrop', false)
            ->assertSee('object-fit: contain', false)
            ->assertSee('noindex, nofollow');
    }

    public function test_showcase_preserves_rich_product_description_formatting(): void
    {
        $vendorId = $this->makeApprovedVendor();
        $this->makeProduct(
            $vendorId,
            'formatted-showcase-product',
            'Formatted Product',
            '<p>Hand-picked product.</p><ul><li>First benefit</li><li>Second benefit</li></ul>'
        );

        $this->get('/showcase/formatted-showcase-product')
            ->assertOk()
            ->assertSee('<p>Hand-picked product.</p>', false)
            ->assertSee('<ul><li>First benefit</li><li>Second benefit</li></ul>', false);
    }

    public function test_showcase_returns_404_for_missing_or_non_storefront_products(): void
    {
        $approvedVendorId = $this->makeApprovedVendor();
        $this->makeProduct($approvedVendorId, 'inactive-showcase-product', 'Inactive Product', '', false);
        $pendingVendorId = $this->makeApprovedVendor('pending');
        $this->makeProduct($pendingVendorId, 'pending-showcase-product', 'Pending Product');

        $this->get('/showcase/does-not-exist')->assertNotFound();
        $this->get('/showcase/inactive-showcase-product')->assertNotFound();
        $this->get('/showcase/pending-showcase-product')->assertNotFound();
    }

    public function test_showcase_handles_missing_description_and_paginates_other_products(): void
    {
        $vendorId = $this->makeApprovedVendor();
        $this->makeProduct($vendorId, 'current-showcase-product', 'Current Product', '', true, false);
        for ($index = 1; $index <= 9; $index++) {
            $this->makeProduct($vendorId, 'other-showcase-'.$index, 'Other Showcase '.$index, '');
        }

        $firstPage = $this->get('/showcase/current-showcase-product')
            ->assertOk()
            ->assertSee('Current Product')
            ->assertSee(__('Other Products'))
            ->assertSee('other_page=2', false);

        preg_match_all('/<article class="showcase-card">(.*?)<\\/article>/s', $firstPage->getContent(), $cards);
        $this->assertCount(8, $cards[0]);
        $this->assertStringNotContainsString('Current Product', implode('', $cards[0]));

        $secondPage = $this->get('/showcase/current-showcase-product?other_page=2')
            ->assertOk()
            ->assertSee('Other Showcase 9');
        preg_match_all('/<article class="showcase-card">(.*?)<\\/article>/s', $secondPage->getContent(), $cards);
        $this->assertCount(1, $cards[0]);
        $this->assertStringNotContainsString('Current Product', implode('', $cards[0]));
    }

    protected function makeApprovedVendor(string $status = 'approved'): int
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Showcase Vendor',
            'email' => uniqid('vendor', true).'@example.test',
            'password' => 'password',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return (int) DB::table('vendors')->insertGetId([
            'user_id' => $userId,
            'shop_name' => 'Showcase Store',
            'slug' => uniqid('showcase-store-'),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function makeProduct(
        int $vendorId,
        string $slug,
        string $name,
        string $description = 'Product description.',
        bool $active = true,
        bool $withImage = true
    ): int {
        $productId = DB::table('products')->insertGetId([
            'vendor_id' => $vendorId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'short_description' => '',
            'base_price' => 125,
            'compare_price' => 150,
            'is_active' => $active,
            'brand' => 'Showcase Brand',
            'sku' => 'SKU-'.$slug,
            'weight_kg' => 0.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($active && $withImage) {
            DB::table('product_images')->insert([
                'product_id' => $productId,
                'path' => 'https://example.test/'.$slug.'.jpg',
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $productId;
    }

    protected function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->timestamps();
        });

        Schema::create('vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('shop_name');
            $table->string('slug');
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('menu_item_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('compare_price', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('brand')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->decimal('weight_kg', 8, 3)->nullable();
            $table->string('hsn')->nullable();
            $table->string('gst')->nullable();
            $table->string('variant_label')->nullable();
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });

        Schema::create('product_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock_qty')->default(0);
            $table->string('status')->default('active');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('product_variant_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_variant_id');
            $table->string('path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('flash_sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('sale_price', 12, 2);
            $table->boolean('is_active')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->nullable();
            $table->unsignedBigInteger('product_variant_id');
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('shipping_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('free_shipping_amount', 12, 2)->default(500);
            $table->decimal('shipping_charge', 12, 2)->default(50);
            $table->unsignedInteger('base_weight_grams')->default(500);
            $table->decimal('additional_500g_charge', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }
}
