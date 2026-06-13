{{-- Legacy desktop nav — same dynamic tree as header --}}
<nav class="cb-nav-main-inner d-flex flex-wrap gap-2 align-items-center" aria-label="{{ __('Primary') }}">
    @php $navRoots = $layoutHeaderMenu ?? collect(); @endphp
    @forelse($navRoots as $item)
        @php $kids = $item->children->where('is_active', true)->sortBy('sort_order')->values(); @endphp
        @if($kids->isNotEmpty())
        <div class="dropdown">
                <a href="{{ $item->resolvedUrl() }}" class="cb-nav-link dropdown-toggle" data-bs-toggle="dropdown">{{ $item->title }}</a>
                <ul class="dropdown-menu">
                @foreach($kids as $ch)
                        <li><a class="dropdown-item" href="{{ $ch->resolvedUrl() }}">{{ $ch->title }}</a></li>
                @endforeach
            </ul>
        </div>
    @else
            <a href="{{ $item->resolvedUrl() }}" class="cb-nav-link">{{ $item->title }}</a>
    @endif
@empty
        <span class="cb-nav-link text-muted small">{{ __('No menu items') }}</span>
                                @endforelse
</nav>
