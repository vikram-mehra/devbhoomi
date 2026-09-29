<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'invoice_number', 'supplier_id', 'warehouse_id', 'purchase_date',
        'subtotal', 'tax_percent', 'tax_amount', 'delivery_charge', 'total_amount',
        'payment_status', 'notes', 'admin_user_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
