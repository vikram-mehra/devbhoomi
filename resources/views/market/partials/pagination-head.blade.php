@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|null $paginator */
    if (empty($paginator) || ! method_exists($paginator, 'hasPages') || ! $paginator->hasPages()) {
        return;
    }
    $links = app(\App\Services\SeoService::class)->paginationHeadLinks($paginator);
@endphp
@if(! empty($links['prev']))
<link rel="prev" href="{{ $links['prev'] }}">
@endif
@if(! empty($links['next']))
<link rel="next" href="{{ $links['next'] }}">
@endif
