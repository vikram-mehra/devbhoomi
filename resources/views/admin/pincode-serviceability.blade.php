@extends('layouts.admin')

@section('title', __('Pincode serviceability'))

@section('content')
    <div class="mb-4">
        <h1 class="h4 mb-1">{{ __('Pincode serviceability') }}</h1>
        <p class="text-muted mb-0">{{ __('Map pincode to city and state, then enable or disable delivery.') }}</p>
    </div>

    <form method="post" action="{{ route('admin.pincodes.store') }}" class="card border-0 shadow-sm p-3 p-md-4 mb-4">@csrf
        <h2 class="h6 fw-bold mb-3">{{ __('Add pincode') }}</h2>
        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label small">{{ __('Pincode') }} *</label>
                <input name="pincode" class="form-control" inputmode="numeric" maxlength="6" required value="{{ old('pincode') }}" placeholder="263645">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('City') }} *</label>
                <input name="city" class="form-control" required value="{{ old('city') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('State') }} *</label>
                <input name="state" class="form-control" required value="{{ old('state') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Day offset') }} *</label>
                <input name="day_offset" type="number" min="0" max="30" class="form-control" required value="{{ old('day_offset', 3) }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Courier name') }}</label>
                <input name="courier_name" class="form-control" value="{{ old('courier_name') }}" placeholder="Delhivery">
            </div>
            <div class="col-md-2">
                <label class="form-label small">{{ __('Status') }} *</label>
                <select name="status" class="form-select">
                    <option value="1" @selected((string) old('status', '1') === '1')>{{ __('Enabled') }}</option>
                    <option value="0" @selected((string) old('status') === '0')>{{ __('Disabled') }}</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">{{ __('Add') }}</button>
            </div>
        </div>
        <p class="small text-muted mt-2 mb-0">{{ __('Day offset is added to today to show the estimated delivery date on the storefront.') }}</p>
    </form>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Mapped pincodes') }}</span>
                <span class="admin-data-card__meta">{{ __('Search, edit, toggle, or delete.') }}</span>
            </div>
            <form method="get" class="d-flex gap-2">
                <input type="search" name="q" value="{{ $q }}" class="form-control form-control-sm" placeholder="{{ __('Search pincode, city, courier') }}" style="min-width: 14rem;">
                <button class="btn btn-sm btn-outline-secondary">{{ __('Search') }}</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Pincode') }}</th>
                            <th>{{ __('City') }}</th>
                            <th>{{ __('State') }}</th>
                            <th>{{ __('Day offset') }}</th>
                            <th>{{ __('Courier') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="fw-bold font-monospace">{{ $row->pincode }}</td>
                                <td>{{ $row->city }}</td>
                                <td>{{ $row->state }}</td>
                                <td>{{ $row->day_offset }}</td>
                                <td>{{ $row->courier_name ?: '—' }}</td>
                                <td>
                                    @if($row->status)
                                        <span class="admin-chip admin-chip--success">{{ __('Enabled') }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--muted">{{ __('Disabled') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editPincode{{ $row->id }}">{{ __('Edit') }}</button>
                                        <form method="post" action="{{ route('admin.pincodes.toggle', $row) }}" class="d-inline">@csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $row->status ? __('Disable') : __('Enable') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.pincodes.destroy', $row) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Delete this pincode mapping?')) }})">@csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <div class="modal fade" id="editPincode{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="post" action="{{ route('admin.pincodes.update', $row) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="modal-header">
                                                <h5 class="modal-title">{{ __('Edit pincode') }} — {{ $row->pincode }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('Pincode') }} *</label>
                                                        <input name="pincode" class="form-control" inputmode="numeric" maxlength="6" required value="{{ $row->pincode }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('Day offset') }} *</label>
                                                        <input name="day_offset" type="number" min="0" max="30" class="form-control" required value="{{ $row->day_offset }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('City') }} *</label>
                                                        <input name="city" class="form-control" required value="{{ $row->city }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('State') }} *</label>
                                                        <input name="state" class="form-control" required value="{{ $row->state }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('Courier name') }}</label>
                                                        <input name="courier_name" class="form-control" value="{{ $row->courier_name }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small">{{ __('Status') }} *</label>
                                                        <select name="status" class="form-select">
                                                            <option value="1" @selected($row->status)>{{ __('Enabled') }}</option>
                                                            <option value="0" @selected(! $row->status)>{{ __('Disabled') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button class="btn btn-primary">{{ __('Save') }}</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">{{ __('No pincode mappings yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
            <div class="card-footer">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
