<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use Tests\TestCase;

class ShopMenuUrlTest extends TestCase
{
    public function test_our_products_url_is_products(): void
    {
        $item = new MenuItem(['slug' => 'our-products', 'title' => 'Our Products']);

        $this->assertSame(url('/products'), $item->resolvedUrl());
        $this->assertSame(url('/products'), route('shop.products'));
    }

    public function test_submenu_url_is_products_slug(): void
    {
        $item = new MenuItem(['slug' => 'millets', 'title' => 'Millets']);

        $this->assertSame(url('/products/millets'), $item->resolvedUrl());
        $this->assertSame(url('/products/millets'), route('shop.menu', 'millets'));
    }

    public function test_legacy_menu_urls_redirect(): void
    {
        $this->get('/menu/our-products')->assertRedirect('/products');
        $this->get('/menu/millets')->assertRedirect('/products/millets');
    }
}
