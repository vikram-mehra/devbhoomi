<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Services\InventoryAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportAdminController extends Controller
{
    public function index(Request $request, InventoryAnalyticsService $analytics)
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from'))->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $request->get('to') ? Carbon::parse($request->get('to'))->endOfDay() : now()->endOfDay();

        $overview = $analytics->overview($from, $to);
        $fast = $analytics->fastMoving($from, $to, 10);
        $slow = $analytics->slowMoving(10);

        $purchasedQty = (int) PurchaseItem::query()
            ->whereHas('purchase', fn ($q) => $q->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()]))
            ->sum('quantity');

        $soldQty = (int) SaleItem::query()
            ->whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()]))
            ->sum('quantity');

        $purchaseTotal = (float) Purchase::query()
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_amount');

        $salesTotal = (float) Sale::query()
            ->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])
            ->sum('total_amount');

        $profitLoss = round($salesTotal - $purchaseTotal, 2);

        $stockHistory = StockMovement::query()
            ->with('variant.product')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->limit(25)
            ->get();

        $balanceRows = ProductVariant::query()
            ->with('product')
            ->orderByDesc('stock_qty')
            ->limit(20)
            ->get();

        return view('admin.reports.inventory', compact(
            'from', 'to', 'overview', 'fast', 'slow', 'purchasedQty', 'soldQty',
            'purchaseTotal', 'salesTotal', 'profitLoss', 'stockHistory', 'balanceRows'
        ));
    }
}
