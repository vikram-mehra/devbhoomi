@extends('layouts.admin')

@section('title', __('Coupons'))
@section('page_subtitle', __('Create and manage discount codes.'))

@push('styles')
<style>
    .coupon-modal .modal-dialog { margin: 1.25rem auto; }
    .coupon-modal .modal-content { overflow: hidden; }
    .coupon-modal .modal-header,
    .coupon-modal .modal-footer { padding-left: 1.5rem; padding-right: 1.5rem; }
    .coupon-modal .modal-body { padding: 1.25rem 1.5rem; }
</style>
@endpush

@php $openCouponModal = old('_form'); @endphp

@section('content')
    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Existing coupons') }}</span>
                <span class="admin-data-card__meta">{{ __('Usage, remaining slots, and status. Reserved checkouts hold a slot until paid, cancelled, or expired.') }}</span>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $coupons->total() }}</span>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCouponModal">
                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ __('Add coupon') }}
                </button>
            </div>
        </div>
        <div class="card-body border-bottom py-3">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1" for="couponSearch">{{ __('Search') }}</label>
                    <input type="search" name="q" id="couponSearch" value="{{ $q }}" class="form-control form-control-sm" placeholder="{{ __('Coupon code') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="couponStatus">{{ __('Status') }}</label>
                    <select name="status" id="couponStatus" class="form-select form-select-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="active" @selected($status === 'active')>{{ __('Active') }}</option>
                        <option value="inactive" @selected($status === 'inactive')>{{ __('Inactive') }}</option>
                        <option value="expired" @selected($status === 'expired')>{{ __('Expired') }}</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-secondary">{{ __('Apply') }}</button>
                    @if($q !== '' || $status !== '')
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Discount') }}</th>
                            <th>{{ __('Limit') }}</th>
                            <th>{{ __('Used') }}</th>
                            <th>{{ __('Remaining') }}</th>
                            <th>{{ __('Per user') }}</th>
                            <th>{{ __('Discount granted') }}</th>
                            <th>{{ __('Valid') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $c)
                            @php
                                $statusKey = $c->adminStatusKey();
                                $chip = $statusKey === 'active' ? 'success' : ($statusKey === 'expired' || $statusKey === 'exhausted' ? 'warning' : 'muted');
                            @endphp
                            <tr>
                                <td class="fw-bold font-monospace">
                                    <a href="{{ route('admin.coupons.show', $c) }}" class="text-decoration-none">{{ $c->code }}</a>
                                </td>
                                <td>
                                    @if($c->coupon_type === 'internal')
                                        <span class="admin-chip admin-chip--warning">{{ __('Internal') }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--success">{{ __('Public') }}</span>
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $c->type === 'percent' ? $c->value.'%' : '₹'.$c->value }}</td>
                                <td>{{ $c->usage_limit ?: __('Unlimited') }}</td>
                                <td>
                                    {{ (int) $c->used_count }}
                                    @if((int) ($c->reserved_count ?? 0) > 0)
                                        <div class="small text-muted">{{ trans_choice(':count reserved|:count reserved', (int) $c->reserved_count, ['count' => (int) $c->reserved_count]) }}</div>
                                    @endif
                                </td>
                                <td>{{ $c->remainingUses() === null ? '—' : $c->remainingUses() }}</td>
                                <td>{{ $c->per_user_limit ?: __('Unlimited') }}</td>
                                <td>₹{{ number_format((float) ($c->total_discount_granted ?? 0), 2) }}</td>
                                <td class="small text-muted">
                                    {{ optional($c->starts_at)->format('d M Y') }} – {{ optional($c->ends_at)->format('d M Y') }}
                                </td>
                                <td><span class="admin-chip admin-chip--{{ $chip }}">{{ $c->adminStatusLabel() }}</span></td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        <a href="{{ route('admin.coupons.show', $c) }}" class="btn btn-sm btn-outline-secondary">{{ __('Usage') }}</a>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editCoupon{{ $c->id }}">{{ __('Edit') }}</button>
                                        <form method="post" action="{{ route('admin.coupons.toggle', $c) }}" class="d-inline">@csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $c->is_active ? __('Disable') : __('Enable') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.coupons.destroy', $c) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Delete this coupon?')) }})">@csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center py-4 text-muted">{{ __('No coupons yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $coupons->links() }}</div>

    @foreach($coupons as $c)
        <div class="modal fade coupon-modal" id="editCoupon{{ $c->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Edit coupon') }} — {{ $c->code }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <form method="post" action="{{ route('admin.coupons.update', $c) }}" id="editCouponForm{{ $c->id }}" class="modal-body">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="_form" value="edit-{{ $c->id }}">
                        @include('admin.partials.coupon-fields', ['coupon' => $c, 'fieldId' => $c->id, 'formKey' => 'edit-'.$c->id, 'products' => $products, 'menuOptions' => $menuOptions])
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" form="editCouponForm{{ $c->id }}">{{ __('Save changes') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade coupon-modal" id="createCouponModal" tabindex="-1" aria-labelledby="createCouponModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCouponModalTitle">{{ __('Add coupon') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <form method="post" action="{{ route('admin.coupons.store') }}" id="createCouponForm" class="modal-body">
                    @csrf
                    <input type="hidden" name="_form" value="create">
                    @if($openCouponModal === 'create' && $errors->any())
                        <div class="alert alert-danger py-2">
                            <ul class="mb-0 small ps-3">
                                @foreach($errors->all() as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @include('admin.partials.coupon-fields', ['coupon' => null, 'fieldId' => 'create', 'formKey' => 'create', 'products' => $products, 'menuOptions' => $menuOptions])
                    <p class="small text-muted mt-3 mb-0">{{ __('Public coupons appear on the Offers page. Internal coupons work only when the code is entered directly (checkout or admin sale). Applying a coupon to a cart does not count as a redemption.') }}</p>
                </form>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary" form="createCouponForm">{{ __('Create coupon') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@if(is_string($openCouponModal) && $openCouponModal !== '')
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var id = @json($openCouponModal === 'create' ? 'createCouponModal' : $openCouponModal);
                if (id.indexOf('edit-') === 0) {
                    id = 'editCoupon' + id.slice(5);
                }
                var el = document.getElementById(id);
                if (el && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        </script>
    @endpush
@endif
