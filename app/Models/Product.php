<?php

namespace App\Models;

use App\Services\ProductStorefrontService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $fillable = [
        'vendor_id', 'menu_item_id', 'name', 'slug', 'weight_kg', 'sku', 'barcode', 'brand', 'gst', 'hsn', 'description', 'short_description',
        'base_price', 'compare_price', 'rating_avg', 'rating_count', 'sales_count',
        'is_active', 'is_featured', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:3',
        'gst' => 'decimal:2',
        'base_price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'rating_avg' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = static function () {
            app(ProductStorefrontService::class)->flushHomeCaches();
        };
        static::saved($flush);
        static::deleted($flush);
    }

    public function scopeStorefront(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereHas('vendor', fn ($q) => $q->where('status', 'approved'));
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Variants shown on storefront (active only; stock checked per action).
     */
    public function storefrontVariants()
    {
        return $this->variants()->where('status', ProductVariant::STATUS_ACTIVE);
    }

    public function flashSale(): HasOne
    {
        return $this->hasOne(FlashSale::class)->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }

    /** Formatted weight for PDP (e.g. "1 kg", "0.5 kg"), or null if not set. */
    public function formattedWeightKg(): ?string
    {
        if ($this->weight_kg === null || $this->weight_kg === '') {
            return null;
        }
        $kg = (float) $this->weight_kg;
        if ($kg <= 0) {
            return null;
        }
        $text = rtrim(rtrim(number_format($kg, 3, '.', ''), '0'), '.');

        return $text.' '.__('kg');
    }

    public function effectivePrice(): float
    {
        $flash = $this->relationLoaded('flashSale')
            ? $this->flashSale
            : $this->flashSale()->first();
        if ($flash) {
            return (float) $flash->sale_price;
        }

        return (float) $this->base_price;
    }

    /**
     * Public URL for a product_images.path value, or null if missing / use placeholder instead.
     * Picsum and other random stock URLs are ignored so card art matches the product title.
     */
    public static function publicImageUrl(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }
        $path = trim($path);
        if (Str::startsWith($path, ['http://', 'https://'])) {
            if (Str::contains(Str::lower($path), 'picsum.photos')) {
                return null;
            }

            return $path;
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return null;
        }

        return $disk->url($path);
    }

    /** Branded placeholder when no real image (teal blocks, product name as label). */
    public function namedPlaceholderUrl(bool $alternate = false): string
    {
        $bg = $alternate ? '0f766e' : '14b8a6';
        $text = rawurlencode(Str::limit($this->name, 24));

        return 'https://placehold.co/520x620/'.$bg.'/ffffff?text='.$text;
    }

    /**
     * Primary / secondary image URLs for product cards (listing + home swiper).
     */
    public function cardImageUrls(): array
    {
        $imgs = $this->relationLoaded('images')
            ? $this->images
            : $this->images()->get();

        $img1 = $imgs->first();
        $img2 = $imgs->skip(1)->first();

        $primaryResolved = ($img1 && $img1->path) ? self::publicImageUrl($img1->path) : null;
        $url1 = $primaryResolved ?? $this->namedPlaceholderUrl(false);

        $secondaryResolved = ($img2 && $img2->path) ? self::publicImageUrl($img2->path) : null;
        if ($secondaryResolved) {
            $url2 = $secondaryResolved;
        } elseif ($primaryResolved) {
            $url2 = $url1;
        } else {
            $url2 = $this->namedPlaceholderUrl(true);
        }

        return [$url1, $url2];
    }
}
