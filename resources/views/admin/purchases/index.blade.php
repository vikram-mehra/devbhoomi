@extends('layouts.admin')

@section('title', __('Purchases'))

@section('content')
    @include('admin.partials.transaction-hub')

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Invoice') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Supplier') }}</th>
                        <th>{{ __('Lines') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th>{{ __('Payment') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td class="fw-semibold font-monospace">{{ $purchase->invoice_number }}</td>
                            <td>{{ optional($purchase->purchase_date)->format('d M Y') }}</td>
                            <td>{{ $purchase->supplier->name ?? '—' }}</td>
                            <td>{{ $purchase->items_count }}</td>
                            <td>₹{{ number_format((float) $purchase->total_amount, 2) }}</td>
                            <td><span class="badge text-bg-light border">{{ ucfirst($purchase->payment_status) }}</span></td>
                            <td class="text-end"><a href="{{ route('admin.purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary">{{ __('View') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">{{ __('No purchases yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $purchases->links() }}</div>
@endsection
