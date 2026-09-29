<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class HeaderMenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuItem::query()->exists()) {
            return;
        }

        MenuItem::create([
            'title' => 'Home',
            'route_name' => 'market.home',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Categories',
            'sort_order' => 2,
            'is_active' => true,
            'is_mega' => true,
            'mega_use_categories' => true,
        ]);

        MenuItem::create([
            'title' => 'New arrivals',
            'url' => '/search?sort=newest',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Deals',
            'url' => '/#deals',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        MenuItem::create([
            'title' => 'Sell with us',
            'route_name' => 'vendor.register',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }
}
