<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

/**
 * Rule-based inventory intelligence (velocity, reorder heuristics, anomaly hints).
 * Replace with ML / external AI when you connect a forecasting API.
 */
class SmartInventoryInsightsService
{
    public function fullInsightPack(): array
    {
        return [
            'summary' => $this->summaryLines(),
            'reorder' => $this->reorderRecommendations(15),
            'dead_stock' => $this->deadStockHints(8),
            'spikes' => $this->salesSpikes(),
            'forecast_preview' => $this->simpleForecastSample(5),
            'optimization' => $this->optimizationTips(),
        ];
    }

    /**
     * @return list<string>
     */
    public function summaryLines(): array
    {
        $margin = (float) Setting::getValue('inventory_estimated_margin_percent', '25');

        return [
            __('Estimated margin setting: :pct% (adjust in admin Settings / DB key inventory_estimated_margin_percent).', ['pct' => $margin]),
            __('Reorder hints use last :days days of paid sales ÷ :days for daily velocity.', ['days' => 30]),
            __('Demand “forecast” is a 14-day linear projection from recent velocity — not ML.'),
        ];
    }

    /**
     * Variants where on-hand is below 7× daily velocity (if velocity > 0).
     *
     * @return list<array{sku: string, product: string, stock: int, suggest_qty: int, days_cover: float|null}>
     */
    public function reorderRecommendations(int $limit = 15): array
    {
        $velocity = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->selectRaw('
                product_variants.id as vid,
                product_variants.sku,
                products.name as product_name,
                product_variants.stock_qty,
                SUM(order_items.qty) / 30 as daily_velocity
            ')
            ->groupBy('product_variants.id', 'product_variants.sku', 'products.name', 'product_variants.stock_qty')
            ->havingRaw('(SUM(order_items.qty) / 30) > 0')
            ->get();

        $out = [];
        foreach ($velocity as $row) {
            $daily = (float) $row->daily_velocity;
            $stock = (int) $row->stock_qty;
            $target = (int) ceil($daily * 14);
            $suggest = max(0, $target - $stock);
            $daysCover = $daily > 0 ? round($stock / $daily, 1) : null;
            if ($suggest < 1 || $daysCover === null || $daysCover >= 7) {
                continue;
            }
            $out[] = [
                'sku' => $row->sku,
                'product' => $row->product_name,
                'stock' => $stock,
                'suggest_qty' => $suggest,
                'days_cover' => $daysCover,
            ];
            if (count($out) >= $limit) {
                break;
            }
        }

        return $out;
    }

    /**
     * @return list<array{sku: string, name: string, stock: int}>
     */
    public function deadStockHints(int $limit = 8): array
    {
        $soldIds = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(90))
            ->pluck('product_variants.id');

        return ProductVariant::query()
            ->with('product:id,name')
            ->where('stock_qty', '>', 0)
            ->whereNotIn('id', $soldIds->unique()->filter()->values()->all())
            ->orderByDesc('stock_qty')
            ->limit($limit)
            ->get()
            ->map(fn ($v) => [
                'sku' => $v->sku,
                'name' => $v->product->name ?? '',
                'stock' => (int) $v->stock_qty,
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, week1: int, week2: int, pct: float}>
     */
    public function salesSpikes(): array
    {
        $prevStart = now()->subDays(14)->startOfDay();
        $prevEnd = now()->subDays(7)->endOfDay();
        $currStart = now()->subDays(7)->startOfDay();

        $prev = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$prevStart, $prevEnd])
            ->selectRaw('products.id as pid, products.name, SUM(order_items.qty) as q')
            ->groupBy('products.id', 'products.name')
            ->pluck('q', 'pid');

        $curr = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', $currStart)
            ->selectRaw('products.id as pid, products.name, SUM(order_items.qty) as q')
            ->groupBy('products.id', 'products.name')
            ->get();

        $spikes = [];
        foreach ($curr as $row) {
            $p = (int) ($prev[$row->pid] ?? 0);
            $c = (int) $row->q;
            if ($p < 3 && $c < 5) {
                continue;
            }
            $pct = $p > 0 ? round((($c - $p) / $p) * 100, 1) : ($c > 0 ? 100.0 : 0.0);
            if ($pct >= 40) {
                $spikes[] = [
                    'name' => $row->name,
                    'week1' => $p,
                    'week2' => $c,
                    'pct' => $pct,
                ];
            }
        }

        usort($spikes, fn ($a, $b) => $b['pct'] <=> $a['pct']);

        return array_slice($spikes, 0, 8);
    }

    /**
     * @return list<array{sku: string, forecast_14d: int, current: int}>
     */
    public function simpleForecastSample(int $limit = 5): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->groupBy('product_variants.id', 'product_variants.sku', 'product_variants.stock_qty')
            ->selectRaw('
                product_variants.sku,
                product_variants.stock_qty,
                SUM(order_items.qty) / 30 * 14 as forecast_raw
            ')
            ->orderByDesc(DB::raw('SUM(order_items.qty)'))
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'sku' => $r->sku,
            'forecast_14d' => (int) round((float) $r->forecast_raw),
            'current' => (int) $r->stock_qty,
        ])->all();
    }

    /**
     * @return list<string>
     */
    public function optimizationTips(): array
    {
        $oos = ProductVariant::where('status', 'active')->where('stock_qty', '<=', 0)->count();
        $low = ProductVariant::where('status', 'active')->whereBetween('stock_qty', [1, 4])->count();

        $tips = [];
        if ($oos > 0) {
            $tips[] = __(':n active variants are out of stock — prioritize restock or hide from storefront.', ['n' => $oos]);
        }
        if ($low > 0) {
            $tips[] = __(':n variants are in low stock (1–4) — review reorder table.', ['n' => $low]);
        }
        $tips[] = __('Connect purchase orders to suppliers to track incoming stock vs velocity.');
        $tips[] = __('For true AI forecasting, plug an external API and replace SmartInventoryInsightsService.');

        return $tips;
    }

    public function syncAlertsFromMetrics(): int
    {
        // lightweight: could insert stock_alerts rows; keep count low
        return 0;
    }
}
