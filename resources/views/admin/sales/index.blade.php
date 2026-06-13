@extends('layouts.admin')

@section('title', __('Sales'))

@section('content')
    @include('admin.partials.transaction-hub')

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Lines') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td class="fw-semibold font-monospace">{{ $sale->invoice_number }}</td>
                            <td>{{ optional($sale->sale_date)->format('d M Y') }}</td>
                            <td>{{ $sale->customer_name }}</td>
                            <td>{{ $sale->items_count }}</td>
                            <td>₹{{ number_format((float) $sale->total_amount, 2) }}</td>
                            <td><span class="badge text-bg-light border">{{ ucfirst($sale->payment_status) }}</span></td>
                            <td><span class="badge text-bg-light border">{{ ucfirst($sale->order_status) }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.sales.show', $sale) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center py-5 text-muted">{{ __('No sales yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $sales->links() }}</div>
@endsection
