@extends('layouts.market')

@section('title', 'Wishlist')

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Wishlist'),
        'items' => [['label' => __('Wishlist')]],
    ])
@endpush

@section('content')
    @if($items->isEmpty())
        @include('market.partials.empty-state', [
            'icon' => 'bi-heart',
            'title' => __('Your wishlist is empty'),
            'text' => __('Save products you love by tapping the heart on a product card. They will show up here so you can shop them later.'),
            'cta' => __('Continue shopping'),
            'ctaUrl' => route('market.home'),
        ])
    @else
        <div class="row g-3">
            @foreach($items as $w)
                <div class="col-md-3 col-6">
                    @include('market.partials.product-card', ['product' => $w->product])
                    <form action="{{ route('wishlist.destroy', $w) }}" method="post" class="mt-2">@csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger w-100" type="submit">{{ __('Remove') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
@endsection
