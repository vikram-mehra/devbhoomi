<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'session_id', 'product_variant_id', 'qty'];

    protected $casts = [
        'user_id' => 'integer',
        'product_variant_id' => 'integer',
        'qty' => 'integer',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
