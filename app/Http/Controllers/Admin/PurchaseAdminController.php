<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\StockLedgerService;
use App\Services\TransactionHubService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseAdminController extends Controller
{
    public function index(Request $request, TransactionHubService $hub)
    {
        $purchases = $hub->applyPurchaseFilters(
            Purchase::query()
                ->with(['supplier', 'warehouse'])
                ->withCount('items'),
            $request
        )
            ->latest('purchase_date')
            ->paginate(20)
            ->withQueryString();

        $filterOptions = $hub->filterOptions();

        return view('admin.purchases.index', [
            'purchases' => $purchases,
            'summaries' => $hub->summaries(),
            'transactionTab' => 'purchases',
            'filterAction' => route('admin.purchases.index'),
            'suppliers' => $filterOptions['suppliers'],
            'warehouses' => $filterOptions['warehouses'],
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::query()->where('is_active', true)->orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.purchases.form', [
            'purchase' => null,
            'suppliers' => $suppliers,
            'warehouses' => $warehouses,
        ]);
    }

    public function store(Request $request, StockLedgerService $ledger)
    {
        $data = $this->validatedPurchase($request);

        $purchase = DB::transaction(function () use ($data, $request, $ledger) {
            $warehouse = Warehouse::query()->find($data['warehouse_id'])
                ?? Warehouse::defaultWarehouse();

            $purchase = Purchase::create([
                'invoice_number' => $data['invoice_number'],
                'supplier_id' => $data['supplier_id'] ?? null,
                'warehouse_id' => $warehouse?->id,
                'purchase_date' => $data['purchase_date'],
                'subtotal' => $data['subtotal'],
                'tax_percent' => $data['tax_percent'],
                'tax_amount' => $data['tax_amount'],
                'delivery_charge' => $data['delivery_charge'],
                'total_amount' => $data['total_amount'],
                'payment_status' => $data['payment_status'],
                'notes' => $data['notes'] ?? null,
                'admin_user_id' => $request->user()?->id,
            ]);

            foreach ($data['items'] as $row) {
                $variant = ProductVariant::query()->with('product')->findOrFail($row['product_variant_id']);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'tax_amount' => $row['tax_amount'] ?? 0,
                    'line_total' => $row['line_total'],
                ]);

                $ledger->increaseFromPurchase(
                    $variant,
                    (int) $row['quantity'],
                    $purchase,
                    $warehouse,
                    $request->user()?->id
                );
            }

            if ($purchase->supplier_id && $purchase->payment_status !== 'paid') {
                $purchase->supplier()->increment('pending_payment_amount', $purchase->total_amount);
            }

            $ledger->recordInventoryAction(
                'purchase.created',
                $request->user()?->id,
                Purchase::class,
                $purchase->id,
                __('Purchase invoice :invoice recorded.', ['invoice' => $purchase->invoice_number]),
                ['invoice_number' => $purchase->invoice_number, 'total' => (float) $purchase->total_amount]
            );

            return $purchase;
        });

        return redirect()
            ->route('admin.purchases.show', $purchase)
            ->with('status', __('Purchase saved and stock updated.'));
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'warehouse', 'items.product', 'items.variant']);

        return view('admin.purchases.show', compact('purchase'));
    }

    protected function validatedPurchase(Request $request): array
    {
        $data = $request->validate([
            'invoice_number' => 'required|string|max:64|unique:purchases,invoice_number',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'purchase_date' => 'required|date',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'delivery_charge' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,paid,partial',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1|max:99999',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
        ]);

        $subtotal = 0.0;
        $lineTax = 0.0;
        $items = [];
        foreach ($data['items'] as $row) {
            $qty = (int) $row['quantity'];
            $unit = (float) $row['unit_price'];
            $tax = (float) ($row['tax_amount'] ?? 0);
            $line = round($qty * $unit, 2);
            $subtotal += $line;
            $lineTax += $tax;
            $items[] = [
                'product_variant_id' => (int) $row['product_variant_id'],
                'quantity' => $qty,
                'unit_price' => $unit,
                'tax_amount' => $tax,
                'line_total' => $line + $tax,
            ];
        }

        $taxPercent = (float) ($data['tax_percent'] ?? 0);
        $headerTax = round($subtotal * ($taxPercent / 100), 2);
        $taxAmount = $lineTax > 0 ? $lineTax : $headerTax;

        if ($subtotal <= 0) {
            throw ValidationException::withMessages([
                'items' => __('Add at least one purchase line with quantity greater than zero.'),
            ]);
        }

        $data['items'] = $items;
        $data['subtotal'] = round($subtotal, 2);
        $deliveryCharge = round((float) ($data['delivery_charge'] ?? 0), 2);

        $data['tax_percent'] = $taxPercent;
        $data['tax_amount'] = round($taxAmount, 2);
        $data['delivery_charge'] = $deliveryCharge;
        $data['total_amount'] = round($subtotal + $taxAmount + $deliveryCharge, 2);

        return $data;
    }
}
