<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\InventoryAnalyticsService;
use App\Services\StockLedgerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryDashboardController extends Controller
{
    public function index(Request $request, InventoryAnalyticsService $analytics)
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->startOfDay() : null;
        $to = $request->get('to') ? Carbon::parse($request->get('to'))->endOfDay() : null;

        $overview = $analytics->overview($from, $to);
        $fast = $analytics->fastMoving($from, $to, 8);
        $slow = $analytics->slowMoving(12);
        $variantCombo = $analytics->variantComboStats();
        $trend = $analytics->trendSeries(30);
        $categories = $analytics->categoriesForFilter();
        $brands = $analytics->brandsForFilter();
        $warehouses = Schema::hasTable('warehouses')
            ? Warehouse::orderBy('name')->get(['id', 'name'])
            : collect();

        $variantInventory = $analytics->variantAnalyticsTable($request);

        $alerts = [
            'low' => ProductVariant::query()->where('status', 'active')->whereBetween('stock_qty', [1, 4])->count(),
            'out' => ProductVariant::query()->where('status', 'active')->where('stock_qty', '<=', 0)->count(),
            'overstock' => ProductVariant::query()->where('status', 'active')->where('stock_qty', '>', 500)->count(),
        ];

        $mostOrdered = Product::query()
            ->orderByDesc('sales_count')
            ->take(6)
            ->get(['id', 'slug', 'name', 'sku', 'sales_count']);

        $dailyReport = $analytics->dailyReport(now());
        $weeklyFrom = now()->subDays(7)->startOfDay();
        $weeklyReport = [
            'orders' => Order::where('payment_status', 'paid')->where('created_at', '>=', $weeklyFrom)->count(),
            'revenue' => (float) Order::where('payment_status', 'paid')->where('created_at', '>=', $weeklyFrom)->sum('total'),
        ];

        return view('admin.inventory.dashboard', array_merge(compact(
            'overview', 'fast', 'slow', 'variantCombo',
            'trend', 'categories', 'brands', 'warehouses', 'from', 'to',
            'alerts', 'mostOrdered', 'dailyReport', 'weeklyReport',
            'variantInventory'
        ), [
            'hasProductBrand' => Schema::hasColumn('products', 'brand'),
            'hasProductBarcode' => Schema::hasColumn('products', 'barcode'),
        ]));
    }

    public function productInventory(Request $request, InventoryAnalyticsService $analytics)
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->startOfDay() : null;
        $to = $request->get('to') ? Carbon::parse($request->get('to'))->endOfDay() : null;

        $products = $analytics->productAnalyticsTable($request);
        $reservedByProduct = $analytics->reservedByProduct();
        $categories = $analytics->categoriesForFilter();
        $brands = $analytics->brandsForFilter();
        $overview = $analytics->overview($from, $to);

        $recentMoves = StockMovement::with(['variant.product'])
            ->latest()
            ->paginate(20, ['*'], 'move_page')
            ->withQueryString();

        return view('admin.inventory.product-inventory', array_merge(compact(
            'products', 'reservedByProduct', 'categories', 'brands', 'from', 'to', 'recentMoves', 'overview'
        ), [
            'hasProductBrand' => Schema::hasColumn('products', 'brand'),
            'hasProductBarcode' => Schema::hasColumn('products', 'barcode'),
        ]));
    }

    public function apiSummary(Request $request, InventoryAnalyticsService $analytics)
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->startOfDay() : null;
        $to = $request->get('to') ? Carbon::parse($request->get('to'))->endOfDay() : null;

        return response()->json([
            'overview' => $analytics->overview($from, $to),
            'alerts' => [
                'low' => ProductVariant::query()->where('status', 'active')->whereBetween('stock_qty', [1, 4])->count(),
                'out' => ProductVariant::query()->where('status', 'active')->where('stock_qty', '<=', 0)->count(),
                'overstock' => ProductVariant::query()->where('status', 'active')->where('stock_qty', '>', 500)->count(),
            ],
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    public function exportVariants(Request $request, InventoryAnalyticsService $analytics): StreamedResponse
    {
        $rows = $analytics->variantAnalyticsTable($request, false);
        $marginPct = (float) \App\Models\Setting::getValue('inventory_estimated_margin_percent', '25');

        $filename = 'inventory-variants-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($rows, $marginPct) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Product', 'SKU', 'Barcode', 'Brand', 'Stock', 'Reserved', 'Available (est.)',
                'Sold (filtered)', 'Revenue', 'Est. profit @ margin %',
            ]);
            foreach ($rows as $v) {
                $stock = (int) $v->stock_qty;
                $res = (int) ($v->va_reserved ?? 0);
                $avail = max(0, $stock - $res);
                $rev = (float) ($v->va_revenue ?? 0);
                $prof = round($rev * ($marginPct / 100), 2);
                $p = $v->product;
                fputcsv($out, [
                    $p->name ?? '',
                    $v->sku,
                    $p->barcode ?? '',
                    $p->brand ?? '',
                    $stock,
                    $res,
                    $avail,
                    (int) ($v->va_sold_qty ?? 0),
                    number_format($rev, 2, '.', ''),
                    number_format($prof, 2, '.', ''),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportProducts(Request $request, InventoryAnalyticsService $analytics): StreamedResponse
    {
        $products = $analytics->productAnalyticsTable($request, false);
        $reserved = $analytics->reservedByProduct();

        $filename = 'inventory-products-'.now()->format('Y-m-d-His').'.csv';

        return Response::streamDownload(function () use ($products, $reserved) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Product', 'SKU', 'Menu', 'Current stock', 'Sold (filtered)', 'Revenue',
                'Reserved', 'Returned', 'Last sale',
            ]);
            foreach ($products as $p) {
                $stock = (int) $p->variants->sum('stock_qty');
                $res = (int) ($reserved[$p->id] ?? 0);
                fputcsv($out, [
                    $p->name,
                    $p->sku,
                    $p->menuItem->title ?? '',
                    $stock,
                    (int) ($p->analytics_sold_qty ?? 0),
                    number_format((float) ($p->analytics_revenue ?? 0), 2, '.', ''),
                    $res,
                    (int) ($p->analytics_returned_qty ?? 0),
                    $p->analytics_last_sale_at ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function bulkAdjust(Request $request, StockLedgerService $ledger)
    {
        $data = $request->validate([
            'variant_sku' => 'required|string|max:64',
            'qty_delta' => 'required|integer|min:-99999|max:99999',
            'note' => 'nullable|string|max:500',
        ]);

        $variant = ProductVariant::query()->where('sku', trim($data['variant_sku']))->first();
        if (! $variant) {
            return back()->with('error', __('Variant SKU not found.'));
        }

        $delta = (int) $data['qty_delta'];
        $variant->refresh();
        $new = max(0, (int) $variant->stock_qty + $delta);
        $variant->update(['stock_qty' => $new]);
        $ledger->mirrorWarehouseQty($variant, $new);

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'type' => StockMovement::TYPE_ADJUSTMENT,
            'qty_delta' => $delta,
            'balance_after' => $new,
            'admin_user_id' => $request->user()?->id,
            'note' => $data['note'] ?? 'Bulk adjust',
        ]);

        InventoryLog::create([
            'admin_user_id' => $request->user()?->id,
            'action' => 'inventory.bulk_adjust',
            'subject_type' => ProductVariant::class,
            'subject_id' => $variant->id,
            'description' => 'Stock adjustment '.$delta.' for '.$variant->sku,
            'payload' => ['sku' => $variant->sku, 'delta' => $delta, 'balance' => $new],
        ]);

        app(InventoryAnalyticsService::class)->clearCache();

        return back()->with('status', __('Stock updated for :sku.', ['sku' => $variant->sku]));
    }
}
