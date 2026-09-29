<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$menus = app(\App\Services\MenuItemService::class);

echo 'DB: '.config('database.connections.mysql.database')."\n";
echo 'APP_URL: '.config('app.url')."\n";
echo 'menu cache_seconds: '.config('menu.cache_seconds')."\n";
echo 'header roots(active): '.$menus->headerTree()->count()."\n";

foreach (App\Models\MenuItem::orderBy('sort_order')->orderBy('id')->get() as $m) {
    echo $m->id.' | '.$m->title.' | parent='.($m->parent_id ?? 'NULL').' | active='.(int) $m->is_active.' | slug='.($m->slug ?? '').' | sort='.$m->sort_order."\n";
}
