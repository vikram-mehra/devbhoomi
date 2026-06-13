<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TransactionHubService
{
    public function summaries(): array
    {
        $purchasePending = (float) Purchase::query()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum('total_amount');

        $salePending = (float) Sale::query()
            ->whereIn('payment_status', ['pending', 'partial'])
            ->sum('total_amount');

        return [
            'total_purchase' => (float) Purchase::query()->sum('total_amount'),
            'total_sales' => (float) Sale::query()->sum('total_amount'),
            'pending_payments' => $purchasePending + $salePending,
            'low_stock' => (int) ProductVariant::query()
                ->where('status', ProductVariant::STATUS_ACTIVE)
                ->where('stock_qty', '>', 0)
                ->where('stock_qty', '<', 5)
                ->count(),
        ];
    }

    public function filterOptions(): array
    {
        return [
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    public function applyPurchaseFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $query->where('invoice_number', 'like', $term);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('purchase_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('purchase_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', (int) $request->get('supplier_id'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', (int) $request->get('warehouse_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        return $query;
    }

    public function applySaleFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $query->where(function ($qq) use ($term) {
                $qq->where('invoice_number', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhere('customer_phone', 'like', $term);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', (int) $request->get('warehouse_id'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        return $query;
    }
}
