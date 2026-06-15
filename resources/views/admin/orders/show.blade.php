@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)
@section('page_subtitle', 'Complete order details, actions, payment, shipping, and notes.')

@section('content')
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="{{ route('admin.orders.invoice.print', $order) }}" target="_blank" class="btn btn-outline-secondary">Print Invoice</a>
        <a href="{{ route('admin.orders.label.print', $order) }}" target="_blank" class="btn btn-outline-secondary">Print Shipping Label</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="mb-3">Customer Information</h6>
                <p class="mb-1"><strong>Name:</strong> {{ $order->customer_name ?: ($order->shippingAddress->name ?? $order->user->name ?? 'N/A') }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email ?: ($order->shippingAddress->email ?? $order->user->email ?? 'N/A') }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ $order->customer_phone ?: ($order->shippingAddress->phone ?? 'N/A') }}</p>
                <p class="mb-1"><strong>Full Address:</strong> {{ $order->shippingAddress?->fullAddress() ?: optional($order->address)->line1 }}</p>
                <p class="mb-1"><strong>Pincode:</strong> {{ $order->shippingAddress->pincode ?? optional($order->address)->pincode ?? 'N/A' }}</p>
                <p class="mb-1"><strong>City:</strong> {{ $order->shippingAddress->city ?? optional($order->address)->city ?? 'N/A' }}</p>
                <p class="mb-0"><strong>State:</strong> {{ $order->shippingAddress->state ?? optional($order->address)->state ?? 'N/A' }}</p>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="mb-3">Payment Details</h6>
                <p class="mb-1"><strong>Payment Method:</strong> {{ strtoupper((string) $order->payment_method) ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Transaction ID:</strong> {{ $order->transaction_id ?: $order->payment_ref ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Razorpay Payment ID:</strong> {{ $order->razorpay_payment_id ?: 'N/A' }}</p>
                <p class="mb-0"><strong>Payment Status:</strong> {{ ucfirst((string) $order->payment_status) }}</p>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body">
        <h6 class="mb-3">Admin Actions</h6>
        <div class="row g-2">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['confirmed' => 'Confirm Order', 'processing' => 'Mark as Processing', 'shipped' => 'Mark as Shipped', 'delivered' => 'Mark as Delivered', 'cancelled' => 'Cancel Order'] as $status => $label)
                        <form method="post" action="{{ route('admin.orders.status', $order) }}">
                            @csrf
                            <input type="hidden" name="status" value="{{ $status }}">
                            <button class="btn btn-sm {{ $status === 'cancelled' ? 'btn-outline-danger' : 'btn-outline-primary' }}">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4">
                <form method="post" action="{{ route('admin.orders.payment', $order) }}" class="d-flex gap-2">
                    @csrf
                    <select class="form-select form-select-sm" name="payment_status">
                        @foreach(\App\Models\Order::paymentStatusOptions() as $k => $v)
                            <option value="{{ $k }}" @selected($order->payment_status === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div></div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header"><strong>Products</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead><tr><th>Image</th><th>Name</th><th>SKU</th><th>Weight</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
                <tbody>
                    @foreach($order->items as $item)
                        @php
                            $img = $item->product_image ?: optional($item->variant)->variantImageUrl();
                            $sku = $item->sku ?: optional($item->variant)->sku;
                            $weight = $item->weight_kg ?: optional(optional($item->variant)->product)->weight_kg;
                        @endphp
                        <tr>
                            <td>@if($img)<img src="{{ $img }}" alt="" style="width:54px;height:54px;object-fit:cover;border-radius:8px;">@else N/A @endif</td>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $sku ?: 'N/A' }}</td>
                            <td>{{ $weight ? rtrim(rtrim(number_format((float) $weight, 3, '.', ''), '0'), '.').' kg' : 'N/A' }}</td>
                            <td>{{ $item->qty }}</td>
                            <td>₹{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td>₹{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="mb-3">Shipping Details</h6>
                <p class="mb-1"><strong>Courier Name:</strong> {{ $order->courier_name ?: 'N/A' }}</p>
                <p class="mb-1"><strong>Tracking ID:</strong> {{ $order->tracking_id ?: 'N/A' }}</p>
                <p class="mb-0"><strong>Delivery Date:</strong> {{ $order->delivery_date?->format('d M Y') ?: 'N/A' }}</p>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <h6 class="mb-3">Order Summary</h6>
                <p class="mb-1 d-flex justify-content-between"><span>Subtotal</span><strong>₹{{ number_format((float) $order->subtotal, 2) }}</strong></p>
                <p class="mb-1 d-flex justify-content-between"><span>Shipping Charge</span><strong>₹{{ number_format((float) $order->shipping, 2) }}</strong></p>
                <p class="mb-1 d-flex justify-content-between"><span>Discount</span><strong>-₹{{ number_format((float) $order->discount, 2) }}</strong></p>
                <p class="mb-1 d-flex justify-content-between"><span>Tax/GST</span><strong>₹{{ number_format((float) $order->tax_amount, 2) }}</strong></p>
                <hr>
                <p class="mb-0 d-flex justify-content-between"><span>Grand Total</span><strong>₹{{ number_format((float) $order->total, 2) }}</strong></p>
            </div></div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body">
            <h6 class="mb-3">Order Notes</h6>
            <form method="post" action="{{ route('admin.orders.notes', $order) }}">
                @csrf
                <textarea name="notes" rows="3" class="form-control mb-2" placeholder="Add internal admin note...">{{ old('notes', $order->notes) }}</textarea>
                <button class="btn btn-primary btn-sm">Save Notes</button>
            </form>
        </div>
    </div>
@endsection
