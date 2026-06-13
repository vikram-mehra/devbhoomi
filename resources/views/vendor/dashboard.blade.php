@extends('layouts.vendor')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3 mb-4">Hi, {{ $vendor->shop_name }}</h1>
    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Products</div><div class="fs-4">{{ $productCount }}</div></div></div>
        <div class="col-md-3"><div class="card p-3"><div class="text-muted small">Low stock SKUs</div><div class="fs-4">{{ $lowStock }}</div></div></div>
        <div class="col-md-2"><div class="card p-3"><div class="text-muted small">Gross</div><div class="fs-6">₹{{ number_format($gross, 0) }}</div></div></div>
        <div class="col-md-2"><div class="card p-3"><div class="text-muted small">Commission</div><div class="fs-6">₹{{ number_format($commission, 0) }}</div></div></div>
        <div class="col-md-2"><div class="card p-3"><div class="text-muted small">Net</div><div class="fs-6">₹{{ number_format($net, 0) }}</div></div></div>
    </div>
    <h2 class="h5">Recent line items</h2>
    <table class="table table-sm bg-white shadow-sm rounded">
        <thead><tr><th>Order</th><th>Product</th><th>Qty</th><th>Line</th></tr></thead>
        <tbody>
            @foreach($recentItems as $it)
                <tr>
                    <td>{{ $it->order->order_number ?? $it->order_id }}</td>
                    <td>{{ $it->product_name }}</td>
                    <td>{{ $it->qty }}</td>
                    <td>₹{{ number_format($it->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h2 class="h5 mt-4">Chats</h2>
    <ul class="list-group">
        @forelse($chatUserIds as $uid)
            <li class="list-group-item d-flex justify-content-between">
                <span>Customer #{{ $uid }}</span>
                <a href="{{ route('chat.show', $vendor) }}?with={{ $uid }}">Open</a>
            </li>
        @empty
            <li class="list-group-item">No messages yet.</li>
        @endforelse
    </ul>
@endsection
