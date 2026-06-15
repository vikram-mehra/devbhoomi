<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use App\Models\Setting;
use App\Services\StockLedgerService;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'items', 'shippingAddress']);

        if (filled($request->order_id)) {
            $query->where(function ($q) use ($request) {
                $q->where('id', $request->order_id)
                    ->orWhere('order_number', 'like', '%'.$request->order_id.'%');
            });
        }
        if (filled($request->customer_name)) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%'.$request->customer_name.'%')
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', '%'.$request->customer_name.'%'));
            });
        }
        if (filled($request->mobile)) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_phone', 'like', '%'.$request->mobile.'%')
                    ->orWhereHas('shippingAddress', fn ($sq) => $sq->where('phone', 'like', '%'.$request->mobile.'%'));
            });
        }
        if (filled($request->payment_status)) {
            $query->where('payment_status', $request->payment_status);
        }
        if (filled($request->order_status)) {
            $query->where('status', $request->order_status);
        }
        if (filled($request->date_from)) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if (filled($request->date_to)) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'total_orders' => (int) Order::count(),
            'pending_orders' => (int) Order::where('status', 'pending')->count(),
            'delivered_orders' => (int) Order::where('status', 'delivered')->count(),
            'total_revenue' => (float) Order::where('payment_status', 'paid')->sum('total'),
            'today_orders' => (int) Order::whereDate('created_at', now()->toDateString())->count(),
        ];

        return view('admin.orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.variant.product', 'shippingAddress']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', Rule::in(Order::allowedStatusValues())],
        ]);
        $previous = $order->status;
        $order->update(['status' => $request->status]);

        if ($request->status === 'confirmed' && $previous !== 'confirmed') {
            $order->confirmed_at = now();
            $order->save();
        }
        if ($request->status === 'shipped' && $previous !== 'shipped') {
            $order->shipped_at = now();
            $order->save();
        }
        if ($request->status === 'delivered' && $previous !== 'delivered') {
            $order->delivered_at = now();
            $order->delivery_date = now()->toDateString();
            $order->save();
        }

        if ($request->status === 'cancelled' && $previous !== 'cancelled') {
            app(StockLedgerService::class)->restoreOrderCancellation($order->fresh(), optional($request->user())->id);
        }

        $this->sendOrderStatusEmail($order, $previous);

        return back()->with('status', 'Order status updated.');
    }

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate(['payment_status' => ['required', 'string', Rule::in(array_keys(Order::paymentStatusOptions()))]]);
        $order->update(['payment_status' => $request->payment_status]);

        return back()->with('status', 'Payment status updated.');
    }

    public function updateNotes(Request $request, Order $order)
    {
        $request->validate(['notes' => 'nullable|string|max:2000']);
        $order->update(['notes' => $request->notes]);

        return back()->with('status', 'Order note updated.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', Rule::exists('orders', 'id')],
            'status' => ['required', Rule::in(Order::allowedStatusValues())],
        ]);

        Order::whereIn('id', $validated['order_ids'])->update(['status' => $validated['status']]);

        return back()->with('status', 'Bulk status updated successfully.');
    }

    public function export(Request $request)
    {
        $type = $request->input('type', 'csv');
        $orders = Order::with('user')->latest()->get();
        $filename = 'orders-'.now()->format('Ymd-His').($type === 'excel' ? '.xls' : '.csv');

        $headers = [
            'Content-Type' => $type === 'excel' ? 'application/vnd.ms-excel' : 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        return response()->stream(function () use ($orders, $type) {
            $out = fopen('php://output', 'w');
            if ($type === 'excel') {
                fwrite($out, "Order ID\tCustomer\tPhone\tProducts\tAmount\tPayment\tPayment Status\tOrder Status\tDate\n");
                foreach ($orders as $order) {
                    fwrite($out, implode("\t", [
                        $order->order_number,
                        $order->customer_name ?: ($order->user->name ?? 'N/A'),
                        $order->customer_phone ?: 'N/A',
                        $order->items()->count(),
                        (float) $order->total,
                        strtoupper((string) $order->payment_method),
                        ucfirst((string) $order->payment_status),
                        Order::statusLabel($order->status),
                        optional($order->created_at)->format('d-m-Y H:i'),
                    ])."\n");
                }

                return;
            }

            fputcsv($out, ['Order ID', 'Customer', 'Phone', 'Products', 'Amount', 'Payment', 'Payment Status', 'Order Status', 'Date']);
            foreach ($orders as $order) {
                fputcsv($out, [
                    $order->order_number,
                    $order->customer_name ?: ($order->user->name ?? 'N/A'),
                    $order->customer_phone ?: 'N/A',
                    $order->items()->count(),
                    (float) $order->total,
                    strtoupper((string) $order->payment_method),
                    ucfirst((string) $order->payment_status),
                    Order::statusLabel($order->status),
                    optional($order->created_at)->format('d-m-Y H:i'),
                ]);
            }
            fclose($out);
        }, Response::HTTP_OK, $headers);
    }

    public function printInvoice(Order $order)
    {
        $order->load(['user', 'items.variant.product', 'shippingAddress']);
        $company = $this->companyData();

        return view('admin.orders.invoice', compact('order', 'company'));
    }

    public function downloadInvoicePdf(Order $order)
    {
        $order->load(['user', 'items.variant.product', 'shippingAddress']);
        $company = $this->companyData();
        $html = view('admin.orders.invoice-pdf', compact('order', 'company'))->render();

        return response($html, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="invoice-'.$order->order_number.'.html"',
        ]);
    }

    public function printShippingLabel(Order $order)
    {
        $order->load(['user', 'items.variant.product', 'shippingAddress']);
        $company = $this->companyData();

        $totalWeight = 0;
        foreach ($order->items as $item) {
            $weight = $item->weight_kg ?: optional(optional($item->variant)->product)->weight_kg ?: 0;
            $totalWeight += $weight * $item->qty;
        }

        return view('admin.orders.shipping-label', compact('order', 'company', 'totalWeight'));
    }

    private function sendOrderStatusEmail(Order $order, ?string $previousStatus): void
    {
        try {
            $recipient = $order->customer_email ?: ($order->user->email ?? null);
            if (! $recipient) {
                return;
            }
            Mail::to($recipient)->send(new OrderStatusUpdatedMail($order, $previousStatus));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function companyData(): array
    {
        return [
            'name' => Setting::getValue('company_name', config('app.name')),
            'logo' => Setting::getValue('site_logo_url', ''),
            'gst' => Setting::getValue('company_gst', 'GSTIN-NA'),
            'phone' => Setting::getValue('company_phone', ''),
            'email' => Setting::getValue('company_email', ''),
            'address' => Setting::getValue('company_address', ''),
        ];
    }
}
