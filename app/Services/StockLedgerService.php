<?php

namespace App\Services;

use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\ReturnModel;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransfer;
use Illuminate\Support\Facades\DB;

class StockLedgerService
{
    public function deductForOrderLine(ProductVariant $variant, int $qty, Order $order): void
    {
        if ($qty < 1) {
            return;
        }

        $variant->refresh();
        if ((int) $variant->stock_qty < $qty) {
            throw new \RuntimeException(__('Insufficient stock for variant :sku.', ['sku' => $variant->sku]));
        }
        $newBalance = (int) $variant->stock_qty - $qty;
        $variant->update(['stock_qty' => $newBalance]);
        $this->mirrorWarehouseQty($variant, $newBalance);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'type' => StockMovement::TYPE_CHECKOUT_DEDUCT,
            'qty_delta' => -$qty,
            'balance_after' => $newBalance,
            'order_id' => $order->id,
            'meta' => ['order_number' => $order->order_number],
        ]);
    }

    /**
     * Restore stock when Razorpay (or other online) payment fails or is abandoned.
     */
    public function restoreUnpaidOrderStock(Order $order, string $note = 'Payment failed'): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $already = StockMovement::query()
            ->where('order_id', $order->id)
            ->where('type', StockMovement::TYPE_PAYMENT_FAILED_RESTORE)
            ->exists();
        if ($already) {
            return;
        }

        $order->load('items.variant');
        DB::transaction(function () use ($order, $note) {
            foreach ($order->items as $item) {
                $variant = $item->variant;
                if (! $variant) {
                    continue;
                }
                $qty = (int) $item->qty;
                $variant->refresh();
                $newBalance = (int) $variant->stock_qty + $qty;
                $variant->update(['stock_qty' => $newBalance]);
                $this->mirrorWarehouseQty($variant, $newBalance);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => StockMovement::TYPE_PAYMENT_FAILED_RESTORE,
                    'qty_delta' => $qty,
                    'balance_after' => $newBalance,
                    'order_id' => $order->id,
                    'note' => $note,
                ]);
            }
        });
    }

    public function restoreOrderCancellation(Order $order, ?int $adminUserId = null): void
    {
        if ($order->status !== 'cancelled') {
            return;
        }

        $already = StockMovement::query()
            ->where('order_id', $order->id)
            ->where('type', StockMovement::TYPE_CANCEL_RESTORE)
            ->exists();
        if ($already) {
            return;
        }

        $order->load('items.variant');
        DB::transaction(function () use ($order, $adminUserId) {
            foreach ($order->items as $item) {
                $variant = $item->variant;
                if (! $variant) {
                    continue;
                }
                $qty = (int) $item->qty;
                $variant->refresh();
                $newBalance = (int) $variant->stock_qty + $qty;
                $variant->update(['stock_qty' => $newBalance]);
                $this->mirrorWarehouseQty($variant, $newBalance);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => StockMovement::TYPE_CANCEL_RESTORE,
                    'qty_delta' => $qty,
                    'balance_after' => $newBalance,
                    'order_id' => $order->id,
                    'admin_user_id' => $adminUserId,
                    'note' => 'Order cancelled',
                ]);
            }
        });
    }

    /**
     * Full order line restore when a return is approved (marketplace-level return).
     */
    public function restoreForApprovedReturn(ReturnModel $return, ?int $adminUserId = null): void
    {
        if (! ReturnModel::restoresStock($return->status)) {
            return;
        }

        $already = StockMovement::query()
            ->where('return_id', $return->id)
            ->where('type', StockMovement::TYPE_RETURN_RESTORE)
            ->exists();
        if ($already) {
            return;
        }

        $order = Order::with('items.variant')->find($return->order_id);
        if (! $order) {
            return;
        }

        DB::transaction(function () use ($order, $return, $adminUserId) {
            foreach ($order->items as $item) {
                $variant = $item->variant;
                if (! $variant) {
                    continue;
                }
                $qty = (int) $item->qty;
                $variant->refresh();
                $newBalance = (int) $variant->stock_qty + $qty;
                $variant->update(['stock_qty' => $newBalance]);
                $this->mirrorWarehouseQty($variant, $newBalance);

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'type' => StockMovement::TYPE_RETURN_RESTORE,
                    'qty_delta' => $qty,
                    'balance_after' => $newBalance,
                    'order_id' => $order->id,
                    'return_id' => $return->id,
                    'admin_user_id' => $adminUserId,
                    'note' => 'Return approved',
                ]);
            }
        });
    }

    public function mirrorWarehouseQty(ProductVariant $variant, int $qty, ?Warehouse $warehouse = null): void
    {
        $wh = $warehouse ?? Warehouse::defaultWarehouse();
        if (! $wh) {
            return;
        }

        WarehouseStock::query()->updateOrCreate(
            [
                'warehouse_id' => $wh->id,
                'product_variant_id' => $variant->id,
            ],
            ['qty' => max(0, $qty)]
        );
    }

    public function increaseFromPurchase(
        ProductVariant $variant,
        int $qty,
        Purchase $purchase,
        ?Warehouse $warehouse = null,
        ?int $adminUserId = null
    ): int {
        if ($qty < 1) {
            throw new \InvalidArgumentException(__('Purchase quantity must be greater than zero.'));
        }

        $variant->refresh();
        $newBalance = (int) $variant->stock_qty + $qty;
        $variant->update(['stock_qty' => $newBalance]);
        $this->mirrorWarehouseQty($variant, $newBalance, $warehouse);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse?->id,
            'type' => StockMovement::TYPE_PURCHASE,
            'qty_delta' => $qty,
            'balance_after' => $newBalance,
            'purchase_id' => $purchase->id,
            'admin_user_id' => $adminUserId,
            'note' => __('Purchase :invoice', ['invoice' => $purchase->invoice_number]),
            'meta' => ['invoice_number' => $purchase->invoice_number],
        ]);

        return $newBalance;
    }

    public function decreaseForSale(
        ProductVariant $variant,
        int $qty,
        Sale $sale,
        ?Warehouse $warehouse = null,
        ?int $adminUserId = null
    ): int {
        if ($qty < 1) {
            throw new \InvalidArgumentException(__('Sale quantity must be greater than zero.'));
        }

        $variant->refresh();
        if ((int) $variant->stock_qty < $qty) {
            throw new \RuntimeException(__('Insufficient stock for variant :sku.', ['sku' => $variant->sku]));
        }

        $newBalance = (int) $variant->stock_qty - $qty;
        $variant->update(['stock_qty' => $newBalance]);
        $this->mirrorWarehouseQty($variant, $newBalance, $warehouse);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse?->id,
            'type' => StockMovement::TYPE_SALE,
            'qty_delta' => -$qty,
            'balance_after' => $newBalance,
            'sale_id' => $sale->id,
            'admin_user_id' => $adminUserId,
            'note' => __('Sale :invoice', ['invoice' => $sale->invoice_number]),
            'meta' => ['invoice_number' => $sale->invoice_number],
        ]);

        return $newBalance;
    }

    public function applySaleStock(Sale $sale, ?int $adminUserId = null): void
    {
        if (! $sale->shouldApplyStock()) {
            return;
        }

        $sale->loadMissing(['items.variant', 'warehouse']);
        DB::transaction(function () use ($sale, $adminUserId) {
            foreach ($sale->items as $item) {
                $variant = $item->variant;
                if (! $variant) {
                    continue;
                }
                $this->decreaseForSale(
                    $variant,
                    (int) $item->quantity,
                    $sale,
                    $sale->warehouse,
                    $adminUserId
                );
            }
            $sale->update(['stock_applied_at' => now()]);
        });
    }

    public function adjustVariantStock(
        ProductVariant $variant,
        int $delta,
        string $type,
        ?Warehouse $warehouse = null,
        ?int $adminUserId = null,
        ?string $note = null,
        array $meta = []
    ): int {
        $variant->refresh();
        $newBalance = (int) $variant->stock_qty + $delta;
        if ($newBalance < 0) {
            throw new \RuntimeException(__('Stock cannot go below zero for variant :sku.', ['sku' => $variant->sku]));
        }

        $variant->update(['stock_qty' => $newBalance]);
        $this->mirrorWarehouseQty($variant, $newBalance, $warehouse);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'warehouse_id' => $warehouse?->id,
            'type' => $type,
            'qty_delta' => $delta,
            'balance_after' => $newBalance,
            'admin_user_id' => $adminUserId,
            'note' => $note,
            'meta' => $meta ?: null,
        ]);

        return $newBalance;
    }

    public function transferBetweenWarehouses(
        WarehouseTransfer $transfer,
        ?int $adminUserId = null
    ): void {
        $transfer->loadMissing(['lines.variant', 'fromWarehouse', 'toWarehouse']);
        if ($transfer->from_warehouse_id === $transfer->to_warehouse_id) {
            throw new \RuntimeException(__('Source and destination warehouse must be different.'));
        }

        DB::transaction(function () use ($transfer, $adminUserId) {
            foreach ($transfer->lines as $line) {
                $variant = $line->variant;
                if (! $variant) {
                    continue;
                }
                $qty = (int) $line->quantity;
                if ($qty < 1) {
                    continue;
                }

                $fromStock = WarehouseStock::query()->firstOrCreate(
                    [
                        'warehouse_id' => $transfer->from_warehouse_id,
                        'product_variant_id' => $variant->id,
                    ],
                    ['qty' => 0, 'damaged_qty' => 0]
                );
                if ((int) $fromStock->qty < $qty) {
                    throw new \RuntimeException(__('Insufficient stock in source warehouse for :sku.', ['sku' => $variant->sku]));
                }

                $fromStock->update(['qty' => (int) $fromStock->qty - $qty]);
                $toStock = WarehouseStock::query()->firstOrCreate(
                    [
                        'warehouse_id' => $transfer->to_warehouse_id,
                        'product_variant_id' => $variant->id,
                    ],
                    ['qty' => 0, 'damaged_qty' => 0]
                );
                $toStock->update(['qty' => (int) $toStock->qty + $qty]);

                if ($transfer->fromWarehouse?->is_default || $transfer->toWarehouse?->is_default) {
                    $variant->refresh();
                    $this->mirrorWarehouseQty($variant, (int) $variant->stock_qty, Warehouse::defaultWarehouse());
                }

                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'type' => StockMovement::TYPE_TRANSFER_OUT,
                    'qty_delta' => -$qty,
                    'balance_after' => (int) $fromStock->qty,
                    'admin_user_id' => $adminUserId,
                    'note' => __('Transfer :ref out', ['ref' => $transfer->reference]),
                    'meta' => ['transfer_id' => $transfer->id],
                ]);
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'type' => StockMovement::TYPE_TRANSFER_IN,
                    'qty_delta' => $qty,
                    'balance_after' => (int) $toStock->qty,
                    'admin_user_id' => $adminUserId,
                    'note' => __('Transfer :ref in', ['ref' => $transfer->reference]),
                    'meta' => ['transfer_id' => $transfer->id],
                ]);
            }
        });
    }

    public function recordInventoryAction(
        string $action,
        ?int $adminUserId,
        ?string $subjectType,
        ?int $subjectId,
        ?string $description = null,
        array $payload = []
    ): void {
        InventoryLog::create([
            'admin_user_id' => $adminUserId,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'payload' => $payload ?: null,
        ]);
    }

    /** After admin creates a new variant row, ensure warehouse stock row exists. */
    public function ensureWarehouseRow(ProductVariant $variant): void
    {
        $wh = Warehouse::defaultWarehouse();
        if (! $wh) {
            return;
        }
        WarehouseStock::query()->firstOrCreate(
            [
                'warehouse_id' => $wh->id,
                'product_variant_id' => $variant->id,
            ],
            ['qty' => (int) $variant->stock_qty, 'damaged_qty' => 0]
        );
    }
}
