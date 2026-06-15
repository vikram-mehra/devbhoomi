@extends('layouts.admin')

@section('title', __('Platform settings'))

@section('content')
    <form method="post" action="{{ route('admin.settings.update') }}" class="row g-3" style="max-width: 800px;">
        @csrf
        
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title mb-0">Platform Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3" style="max-width: 360px;">
                        <label class="form-label">Default commission %</label>
                        <input type="number" step="0.1" name="default_commission_percent" class="form-control" value="{{ $default_commission_percent }}" required>
                    </div>
                    <p class="small text-muted mb-0">
                        {{ __('Shipping charges are managed on the') }}
                        <a href="{{ route('admin.shipping-settings.edit') }}">{{ __('Shipping settings') }}</a>
                        {{ __('page.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-0 pt-3">
                    <h5 class="card-title mb-0">Company / Billing Details (Invoice & Labels)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ $company_name }}" placeholder="e.g. Devbhoomi Naturals">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Logo URL</label>
                            <input type="url" name="site_logo_url" class="form-control" value="{{ $site_logo_url }}" placeholder="e.g. https://domain.com/logo.png">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">GSTIN / Tax ID</label>
                            <input type="text" name="company_gst" class="form-control" value="{{ $company_gst }}" placeholder="e.g. 05ABCDE1234F1Z0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Company Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ $company_phone }}" placeholder="e.g. +91 98765 43210">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Company Email</label>
                            <input type="email" name="company_email" class="form-control" value="{{ $company_email }}" placeholder="e.g. contact@domain.com">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Company Address</label>
                            <textarea name="company_address" class="form-control" rows="3" placeholder="Full street address, city, state, pincode">{{ $company_address }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <button class="btn btn-primary">Save Settings</button>
        </div>
    </form>
@endsection
