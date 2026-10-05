@extends('layouts.admin')

@section('title', __('Pincode serviceability'))

@section('content')
    @php
        $openModal = old('_form');
        if ($openModal !== 'add' && $openModal !== 'import' && session('pincode_import_errors')) {
            $openModal = 'import';
        }
    @endphp

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Mapped pincodes') }}</span>
                <span class="admin-data-card__meta">{{ __('Search, edit, toggle, or delete.') }}</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPincodeModal">{{ __('Add pincode') }}</button>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadPincodeModal">{{ __('Upload CSV') }}</button>
                <a href="{{ route('admin.pincodes.export', request()->only(['q', 'status'])) }}" class="btn btn-sm btn-outline-secondary">{{ __('Export CSV') }}</a>
            </div>
        </div>
        <div class="card-body border-bottom py-3">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small mb-1" for="pincodeSearch">{{ __('Search') }}</label>
                    <input type="search" name="q" id="pincodeSearch" value="{{ $q }}" class="form-control form-control-sm" placeholder="{{ __('Pincode, city, state, courier') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1" for="pincodeStatus">{{ __('Status') }}</label>
                    <select name="status" id="pincodeStatus" class="form-select form-select-sm">
                        <option value="">{{ __('All statuses') }}</option>
                        <option value="1" @selected($status === '1')>{{ __('Enabled') }}</option>
                        <option value="0" @selected($status === '0')>{{ __('Disabled') }}</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-secondary">{{ __('Apply') }}</button>
                    @if($q !== '' || $status !== '')
                        <a href="{{ route('admin.pincodes.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Clear') }}</a>
                    @endif
                </div>
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
        @if($rows->total() > 0)
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex flex-column flex-lg-row align-items-stretch align-items-lg-center justify-content-between gap-3">
                    <div class="small text-muted text-center text-lg-start">
                        {{ __('Showing :from–:to of :total results', [
                            'from' => $rows->firstItem(),
                            'to' => $rows->lastItem(),
                            'total' => $rows->total(),
                        ]) }}
                    </div>
                    @if($rows->hasPages())
                        <div class="admin-pagination-wrap mt-0 pt-0 pb-0">
                            {{ $rows->links('admin.components.pagination-advanced') }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="addPincodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.pincodes.store') }}">
                    @csrf
                    <input type="hidden" name="_form" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Add pincode') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('Pincode') }} *</label>
                                <input name="pincode" class="form-control @error('pincode') is-invalid @enderror" inputmode="numeric" maxlength="6" required value="{{ old('_form') === 'add' ? old('pincode') : '' }}" placeholder="263645">
                                @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('City') }} *</label>
                                <input name="city" class="form-control @error('city') is-invalid @enderror" required value="{{ old('_form') === 'add' ? old('city') : '' }}">
                                @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('State') }} *</label>
                                <input name="state" class="form-control @error('state') is-invalid @enderror" required value="{{ old('_form') === 'add' ? old('state') : '' }}">
                                @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('Day offset') }} *</label>
                                <input name="day_offset" type="number" min="0" max="30" class="form-control" required value="{{ old('_form') === 'add' ? old('day_offset', 3) : 3 }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('Courier name') }}</label>
                                <input name="courier_name" class="form-control" value="{{ old('_form') === 'add' ? old('courier_name') : '' }}" placeholder="Delhivery">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('Status') }} *</label>
                                <select name="status" class="form-select">
                                    <option value="1" @selected(old('_form') !== 'add' || (string) old('status', '1') === '1')>{{ __('Enabled') }}</option>
                                    <option value="0" @selected(old('_form') === 'add' && (string) old('status') === '0')>{{ __('Disabled') }}</option>
                                </select>
                            </div>
                        </div>
                        <p class="small text-muted mt-3 mb-0">{{ __('Day offset is added to today to show the estimated delivery date on the storefront.') }}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button class="btn btn-primary">{{ __('Add') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadPincodeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('admin.pincodes.import') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_form" value="import">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('Upload CSV') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted">{{ __('Existing pincodes are updated. New pincodes are inserted. Use the same columns as Export CSV.') }}</p>
                        <label class="form-label small">{{ __('CSV file') }} *</label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,text/csv,text/plain" required>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <p class="small text-muted mt-2 mb-0">{{ __('Columns: pincode, city, state, day_offset, courier_name, status (1 or 0).') }}</p>
                        @if(session('pincode_import_errors'))
                            <ul class="small text-danger mt-2 mb-0">
                                @foreach(session('pincode_import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <a href="{{ route('admin.pincodes.template') }}" class="btn btn-outline-secondary me-auto">{{ __('Download sample CSV') }}</a>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button class="btn btn-primary">{{ __('Upload CSV') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@if($openModal === 'add' || $openModal === 'import')
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.getElementById(@json($openModal === 'import' ? 'uploadPincodeModal' : 'addPincodeModal'));
                if (el && window.bootstrap) {
                    window.bootstrap.Modal.getOrCreateInstance(el).show();
                }
            });
        </script>
    @endpush
@endif
