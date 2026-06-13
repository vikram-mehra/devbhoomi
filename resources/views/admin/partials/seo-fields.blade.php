{{-- Reusable SEO fields for admin forms --}}
@php
    $prefix = $prefix ?? '';
    $entity = $entity ?? null;
    $titleVal = old($prefix.'meta_title', $entity->meta_title ?? '');
    $descVal = old($prefix.'meta_description', $entity->meta_description ?? '');
    $keywordsVal = old($prefix.'meta_keywords', $entity->meta_keywords ?? '');
    $canonicalVal = old($prefix.'canonical_url', $entity->canonical_url ?? '');
    $ogVal = old($prefix.'og_image', $entity->og_image ?? '');
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header fw-semibold">{{ __('SEO') }}</div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label small">{{ __('Meta title') }}</label>
                <input type="text" name="{{ $prefix }}meta_title" class="form-control form-control-sm js-seo-meta-title" maxlength="255" value="{{ $titleVal }}" placeholder="{{ __('50–60 characters recommended') }}">
                <div class="form-text"><span class="js-seo-title-count">{{ mb_strlen($titleVal) }}</span> / 60</div>
            </div>
            <div class="col-md-6">
                <label class="form-label small">{{ __('Canonical URL') }}</label>
                <input type="url" name="{{ $prefix }}canonical_url" class="form-control form-control-sm" maxlength="2048" value="{{ $canonicalVal }}" placeholder="{{ __('Leave empty for default') }}">
            </div>
            <div class="col-12">
                <label class="form-label small">{{ __('Meta description') }}</label>
                <textarea name="{{ $prefix }}meta_description" class="form-control form-control-sm js-seo-meta-desc" rows="2" maxlength="500" placeholder="{{ __('150–160 characters recommended') }}">{{ $descVal }}</textarea>
                <div class="form-text"><span class="js-seo-desc-count">{{ mb_strlen($descVal) }}</span> / 160</div>
            </div>
            <div class="col-md-6">
                <label class="form-label small">{{ __('Meta keywords') }}</label>
                <input type="text" name="{{ $prefix }}meta_keywords" class="form-control form-control-sm" maxlength="500" value="{{ $keywordsVal }}">
            </div>
            <div class="col-md-6">
                <label class="form-label small">{{ __('OG image URL') }}</label>
                <input type="text" name="{{ $prefix }}og_image" class="form-control form-control-sm" maxlength="2048" value="{{ $ogVal }}" placeholder="/storage/... or https://...">
            </div>
        </div>
        <div class="mt-3 p-3 bg-light rounded small seo-preview">
            <div class="text-success mb-1">{{ parse_url(config('app.url'), PHP_URL_HOST) }}</div>
            <div class="text-primary fw-semibold js-seo-preview-title">{{ $titleVal ?: __('Page title preview') }}</div>
            <div class="text-muted js-seo-preview-desc">{{ Str::limit($descVal, 160) ?: __('Meta description preview') }}</div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-seo-meta-title').forEach(function (el) {
        var card = el.closest('.card-body');
        var count = card && card.querySelector('.js-seo-title-count');
        var preview = card && card.querySelector('.js-seo-preview-title');
        function sync() {
            if (count) count.textContent = el.value.length;
            if (preview) preview.textContent = el.value || {{ json_encode(__('Page title preview')) }};
        }
        el.addEventListener('input', sync);
    });
    document.querySelectorAll('.js-seo-meta-desc').forEach(function (el) {
        var card = el.closest('.card-body');
        var count = card && card.querySelector('.js-seo-desc-count');
        var preview = card && card.querySelector('.js-seo-preview-desc');
        function sync() {
            if (count) count.textContent = el.value.length;
            if (preview) preview.textContent = el.value.substring(0, 160) || {{ json_encode(__('Meta description preview')) }};
        }
        el.addEventListener('input', sync);
    });
});
</script>
@endpush
@endonce
