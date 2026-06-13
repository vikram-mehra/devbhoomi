<?php

namespace App\Services;

use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuItemService
{
    public const CACHE_HEADER = 'layout.header_menu';

    public const CACHE_HOME_DEPARTMENTS = 'home.menu_departments';

    public const CACHE_API_TREE = 'api.menu_tree';

    /** @var list<string> */
    private const LEGACY_CACHE_KEYS = [
        'home.categories.v2',
        'home.categories.v5',
    ];

    public function cacheSeconds(): int
    {
        return max(0, (int) config('menu.cache_seconds', 0));
    }

    public function shouldCache(): bool
    {
        return $this->cacheSeconds() > 0;
    }

    public function flushCache(): void
    {
        foreach ([
            self::CACHE_HEADER,
            self::CACHE_HOME_DEPARTMENTS,
            self::CACHE_API_TREE,
            ...self::LEGACY_CACHE_KEYS,
        ] as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Active root menu items with nested children (header / mobile nav).
     */
    public function headerTree(): Collection
    {
        $loader = fn () => $this->queryRootTree();

        if (! $this->shouldCache()) {
            return $loader();
        }

        return Cache::remember(self::CACHE_HEADER, $this->cacheSeconds(), $loader);
    }

    /**
     * Root items with slugs for homepage “Shop by menu” grid.
     */
    public function homepageDepartments(): Collection
    {
        $loader = fn () => MenuItem::query()
            ->roots()
            ->active()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->with([
                'children' => fn ($q) => $q->active()
                    ->whereNotNull('slug')
                    ->where('slug', '!=', '')
                    ->orderBy('sort_order')
                    ->orderBy('id'),
            ])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->limit(24)
            ->get();

        if (! $this->shouldCache()) {
            return $loader();
        }

        return Cache::remember(self::CACHE_HOME_DEPARTMENTS, $this->cacheSeconds(), $loader);
    }

    /**
     * Flat list of root + child links for footer (active only).
     */
    public function footerLinks(int $limit = 12): Collection
    {
        $links = collect();

        foreach ($this->headerTree() as $root) {
            $links->push($root);
            foreach ($root->children as $child) {
                if ($child->is_active) {
                    $links->push($child);
                }
            }
        }

        return $links->take($limit);
    }

    /**
     * Full tree for API consumers.
     */
    public function apiTree(): Collection
    {
        $loader = fn () => $this->queryRootTree();

        if (! $this->shouldCache()) {
            return $loader();
        }

        return Cache::remember(self::CACHE_API_TREE, $this->cacheSeconds(), $loader);
    }

    /**
     * @param  array<int, int>  $sortMap  menu_item_id => sort_order
     */
    public function applySortOrder(array $sortMap): void
    {
        if ($sortMap === []) {
            return;
        }

        MenuItem::withoutEvents(function () use ($sortMap) {
            foreach ($sortMap as $id => $sortOrder) {
                MenuItem::query()
                    ->whereKey((int) $id)
                    ->update(['sort_order' => max(0, (int) $sortOrder)]);
            }
        });

        $this->flushCache();
    }

    protected function queryRootTree(): Collection
    {
        return MenuItem::query()
            ->roots()
            ->active()
            ->with($this->nestedChildrenWith())
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /** @return array<string, mixed> */
    protected function nestedChildrenWith(): array
    {
        return [
            'children' => fn ($q) => $q->active()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->with([
                    'children' => fn ($q2) => $q2->active()
                        ->orderBy('sort_order')
                        ->orderBy('id'),
                ]),
        ];
    }
}
