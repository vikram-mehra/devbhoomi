<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariantLabel extends Model
{
    protected $fillable = ['name'];

    public function options(): HasMany
    {
        return $this->hasMany(VariantOption::class, 'variant_label_id');
    }
}
