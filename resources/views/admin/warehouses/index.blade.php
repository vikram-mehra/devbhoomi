@extends('layouts.admin')

@section('title', __('Warehouses'))

@section('content')
    <form method="post" action="{{ route('admin.warehouses.store') }}" class="card border-0 shadow-sm p-3 mb-4">@csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-4"><input name="name" class="form-control" placeholder="{{ __('Warehouse name') }}" required></div>
            <div class="col-md-2 form-check"><input type="checkbox" name="is_default" value="1" class="form-check-input" id="whDefault"><label for="whDefault" class="form-check-label">{{ __('Default') }}</label></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('Add warehouse') }}</button></div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @foreach ($warehouses as $warehouse)
            <div class="col-md-4">
                <div class="admin-stat-card h-100">
                    <div class="admin-stat-card__label">{{ $warehouse->name }} @if($warehouse->is_default)<span class="badge text-bg-light">{{ __('Default') }}</span>@endif</div>
                    <div class="admin-stat-card__value">{{ $warehouse->stocks_count }}</div>
                    <p class="small text-muted mb-2">{{ __('Tracked variant rows') }}</p>
                    <a href="{{ route('admin.warehouses.show', $warehouse) }}" class="btn btn-sm btn-outline-primary">{{ __('Open') }}</a>
                    <a href="{{ route('admin.warehouses.report', $warehouse) }}" class="btn btn-sm btn-outline-secondary">{{ __('Report') }}</a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="card-header"><strong>{{ __('Recent transfers') }}</strong></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('Date') }}</th></tr></thead>
                <tbody>
                    @forelse ($transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->reference }}</td>
                            <td>{{ $transfer->fromWarehouse->name ?? '—' }}</td>
                            <td>{{ $transfer->toWarehouse->name ?? '—' }}</td>
                            <td>{{ optional($transfer->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('No transfers yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
