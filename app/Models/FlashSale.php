<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class FlashSale extends Model
{
    protected $fillable = [
        'product_id', 'sale_price', 'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        $flush = function () {
            Cache::forget('home.flash_sales');
        };
        static::saved($flush);
        static::deleted($flush);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
