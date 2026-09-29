@extends('layouts.admin')

@section('title', $supplier->name)

@section('content')
    <div class="card border-0 shadow-sm p-3 p-md-4 mb-4">
        <form method="post" action="{{ route('admin.suppliers.update', $supplier) }}">@csrf @method('PATCH')
            <div class="row g-2">
                <div class="col-md-3"><input name="name" class="form-control" value="{{ $supplier->name }}" required></div>
                <div class="col-md-2"><input name="phone" class="form-control" value="{{ $supplier->phone }}"></div>
                <div class="col-md-2"><input name="email" class="form-control" value="{{ $supplier->email }}"></div>
                <div class="col-md-2"><input name="gst_number" class="form-control" value="{{ $supplier->gst_number }}"></div>
                <div class="col-md-2"><input name="pending_payment_amount" type="number" step="0.01" class="form-control" value="{{ $supplier->pending_payment_amount }}"></div>
                <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('Save') }}</button></div>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-header"><strong>{{ __('Purchase history') }}</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead><tr><th>{{ __('Invoice') }}</th><th>{{ __('Date') }}</th><th>{{ __('Total') }}</th><th>{{ __('Payment') }}</th></tr></thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td><a href="{{ route('admin.purchases.show', $purchase) }}">{{ $purchase->invoice_number }}</a></td>
                            <td>{{ optional($purchase->purchase_date)->format('d M Y') }}</td>
                            <td>₹{{ number_format((float) $purchase->total_amount, 2) }}</td>
                            <td>{{ ucfirst($purchase->payment_status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('No purchases for this supplier yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $purchases->links() }}</div>
@endsection
