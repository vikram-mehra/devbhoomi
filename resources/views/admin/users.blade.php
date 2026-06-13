@extends('layouts.admin')

@section('title', __('Users'))

@section('content')
    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Accounts') }}</span>
                <span class="admin-data-card__meta">{{ __('Change role per user.') }}</span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $users->total() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-table">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Email verified') }}</th>
                            <th>{{ __('Role') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td class="fw-semibold">{{ $u->name }}</td>
                                <td class="small text-break">{{ $u->email }}</td>
                                <td>
                                    @if($u->hasVerifiedEmail())
                                        <span class="admin-chip admin-chip--success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--warning">{{ __('No') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($u->role === 'admin')
                                        <span class="admin-chip admin-chip--danger">{{ $u->role }}</span>
                                    @elseif($u->role === 'vendor')
                                        <span class="admin-chip admin-chip--warning">{{ $u->role }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--muted">{{ $u->role }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form method="post" action="{{ route('admin.users.role', $u) }}" class="d-inline-flex flex-wrap gap-1 justify-content-end align-items-center">
                                        @csrf
                                        <select name="role" class="form-select form-select-sm" style="width: auto; min-width: 7rem;">
                                            <option value="user" @if($u->role==='user') selected @endif>user</option>
                                            <option value="vendor" @if($u->role==='vendor') selected @endif>vendor</option>
                                            <option value="admin" @if($u->role==='admin') selected @endif>admin</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">{{ __('Update') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $users->links() }}</div>
@endsection
