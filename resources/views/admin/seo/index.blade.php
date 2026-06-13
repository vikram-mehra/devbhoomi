@extends('layouts.admin')

@section('title', __('SEO Settings'))

@section('page_subtitle')
    {{ __('Manage site-wide SEO defaults, Open Graph settings, FAQ schema, and preview how pages appear in search results.') }}
@endsection

@section('content')
    @if(session('status'))
        <div class="alert alert-success py-2">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="post" action="{{ route('admin.seo.update') }}" class="card border-0 shadow-sm">
                @csrf
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">{{ __('Global SEO settings') }}</h2>

                    <div class="mb-3">
                        <label class="form-label" for="seo-title-suffix">{{ __('Title suffix') }}</label>
                        <input type="text" name="site_title_suffix" id="seo-title-suffix" class="form-control @error('site_title_suffix') is-invalid @enderror" value="{{ old('site_title_suffix', $settings['site_title_suffix']) }}" maxlength="120" required>
                        @error('site_title_suffix')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seo-default-desc">{{ __('Default meta description') }}</label>
                        <textarea name="default_description" id="seo-default-desc" class="form-control @error('default_description') is-invalid @enderror" rows="3" maxlength="500" required data-seo-preview="description">{{ old('default_description', $settings['default_description']) }}</textarea>
                        <div class="form-text"><span data-seo-count="description">{{ mb_strlen($settings['default_description']) }}</span> / 160 {{ __('chars recommended') }}</div>
                        @error('default_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seo-keywords">{{ __('Default meta keywords') }}</label>
                        <input type="text" name="default_keywords" id="seo-keywords" class="form-control" value="{{ old('default_keywords', $settings['default_keywords']) }}" maxlength="500">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seo-og-image">{{ __('Default OG image URL') }}</label>
                        <input type="text" name="default_og_image" id="seo-og-image" class="form-control" value="{{ old('default_og_image', $settings['default_og_image']) }}" maxlength="2048" placeholder="/images/logo.png">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seo-ga">{{ __('Google Analytics ID') }}</label>
                        <input type="text" name="google_analytics_id" id="seo-ga" class="form-control" value="{{ old('google_analytics_id', $settings['google_analytics_id']) }}" maxlength="32" placeholder="G-XXXXXXXXXX">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seo-twitter">{{ __('Twitter handle') }}</label>
                        <input type="text" name="twitter_handle" id="seo-twitter" class="form-control" value="{{ old('twitter_handle', $settings['twitter_handle']) }}" maxlength="64" placeholder="@devbhoominaturals">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="seo-faq">{{ __('FAQ schema (JSON)') }}</label>
                        <textarea name="faq_schema_json" id="seo-faq" class="form-control font-monospace small" rows="8" maxlength="65000" placeholder='[{"question":"Do you deliver across India?","answer":"Yes, we ship pan-India."}]'>{{ old('faq_schema_json', $settings['faq_schema_json']) }}</textarea>
                        <div class="form-text">{{ __('Array of question/answer objects for FAQ rich results on the homepage.') }}</div>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ __('Save SEO settings') }}</button>
                    <a href="{{ route('admin.seo.report') }}" class="btn btn-outline-secondary ms-2">{{ __('View SEO report') }}</a>
                </div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">{{ __('SEO score') }}</h2>
                    @php
                        $scoreColor = $score['score'] >= 85 ? 'success' : ($score['score'] >= 60 ? 'warning' : 'danger');
                    @endphp
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="display-6 fw-bold text-{{ $scoreColor }} mb-0">{{ $score['score'] }}%</div>
                        <div>
                            <div class="fw-semibold">{{ $score['grade'] }}</div>
                            <div class="small text-muted">{{ __('Based on global defaults preview') }}</div>
                        </div>
                    </div>
                    @if(!empty($score['issues']))
                        <ul class="small text-muted mb-0 ps-3">
                            @foreach($score['issues'] as $issue)
                                <li>{{ $issue }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-bold mb-3">{{ __('Search preview') }}</h2>
                    <div class="seo-preview border rounded p-3 bg-light">
                        <div class="seo-preview__url text-success small mb-1">{{ parse_url(url('/'), PHP_URL_HOST) }}</div>
                        <div class="seo-preview__title text-primary fw-semibold mb-1" data-seo-preview="title">{{ $preview->title }}</div>
                        <div class="seo-preview__desc small text-muted" data-seo-preview="desc">{{ $preview->description }}</div>
                    </div>
                    <div class="mt-3 small text-muted">
                        <strong>{{ __('Open Graph') }}:</strong> {{ __('Title, description & image are injected on every public page.') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var desc = document.getElementById('seo-default-desc');
    var counter = document.querySelector('[data-seo-count="description"]');
    if (desc && counter) {
        desc.addEventListener('input', function () {
            counter.textContent = desc.value.length;
            var preview = document.querySelector('[data-seo-preview="desc"]');
            if (preview) preview.textContent = desc.value.substring(0, 160);
        });
    }
});
</script>
@endpush
