@extends('layouts.admin')

@section('title', __('Platform settings'))

@section('content')
    <form method="post" action="{{ route('admin.settings.update') }}" class="card p-4" style="max-width:480px">@csrf
        <div class="mb-3">
            <label class="form-label">Default commission %</label>
            <input type="number" step="0.1" name="default_commission_percent" class="form-control" value="{{ $commission }}" required>
        </div>
        <p class="small text-muted mb-0">
            {{ __('Shipping charges are managed on the') }}
            <a href="{{ route('admin.shipping-settings.edit') }}">{{ __('Shipping settings') }}</a>
            {{ __('page.') }}
        </p>
        <button class="btn btn-primary mt-3">Save</button>
    </form>
@endsection
