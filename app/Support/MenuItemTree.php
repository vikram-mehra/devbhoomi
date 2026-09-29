<?php

namespace App\Support;

use App\Models\MenuItem;
use Illuminate\Support\Collection;

class MenuItemTree
{
    /**
     * @return Collection<int|string, Collection<int, MenuItem>>
     */
    public static function byParentKey(): Collection
    {
        return MenuItem::query()
            ->where('is_active', true)
            ->get(['id', 'parent_id'])
            ->groupBy(function (MenuItem $item) {
                $p = $item->parent_id;
                if ($p === null || $p === '') {
                    return '__root__';
                }

                return (int) $p;
            });
    }

    /**
     * @return list<int>
     */
    public static function subtreeIds(int $rootId, ?Collection $byParent = null): array
    {
        $byParent ??= self::byParentKey();
        $ids = [$rootId];
        $children = $byParent->get($rootId, collect());
        foreach ($children as $child) {
            $ids = array_merge($ids, self::subtreeIds($child->id, $byParent));
        }

        return $ids;
    }

    /**
     * Flat options for &lt;select&gt; (indented by depth).
     *
     * @return list<array{id:int,label:string,depth:int,color_only:bool}>
     */
    public static function selectOptions(bool $activeOnly = true): array
    {
        $q = MenuItem::query()->orderBy('sort_order')->orderBy('title');
        if ($activeOnly) {
            $q->where('is_active', true);
        }
        $items = $q->get(['id', 'parent_id', 'title', 'color_only_variants']);
        $byParent = $items->groupBy(fn (MenuItem $m) => $m->parent_id ? (int) $m->parent_id : '__root__');
        $out = [];

        $walk = function ($parentKey, int $depth) use (&$walk, &$out, $byParent): void {
            foreach ($byParent->get($parentKey, collect()) as $item) {
                $out[] = [
                    'id' => $item->id,
                    'label' => str_repeat('— ', $depth).$item->title,
                    'depth' => $depth,
                    'color_only' => $item->isColorOnlyMenu(),
                ];
                $walk((int) $item->id, $depth + 1);
            }
        };

        $walk('__root__', 0);

        return $out;
    }
}
