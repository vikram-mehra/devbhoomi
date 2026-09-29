@extends('layouts.admin')

@section('title', __('Stock ledger'))

@section('content')
    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3"><input name="variant_sku" value="{{ request('variant_sku') }}" class="form-control" placeholder="{{ __('Variant SKU') }}"></div>
        <div class="col-md-3">
            <select name="type" class="form-select">
                <option value="">{{ __('All types') }}</option>
                @foreach ($types as $key => $label)
                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><button class="btn btn-outline-primary w-100">{{ __('Filter') }}</button></div>
    </form>

    <form method="post" action="{{ route('admin.inventory.ledger.adjust') }}" class="card border-0 shadow-sm p-3 mb-4">@csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><input name="variant_sku" class="form-control" placeholder="{{ __('Variant SKU') }}" required></div>
            <div class="col-md-2"><input type="number" name="qty_delta" class="form-control" placeholder="{{ __('Qty +/-') }}" required></div>
            <div class="col-md-2">
                <select name="adjustment_type" class="form-select">
                    <option value="adjustment">{{ __('Manual adjustment') }}</option>
                    <option value="damage">{{ __('Damaged stock') }}</option>
                </select>
            </div>
            <div class="col-md-3"><input name="note" class="form-control" placeholder="{{ __('Note') }}"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100">{{ __('Apply') }}</button></div>
        </div>
    </form>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead><tr><th>{{ __('When') }}</th><th>{{ __('Type') }}</th><th>{{ __('Product') }}</th><th>{{ __('Delta') }}</th><th>{{ __('Balance') }}</th><th>{{ __('Note') }}</th></tr></thead>
                <tbody>
                    @foreach ($movements as $move)
                        <tr>
                            <td class="small">{{ optional($move->created_at)->format('d M Y H:i') }}</td>
                            <td>{{ $types[$move->type] ?? $move->type }}</td>
                            <td>{{ $move->variant->product->name ?? '—' }} <span class="text-muted small">{{ $move->variant->sku ?? '' }}</span></td>
                            <td class="{{ $move->qty_delta < 0 ? 'text-danger' : 'text-success' }} fw-semibold">{{ $move->qty_delta > 0 ? '+' : '' }}{{ $move->qty_delta }}</td>
                            <td>{{ $move->balance_after }}</td>
                            <td class="small">{{ $move->note }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{ $movements->links() }}
@endsection
