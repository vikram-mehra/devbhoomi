@php
    $addr = $address ?? null;
    $od = old('is_default');
    $defaultChecked = $od !== null
        ? (filter_var($od, FILTER_VALIDATE_BOOLEAN) || (string) $od === '1')
        : (bool) ($addr && $addr->is_default);
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-semibold text-secondary">{{ __('Label') }}</label>
        <input type="text" name="label" class="form-control" value="{{ old('label', $addr->label ?? '') }}" placeholder="{{ __('e.g. Home, Office') }}" maxlength="64">
        @error('label')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" name="is_default" value="1" class="form-check-input" id="addrIsDefault" @if($defaultChecked) checked @endif>
            <label class="form-check-label" for="addrIsDefault">{{ __('Set as default address') }}</label>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold text-secondary">{{ __('Full name') }} <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $addr->name ?? auth()->user()->name) }}" required maxlength="255">
        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-semibold text-secondary">{{ __('Phone') }} <span class="text-danger">*</span></label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $addr->phone ?? '') }}" required maxlength="32">
        @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold text-secondary">{{ __('Address line 1') }} <span class="text-danger">*</span></label>
        <input type="text" name="line1" class="form-control" value="{{ old('line1', $addr->line1 ?? '') }}" required maxlength="255">
        @error('line1')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label small fw-semibold text-secondary">{{ __('Line 2') }}</label>
        <input type="text" name="line2" class="form-control" value="{{ old('line2', $addr->line2 ?? '') }}" maxlength="255">
        @error('line2')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold text-secondary">{{ __('City') }} <span class="text-danger">*</span></label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $addr->city ?? '') }}" required maxlength="120">
        @error('city')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold text-secondary">{{ __('State') }} <span class="text-danger">*</span></label>
        <input type="text" name="state" class="form-control" value="{{ old('state', $addr->state ?? '') }}" required maxlength="120">
        @error('state')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label small fw-semibold text-secondary">{{ __('Pincode') }} <span class="text-danger">*</span></label>
        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $addr->pincode ?? '') }}" required maxlength="16">
        @error('pincode')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
</div>
