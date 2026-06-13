@extends('layouts.admin')

@section('title', __('Home promo cards'))

@section('page_subtitle')
    {{ __('Three image cards below the homepage slider. Sort 0, 1, 2 — only the first three active cards appear on the site.') }}
@endsection

@section('content')
    <p class="mb-3">
        <a href="{{ route('market.home') }}" class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener">{{ __('Preview homepage') }}</a>
    </p>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header fw-semibold bg-white">{{ __('Add promo card') }}</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.showcase.home-promo.store') }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Offer label (eyebrow)') }}</label>
                    <input type="text" name="eyebrow" class="form-control" value="{{ old('eyebrow') }}" placeholder="{{ __('UP TO 70% OFF') }}" maxlength="255">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('Heading') }} *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="{{ __('COOL CASUALS') }}" required maxlength="255">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Image') }} *</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp" required>
                    @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('JPEG, PNG, GIF or WebP — max 5 MB.') }}</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Button text') }}</label>
                    <input type="text" name="button_label" class="form-control" value="{{ old('button_label', __('Shop now')) }}" maxlength="120">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Button URL') }}</label>
                    <input type="text" name="link" class="form-control" value="{{ old('link') }}" placeholder="/search">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">{{ __('Sort') }}</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0" max="10">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">{{ __('Add card') }}</button>
                </div>
            </form>
        </div>
    </div>

    <h2 class="h5 mb-3">{{ __('Promo cards') }} <span class="badge bg-secondary">{{ $cards->count() }}</span></h2>

    @forelse($cards as $card)
        <div class="card mb-4 border shadow-sm">
            <div class="card-body">
                <form method="post" action="{{ route('admin.showcase.home-promo.update', $card) }}" enctype="multipart/form-data" class="row g-3 align-items-start">
                    @csrf
                    @method('PATCH')
                    <div class="col-lg-3 col-md-4">
                        <div class="ratio ratio-4x3 bg-light rounded border overflow-hidden mb-2">
                            <img src="{{ $card->imageUrl() }}" alt="" class="object-fit-cover w-100 h-100">
                        </div>
                        <label class="form-label small">{{ __('Replace image') }}</label>
                        <input type="file" name="image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Offer label') }}</label>
                                <input type="text" name="eyebrow" class="form-control form-control-sm" value="{{ old('eyebrow', $card->eyebrow) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Heading') }}</label>
                                <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $card->title) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Button URL') }}</label>
                                <input type="text" name="link" class="form-control form-control-sm" value="{{ old('link', $card->link) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Button text') }}</label>
                                <input type="text" name="button_label" class="form-control form-control-sm" value="{{ old('button_label', $card->button_label) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-0">{{ __('Sort') }}</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $card->sort_order) }}" min="0">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="promo-act-{{ $card->id }}" @if(old('is_active', $card->is_active)) checked @endif>
                                    <label class="form-check-label" for="promo-act-{{ $card->id }}">{{ __('Visible') }}</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm mt-3">{{ __('Save') }}</button>
                    </div>
                </form>
                <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                    <form method="post" action="{{ route('admin.showcase.home-promo.toggle-active', $card) }}" class="d-inline">@csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">{{ $card->is_active ? __('Hide') : __('Show') }}</button>
                    </form>
                    <form method="post" action="{{ route('admin.showcase.home-promo.destroy', $card) }}" class="d-inline" onsubmit="return confirm(@json(__('Delete this card?')));">@csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">{{ __('No promo cards yet. Add up to three for the row under the slider.') }}</p>
    @endforelse
@endsection
