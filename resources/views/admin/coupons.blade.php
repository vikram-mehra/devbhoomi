@extends('layouts.admin')

@section('title', __('Coupons'))

@section('content')
    <form method="post" action="{{ route('admin.coupons.store') }}" class="card border-0 shadow-sm p-3 p-md-4 mb-4">@csrf
        <h2 class="h6 fw-bold mb-3">{{ __('Create coupon') }}</h2>
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label small">{{ __('Code') }} *</label>
                <input name="code" class="form-control text-uppercase" placeholder="SAVE20" required value="{{ old('code') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Coupon type') }} *</label>
                <select name="coupon_type" class="form-select">
                    <option value="public" @selected(old('coupon_type', 'public') === 'public')>{{ __('Public') }}</option>
                    <option value="internal" @selected(old('coupon_type') === 'internal')>{{ __('Internal') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Discount type') }} *</label>
                <select name="type" class="form-select">
                    <option value="percent" @selected(old('type') === 'percent')>%</option>
                    <option value="fixed" @selected(old('type') === 'fixed')>{{ __('Fixed ₹') }}</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Value') }} *</label>
                <input name="value" type="number" step="0.01" min="0" class="form-control" required value="{{ old('value') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Min cart') }}</label>
                <input name="min_cart" type="number" step="0.01" min="0" class="form-control" value="{{ old('min_cart') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Max discount') }}</label>
                <input name="max_discount" type="number" step="0.01" min="0" class="form-control" value="{{ old('max_discount') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Usage limit') }}</label>
                <input name="usage_limit" type="number" min="1" class="form-control" value="{{ old('usage_limit') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('Starts at') }} *</label>
                <input name="starts_at" type="datetime-local" class="form-control" required value="{{ old('starts_at', now()->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small">{{ __('Ends at') }} *</label>
                <input name="ends_at" type="datetime-local" class="form-control" required value="{{ old('ends_at', now()->addMonths(3)->format('Y-m-d\TH:i')) }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">{{ __('Create') }}</button>
            </div>
        </div>
        <p class="small text-muted mt-2 mb-0">{{ __('Public coupons appear on the Offers page. Internal coupons work only when the code is entered directly (checkout or admin sale).') }}</p>
    </form>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Existing coupons') }}</span>
                <span class="admin-data-card__meta">{{ __('Edit, delete, or toggle active status.') }}</span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $coupons->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Coupon type') }}</th>
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Value') }}</th>
                            <th>{{ __('Min cart') }}</th>
                            <th>{{ __('Used') }}</th>
                            <th>{{ __('Valid') }}</th>
                            <th>{{ __('Active') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $c)
                            <tr>
                                <td class="fw-bold font-monospace">{{ $c->code }}</td>
                                <td>
                                    @if($c->coupon_type === 'internal')
                                        <span class="admin-chip admin-chip--warning">{{ __('Internal') }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--success">{{ __('Public') }}</span>
                                    @endif
                                </td>
                                <td><span class="admin-chip admin-chip--muted">{{ $c->type === 'percent' ? '%' : __('Fixed') }}</span></td>
                                <td class="fw-semibold">{{ $c->type === 'percent' ? $c->value.'%' : '₹'.$c->value }}</td>
                                <td>₹{{ number_format((float) $c->min_cart, 0) }}</td>
                                <td>{{ $c->used_count }}{{ $c->usage_limit ? ' / '.$c->usage_limit : '' }}</td>
                                <td class="small text-muted">
                                    {{ optional($c->starts_at)->format('d M Y') }} – {{ optional($c->ends_at)->format('d M Y') }}
                                </td>
                                <td>
                                    @if($c->is_active)
                                        <span class="admin-chip admin-chip--success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--muted">{{ __('No') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCoupon{{ $c->id }}">{{ __('Edit') }}</button>
                                        <form method="post" action="{{ route('admin.coupons.toggle', $c) }}" class="d-inline">@csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ __('Toggle') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.coupons.destroy', $c) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Delete this coupon?')) }})">@csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editCoupon{{ $c->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <form method="post" action="{{ route('admin.coupons.update', $c) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Edit coupon') }} — {{ $c->code }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-2">
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Code') }} *</label>
                                                        <input name="code" class="form-control text-uppercase" required value="{{ $c->code }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Coupon type') }} *</label>
                                                        <select name="coupon_type" class="form-select">
                                                            <option value="public" @selected($c->coupon_type === 'public')>{{ __('Public') }}</option>
                                                            <option value="internal" @selected($c->coupon_type === 'internal')>{{ __('Internal') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Discount type') }} *</label>
                                                        <select name="type" class="form-select">
                                                            <option value="percent" @selected($c->type === 'percent')>%</option>
                                                            <option value="fixed" @selected($c->type === 'fixed')>{{ __('Fixed ₹') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Value') }} *</label>
                                                        <input name="value" type="number" step="0.01" min="0" class="form-control" required value="{{ $c->value }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Min cart') }}</label>
                                                        <input name="min_cart" type="number" step="0.01" min="0" class="form-control" value="{{ $c->min_cart }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Max discount') }}</label>
                                                        <input name="max_discount" type="number" step="0.01" min="0" class="form-control" value="{{ $c->max_discount }}">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">{{ __('Usage limit') }}</label>
                                                        <input name="usage_limit" type="number" min="1" class="form-control" value="{{ $c->usage_limit }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('Starts at') }} *</label>
                                                        <input name="starts_at" type="datetime-local" class="form-control" required value="{{ optional($c->starts_at)->format('Y-m-d\TH:i') }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('Ends at') }} *</label>
                                                        <input name="ends_at" type="datetime-local" class="form-control" required value="{{ optional($c->ends_at)->format('Y-m-d\TH:i') }}">
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="form-check">
                                                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="active{{ $c->id }}" {{ $c->is_active ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="active{{ $c->id }}">{{ __('Active') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr><td colspan="9" class="text-center py-4 text-muted">{{ __('No coupons yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $coupons->links() }}</div>
@endsection
