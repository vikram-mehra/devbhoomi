<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupplierAdminController extends Controller
{
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
            ->paginate(15);

        return view('admin.suppliers.show', compact('supplier', 'purchases'));
    }
}
