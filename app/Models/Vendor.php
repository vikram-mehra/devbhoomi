<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'user_id', 'shop_name', 'slug', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'og_image',
        'logo', 'description', 'status',
        'commission_percent', 'city', 'state', 'rating_avg', 'rating_count',
    ];

    protected $casts = [
        'rating_avg' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
