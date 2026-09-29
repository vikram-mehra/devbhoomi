<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'product_id', 'sku', 'size', 'color', 'price', 'stock_qty', 'status', 'image',
    ];

    protected $casts = ['price' => 'decimal:2'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(ProductVariantImage::class, 'product_variant_id')->orderBy('sort_order');
    }

    public function isActiveStatus(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    /** Purchasable on storefront: active and in stock. */
    public function isBuyable(): bool
    {
        return $this->isActiveStatus() && (int) $this->stock_qty > 0;
    }

    public function variantImageUrl(): ?string
    {
        if ($this->image !== null && trim((string) $this->image) !== '') {
            return Product::publicImageUrl($this->image);
        }

        if ($this->relationLoaded('galleryImages')) {
            $first = $this->galleryImages->first();
        } else {
            $first = $this->galleryImages()->orderBy('sort_order')->first();
        }

        if ($first && filled($first->path)) {
            return Product::publicImageUrl($first->path);
        }

        return null;
    }

    public function unitPrice(): float
    {
        if ($this->price !== null && $this->price !== '') {
            return (float) $this->price;
        }

        return (float) ($this->product?->base_price ?? 0);
    }

    public function effectivePrice(): float
    {
        $this->loadMissing(['product.flashSale']);
        if ($this->product?->flashSale) {
            return (float) $this->product->flashSale->sale_price;
        }

        return $this->unitPrice();
    }

    public function label(): string
    {
        $parts = array_filter([$this->size, $this->color]);

        return implode(' / ', $parts) ?: 'Default';
    }

    public function comboKey(): string
    {
        $c = mb_strtolower(trim((string) ($this->color ?? '')));
        $s = mb_strtolower(trim((string) ($this->size ?? '')));

        return $c.'|'.$s;
    }
}
