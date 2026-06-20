<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CouponService;
use App\Services\OrderConfirmationMailService;
use App\Services\StockLedgerService;
use App\Services\GoogleAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function razorpay(Order $order)
    {
        $this->authorizeOrder($order);
        abort_unless($order->payment_method === 'razorpay', 400);
        $payable = $this->payableAmount($order);
        if ($payable <= 0) {
            return redirect()->route('orders.show', $order);
        }

        if ($this->razorpayDummy()) {
            return view('market.pay-razorpay-dummy', compact('order', 'payable'));
        }

        $key = config('services.razorpay.key');

        $order->loadMissing('address', 'user');
        $prefill = [
            'name' => (string) ($order->address?->name ?? $order->user?->name ?? ''),
            'email' => (string) ($order->user?->email ?? ''),
            'contact' => preg_replace('/\D/', '', (string) ($order->address?->phone ?? $order->user?->phone ?? '')),
        ];

        return view('market.pay-razorpay', compact('order', 'payable', 'key', 'prefill'));
    }

    public function razorpayDummyComplete(Order $order)
    {
        $this->authorizeOrder($order);
        abort_unless($order->payment_method === 'razorpay', 400);
        abort_if($this->razorpayLive(), 403);

        $payable = $this->payableAmount($order);
        if ($payable <= 0) {
            return redirect()->route('orders.show', $order);
        }

        $this->markOrderPaid($order, 'demo_'.Str::lower(Str::random(14)));
        GoogleAnalyticsService::flashPurchase($order);

        return redirect()->route('orders.show', $order)->with('status', __('Payment successful (demo).'));
    }

    public function razorpayVerify(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::findOrFail($request->order_id);
        $this->authorizeOrder($order);
        abort_unless($order->payment_method === 'razorpay', 400);

        $secret = config('services.razorpay.secret');
        $key = config('services.razorpay.key');
        if (! $secret || ! $key) {
            return redirect()->route('orders.show', $order)
                ->with('error', __('Payment gateway is not configured.'));
        }

        $api = new \Razorpay\Api\Api($key, $secret);
        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Razorpay verify failed', ['e' => $e->getMessage()]);

            $this->failUnpaidOrder($order);

            return redirect()->route('orders.show', $order)->with('error', __('Payment verification failed. If money was debited, contact support with your order number.'));
        }

        $this->markOrderPaid($order, $request->razorpay_payment_id);
        GoogleAnalyticsService::flashPurchase($order);

        return redirect()->route('orders.show', $order)->with('status', __('Payment successful.'));
    }

    public function razorpayAbandon(Order $order)
    {
        $this->authorizeOrder($order);
        abort_unless($order->payment_method === 'razorpay', 400);

        if ($order->payment_status !== 'paid') {
            $this->failUnpaidOrder($order);
        }

        return response()->json(['ok' => true]);
    }

    public function createRazorpayOrder(Order $order)
    {
        $this->authorizeOrder($order);
        abort_unless($order->payment_method === 'razorpay', 400);
        $payable = $this->payableAmount($order);
        if ($payable <= 0) {
            return response()->json(['error' => 'nothing_to_pay'], 422);
        }

        if (! config('services.razorpay.secret') || ! config('services.razorpay.key')) {
            return response()->json(['error' => 'gateway_not_configured'], 503);
        }

        try {
            $api = new \Razorpay\Api\Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $receipt = substr(preg_replace('/\s+/', '', $order->order_number), 0, 40);
            $rz = $api->order->create([
                'receipt' => $receipt !== '' ? $receipt : (string) $order->id,
                'amount' => (int) round($payable * 100),
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);

            return response()->json(['id' => $rz['id'], 'amount' => (int) $rz['amount']]);
        } catch (\Throwable $e) {
            Log::warning('Razorpay order create failed', ['e' => $e->getMessage()]);

            return response()->json(['error' => 'order_create_failed'], 502);
        }
    }

    protected function authorizeOrder(Order $order): void
    {
        abort_unless((int)$order->user_id === (int)auth()->id(), 403);
        abort_if($order->payment_status === 'paid', 400);
    }

    protected function payableAmount(Order $order): float
    {
        return max(0, (float) $order->total - (float) $order->wallet_used);
    }

    protected function razorpayLive(): bool
    {
        return (bool) (config('services.razorpay.key') && config('services.razorpay.secret'));
    }

    protected function razorpayDummy(): bool
    {
        return ! $this->razorpayLive();
    }

    protected function failUnpaidOrder(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);

        app(StockLedgerService::class)->restoreUnpaidOrderStock($order->fresh(), 'Payment failed or abandoned');
    }

    protected function markOrderPaid(Order $order, string $paymentRef): void
    {
        $wasPaid = $order->payment_status === 'paid';
        $couponCode = $order->coupon_code;

        $order->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_ref' => $paymentRef,
            'razorpay_payment_id' => Str::startsWith($paymentRef, 'pay_') ? $paymentRef : $order->razorpay_payment_id,
        ]);

        if (! $wasPaid && $couponCode) {
            app(CouponService::class)->markUsed($couponCode);
        }

        if (! $wasPaid) {
            app(OrderConfirmationMailService::class)->dispatchForOrder($order->fresh());
        }
    }
}
