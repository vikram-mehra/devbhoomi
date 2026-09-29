<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'slug', 'email', 'phone', 'gst_number', 'contact_person',
        'address', 'pending_payment_amount', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'pending_payment_amount' => 'decimal:2',
    ];

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
