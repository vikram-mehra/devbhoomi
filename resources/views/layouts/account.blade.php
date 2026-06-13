@extends('layouts.market')

@section('title')
{{ trim($__env->yieldContent('account_title')) ?: __('My account') }}
@endsection

@section('content')
<div class="cb-container account-dash-wrap pb-5">
    <div class="account-dash pt-3">
        <div class="row g-4 align-items-start">
            <aside class="col-lg-3">
                @include('market.account.partials.sidebar')
            </aside>
            <div class="col-lg-9">
                <div class="account-dash__panel shadow-sm">
                    @yield('account_content')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
