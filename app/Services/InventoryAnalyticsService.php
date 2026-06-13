<?php

namespace App\Services;

use App\Models\MenuItem;
use App\Support\MenuItemTree;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryAnalyticsService
{
    protected int $cacheTtl = 90;

    public function overview(?Carbon $from = null, ?Carbon $to = null): array
    {
        $paidOrderQuery = Order::query()->where('payment_status', 'paid');
        if ($from) {
            $paidOrderQuery->where('created_at', '>=', $from->copy()->startOfDay());
        }
        if ($to) {
            $paidOrderQuery->where('created_at', '<=', $to->copy()->endOfDay());
        }

        $orderIds = (clone $paidOrderQuery)->pluck('id');

        $totalProducts = Product::count();
        $totalVariants = (int) ProductVariant::count();
        $totalStock = (int) ProductVariant::sum('stock_qty');
        $unitsSoldPeriod = $orderIds->isEmpty() ? 0 : (int) DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->sum('qty');
        $unitsSoldAll = (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->sum('order_items.qty');

        $revenue = (float) (clone $paidOrderQuery)->sum('total');
        $ordersCount = (int) (clone $paidOrderQuery)->count();
        $ordersPaidAllTime = (int) Order::where('payment_status', 'paid')->count();

        $reservedTotal = (int) DB::table('cart_items')->sum('qty');
        $returnedUnitsTotal = (int) DB::table('stock_movements')
            ->where('type', StockMovement::TYPE_RETURN_RESTORE)
            ->sum('qty_delta');
        $damagedTotal = (int) WarehouseStock::sum('damaged_qty');

        $pendingOrderUnits = (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'pending')
            ->where('orders.status', '!=', 'cancelled')
            ->sum('order_items.qty');

        $incomingQty = Schema::hasTable('purchase_order_lines')
            ? (int) DB::table('purchase_order_lines')
                ->whereRaw('qty_ordered > qty_received')
                ->sum(DB::raw('qty_ordered - qty_received'))
            : 0;

        $marginPct = (float) Setting::getValue('inventory_estimated_margin_percent', '25');
        $estimatedProfit = round($revenue * ($marginPct / 100), 2);

        $inventoryValue = (float) DB::table('product_variants')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->selectRaw('
                SUM(product_variants.stock_qty * COALESCE(product_variants.price, products.base_price)) as v
            ')
            ->value('v');

        $stockAddedLifetimeEstimate = $totalStock + $unitsSoldAll - $returnedUnitsTotal;

        $outOfStockProducts = Product::query()
            ->whereDoesntHave('variants', fn ($q) => $q->where('stock_qty', '>', 0))
            ->count();

        $lowStockVariants = ProductVariant::query()
            ->where('status', 'active')
            ->where('stock_qty', '>', 0)
            ->where('stock_qty', '<', 5)
            ->count();

        $balanceAfterReserved = max(0, $totalStock - $reservedTotal);

        return [
            'total_products' => $totalProducts,
            'total_variants' => $totalVariants,
            'total_stock_available' => $totalStock,
            'total_units_sold_period' => $unitsSoldPeriod,
            'total_units_sold_all' => $unitsSoldAll,
            'remaining_stock' => $balanceAfterReserved,
            'reserved_stock_total' => $reservedTotal,
            'returned_stock_total' => $returnedUnitsTotal,
            'damaged_stock_total' => $damagedTotal,
            'pending_order_units' => $pendingOrderUnits,
            'incoming_stock_units' => $incomingQty,
            'stock_added_lifetime_estimate' => max(0, $stockAddedLifetimeEstimate),
            'out_of_stock_products' => $outOfStockProducts,
            'low_stock_variants' => $lowStockVariants,
            'total_revenue' => $revenue,
            'total_orders' => $ordersCount,
            'total_orders_paid_all_time' => $ordersPaidAllTime,
            'estimated_gross_profit' => $estimatedProfit,
            'inventory_value_retail' => round((float) $inventoryValue, 2),
            'margin_percent_setting' => $marginPct,
        ];
    }

    public function reservedByVariant(): array
    {
        return Cache::remember('inv.reserved.v1', 45, function () {
            return DB::table('cart_items')
                ->select('product_variant_id', DB::raw('SUM(qty) as reserved'))
                ->groupBy('product_variant_id')
                ->pluck('reserved', 'product_variant_id')
                ->map(fn ($v) => (int) $v)
                ->all();
        });
    }

    public function reservedByProduct(): array
    {
        return Cache::remember('inv.reserved_prod.v1', 45, function () {
            return DB::table('cart_items')
                ->join('product_variants', 'product_variants.id', '=', 'cart_items.product_variant_id')
                ->selectRaw('product_variants.product_id as pid, SUM(cart_items.qty) as r')
                ->groupBy('product_variants.product_id')
                ->pluck('r', 'pid')
                ->map(fn ($v) => (int) $v)
                ->all();
        });
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection<int, Product>
     */
    public function productAnalyticsTable(Request $request, bool $paginate = true)
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->copy()->startOfDay() : null;
        $to = $request->get('to') ? Carbon::parse($request->get('to'))->copy()->endOfDay() : null;

        $soldSub = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.payment_status', 'paid')
            ->when($from, fn ($q) => $q->where('orders.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('orders.created_at', '<=', $to))
            ->selectRaw('
                product_variants.product_id as pid,
                SUM(order_items.qty) as sold_qty,
                SUM(order_items.line_total) as revenue
            ')
            ->groupBy('product_variants.product_id');

        $lastSaleSub = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('product_variants.product_id as pid, MAX(orders.created_at) as last_sale_at')
            ->groupBy('product_variants.product_id');

        $returnedSub = DB::table('stock_movements')
            ->join('product_variants', 'product_variants.id', '=', 'stock_movements.product_variant_id')
            ->where('stock_movements.type', StockMovement::TYPE_RETURN_RESTORE)
            ->selectRaw('product_variants.product_id as pid, SUM(stock_movements.qty_delta) as returned_qty')
            ->groupBy('product_variants.product_id');

        $q = Product::query()
            ->with(['menuItem', 'vendor', 'variants'])
            ->leftJoinSub($soldSub, 'sold', function ($join) {
                $join->on('products.id', '=', 'sold.pid');
            })
            ->leftJoinSub($lastSaleSub, 'ls', function ($join) {
                $join->on('products.id', '=', 'ls.pid');
            })
            ->leftJoinSub($returnedSub, 'ret', function ($join) {
                $join->on('products.id', '=', 'ret.pid');
            })
            ->selectRaw('
                products.*,
                COALESCE(sold.sold_qty, 0) as analytics_sold_qty,
                COALESCE(sold.revenue, 0) as analytics_revenue,
                ls.last_sale_at as analytics_last_sale_at,
                COALESCE(ret.returned_qty, 0) as analytics_returned_qty
            ');

        if ($request->filled('menu_item_id')) {
            $q->where('products.menu_item_id', (int) $request->get('menu_item_id'));
        }

        if ($request->filled('stock_status')) {
            match ($request->get('stock_status')) {
                'out' => $q->whereDoesntHave('variants', fn ($vq) => $vq->where('stock_qty', '>', 0)),
                'low' => $q->whereHas('variants', fn ($vq) => $vq->where('stock_qty', '>', 0)->where('stock_qty', '<', 5)),
                'ok' => $q->whereHas('variants', fn ($vq) => $vq->where('stock_qty', '>=', 5)),
                default => null,
            };
        }

        if (Schema::hasColumn('products', 'brand') && $request->filled('brand')) {
            $q->where('products.brand', $request->get('brand'));
        }

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('products.name', 'like', $term)
                    ->orWhere('products.sku', 'like', $term)
                    ->orWhereHas('variants', fn ($vq) => $vq->where('sku', 'like', $term));
                if (Schema::hasColumn('products', 'barcode')) {
                    $qq->orWhere('products.barcode', 'like', $term);
                }
            });
        }

        $q->orderByDesc('analytics_revenue');

        return $paginate
            ? $q->paginate(40)->withQueryString()
            : $q->get();
    }

    public function fastMoving(?Carbon $from = null, ?Carbon $to = null, int $limit = 10): array
    {
        $days = 30;
        $to = ($to ?? now())->copy()->endOfDay();
        $from = ($from ?? now()->copy()->subDays($days))->copy()->startOfDay();

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$from, $to])
            ->selectRaw('
                products.id as product_id,
                products.name,
                products.sku,
                SUM(order_items.qty) as sold_qty,
                SUM(order_items.line_total) as revenue
            ')
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('sold_qty')
            ->limit($limit)
            ->get();

        $prevTo = $from->copy()->subSecond();
        $prevFrom = $from->copy()->subDays($days);

        $prevMap = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$prevFrom, $prevTo])
            ->selectRaw('product_variants.product_id as pid, SUM(order_items.qty) as c')
            ->groupBy('product_variants.product_id')
            ->pluck('c', 'pid');

        return $rows->map(function ($r) use ($prevMap) {
            $cur = (int) $r->sold_qty;
            $prev = (int) ($prevMap[$r->product_id] ?? 0);
            $growth = $prev > 0 ? round((($cur - $prev) / $prev) * 100, 1) : ($cur > 0 ? 100.0 : 0.0);
            $stockLeft = (int) ProductVariant::where('product_id', $r->product_id)->sum('stock_qty');

            return [
                'product_id' => $r->product_id,
                'name' => $r->name,
                'sku' => $r->sku,
                'sold_qty' => $cur,
                'revenue' => (float) $r->revenue,
                'remaining_stock' => $stockLeft,
                'growth_pct' => $growth,
            ];
        })->all();
    }

    public function slowMoving(int $limit = 15): array
    {
        $soldRecently = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(90))
            ->pluck('product_variants.product_id');

        $soldIds = $soldRecently->unique()->filter()->values()->all();

        $q = Product::query()
            ->withSum(['variants as stock_sum'], 'stock_qty');
        if ($soldIds !== []) {
            $q->whereNotIn('id', $soldIds);
        }

        return $q
            ->orderByDesc('stock_sum')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => [
                'product_id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'stock' => (int) ($p->stock_sum ?? 0),
                'reason' => __('No paid sales in the last 90 days'),
            ])
            ->all();
    }

    public function variantComboStats(): array
    {
        return Cache::remember('inv.variant_combo.v2', $this->cacheTtl, function () {
            return DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
                ->join('products', 'products.id', '=', 'product_variants.product_id')
                ->where('orders.payment_status', 'paid')
                ->selectRaw('
                    products.name as product_name,
                    product_variants.sku,
                    SUM(order_items.qty) as sold,
                    SUM(order_items.line_total) as revenue
                ')
                ->groupBy('product_variants.id', 'products.name', 'product_variants.sku')
                ->orderByDesc('sold')
                ->limit(50)
                ->get()
                ->map(fn ($r) => [
                    'product_name' => $r->product_name,
                    'sku' => $r->sku,
                    'sold' => (int) $r->sold,
                    'revenue' => (float) $r->revenue,
                ])
                ->all();
        });
    }

    public function trendSeries(int $days = 30): array
    {
        $start = now()->subDays($days)->startOfDay();

        $sales = DB::table('orders')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as d, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $stock = DB::table('stock_movements')
            ->where('created_at', '>=', $start)
            ->where('type', StockMovement::TYPE_CHECKOUT_DEDUCT)
            ->selectRaw('DATE(created_at) as d, SUM(ABS(qty_delta)) as units_out')
            ->groupBy('d')
            ->get()
            ->keyBy('d');

        $labels = [];
        $rev = [];
        $ord = [];
        $invOut = [];
        for ($i = $days; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $labels[] = $d;
            $rev[] = round((float) ($sales[$d]->revenue ?? 0), 2);
            $ord[] = (int) ($sales[$d]->orders ?? 0);
            $invOut[] = (int) ($stock[$d]->units_out ?? 0);
        }

        return compact('labels', 'rev', 'ord', 'invOut');
    }

    public function dailyReport(Carbon $day): array
    {
        $start = $day->copy()->startOfDay();
        $end = $day->copy()->endOfDay();

        return [
            'date' => $day->toDateString(),
            'orders' => Order::whereBetween('created_at', [$start, $end])->count(),
            'paid_orders' => Order::where('payment_status', 'paid')->whereBetween('created_at', [$start, $end])->count(),
            'revenue' => (float) Order::where('payment_status', 'paid')->whereBetween('created_at', [$start, $end])->sum('total'),
            'items' => (int) DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', 'paid')
                ->whereBetween('orders.created_at', [$start, $end])
                ->sum('order_items.qty'),
        ];
    }

    public function clearCache(): void
    {
        foreach (['inv.variant_combo.v1', 'inv.variant_combo.v2', 'inv.color_sales.v1', 'inv.size_sales.v1', 'inv.reserved.v1', 'inv.reserved_prod.v1'] as $k) {
            Cache::forget($k);
        }
    }

    public function categoriesForFilter()
    {
        return collect(MenuItemTree::selectOptions())->map(fn ($o) => (object) ['id' => $o['id'], 'name' => trim(preg_replace('/^—\s*/', '', $o['label']))]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function brandsForFilter()
    {
        if (! Schema::hasColumn('products', 'brand')) {
            return collect();
        }

        return Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection<int, ProductVariant>
     */
    public function variantAnalyticsTable(Request $request, bool $paginate = true)
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->copy()->startOfDay() : null;
        $to = $request->get('to') ? Carbon::parse($request->get('to'))->copy()->endOfDay() : null;

        $soldSub = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->when($from, fn ($q) => $q->where('orders.created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('orders.created_at', '<=', $to))
            ->selectRaw('order_items.product_variant_id as vid, SUM(order_items.qty) as sold_qty, SUM(order_items.line_total) as revenue')
            ->groupBy('order_items.product_variant_id');

        $reservedSub = DB::table('cart_items')
            ->selectRaw('product_variant_id as vid, SUM(qty) as reserved')
            ->groupBy('product_variant_id');

        $q = ProductVariant::query()
            ->with(['product.vendor', 'product.menuItem'])
            ->leftJoinSub($soldSub, 'sold', function ($join) {
                $join->on('product_variants.id', '=', 'sold.vid');
            })
            ->leftJoinSub($reservedSub, 'res', function ($join) {
                $join->on('product_variants.id', '=', 'res.vid');
            })
            ->selectRaw('
                product_variants.*,
                COALESCE(sold.sold_qty, 0) as va_sold_qty,
                COALESCE(sold.revenue, 0) as va_revenue,
                COALESCE(res.reserved, 0) as va_reserved
            ');

        if ($request->filled('warehouse_id') && Schema::hasTable('warehouse_stocks')) {
            $wid = (int) $request->get('warehouse_id');
            $q->whereExists(function ($sub) use ($wid) {
                $sub->select(DB::raw(1))
                    ->from('warehouse_stocks')
                    ->whereColumn('warehouse_stocks.product_variant_id', 'product_variants.id')
                    ->where('warehouse_stocks.warehouse_id', $wid);
            });
        }

        if (Schema::hasColumn('products', 'brand') && $request->filled('brand')) {
            $b = $request->get('brand');
            $q->whereHas('product', fn ($pq) => $pq->where('brand', $b));
        }

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('product_variants.sku', 'like', $term)
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', $term));
            });
        }

        $q->orderByDesc(DB::raw('COALESCE(sold.revenue, 0)'));

        return $paginate
            ? $q->paginate(35, ['*'], 'variant_page')->withQueryString()
            : $q->get();
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function warehouseStockTotals()
    {
        if (! Schema::hasTable('warehouses')) {
            return collect();
        }

        return DB::table('warehouses')
            ->leftJoin('warehouse_stocks', 'warehouses.id', '=', 'warehouse_stocks.warehouse_id')
            ->selectRaw('warehouses.id, warehouses.name, COALESCE(SUM(warehouse_stocks.qty), 0) as qty')
            ->groupBy('warehouses.id', 'warehouses.name')
            ->orderBy('warehouses.name')
            ->get();
    }
}
