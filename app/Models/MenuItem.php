<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\MenuItemService;
use App\Support\MenuItemTree;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MenuItem extends Model
{
    protected $fillable = [
        'parent_id', 'title', 'slug', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image',
        'color_only_variants', 'url', 'route_name', 'route_parameters',
        'sort_order', 'is_active', 'is_mega', 'is_group_heading', 'image_url', 'target_blank',
    ];

    protected $casts = [
        'route_parameters' => 'array',
        'is_active' => 'boolean',
        'is_mega' => 'boolean',
        'is_group_heading' => 'boolean',
        'color_only_variants' => 'boolean',
        'target_blank' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = static function () {
            app(MenuItemService::class)->flushCache();
        };
        static::saved($flush);
        static::deleted($flush);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Storefront products under this menu (and sub-menus) for header dropdown when no child links exist.
     */
    public function dropdownProducts(int $limit = 15): Collection
    {
        return Product::query()
            ->storefront()
            ->whereIn('menu_item_id', MenuItemTree::subtreeIds($this->id))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('parent_id')->orWhere('parent_id', 0);
        });
    }

    /**
     * Menu slugs that point to fixed site pages (not product listing /menu/{slug}).
     */
    public static function builtInPageRouteName(string $slug): ?string
    {
        $key = Str::lower(trim($slug));
        $map = [
            'home' => 'market.home',
            'about-us' => 'pages.about',
            'about' => 'pages.about',
            'contact-us' => 'pages.contact',
            'contact' => 'pages.contact',
        ];

        return $map[$key] ?? null;
    }

    public function isBuiltInPage(): bool
    {
        return $this->slug && static::builtInPageRouteName($this->slug) !== null;
    }

    public function resolvedUrl(): string
    {
        try {
            if ($this->slug) {
                $routeName = static::builtInPageRouteName($this->slug);
                if ($routeName !== null) {
                    return route($routeName);
                }

                return route('shop.menu', $this->slug);
            }
            if ($this->route_name) {
                return route($this->route_name, $this->route_parameters ?? []);
            }
            if ($this->url !== null && $this->url !== '') {
                $u = trim($this->url);
                if (preg_match('#^https?://#i', $u)) {
                    return $u;
                }
                if (str_starts_with($u, '/')) {
                    return url($u);
                }

                return url('/'.$u);
            }
        } catch (\Throwable) {
            return '#';
        }

        return '#';
    }

    public function hasDropdown(): bool
    {
        if ($this->relationLoaded('children')) {
            return $this->children->where('is_active', true)->isNotEmpty();
        }

        return $this->children()->active()->exists();
    }

    /**
     * Color-only variants (no size): Saree, dupatta-only, etc.
     */
    public function isColorOnlyMenu(): bool
    {
        if ($this->color_only_variants === true) {
            return true;
        }

        $slug = Str::lower(trim((string) $this->slug));
        $title = Str::lower(trim((string) $this->title));
        $haystack = $slug.' '.$title;

        if ($slug === '' && $title === '') {
            return false;
        }

        foreach (['saree', 'sari', 'shari'] as $kw) {
            if (Str::contains($slug, $kw) || Str::contains($title, $kw)) {
                return true;
            }
        }

        foreach (['साड़ी', 'साडी'] as $hi) {
            if (str_contains($haystack, $hi)) {
                return true;
            }
        }

        return false;
    }

    public function imageUrl(): ?string
    {
        if ($this->image_url === null || $this->image_url === '') {
            return null;
        }
        if (filter_var($this->image_url, FILTER_VALIDATE_URL)) {
            return $this->image_url;
        }

        return asset(ltrim($this->image_url, '/'));
    }
}
