@extends('layouts.admin')

@section('title', $vendor ? __('Edit vendor') : __('Add vendor'))

@section('page_subtitle')
    {{ $vendor ? __('Update shop details, owner account, status, and commission.') : __('Create a vendor shop and owner login.') }}
@endsection

@section('content')
    @php
        $action = $vendor ? route('admin.vendors.update', $vendor) : route('admin.vendors.store');
        $user = $vendor?->user;
    @endphp
    <div class="admin-form-hero card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="admin-form-hero__strip"></div>
        <div class="card-body p-4 p-lg-5">
            <form method="post" action="{{ $action }}" class="row g-3">
                @csrf
                @if($vendor)
                    @method('PATCH')
                @endif

                <div class="col-12">
                    <h2 class="h6 fw-bold mb-0">{{ __('Owner account') }}</h2>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Full name') }} *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required maxlength="255">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Email') }} *</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required maxlength="255">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ $vendor ? __('New password') : __('Password') }}{{ $vendor ? '' : ' *' }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" {{ $vendor ? '' : 'required' }} minlength="8" autocomplete="new-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if($vendor)
                        <div class="form-text">{{ __('Leave blank to keep the current password.') }}</div>
                    @endif
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Confirm password') }}</label>
                    <input type="password" name="password_confirmation" class="form-control" minlength="8" autocomplete="new-password">
                </div>

                <div class="col-12 pt-2">
                    <h2 class="h6 fw-bold mb-0">{{ __('Shop details') }}</h2>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Shop name') }} *</label>
                    <input type="text" name="shop_name" class="form-control @error('shop_name') is-invalid @enderror" value="{{ old('shop_name', $vendor->shop_name ?? '') }}" required maxlength="255">
                    @error('shop_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('URL slug') }}</label>
                    <input type="text" name="slug" class="form-control font-monospace small @error('slug') is-invalid @enderror" value="{{ old('slug', $vendor->slug ?? '') }}" placeholder="{{ __('auto from shop name') }}" maxlength="255">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('Used in /store/{slug}. Leave empty to generate automatically.') }}</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('City') }}</label>
                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $vendor->city ?? '') }}" maxlength="120">
                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('State') }}</label>
                    <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $vendor->state ?? '') }}" maxlength="120">
                    @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Commission %') }} *</label>
                    <input type="number" name="commission_percent" class="form-control @error('commission_percent') is-invalid @enderror" value="{{ old('commission_percent', $vendor->commission_percent ?? $defaultCommission) }}" min="0" max="90" required>
                    @error('commission_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Status') }} *</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                        @foreach(['pending' => __('Pending'), 'approved' => __('Approved'), 'rejected' => __('Rejected')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $vendor->status ?? 'pending') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Description') }}</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="5000">{{ old('description', $vendor->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                @if($vendor)
                    <div class="col-12">
                        @include('admin.partials.seo-fields', ['entity' => $vendor, 'nameField' => 'shop_name'])
                    </div>
                @endif

                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">{{ $vendor ? __('Save changes') : __('Create vendor') }}</button>
                    <a href="{{ route('admin.vendors.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
