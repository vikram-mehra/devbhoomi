<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = ['name', 'code', 'is_enabled'];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
