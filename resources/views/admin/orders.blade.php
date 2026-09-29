@extends('layouts.admin')

@section('title', __('Orders'))

@section('content')
    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('All orders') }}</span>
                <span class="admin-data-card__meta">{{ __('Update status and payment from the row.') }}</span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $orders->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('#') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Total') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Payment') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                            <tr>
                                <td class="fw-semibold font-monospace small">{{ $o->order_number }}</td>
                                <td class="small text-break">{{ $o->user->email }}</td>
                                <td class="fw-semibold">₹{{ number_format($o->total, 2) }}</td>
                                <td>
                                    <form method="post" action="{{ route('admin.orders.status', $o) }}" class="d-flex flex-wrap gap-1 align-items-center">@csrf
                                        <select name="status" class="form-select form-select-sm" style="min-width: 11rem; max-width: 16rem;" aria-label="{{ __('Order status') }}">
                                            @foreach(\App\Models\Order::adminStatusOptions() as $value => $label)
                                                <option value="{{ $value }}"{{ $o->status === $value ? ' selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                            @unless(array_key_exists($o->status, \App\Models\Order::adminStatusOptions()))
                                                <option value="{{ $o->status }}" selected>{{ $o->status }}</option>
                                            @endunless
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" action="{{ route('admin.orders.payment', $o) }}" class="d-flex flex-wrap gap-1 align-items-center">@csrf
                                        <input name="payment_status" class="form-control form-control-sm" style="min-width: 7rem; max-width: 12rem;" value="{{ $o->payment_status }}">
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $orders->links() }}</div>
@endsection
