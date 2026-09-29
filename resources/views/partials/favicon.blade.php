@php $siteFavicon = \App\Support\SiteFavicon::asset(); @endphp
@if($siteFavicon)
    <link rel="icon" href="{{ $siteFavicon['url'] }}" type="{{ $siteFavicon['type'] }}">
    <link rel="shortcut icon" href="{{ $siteFavicon['url'] }}" type="{{ $siteFavicon['type'] }}">
    <link rel="apple-touch-icon" href="{{ $siteFavicon['url'] }}">
@endif
