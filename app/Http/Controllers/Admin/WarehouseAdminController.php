<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferLine;
use App\Services\StockLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WarehouseAdminController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::query()
            ->withCount('stocks')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $transfers = WarehouseTransfer::query()
            ->with(['fromWarehouse', 'toWarehouse'])
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.warehouses.index', compact('warehouses', 'transfers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'nullable|boolean',
        ]);

        $base = Str::slug($data['name']) ?: 'warehouse';
        $slug = $base;
        $i = 1;
        while (Warehouse::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        DB::transaction(function () use ($data, $slug, $request) {
            if ($request->boolean('is_default')) {
                Warehouse::query()->update(['is_default' => false]);
            }

            Warehouse::create([
                'name' => $data['name'],
                'slug' => $slug,
                'is_default' => $request->boolean('is_default'),
            ]);
        });

        return back()->with('status', __('Warehouse created.'));
    }

    public function show(Warehouse $warehouse)
    {
        $stocks = WarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->with(['variant.product'])
            ->orderByDesc('qty')
            ->paginate(25);

        $warehouses = Warehouse::query()->where('id', '!=', $warehouse->id)->orderBy('name')->get();

        return view('admin.warehouses.show', compact('warehouse', 'stocks', 'warehouses'));
    }

    public function transfer(Request $request, StockLedgerService $ledger)
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'notes' => 'nullable|string|max:2000',
            'lines' => 'required|array|min:1',
            'lines.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'lines.*.variant_sku' => 'nullable|string|max:64',
            'lines.*.quantity' => 'required|integer|min:1|max:99999',
        ]);

        foreach ($data['lines'] as $i => $row) {
            if (empty($row['product_variant_id']) && ! empty($row['variant_sku'])) {
                $variant = ProductVariant::query()->where('sku', trim($row['variant_sku']))->first();
                if (! $variant) {
                    return back()->with('error', __('Variant SKU not found.'));
                }
                $data['lines'][$i]['product_variant_id'] = $variant->id;
            }
            if (empty($data['lines'][$i]['product_variant_id'])) {
                return back()->with('error', __('Each transfer line needs a variant.'));
            }
        }

        $reference = 'TR-'.now()->format('YmdHis').'-'.random_int(100, 999);

        $transfer = DB::transaction(function () use ($data, $reference, $request, $ledger) {
            $transfer = WarehouseTransfer::create([
                'reference' => $reference,
                'from_warehouse_id' => $data['from_warehouse_id'],
                'to_warehouse_id' => $data['to_warehouse_id'],
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
                'admin_user_id' => $request->user()?->id,
            ]);

            foreach ($data['lines'] as $row) {
                WarehouseTransferLine::create([
                    'warehouse_transfer_id' => $transfer->id,
                    'product_variant_id' => $row['product_variant_id'],
                    'quantity' => $row['quantity'],
                ]);
            }

            $ledger->transferBetweenWarehouses($transfer, $request->user()?->id);
            $ledger->recordInventoryAction(
                'warehouse.transfer',
                $request->user()?->id,
                WarehouseTransfer::class,
                $transfer->id,
                __('Stock transferred :ref', ['ref' => $transfer->reference]),
                ['reference' => $transfer->reference]
            );

            return $transfer;
        });

        return back()->with('status', __('Transfer :ref completed.', ['ref' => $transfer->reference]));
    }

    public function report(Warehouse $warehouse)
    {
        $stocks = WarehouseStock::query()
            ->where('warehouse_id', $warehouse->id)
            ->with(['variant.product'])
            ->orderBy('variant_id')
            ->get();

        $totalQty = (int) $stocks->sum('qty');
        $totalDamaged = (int) $stocks->sum('damaged_qty');
        $value = $stocks->sum(function (WarehouseStock $row) {
            $variant = $row->variant;
            if (! $variant) {
                return 0;
            }

            return (int) $row->qty * (float) $variant->unitPrice();
        });

        return view('admin.warehouses.report', compact('warehouse', 'stocks', 'totalQty', 'totalDamaged', 'value'));
    }
}
