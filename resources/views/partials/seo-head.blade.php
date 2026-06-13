@php
    /** @var \App\Support\SeoMeta $seo */
    $seo = $seo ?? app(\App\Services\SeoService::class)->build([
        'title' => trim(strip_tags((string) view()->yieldContent('title'))),
        'description' => trim(strip_tags((string) view()->yieldContent('meta_description'))),
        'keywords' => trim(strip_tags((string) view()->yieldContent('meta_keywords'))),
        'canonical' => trim(strip_tags((string) view()->yieldContent('canonical'))),
        'og_image' => trim(strip_tags((string) view()->yieldContent('og_image'))),
        'robots' => trim(strip_tags((string) view()->yieldContent('robots'))),
        'og_type' => trim(strip_tags((string) view()->yieldContent('og_type'))) ?: 'website',
    ]);
    $twitterHandle = app(\App\Services\SeoService::class)->global('twitter_handle');
@endphp
<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
@if(filled($seo->keywords))
<meta name="keywords" content="{{ $seo->keywords }}">
@endif
<link rel="canonical" href="{{ $seo->canonical }}">
@stack('pagination_head')
@if(filled($seo->robots))
<meta name="robots" content="{{ $seo->robots }}">
@endif
<meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">
<meta property="og:type" content="{{ $seo->ogType }}">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
<meta property="og:site_name" content="{{ config('seo.organization.name', config('app.name')) }}">
@if(filled($seo->ogImage))
<meta property="og:image" content="{{ $seo->ogImage }}">
<meta property="og:image:alt" content="{{ $seo->title }}">
@endif
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
@if(filled($seo->ogImage))
<meta name="twitter:image" content="{{ $seo->ogImage }}">
@endif
@if(filled($twitterHandle))
<meta name="twitter:site" content="@{{ ltrim($twitterHandle, '@') }}">
@endif
@if(! empty($seo->schemaExtra))
<script type="application/ld+json">
{!! is_string($seo->schemaExtra) ? $seo->schemaExtra : json_encode($seo->schemaExtra, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
