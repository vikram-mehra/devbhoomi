<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            if (! Schema::hasColumn('menu_items', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (! Schema::hasColumn('menu_items', 'color_only_variants')) {
                $table->boolean('color_only_variants')->default(false)->after('slug');
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'menu_item_id')) {
                $table->foreignId('menu_item_id')->nullable()->after('vendor_id')->constrained('menu_items')->nullOnDelete();
            }
        });

        $this->migrateCategoriesToMenuItems();
        $this->migrateProductMenuItems();

        if (Schema::hasColumn('menu_items', 'category_id')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        if (Schema::hasColumn('menu_items', 'mega_use_categories')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('mega_use_categories');
            });
        }

        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }

        if (Schema::hasTable('categories')) {
            Schema::drop('categories');
        }

        foreach (DB::table('menu_items')->where(function ($q) {
            $q->whereNull('slug')->orWhere('slug', '');
        })->get(['id', 'title']) as $row) {
            DB::table('menu_items')->where('id', $row->id)->update([
                'slug' => $this->uniqueMenuSlug(Str::slug($row->title) ?: 'menu-'.$row->id, (int) $row->id),
            ]);
        }

        Schema::table('menu_items', function (Blueprint $table) {
            if (Schema::hasColumn('menu_items', 'slug')) {
                $table->unique('slug');
            }
        });
    }

    public function down(): void
    {
        // Irreversible without data loss — categories are not recreated.
    }

    private function migrateCategoriesToMenuItems(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        $categories = DB::table('categories')->orderByRaw('parent_id is null desc')->orderBy('parent_id')->orderBy('id')->get();
        $map = [];

        foreach ($categories as $cat) {
            $existingId = DB::table('menu_items')->where('category_id', $cat->id)->value('id');
            $parentMenuId = null;
            if ($cat->parent_id && isset($map[$cat->parent_id])) {
                $parentMenuId = $map[$cat->parent_id];
            } elseif ($cat->menu_item_id) {
                $parentMenuId = (int) $cat->menu_item_id;
            }

            $slug = $cat->slug ?: Str::slug($cat->name);
            $slug = $this->uniqueMenuSlug($slug, $existingId);

            $payload = [
                'parent_id' => $parentMenuId,
                'title' => $cat->name,
                'slug' => $slug,
                'color_only_variants' => (bool) ($cat->color_only_variants ?? false),
                'sort_order' => $cat->sort_order ?? 0,
                'is_active' => (bool) ($cat->is_active ?? true),
                'updated_at' => now(),
            ];

            if ($existingId) {
                DB::table('menu_items')->where('id', $existingId)->update($payload);
                $map[$cat->id] = (int) $existingId;
            } else {
                $payload['created_at'] = now();
                $map[$cat->id] = (int) DB::table('menu_items')->insertGetId($payload);
            }
        }

        foreach ($categories as $cat) {
            if (! $cat->parent_id || ! isset($map[$cat->id], $map[$cat->parent_id])) {
                continue;
            }
            DB::table('menu_items')->where('id', $map[$cat->id])->update(['parent_id' => $map[$cat->parent_id]]);
        }
    }

    private function migrateProductMenuItems(): void
    {
        if (! Schema::hasColumn('products', 'category_id')) {
            return;
        }

        $products = DB::table('products')->whereNotNull('category_id')->get(['id', 'category_id']);
        foreach ($products as $product) {
            $menuId = DB::table('menu_items')->where('category_id', $product->category_id)->value('id');
            if (! $menuId && Schema::hasTable('categories')) {
                $cat = DB::table('categories')->where('id', $product->category_id)->first();
                if ($cat) {
                    $menuId = DB::table('menu_items')->where('slug', $cat->slug)->value('id');
                }
            }
            if ($menuId) {
                DB::table('products')->where('id', $product->id)->update(['menu_item_id' => $menuId]);
            }
        }
    }

    private function uniqueMenuSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'item';
        $candidate = $base;
        $n = 0;
        while (true) {
            $q = DB::table('menu_items')->where('slug', $candidate);
            if ($ignoreId) {
                $q->where('id', '!=', $ignoreId);
            }
            if (! $q->exists()) {
                return $candidate;
            }
            $n++;
            $candidate = $base.'-'.$n;
        }
    }
};
