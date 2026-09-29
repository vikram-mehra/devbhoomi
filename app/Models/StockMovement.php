<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const TYPE_CHECKOUT_DEDUCT = 'checkout_deduct';

    public const TYPE_CANCEL_RESTORE = 'cancel_restore';

    public const TYPE_PAYMENT_FAILED_RESTORE = 'payment_failed_restore';

    public const TYPE_RETURN_RESTORE = 'return_restore';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_SALE = 'sale';

    public const TYPE_DAMAGE = 'damage';

    public const TYPE_TRANSFER_OUT = 'transfer_out';

    public const TYPE_TRANSFER_IN = 'transfer_in';

    protected $fillable = [
        'product_variant_id', 'warehouse_id', 'type', 'qty_delta', 'balance_after',
        'order_id', 'return_id', 'purchase_id', 'sale_id', 'admin_user_id', 'note', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }
}
