@extends('layouts.admin')

@php
    $isCustomer = ($type ?? 'customer') === 'customer';
    $pageTitle = $isCustomer ? __('Customers') : __('Admin & Staff');
@endphp

@section('title', $pageTitle)

@section('content')

    <form method="get" action="{{ $isCustomer ? route('admin.users.customers') : route('admin.users.admins') }}" class="mb-3 d-flex gap-2 align-items-center" style="max-width: 340px;">
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ $isCustomer ? __('Search customers…') : __('Search admin/staff…') }}">
            <button class="btn btn-outline-primary btn-sm">{{ __('Search') }}</button>
        </div>
        @if(request()->filled('q'))
            <a href="{{ $isCustomer ? route('admin.users.customers') : route('admin.users.admins') }}" class="btn btn-sm btn-outline-secondary text-nowrap">{{ __('Reset') }}</a>
        @endif
    </form>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ $pageTitle }}</span>
                <span class="admin-data-card__meta">
                    @if($isCustomer)
                        {{ __('Total registered customer accounts.') }}
                    @else
                        {{ __('Change role or access level per administrator.') }}
                    @endif
                </span>
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
                            @if($isCustomer)
                                <th>{{ __('Orders') }}</th>
                                <th>{{ __('Joined') }}</th>
                            @else
                                <th>{{ __('Role') }}</th>
                            @endif
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
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
                                @if($isCustomer)
                                    <td>
                                        <span class="badge rounded-pill bg-light text-dark border px-2 py-1">
                                            {{ $u->orders_count ?? 0 }} {{ __('orders') }}
                                        </span>
                                    </td>
                                    <td class="small text-muted">
                                        {{ optional($u->created_at)->format('d M Y') }}
                                    </td>
                                @else
                                    <td>
                                        @if($u->role === 'admin')
                                            <span class="admin-chip admin-chip--danger">{{ $u->role }}</span>
                                        @elseif($u->role === 'seo')
                                            <span class="admin-chip admin-chip--success">{{ $u->role }}</span>
                                        @elseif($u->role === 'vendor')
                                            <span class="admin-chip admin-chip--warning">{{ $u->role }}</span>
                                        @else
                                            <span class="admin-chip admin-chip--muted">{{ $u->role }}</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-2 justify-content-end align-items-center">
                                        @if($isCustomer)
                                            {{-- Customer List Actions: View Orders button & Delete button (NO Update button) --}}
                                            <a href="{{ route('admin.orders.index', ['user_id' => $u->id]) }}"
                                                class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                                title="{{ __('View orders for this customer') }}">
                                                <i class="bi bi-bag-check"></i>
                                                <span>{{ __('View orders') }}</span>
                                                <span class="badge bg-primary text-white ms-1">{{ $u->orders_count ?? 0 }}</span>
                                            </a>
                                        @else
                                            {{-- Admin/Staff Actions: Role change dropdown + Update button --}}
                                            <form method="post" action="{{ route('admin.users.role', $u) }}" class="d-inline-flex gap-1 align-items-center">
                                                @csrf
                                                <select name="role" class="form-select form-select-sm" style="width: auto; min-width: 7rem;">
                                                    <option value="user" @if($u->role==='user') selected @endif>user</option>
                                                    <option value="vendor" @if($u->role==='vendor') selected @endif>vendor</option>
                                                    <option value="seo" @if($u->role==='seo') selected @endif>seo</option>
                                                    <option value="admin" @if($u->role==='admin') selected @endif>admin</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary">{{ __('Update') }}</button>
                                            </form>
                                        @endif

                                        {{-- Delete User button --}}
                                        @if($u->id !== auth()->id())
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="{{ __('Delete user') }}"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteUserModal"
                                                data-action="{{ route('admin.users.destroy', $u) }}"
                                                data-name="{{ $u->name }}"
                                                data-email="{{ $u->email }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary opacity-50" disabled title="{{ __('Cannot delete yourself') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isCustomer ? 6 : 5 }}" class="text-center py-4 text-muted">
                                    {{ $isCustomer ? __('No customer accounts found.') : __('No admin or staff accounts found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="admin-pagination-wrap">{{ $users->links() }}</div>

    {{-- Delete User Confirmation Modal --}}
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header bg-light border-0 py-3">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center gap-2 mb-0" id="deleteUserModalLabel">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        {{ __('Confirm User Deletion') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="mb-2">{{ __('Are you sure you want to delete this user?') }}</p>
                    <div class="alert alert-danger py-2 px-3 small mb-3">
                        <strong id="deleteUserName"></strong> (<span id="deleteUserEmail"></span>)
                    </div>
                    <div class="p-3 bg-light rounded small text-danger border border-danger-subtle mb-0">
                        <i class="bi bi-exclamation-octagon me-1"></i>
                        <strong>{{ __('Warning') }}:</strong> {{ __('This will permanently delete this user and all related records including their orders, order items, addresses, reviews, cart, and profile. This action cannot be undone.') }}
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form id="deleteUserForm" method="post" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger d-inline-flex align-items-center gap-2">
                            <i class="bi bi-trash"></i>
                            {{ __('Yes, Delete Everything') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteUserModal');
        if (!deleteModal) return;

        deleteModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var action = button.getAttribute('data-action');
            var name = button.getAttribute('data-name');
            var email = button.getAttribute('data-email');

            document.getElementById('deleteUserForm').action = action;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteUserEmail').textContent = email;
        });
    });
    </script>
    @endpush
@endsection
