@php $footerMenu = $layoutFooterMenu ?? collect(); @endphp
@forelse($footerMenu as $link)
    <a href="{{ $link->resolvedUrl() }}" @if($link->target_blank) target="_blank" rel="noopener noreferrer" @endif>{{ $link->title }}</a>
@empty
    <span class="small text-muted">{{ __('No menu links yet.') }}</span>
@endforelse
