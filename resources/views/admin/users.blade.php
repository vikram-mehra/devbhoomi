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
                                    @elseif($u->role === 'seo')
                                        <span class="admin-chip admin-chip--success">{{ $u->role }}</span>
                                    @elseif($u->role === 'vendor')
                                        <span class="admin-chip admin-chip--warning">{{ $u->role }}</span>
                                    @else
                                        <span class="admin-chip admin-chip--muted">{{ $u->role }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap gap-2 justify-content-end align-items-center">
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
                        @endforeach
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
                    <p class="mb-2 text-secondary">{{ __('Are you sure you want to delete this user account?') }}</p>
                    <div class="p-3 rounded bg-light border">
                        <div class="fw-bold text-dark fs-6 text-truncate" id="deleteUserName"></div>
                        <div class="small text-muted text-break" id="deleteUserEmail"></div>
                    </div>
                    <div class="alert alert-danger py-2 px-3 small mt-3 mb-0">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-bold">
                            <i class="bi bi-exclamation-circle-fill"></i>
                            <span>{{ __('Warning: Everything related to this user will be deleted!') }}</span>
                        </div>
                        <p class="mb-0 text-danger-emphasis">
                            {{ __('Deleting this user will permanently delete all associated data including their orders, order items, shipments, addresses, cart items, wishlists, reviews, wallet transactions, chat messages, and account credentials. This action cannot be undone.') }}
                        </p>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <form id="deleteUserForm" method="post" action="" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger px-3">
                            <i class="bi bi-trash me-1"></i>{{ __('Delete User & Everything') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteUserModal = document.getElementById('deleteUserModal');
        if (deleteUserModal) {
            deleteUserModal.addEventListener('show.bs.modal', function (event) {
                var btn = event.relatedTarget;
                if (!btn) return;
                var action = btn.getAttribute('data-action');
                var name = btn.getAttribute('data-name');
                var email = btn.getAttribute('data-email');
                var form = deleteUserModal.querySelector('#deleteUserForm');
                var nameEl = deleteUserModal.querySelector('#deleteUserName');
                var emailEl = deleteUserModal.querySelector('#deleteUserEmail');
                if (form && action) form.action = action;
                if (nameEl) nameEl.textContent = name || '';
                if (emailEl) emailEl.textContent = email || '';
            });
        }
    });
    </script>
    @endpush
@endsection
