{{-- Storefront pagination: record count + compact page controls. --}}
@if ($paginator->total() > 0)
    @php
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        $from = $paginator->firstItem();
        $to = $paginator->lastItem();
        $total = $paginator->total();
        $window = 2;
        $start = max(1, $current - $window);
        $end = min($last, $current + $window);
        if ($end - $start < 4 && $last > 1) {
            $start = max(1, min($start, $last - 4));
            $end = min($last, $start + 4);
        }
        $meta = ($from === $to)
            ? __('Showing :from of :total records', ['from' => $from, 'total' => $total])
            : __('Showing :from–:to of :total records', ['from' => $from, 'to' => $to, 'total' => $total]);
    @endphp
    <nav class="mk-pager" aria-label="{{ __('Page navigation') }}">
        <p class="mk-pager__meta">{{ $meta }}</p>
        @if ($paginator->hasPages())
            <ul class="mk-pager__pages">
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="mk-pager__btn is-disabled" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                    @else
                        <a class="mk-pager__btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}"><i class="bi bi-chevron-left"></i></a>
                    @endif
                </li>
                @if ($start > 1)
                    <li>
                        <a class="mk-pager__btn" href="{{ $paginator->url(1) }}">1</a>
                    </li>
                    @if ($start > 2)
                        <li><span class="mk-pager__ellipsis" aria-hidden="true">…</span></li>
                    @endif
                @endif
                @for ($page = $start; $page <= $end; $page++)
                    <li>
                        @if ($page === $current)
                            <span class="mk-pager__btn is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="mk-pager__btn" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                        @endif
                    </li>
                @endfor
                @if ($end < $last)
                    @if ($end < $last - 1)
                        <li><span class="mk-pager__ellipsis" aria-hidden="true">…</span></li>
                    @endif
                    <li>
                        <a class="mk-pager__btn" href="{{ $paginator->url($last) }}">{{ $last }}</a>
                    </li>
                @endif
                <li>
                    @if ($paginator->hasMorePages())
                        <a class="mk-pager__btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}"><i class="bi bi-chevron-right"></i></a>
                    @else
                        <span class="mk-pager__btn is-disabled" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                    @endif
                </li>
            </ul>
        @endif
    </nav>
@endif
