@extends('layouts.admin')

@section('title', __('About page'))

@section('page_subtitle')
    {{ __('Edit hero, story, mission, stats, and gallery images shown on the About us page.') }}
@endsection

@section('content')
    @php
        $highlights = old('highlights', $page->highlights->map(function ($h) {
            return $h->only(['id', 'icon', 'label', 'value', 'sort_order']);
        })->values()->all());
        if (count($highlights) < 4) {
            for ($i = count($highlights); $i < 4; $i++) {
                $highlights[] = ['id' => null, 'icon' => 'bi-star', 'label' => '', 'value' => '', 'sort_order' => $i];
            }
        }
    @endphp

    <div class="admin-form-hero card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="admin-form-hero__strip"></div>
        <div class="card-body p-4 p-lg-5">
            <form method="post" action="{{ route('admin.about-page.update') }}" enctype="multipart/form-data" class="row g-3">
                @csrf

                <div class="col-12"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Hero section') }}</h2></div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Eyebrow') }}</label>
                    <input type="text" name="hero_eyebrow" class="form-control" value="{{ old('hero_eyebrow', $page->hero_eyebrow) }}" maxlength="120">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('Hero title') }} *</label>
                    <input type="text" name="hero_title" class="form-control @error('hero_title') is-invalid @enderror" value="{{ old('hero_title', $page->hero_title) }}" required maxlength="255">
                    @error('hero_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Hero subtitle') }}</label>
                    <textarea name="hero_subtitle" class="form-control" rows="2" maxlength="2000">{{ old('hero_subtitle', $page->hero_subtitle) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Hero image') }}</label>
                    <input type="file" name="hero_image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    @if($page->hero_image)
                        <img src="{{ $page->heroImageUrl() }}" alt="" class="rounded border mt-2" style="max-height:100px">
                    @endif
                </div>

                <div class="col-12 pt-3"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Story') }}</h2></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Story heading') }}</label>
                    <input type="text" name="story_heading" class="form-control" value="{{ old('story_heading', $page->story_heading) }}" maxlength="255">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Story image') }}</label>
                    <input type="file" name="story_image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                    @if($page->story_image)
                        <img src="{{ $page->storyImageUrl() }}" alt="" class="rounded border mt-2" style="max-height:100px">
                    @endif
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Story body') }}</label>
                    <textarea name="story_body" class="form-control" rows="8">{{ old('story_body', $page->story_body) }}</textarea>
                    <div class="form-text">{{ __('Line breaks are shown on the site.') }}</div>
                </div>

                <div class="col-12 pt-3"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Mission & vision') }}</h2></div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Mission title') }}</label>
                    <input type="text" name="mission_title" class="form-control" value="{{ old('mission_title', $page->mission_title) }}" maxlength="255">
                    <textarea name="mission_body" class="form-control mt-2" rows="4">{{ old('mission_body', $page->mission_body) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Vision title') }}</label>
                    <input type="text" name="vision_title" class="form-control" value="{{ old('vision_title', $page->vision_title) }}" maxlength="255">
                    <textarea name="vision_body" class="form-control mt-2" rows="4">{{ old('vision_body', $page->vision_body) }}</textarea>
                </div>

                <div class="col-12 pt-3"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Highlight stats') }}</h2></div>
                @foreach($highlights as $i => $h)
                    <div class="col-12">
                        <div class="row g-2 align-items-end border rounded p-2 bg-light">
                            <input type="hidden" name="highlights[{{ $i }}][id]" value="{{ $h['id'] ?? '' }}">
                            <div class="col-md-2">
                                <label class="form-label small">{{ __('Icon class') }}</label>
                                <input type="text" name="highlights[{{ $i }}][icon]" class="form-control form-control-sm font-monospace" value="{{ $h['icon'] ?? 'bi-star' }}" placeholder="bi-leaf">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">{{ __('Label') }}</label>
                                <input type="text" name="highlights[{{ $i }}][label]" class="form-control form-control-sm" value="{{ $h['label'] ?? '' }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">{{ __('Value') }}</label>
                                <input type="text" name="highlights[{{ $i }}][value]" class="form-control form-control-sm" value="{{ $h['value'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">{{ __('Order') }}</label>
                                <input type="number" name="highlights[{{ $i }}][sort_order]" class="form-control form-control-sm" value="{{ $h['sort_order'] ?? $i }}" min="0">
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="col-12 pt-3"><h2 class="h6 text-uppercase text-muted mb-0">{{ __('Gallery') }}</h2></div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Gallery heading') }}</label>
                    <input type="text" name="gallery_heading" class="form-control" value="{{ old('gallery_heading', $page->gallery_heading) }}" maxlength="255">
                </div>

                @if($page->galleryItems->isNotEmpty())
                    <div class="col-12">
                        <div class="row g-2">
                            @foreach($page->galleryItems as $item)
                                <div class="col-6 col-md-3">
                                    <div class="border rounded p-2 text-center">
                                        <img src="{{ $item->imageUrl() }}" alt="" class="img-fluid rounded mb-2" style="max-height:80px;object-fit:cover;width:100%">
                                        @if($item->caption)<p class="small mb-2">{{ $item->caption }}</p>@endif
                                        <button type="submit" form="del-gallery-{{ $item->id }}" class="btn btn-sm btn-outline-danger w-100">{{ __('Remove') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Add gallery images') }}</label>
                    <input type="file" name="gallery_images[]" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                    <div class="form-text">{{ __('Optional captions (one per new image, same order).') }}</div>
                    <input type="text" name="gallery_captions[0]" class="form-control form-control-sm mt-2" placeholder="{{ __('Caption for image 1') }}">
                    <input type="text" name="gallery_captions[1]" class="form-control form-control-sm mt-1" placeholder="{{ __('Caption for image 2') }}">
                </div>

                @include('admin.partials.seo-fields', ['entity' => $page])

                <div class="col-12">
                    <div class="form-check">
                        <input type="hidden" name="is_published" value="0">
                        <input class="form-check-input" type="checkbox" name="is_published" id="about_pub" value="1" {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="about_pub">{{ __('Published on site') }}</label>
                    </div>
                </div>

                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">{{ __('Save about page') }}</button>
                    <a href="{{ route('pages.about') }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">{{ __('Preview') }}</a>
                </div>
            </form>
        </div>
    </div>

    @foreach($page->galleryItems as $item)
        <form id="del-gallery-{{ $item->id }}" method="post" action="{{ route('admin.about-page.gallery.destroy', $item) }}" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
@endsection
