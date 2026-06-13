<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warehouse;
use App\Services\CouponService;
use App\Services\StockLedgerService;
use App\Services\TransactionHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleAdminController extends Controller
{
    public function index(Request $request, TransactionHubService $hub)
    {
        $sales = $hub->applySaleFilters(
            Sale::query()
                ->with('warehouse')
                ->withCount('items'),
            $request
        )
            ->latest('sale_date')
            ->paginate(20)
            ->withQueryString();

        $filterOptions = $hub->filterOptions();

        return view('admin.sales.index', [
            'sales' => $sales,
            'summaries' => $hub->summaries(),
            'transactionTab' => 'sales',
            'filterAction' => route('admin.sales.index'),
            'suppliers' => $filterOptions['suppliers'],
            'warehouses' => $filterOptions['warehouses'],
        ]);
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.sales.form', [
            'sale' => null,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request, StockLedgerService $ledger, CouponService $coupons)
    {
        $data = $this->validatedSale($request, $coupons);

        $sale = DB::transaction(function () use ($data, $request, $ledger, $coupons) {
            $warehouse = Warehouse::query()->find($data['warehouse_id'])
                ?? Warehouse::defaultWarehouse();

            $sale = Sale::create([
                'invoice_number' => $data['invoice_number'],
                'warehouse_id' => $warehouse?->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'sale_date' => $data['sale_date'],
                'subtotal' => $data['subtotal'],
                'tax_amount' => $data['tax_amount'],
                'coupon_code' => $data['coupon_code'] ?? null,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'total_amount' => $data['total_amount'],
                'payment_status' => $data['payment_status'],
                'order_status' => $data['order_status'],
                'notes' => $data['notes'] ?? null,
                'admin_user_id' => $request->user()?->id,
            ]);

            foreach ($data['items'] as $row) {
                $variant = ProductVariant::query()->findOrFail($row['product_variant_id']);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                ]);
            }

            $ledger->applySaleStock($sale, $request->user()?->id);

            $ledger->recordInventoryAction(
                'sale.created',
                $request->user()?->id,
                Sale::class,
                $sale->id,
                __('Sales invoice :invoice recorded.', ['invoice' => $sale->invoice_number]),
                ['invoice_number' => $sale->invoice_number, 'total' => (float) $sale->total_amount]
            );

            return $sale;
        });

        if (! empty($data['coupon_code']) && $data['payment_status'] === 'paid') {
            $coupons->markUsed($data['coupon_code']);
        }

        return redirect()
            ->route('admin.sales.show', $sale)
            ->with('status', __('Sale saved.'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['warehouse', 'items.product', 'items.variant']);

        return view('admin.sales.show', compact('sale'));
    }

    public function updateStatus(Request $request, Sale $sale, StockLedgerService $ledger)
    {
        $data = $request->validate([
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'order_status' => 'required|in:pending,confirmed,completed,shipped,cancelled',
        ]);

        $sale->update($data);
        $ledger->applySaleStock($sale->fresh(), $request->user()?->id);

        return back()->with('status', __('Sale status updated.'));
    }

    protected function validatedSale(Request $request, ?CouponService $coupons = null): array
    {
        $data = $request->validate([
            'invoice_number' => 'required|string|max:64|unique:sales,invoice_number',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:32',
            'sale_date' => 'required|date',
            'tax_amount' => 'nullable|numeric|min:0',
            'coupon_code' => 'nullable|string|max:64',
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'order_status' => 'required|in:pending,confirmed,completed,shipped,cancelled',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:99999',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0.0;
        $items = [];
        foreach ($data['items'] as $row) {
            $variant = ProductVariant::query()->find($row['product_variant_id']);
            if (! $variant) {
                continue;
            }
            $qty = (int) $row['quantity'];
            $unit = (float) $row['unit_price'];
            $line = round($qty * $unit, 2);
            $subtotal += $line;

            if ($data['payment_status'] === 'paid'
                && in_array($data['order_status'], ['confirmed', 'completed', 'shipped'], true)
                && (int) $variant->stock_qty < $qty) {
                throw ValidationException::withMessages([
                    'items' => __('Insufficient stock for variant :sku.', ['sku' => $variant->sku]),
                ]);
            }

            $items[] = [
                'product_variant_id' => (int) $row['product_variant_id'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'line_total' => $line,
            ];
        }

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => __('Add at least one sale line.'),
            ]);
        }

        $taxAmount = round((float) ($data['tax_amount'] ?? 0), 2);
        $discountAmount = 0.0;
        $couponCode = null;
        $coupons = $coupons ?? app(CouponService::class);

        if ($coupons->normalizeCode($data['coupon_code'] ?? '') !== '') {
            try {
                $resolved = $coupons->resolveForCart($data['coupon_code'], round($subtotal, 2));
                $discountAmount = $resolved['discount'];
                $couponCode = $resolved['coupon']->code;
            } catch (ValidationException $e) {
                throw ValidationException::withMessages($e->errors());
            }
        }

        $data['items'] = $items;
        $data['subtotal'] = round($subtotal, 2);
        $data['tax_amount'] = $taxAmount;
        $data['discount_amount'] = $discountAmount;
        $data['coupon_code'] = $couponCode;
        $data['total_amount'] = round(max(0, $subtotal + $taxAmount - $discountAmount), 2);

        return $data;
    }
}
