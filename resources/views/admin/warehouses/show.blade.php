@extends('layouts.admin')

@section('title', $warehouse->name)

@section('content')
    <div class="card border-0 shadow-sm p-3 mb-4">
        <h2 class="h6">{{ __('Transfer stock') }}</h2>
        <form method="post" action="{{ route('admin.warehouses.transfer') }}">@csrf
            <input type="hidden" name="from_warehouse_id" value="{{ $warehouse->id }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">{{ __('To warehouse') }}</label>
                    <select name="to_warehouse_id" class="form-select" required>
                        @foreach ($warehouses as $wh)
                            <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('Variant SKU') }}</label>
                    <input name="lines[0][variant_sku]" class="form-control" placeholder="{{ __('Variant SKU') }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">{{ __('Qty') }}</label>
                    <input type="number" min="1" name="lines[0][quantity]" class="form-control" value="1" required>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('Transfer') }}</button></div>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Variant') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Damaged') }}</th></tr></thead>
                <tbody>
                    @foreach ($stocks as $stock)
                        <tr>
                            <td>{{ $stock->variant->product->name ?? '—' }}</td>
                            <td>{{ $stock->variant->label() ?? $stock->variant->sku ?? '—' }}</td>
                            <td>{{ $stock->qty }}</td>
                            <td>{{ $stock->damaged_qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $stocks->links() }}</div>
@endsection
