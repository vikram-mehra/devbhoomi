<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['name', 'slug', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function stocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public static function defaultWarehouse(): ?self
    {
        return static::query()->where('is_default', true)->first()
            ?? static::query()->orderBy('id')->first();
    }
}
