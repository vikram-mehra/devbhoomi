@extends('layouts.admin')

@section('title', $post ? __('Edit blog post') : __('New blog post'))

@section('page_subtitle')
    {{ $post ? __('Update title, body, image, and publish settings.') : __('Write a post for the home page blog strip and /blog listing.') }}
@endsection

@section('content')
    @php
        $action = $post ? route('admin.blog-posts.update', $post) : route('admin.blog-posts.store');
        $method = $post ? 'PATCH' : 'POST';
    @endphp
    <div class="admin-form-hero card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="admin-form-hero__strip"></div>
        <div class="card-body p-4 p-lg-5">
            <form method="post" action="{{ $action }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                @if($post)
                    @method('PATCH')
                @endif

                <div class="col-12 col-lg-8">
                    <label class="form-label fw-semibold">{{ __('Title') }} *</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $post->title ?? '') }}" required maxlength="255">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-lg-4">
                    <label class="form-label fw-semibold">{{ __('URL slug') }}</label>
                    <input type="text" name="slug" class="form-control font-monospace small @error('slug') is-invalid @enderror" value="{{ old('slug', $post->slug ?? '') }}" placeholder="{{ __('auto from title') }}" maxlength="255">
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('Leave empty to generate from the title.') }}</div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Excerpt') }}</label>
                    <textarea name="excerpt" class="form-control @error('excerpt') is-invalid @enderror" rows="2" maxlength="500" placeholder="{{ __('Short summary for listings and SEO') }}">{{ old('excerpt', $post->excerpt ?? '') }}</textarea>
                    @error('excerpt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="blogBodyEditor">{{ __('Body') }} *</label>
                    <div class="form-text mb-2">{{ __('Use headings (H2–H4), lists, links, and images for SEO-friendly blog formatting.') }}</div>
                    @include('admin.partials.rich-text-editor', [
                        'editorId' => 'blogBodyEditor',
                        'editorName' => 'body',
                        'editorValue' => $post->body ?? '',
                        'editorHeight' => 520,
                        'editorRequired' => true,
                    ])
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="blogCoverImage">{{ __('Cover image') }}</label>
                    <input type="file" name="image" id="blogCoverImage" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/gif,image/webp">
                    @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('JPEG, PNG, GIF or WebP — max 5 MB. Saved to /public/storage/blog/.') }}</div>
                    <div id="blogCoverPreview" class="mt-2"></div>
                    @if($post && $post->image)
                        <div class="mt-2">
                            <img src="{{ $post->imageUrl() }}" alt="{{ $post->title }}" class="rounded border" style="max-height: 160px; width: auto;" id="blogCurrentCover">
                            <div class="small text-muted mt-1">{{ __('Current cover') }}: {{ basename($post->image) }}</div>
                        </div>
                    @endif
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Publish date') }}</label>
                    <input type="datetime-local" name="published_at" class="form-control @error('published_at') is-invalid @enderror" value="{{ old('published_at', optional(optional($post)->published_at)->format('Y-m-d\TH:i')) }}">
                    @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">{{ __('Sort order') }}</label>
                    <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $post->sort_order ?? 0) }}" min="0">
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input type="hidden" name="is_published" value="0">
                        <input class="form-check-input" type="checkbox" name="is_published" id="bp_pub" value="1" {{ (string) old('is_published', $post && $post->is_published ? '1' : '0') === '1' ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="bp_pub">{{ __('Published (visible on site)') }}</label>
                    </div>
                </div>

                @include('admin.partials.seo-fields', ['entity' => $post])

                <div class="col-12 d-flex flex-wrap gap-2 pt-2">
                    <button type="submit" class="btn btn-primary">{{ $post ? __('Save changes') : __('Create post') }}</button>
                    <a href="{{ route('admin.blog-posts.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('blogCoverImage');
    var preview = document.getElementById('blogCoverPreview');
    if (!input || !preview) return;
    input.addEventListener('change', function () {
        preview.innerHTML = '';
        var file = input.files && input.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            preview.innerHTML = '<div class="alert alert-warning py-2 small mb-0">{{ __("File is larger than 5 MB. Choose a smaller image.") }}</div>';
            return;
        }
        var img = document.createElement('img');
        img.className = 'rounded border';
        img.style.maxHeight = '160px';
        img.alt = file.name;
        img.src = URL.createObjectURL(file);
        preview.appendChild(img);
        var cap = document.createElement('div');
        cap.className = 'small text-muted mt-1';
        cap.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
        preview.appendChild(cap);
    });
});
</script>
@endpush
