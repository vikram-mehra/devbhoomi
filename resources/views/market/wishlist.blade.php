@extends('layouts.market')

@section('title', 'Wishlist')

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Wishlist'),
        'items' => [['label' => __('Wishlist')]],
    ])
@endpush

@section('content')
    <div class="row g-3">
        @forelse($items as $w)
            <div class="col-md-3 col-6">
                @include('market.partials.product-card', ['product' => $w->product])
                <form action="{{ route('wishlist.destroy', $w) }}" method="post" class="mt-2">@csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger w-100" type="submit">Remove</button>
                </form>
            </div>
        @empty
            <p>Nothing saved yet.</p>
        @endforelse
    </div>
@endsection
