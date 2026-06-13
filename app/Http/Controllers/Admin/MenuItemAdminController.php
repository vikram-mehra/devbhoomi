<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\MenuItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MenuItemAdminController extends Controller
{
    /** @var MenuItemService */
    private $menus;

    public function __construct(MenuItemService $menus)
    {
        $this->menus = $menus;
    }

    public function index()
    {
        $items = MenuItem::with('parent')
            ->orderByRaw('parent_id is null desc')
            ->orderBy('parent_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $parents = MenuItem::roots()->orderBy('sort_order')->orderBy('id')->get();
        $parentChoices = MenuItem::with('parent')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.menu-items', compact('items', 'parents', 'parentChoices'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        MenuItem::create($data);
        $this->menus->flushCache();

        return back()->with('status', __('Menu item added.'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $this->validated($request, $menuItem->id);
        $menuItem->update($data);
        $this->menus->flushCache();

        return back()->with('status', __('Menu item updated.'));
    }

    public function destroy(MenuItem $menuItem)
    {
        if ($menuItem->products()->exists()) {
            return back()->withErrors(['menu' => __('Cannot delete: products are assigned to this menu item. Reassign them first.')]);
        }

        $menuItem->delete();
        $this->menus->flushCache();

        return back()->with('status', __('Menu item removed.'));
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'sort' => 'required|array',
            'sort.*' => 'integer|min:0|max:65535',
        ]);

        $sortMap = [];
        foreach ($validated['sort'] as $id => $order) {
            $sortMap[(int) $id] = (int) $order;
        }

        $this->menus->applySortOrder($sortMap);

        return back()->with('status', __('Menu order updated.'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $parentRule = 'nullable|exists:menu_items,id';
        if ($ignoreId) {
            $parentRule .= '|not_in:'.$ignoreId;
        }

        $slugRule = 'nullable|string|max:255|unique:menu_items,slug';
        if ($ignoreId) {
            $slugRule .= ','.$ignoreId;
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => $slugRule,
            'parent_id' => $parentRule,
            'url' => 'nullable|string|max:2048',
            'route_name' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'sometimes|boolean',
            'target_blank' => 'sometimes|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:2048',
            'og_image' => 'nullable|string|max:2048',
        ]);

        $slug = trim((string) ($validated['slug'] ?? ''));
        if ($slug === '') {
            $slug = Str::slug($validated['title']);
        }
        $slug = $this->uniqueSlug($slug, $ignoreId);

        $parentId = $validated['parent_id'] ?? null;
        if ($parentId === '' || $parentId === '0' || $parentId === 0) {
            $parentId = null;
        }

        return [
            'parent_id' => $parentId,
            'title' => $validated['title'],
            'slug' => $slug,
            'url' => $validated['url'] ?: null,
            'route_name' => $validated['route_name'] ?: null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
            'is_mega' => false,
            'is_group_heading' => false,
            'color_only_variants' => false,
            'target_blank' => $request->boolean('target_blank'),
            'meta_title' => trim((string) ($validated['meta_title'] ?? '')) ?: null,
            'meta_description' => trim((string) ($validated['meta_description'] ?? '')) ?: null,
            'meta_keywords' => trim((string) ($validated['meta_keywords'] ?? '')) ?: null,
            'canonical_url' => trim((string) ($validated['canonical_url'] ?? '')) ?: null,
            'og_image' => trim((string) ($validated['og_image'] ?? '')) ?: null,
        ];
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'menu';
        $candidate = $base;
        $n = 0;
        while (true) {
            $q = MenuItem::query()->where('slug', $candidate);
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
}
