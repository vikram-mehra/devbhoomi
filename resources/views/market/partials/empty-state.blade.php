@php
    $icon = $icon ?? 'bi-search';
    $title = $title ?? __('Nothing here yet');
    $text = $text ?? '';
    $cta = $cta ?? __('Continue shopping');
    $ctaUrl = $ctaUrl ?? route('market.home');
@endphp
<div class="pro-empty-state">
    <div class="pro-empty-state__glow" aria-hidden="true"></div>
    <div class="pro-empty-state__icon" aria-hidden="true">
        <i class="bi {{ $icon }}"></i>
    </div>
    <h2 class="pro-empty-state__title">{{ $title }}</h2>
    @if($text !== '')
        <p class="pro-empty-state__text">{{ $text }}</p>
    @endif
    <a href="{{ $ctaUrl }}" class="pro-empty-state__cta">{{ $cta }}</a>
</div>
