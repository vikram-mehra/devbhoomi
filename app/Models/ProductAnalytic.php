<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAnalytic extends Model
{
    protected $fillable = [
        'product_id', 'units_sold', 'revenue', 'units_returned',
        'last_sale_at', 'refreshed_at',
    ];

    protected $casts = [
        'revenue' => 'decimal:2',
        'last_sale_at' => 'datetime',
        'refreshed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
