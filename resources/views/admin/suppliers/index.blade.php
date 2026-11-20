@extends('layouts.admin')

@section('title', __('Suppliers'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h4 mb-0 fw-bold">{{ __('Suppliers') }}</h1>
            <p class="text-muted small mb-0">{{ __('Manage vendor suppliers and inventory item purchases.') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.suppliers.items') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-list-check me-1"></i>{{ __('Item Listing with Cost') }}
            </a>
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm rounded-pill">
                <i class="bi bi-plus-lg me-1"></i>{{ __('New Purchase') }}
            </a>
        </div>
    </div>

    <form method="post" action="{{ route('admin.suppliers.store') }}" class="card border-0 shadow-sm p-3 p-md-4 mb-4">@csrf
        <div class="row g-2">
            <div class="col-md-3"><input name="name" class="form-control" placeholder="{{ __('Supplier name') }}" required></div>
            <div class="col-md-2"><input name="phone" class="form-control" placeholder="{{ __('Phone') }}"></div>
            <div class="col-md-2"><input name="email" class="form-control" placeholder="{{ __('Email') }}"></div>
            <div class="col-md-2"><input name="gst_number" class="form-control" placeholder="{{ __('GST number') }}"></div>
            <div class="col-md-2"><input name="pending_payment_amount" type="number" step="0.01" min="0" class="form-control" placeholder="{{ __('Pending payment') }}"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">{{ __('Add') }}</button></div>
        </div>
    </form>

    <form method="get" class="mb-3 d-flex gap-2 align-items-center" style="max-width: 340px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('Search suppliers…') }}">
            <button class="btn btn-outline-primary btn-sm">{{ __('Search') }}</button>
        </div>
        @if(request()->filled('q'))
            <a href="{{ route('admin.suppliers.index') }}" class="btn btn-sm btn-outline-secondary text-nowrap">{{ __('Reset') }}</a>
        @endif
    </form>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('GST') }}</th>
                        <th>{{ __('Purchases') }}</th>
                        <th>{{ __('Pending') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($suppliers as $supplier)
                        <tr>
                            <td class="fw-semibold">{{ $supplier->name }}</td>
                            <td class="small">{{ $supplier->phone ?: '—' }}<br>{{ $supplier->email ?: '' }}</td>
                            <td>{{ $supplier->gst_number ?: '—' }}</td>
                            <td>{{ $supplier->purchases_count }}</td>
                            <td>₹{{ number_format((float) $supplier->pending_payment_amount, 2) }}</td>
                            <td>{{ $supplier->is_active ? __('Active') : __('Inactive') }}</td>
                            <td class="text-end"><a href="{{ route('admin.suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>{{ __('Items & Cost') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $suppliers->links() }}</div>
@endsection
