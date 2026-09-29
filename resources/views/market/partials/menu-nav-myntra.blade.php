{{-- Desktop header: dropdown = sub-menu links OR products assigned to this menu --}}
@php $navRoots = $layoutHeaderMenu ?? collect(); @endphp
@forelse($navRoots as $item)
    @php
        $kids = $item->children->where('is_active', true)->sortBy('sort_order')->values();
        $menuProducts = ($kids->isEmpty() && ! $item->isBuiltInPage()) ? $item->dropdownProducts() : collect();
    @endphp

    @if($kids->isNotEmpty() || $menuProducts->isNotEmpty())
        <div class="dropdown mk-myntra-nav-dropdown">
            <a href="{{ $item->resolvedUrl() }}" class="mk-myntra-nav__link dropdown-toggle" id="hdr-menu-{{ $item->id }}" aria-haspopup="true">
                {{ \Illuminate\Support\Str::upper($item->title) }}
            </a>
            <ul class="dropdown-menu border-0 shadow-sm mk-myntra-nav-dropdown__menu" aria-labelledby="hdr-menu-{{ $item->id }}">
                @foreach($kids as $ch)
                    <li>
                        <a class="dropdown-item" href="{{ $ch->resolvedUrl() }}" @if($ch->target_blank) target="_blank" rel="noopener noreferrer" @endif>{{ $ch->title }}</a>
                    </li>
                @endforeach
                @foreach($menuProducts as $prod)
                    <li>
                        <a class="dropdown-item" href="{{ route('product.show', $prod) }}">{{ $prod->name }}</a>
                    </li>
                @endforeach
                @if($menuProducts->isNotEmpty())
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item fw-semibold" href="{{ $item->resolvedUrl() }}">{{ __('View all') }}</a>
                    </li>
                @endif
            </ul>
        </div>
    @else
        <a href="{{ $item->resolvedUrl() }}" class="mk-myntra-nav__link" @if($item->target_blank) target="_blank" rel="noopener noreferrer" @endif>
            {{ \Illuminate\Support\Str::upper($item->title) }}
        </a>
    @endif
@empty
    <span class="mk-myntra-nav__link mk-myntra-nav__link--muted text-muted small px-2">{{ __('No menu items') }}</span>
@endforelse
