<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;

class InventoryLedgerAdminController extends Controller
{
    public function index(Request $request)
    {
        $movements = StockMovement::query()
            ->with(['variant.product', 'warehouse', 'purchase', 'sale', 'order'])
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->get('type')))
            ->when($request->filled('variant_sku'), function ($q) use ($request) {
                $sku = trim((string) $request->get('variant_sku'));
                $q->whereHas('variant', fn ($vq) => $vq->where('sku', $sku));
            })
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $logs = InventoryLog::query()
            ->latest()
            ->limit(20)
            ->get();

        $types = [
            StockMovement::TYPE_PURCHASE => __('Purchase'),
            StockMovement::TYPE_SALE => __('Sale'),
            StockMovement::TYPE_ADJUSTMENT => __('Manual adjustment'),
            StockMovement::TYPE_DAMAGE => __('Damaged stock'),
            StockMovement::TYPE_CHECKOUT_DEDUCT => __('Checkout'),
            StockMovement::TYPE_CANCEL_RESTORE => __('Order cancel restore'),
            StockMovement::TYPE_RETURN_RESTORE => __('Return restore'),
            StockMovement::TYPE_TRANSFER_IN => __('Transfer in'),
            StockMovement::TYPE_TRANSFER_OUT => __('Transfer out'),
        ];

        return view('admin.inventory.ledger', compact('movements', 'logs', 'types'));
    }

    public function adjust(Request $request, StockLedgerService $ledger)
    {
        $data = $request->validate([
            'variant_sku' => 'required|string|max:64',
            'qty_delta' => 'required|integer|not_in:0|between:-99999,99999',
            'adjustment_type' => 'required|in:adjustment,damage',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'note' => 'nullable|string|max:500',
        ]);

        $variant = ProductVariant::query()->where('sku', trim($data['variant_sku']))->first();
        if (! $variant) {
            return back()->with('error', __('Variant SKU not found.'));
        }

        $warehouse = isset($data['warehouse_id'])
            ? Warehouse::query()->find($data['warehouse_id'])
            : Warehouse::defaultWarehouse();

        $type = $data['adjustment_type'] === 'damage'
            ? StockMovement::TYPE_DAMAGE
            : StockMovement::TYPE_ADJUSTMENT;

        try {
            $ledger->adjustVariantStock(
                $variant,
                (int) $data['qty_delta'],
                $type,
                $warehouse,
                $request->user()?->id,
                $data['note'] ?? ($type === StockMovement::TYPE_DAMAGE ? __('Damaged stock') : __('Manual adjustment'))
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($type === StockMovement::TYPE_DAMAGE && $warehouse && (int) $data['qty_delta'] < 0) {
            $row = \App\Models\WarehouseStock::query()
                ->where('warehouse_id', $warehouse->id)
                ->where('product_variant_id', $variant->id)
                ->first();
            if ($row) {
                $row->update(['damaged_qty' => (int) $row->damaged_qty + abs((int) $data['qty_delta'])]);
            }
        }

        $ledger->recordInventoryAction(
            'inventory.'.$data['adjustment_type'],
            $request->user()?->id,
            ProductVariant::class,
            $variant->id,
            $data['note'] ?? null,
            ['sku' => $variant->sku, 'qty_delta' => (int) $data['qty_delta']]
        );

        return back()->with('status', __('Stock updated for :sku.', ['sku' => $variant->sku]));
    }
}
