<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierAdminController extends Controller
{
    public function items(Request $request)
    {
        $q = PurchaseItem::query()
            ->with(['product', 'variant', 'purchase.supplier']);

        if ($request->filled('supplier_id')) {
            $q->whereHas('purchase', fn ($pq) => $pq->where('supplier_id', $request->supplier_id));
        }

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function ($qq) use ($term) {
                $qq->whereHas('product', fn ($pr) => $pr->where('name', 'like', $term))
                    ->orWhereHas('purchase.supplier', fn ($sp) => $sp->where('name', 'like', $term))
                    ->orWhereHas('purchase', fn ($pu) => $pu->where('invoice_number', 'like', $term));
            });
        }

        $items = $q->latest()->paginate(25)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('admin.suppliers.items', compact('items', 'suppliers'));
    }

    public function index(Request $request)
    {
        $q = Supplier::query()->withCount('purchases')->latest();

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('gst_number', 'like', $term);
            });
        }

        $suppliers = $q->paginate(20)->withQueryString();

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'gst_number' => 'nullable|string|max:32',
            'contact_person' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:2000',
            'pending_payment_amount' => 'nullable|numeric|min:0',
        ]);

        $base = Str::slug($data['name']) ?: 'supplier';
        $slug = $base;
        $i = 1;
        while (Supplier::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        Supplier::create(array_merge($data, [
            'slug' => $slug,
            'pending_payment_amount' => $data['pending_payment_amount'] ?? 0,
            'is_active' => true,
        ]));

        return back()->with('status', __('Supplier created.'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:32',
            'gst_number' => 'nullable|string|max:32',
            'contact_person' => 'nullable|string|max:120',
            'address' => 'nullable|string|max:2000',
            'pending_payment_amount' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $supplier->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'gst_number' => $data['gst_number'] ?? null,
            'contact_person' => $data['contact_person'] ?? null,
            'address' => $data['address'] ?? null,
            'pending_payment_amount' => $data['pending_payment_amount'] ?? 0,
            'is_active' => $request->boolean('is_active', $supplier->is_active),
        ]);

        return back()->with('status', __('Supplier updated.'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount('purchases');
        $purchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->with('warehouse')
            ->latest('purchase_date')
            ->paginate(10, ['*'], 'purchases_page');

        $items = PurchaseItem::query()
            ->whereHas('purchase', fn ($q) => $q->where('supplier_id', $supplier->id))
            ->with(['product', 'variant', 'purchase'])
            ->latest()
            ->paginate(20, ['*'], 'items_page');

        return view('admin.suppliers.show', compact('supplier', 'purchases', 'items'));
    }
}
