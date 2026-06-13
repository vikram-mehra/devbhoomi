@extends('layouts.admin')

@section('title', __('Hero slider'))

@section('page_subtitle')
    {{ __('Large carousel slides at the top of the homepage.') }}
@endsection

@section('content')
    <p class="mb-3 small text-muted">
        {{ __('Promo cards below the slider are managed under') }}
        <a href="{{ route('admin.showcase.home-promo.index') }}">{{ __('Showcase → Home promo cards') }}</a>.
    </p>
    <div class="card mb-4">
        <div class="card-header fw-semibold">{{ __('Add banner') }}</div>
        <div class="card-body">
            <form method="post" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data" class="row g-3">@csrf
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Eyebrow') }} <span class="text-muted small">({{ __('small line above title') }})</span></label>
                    <input type="text" name="eyebrow" class="form-control" value="{{ old('eyebrow') }}" placeholder="{{ __('e.g. New season') }}">
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Heading (main title)') }} *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="{{ __('Shown as large headline on slide') }}" required maxlength="255">
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Subtitle') }}</label>
                    <textarea name="subtitle" class="form-control" rows="2" placeholder="{{ __('Paragraph under the heading') }}">{{ old('subtitle') }}</textarea>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Image file') }} *</label>
                    <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp,.jpg,.jpeg,.png,.gif,.webp" required>
                    @error('image')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    <div class="form-text">{{ __('JPEG, PNG, GIF or WebP — max 5 MB. If upload fails, increase PHP upload_max_filesize and post_max_size.') }}</div>
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Primary button link') }}</label>
                    <input type="text" name="link" class="form-control" value="{{ old('link') }}" placeholder="/search or https://…">
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Primary button label') }}</label>
                    <input type="text" name="button_label" class="form-control" value="{{ old('button_label') }}" placeholder="{{ __('Shop now') }}">
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Second button label') }}</label>
                    <input type="text" name="secondary_button_label" class="form-control" value="{{ old('secondary_button_label') }}" placeholder="{{ __('Sell with us') }}">
                </div>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label">{{ __('Second button link') }}</label>
                    <input type="text" name="secondary_link" class="form-control" value="{{ old('secondary_link') }}" placeholder="{{ __('Defaults to vendor signup if empty') }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label">{{ __('Sort order') }}</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">{{ __('Add banner') }}</button>
                </div>
            </form>
        </div>
    </div>

    <h2 class="h5 mb-3">{{ __('Edit banners') }}</h2>
    @forelse($banners as $b)
        <div class="card mb-4 border shadow-sm">
            <div class="card-body">
                <form method="post" action="{{ route('admin.banners.update', $b) }}" enctype="multipart/form-data" class="row g-3 align-items-start">@csrf @method('PATCH')
                    <div class="col-lg-3 col-md-4">
                        <div class="ratio ratio-16x9 bg-light rounded border overflow-hidden mb-2">
                            <img src="{{ $b->imageUrl() }}" alt="" class="object-fit-cover w-100 h-100" style="object-fit: cover;">
                        </div>
                        <label class="form-label small">{{ __('Replace image (optional)') }}</label>
                        <input type="file" name="image" class="form-control form-control-sm" accept="image/jpeg,image/png,image/gif,image/webp">
                        <div class="form-text small">{{ __('Leave empty to keep the current image.') }}</div>
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Eyebrow') }}</label>
                                <input type="text" name="eyebrow" class="form-control form-control-sm" value="{{ old('eyebrow', $b->eyebrow) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Heading') }}</label>
                                <input type="text" name="title" class="form-control form-control-sm" value="{{ old('title', $b->title) }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label small mb-0">{{ __('Subtitle') }}</label>
                                <textarea name="subtitle" class="form-control form-control-sm" rows="2">{{ old('subtitle', $b->subtitle) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Primary link') }}</label>
                                <input type="text" name="link" class="form-control form-control-sm" value="{{ old('link', $b->link) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Primary button') }}</label>
                                <input type="text" name="button_label" class="form-control form-control-sm" value="{{ old('button_label', $b->button_label) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Second button') }}</label>
                                <input type="text" name="secondary_button_label" class="form-control form-control-sm" value="{{ old('secondary_button_label', $b->secondary_button_label) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small mb-0">{{ __('Second link') }}</label>
                                <input type="text" name="secondary_link" class="form-control form-control-sm" value="{{ old('secondary_link', $b->secondary_link) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small mb-0">{{ __('Sort') }}</label>
                                <input type="number" name="sort_order" class="form-control form-control-sm" value="{{ old('sort_order', $b->sort_order) }}" min="0">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="act-{{ $b->id }}" @if(old('is_active', $b->is_active)) checked @endif>
                                    <label class="form-check-label" for="act-{{ $b->id }}">{{ __('Visible on site') }}</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">{{ __('Save changes') }}</button>
                        </div>
                    </div>
                </form>
                <div class="d-flex flex-wrap gap-2 mt-2 pt-2 border-top">
                    <form method="post" action="{{ route('admin.banners.toggle-active', $b) }}" class="d-inline">@csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">{{ $b->is_active ? __('Toggle hide') : __('Toggle show') }}</button>
                    </form>
                    <form method="post" action="{{ route('admin.banners.destroy', $b) }}" class="d-inline" onsubmit="return confirm(@json(__('Delete this banner?')));">@csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">{{ __('Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <p class="text-muted">{{ __('No banners yet. Add one above.') }}</p>
    @endforelse

    <div class="mt-3">{{ $banners->links() }}</div>
@endsection
