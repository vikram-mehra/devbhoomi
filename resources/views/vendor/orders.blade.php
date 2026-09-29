@extends('layouts.vendor')

@section('title', 'Orders')

@section('content')
    <h1 class="h4 mb-3">Your orders</h1>
    @foreach($orders as $o)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <span>{{ $o->order_number }}</span>
                <span>{{ \App\Models\Order::statusLabel($o->status) }} · {{ $o->payment_status }}</span>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($o->items as $it)
                    <li class="list-group-item">{{ $it->product_name }} × {{ $it->qty }} — ₹{{ number_format($it->line_total, 2) }}</li>
                @endforeach
            </ul>
        </div>
    @endforeach
    {{ $orders->links() }}
@endsection
