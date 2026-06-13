@extends('layouts.admin')

@section('title', __('Returns'))

@section('content')
    @include('admin.partials.transaction-hub')

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Return requests') }}</span>
                <span class="admin-data-card__meta">{{ __('Status and internal note per request.') }}</span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $returns->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Order') }}</th>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Reason') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="min-width: 14rem;">{{ __('Admin') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $r)
                            <tr>
                                <td class="fw-semibold font-monospace small">#{{ $r->order_id }}</td>
                                <td class="small text-break">{{ $r->user->email }}</td>
                                <td class="small">{{ Str::limit($r->reason, 80) }}</td>
                                <td><span class="admin-chip {{ $r->statusChipClass() }}">{{ $r->statusLabel() }}</span></td>
                                <td>
                                    <form method="post" action="{{ route('admin.returns.update', $r) }}" class="d-grid gap-1">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-select form-select-sm" required>
                                            @foreach(\App\Models\ReturnModel::statusOptions() as $value => $label)
                                                <option value="{{ $value }}" @selected($r->normalizedStatus() === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <textarea name="admin_note" class="form-control form-control-sm" rows="2" placeholder="{{ __('Internal note (optional)') }}">{{ $r->admin_note }}</textarea>
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
    <div class="admin-pagination-wrap">{{ $returns->links() }}</div>
@endsection
