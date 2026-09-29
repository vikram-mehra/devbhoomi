@extends('layouts.account')

@section('account_title', __('Address book'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Address book'),
        'items' => [
            ['label' => __('My account'), 'url' => route('account.dashboard')],
            ['label' => __('Address book')],
        ],
    ])
@endpush

@section('account_content')
    @include('market.account.partials.account-tabs', ['activeTab' => 'addresses'])

    <div class="address-book__toolbar d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <p class="address-book__title h5 fw-bold mb-0">{{ __('Address Book') }}</p>
        <button type="button" class="btn address-book__btn-add" data-bs-toggle="collapse" data-bs-target="#addressBookAdd" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}" aria-controls="addressBookAdd">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>{{ __('Add New') }}
        </button>
    </div>

    @if($addresses->isEmpty() && ! $errors->any())
        <p class="text-muted mb-4">{{ __('You have no saved addresses yet. Add one to speed up checkout.') }}</p>
    @endif

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 address-book__grid">
        @foreach($addresses as $addr)
            @php
                $tagLabel = $addr->label ?: ($addr->is_default ? __('Default') : __('Home'));
            @endphp
            <div class="col">
                <article class="address-book__card h-100 d-flex flex-column">
                    <div class="address-book__card-inner flex-grow-1">
                        <div class="address-book__card-head d-flex justify-content-between align-items-start gap-2 mb-3">
                            <span class="address-book__name">{{ $addr->name }}</span>
                            <span class="address-book__tag">{{ $tagLabel }}</span>
                        </div>
                        <div class="address-book__lines text-muted small">
                            <p class="mb-1">{{ $addr->line1 }}</p>
                            @if($addr->line2)
                                <p class="mb-1">{{ $addr->line2 }}</p>
                            @endif
                            <p class="mb-3">{{ $addr->city }}, {{ $addr->state }} {{ $addr->pincode }}</p>
                            <p class="mb-0">{{ __('Phone') }}: {{ $addr->phone }}</p>
                        </div>
                    </div>
                    <div class="address-book__card-actions d-flex">
                        <a href="{{ route('account.addresses.edit', $addr) }}" class="address-book__action-btn">{{ __('Edit') }}</a>
                        <form action="{{ route('account.addresses.destroy', $addr) }}" method="post" class="address-book__action-form flex-fill" onsubmit="return confirm('{{ __('Remove this address?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="address-book__action-btn">{{ __('Remove') }}</button>
                        </form>
                    </div>
                </article>
            </div>
        @endforeach
    </div>

    <div class="collapse @if($errors->any()) show @endif mt-4" id="addressBookAdd">
        <div class="address-book__form-panel">
            <h2 class="h6 fw-bold mb-3">{{ __('Add new address') }}</h2>
            <form method="post" action="{{ route('account.addresses.store') }}" class="address-book__form">
                @csrf
                @include('market.account.partials.address-fields', ['address' => null])
                <div class="mt-4 d-flex flex-wrap gap-2">
                    <button type="submit" class="btn address-book__btn-add">{{ __('Save address') }}</button>
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#addressBookAdd">{{ __('Cancel') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
