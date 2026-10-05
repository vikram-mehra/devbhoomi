@php
    $url = $url ?? null;
    $label = $label ?? __('Track on Delhivery');
    $class = $class ?? 'btn btn-primary rounded-pill px-3';
@endphp
@if(filled($url))
    <a href="{{ $url }}" class="{{ $class }}" target="_blank" rel="noopener noreferrer">
        <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>{{ $label }}
    </a>
@endif
