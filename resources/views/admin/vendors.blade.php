@extends('layouts.admin')

@section('title', __('Vendors'))

@section('content')
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div></div>
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ __('Add vendor') }}
        </a>
    </div>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Vendor shops') }}</span>
                <span class="admin-data-card__meta">{{ __('Create, edit, approve, reject, and set commission.') }}</span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $vendors->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Shop') }}</th>
                            <th>{{ __('Owner') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Commission %') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $v)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $v->shop_name }}</span>
                                    <div class="small text-muted font-monospace">{{ $v->slug }}</div>
                                </td>
                                <td class="small text-break">{{ $v->user->email }}</td>
                                <td>
                                    @if($v->status === 'approved')
                                        <span class="admin-chip admin-chip--success">{{ $v->status }}</span>
                                    @elseif($v->status === 'rejected')
                                        <span class="admin-chip admin-chip--danger">{{ $v->status }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--warning">{{ $v->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="post" action="{{ route('admin.vendors.commission', $v) }}" class="d-flex flex-wrap gap-1 align-items-center">
                                        @csrf
                                        <input type="number" name="commission_percent" value="{{ $v->commission_percent }}" class="form-control form-control-sm" style="width:5.5rem" min="0" max="90">
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Save') }}</button>
                                    </form>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-1 justify-content-end">
                                        <a href="{{ route('admin.vendors.edit', $v) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                                        @if($v->status !== 'approved')
                                            <form method="post" action="{{ route('admin.vendors.approve', $v) }}" class="d-inline">@csrf
                                                <button type="submit" class="btn btn-sm btn-success">{{ __('Approve') }}</button>
                                            </form>
                                        @endif
                                        <form method="post" action="{{ route('admin.vendors.reject', $v) }}" class="d-inline">@csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning">{{ __('Reject') }}</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.vendors.destroy', $v) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Delete this vendor and their shop? Products will also be removed.')) }})">@csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">{{ __('No vendors yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $vendors->links() }}</div>
@endsection
