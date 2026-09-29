{{--
  Bootstrap 5 pagination: First / Prev / sliding window / Next / Last.
  Links include class js-adm-prod-ajax for optional AJAX interception on admin products.
--}}
@if ($paginator->hasPages())
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $window = 2;
        $start = max(1, $current - $window);
        $end = min($last, $current + $window);
        if ($end - $start < 4 && $last > 1) {
            $start = max(1, min($start, $last - 4));
            $end = min($last, $start + 4);
        }
    @endphp
    <nav class="admin-pagination-advanced w-100" aria-label="{{ __('Page navigation') }}">
        <ul class="pagination pagination-sm mb-0 justify-content-center flex-wrap gap-1">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link rounded-pill px-2"><span class="d-none d-sm-inline">{{ __('First') }}</span><span class="d-sm-none" aria-hidden="true">««</span></span>
                @else
                    <a class="page-link rounded-pill px-2 js-adm-prod-ajax" href="{{ $paginator->url(1) }}" rel="first"><span class="d-none d-sm-inline">{{ __('First') }}</span><span class="d-sm-none" aria-hidden="true">««</span></a>
                @endif
            </li>
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link" aria-hidden="true">&lsaquo;</span>
                @else
                    <a class="page-link js-adm-prod-ajax" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}">&lsaquo;</a>
                @endif
            </li>
            @for ($page = $start; $page <= $end; $page++)
                <li class="page-item {{ $page === $current ? 'active' : '' }}" @if($page === $current) aria-current="page" @endif>
                    @if ($page === $current)
                        <span class="page-link fw-semibold">{{ $page }}</span>
                    @else
                        <a class="page-link js-adm-prod-ajax" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                    @endif
                </li>
            @endfor
            <li class="page-item {{ ! $paginator->hasMorePages() ? 'disabled' : '' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link js-adm-prod-ajax" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}">&rsaquo;</a>
                @else
                    <span class="page-link" aria-hidden="true">&rsaquo;</span>
                @endif
            </li>
            <li class="page-item {{ $current >= $last ? 'disabled' : '' }}">
                @if ($current >= $last)
                    <span class="page-link rounded-pill px-2"><span class="d-none d-sm-inline">{{ __('Last') }}</span><span class="d-sm-none" aria-hidden="true">»»</span></span>
                @else
                    <a class="page-link rounded-pill px-2 js-adm-prod-ajax" href="{{ $paginator->url($last) }}" rel="last"><span class="d-none d-sm-inline">{{ __('Last') }}</span><span class="d-sm-none" aria-hidden="true">»»</span></a>
                @endif
            </li>
        </ul>
    </nav>
@endif
