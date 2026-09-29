<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantOption extends Model
{
    protected $fillable = ['variant_label_id', 'value'];

    public function label(): BelongsTo
    {
        return $this->belongsTo(VariantLabel::class, 'variant_label_id');
    }
}
