@php
    $items = $items ?? [];
    $title = $title ?? null;
    if ($title === null && ! empty($items)) {
        $last = $items[array_key_last($items)] ?? null;
        $title = is_array($last) ? ($last['label'] ?? '') : '';
    }
    $title = trim((string) ($title ?? ''));
@endphp
@once('market-page-breadcrumb')
@if($title !== '' || ! empty($items))
<section class="pro-page-hero">
    <div class="pro-page-hero__inner">
        @if($title !== '')
            <h1 class="pro-page-hero__title">{{ $title }}</h1>
        @endif
        @if(! empty($items))
            <nav class="pro-page-hero__crumb" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('market.home') }}">{{ __('Home') }}</a>
                @foreach($items as $item)
                    @php
                        $label = $item['label'] ?? '';
                        $url = $item['url'] ?? null;
                    @endphp
                    <span class="pro-page-hero__sep" aria-hidden="true">—</span>
                    @if($loop->last || empty($url))
                        <span class="pro-page-hero__current">{{ $label }}</span>
                    @else
                        <a href="{{ $url }}">{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        @endif
    </div>
</section>
@push('schema')
<script type="application/ld+json">
{!! json_encode(app(\App\Services\SeoService::class)->breadcrumbSchema($items), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush
@endif
@endonce
