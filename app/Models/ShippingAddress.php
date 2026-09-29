<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingAddress extends Model
{
    protected $fillable = [
        'order_id',
        'name',
        'email',
        'phone',
        'line1',
        'line2',
        'city',
        'state',
        'pincode',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function fullAddress(): string
    {
        return collect([$this->line1, $this->line2, $this->city, $this->state, $this->pincode])
            ->filter(fn ($part) => filled($part))
            ->implode(', ');
    }
}
