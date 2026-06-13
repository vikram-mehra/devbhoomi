<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'invoice_number', 'warehouse_id', 'customer_name', 'customer_email', 'customer_phone',
        'sale_date', 'subtotal', 'tax_amount', 'coupon_code', 'discount_amount', 'total_amount',
        'payment_status', 'order_status', 'notes', 'admin_user_id', 'stock_applied_at',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'stock_applied_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function shouldApplyStock(): bool
    {
        return $this->stock_applied_at === null
            && $this->payment_status === 'paid'
            && in_array($this->order_status, ['confirmed', 'completed', 'shipped'], true);
    }
}
