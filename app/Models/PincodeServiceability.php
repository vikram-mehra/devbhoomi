<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PincodeServiceability extends Model
{
    protected $fillable = [
        'pincode',
        'city',
        'state',
        'status',
        'day_offset',
        'courier_name',
    ];

    protected $casts = [
        'status' => 'boolean',
        'day_offset' => 'integer',
    ];

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('status', true);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->status;
    }

    public static function normalizePincode(?string $pincode): string
    {
        return substr(preg_replace('/\D/', '', (string) $pincode) ?? '', 0, 6);
    }
}
