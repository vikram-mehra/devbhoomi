{{-- Mobile offcanvas: sub-menu links OR products for this menu --}}
@php $navRoots = $layoutHeaderMenu ?? collect(); @endphp
@forelse($navRoots as $item)
    @php
        $kids = $item->children->where('is_active', true)->sortBy('sort_order')->values();
        $menuProducts = ($kids->isEmpty() && ! $item->isBuiltInPage()) ? $item->dropdownProducts() : collect();
        $collapseId = 'mnav-'.$item->id;
    @endphp

    @if($kids->isNotEmpty() || $menuProducts->isNotEmpty())
        <div class="pro-mnav-item">
            <button type="button" class="pro-mnav-toggle" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                <span class="pro-mnav-toggle__text">{{ $item->title }}</span>
                <span class="pro-mnav-toggle__icon" aria-hidden="true"><i class="bi bi-plus-lg"></i></span>
            </button>
            <div id="{{ $collapseId }}" class="collapse pro-mnav-collapse">
                <div class="pro-mnav-collapse__inner">
                    @foreach($kids as $ch)
                        <a class="pro-mnav-sublink" href="{{ $ch->resolvedUrl() }}" @if($ch->target_blank) target="_blank" rel="noopener noreferrer" @endif>{{ $ch->title }}</a>
                    @endforeach
                    @foreach($menuProducts as $prod)
                        <a class="pro-mnav-sublink" href="{{ route('product.show', $prod) }}">{{ $prod->name }}</a>
                    @endforeach
                    @if($menuProducts->isNotEmpty())
                        <a class="pro-mnav-sublink fw-semibold" href="{{ $item->resolvedUrl() }}">{{ __('View all') }}</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <a class="pro-mnav-rowlink" href="{{ $item->resolvedUrl() }}" @if($item->target_blank) target="_blank" rel="noopener noreferrer" @endif>{{ $item->title }}</a>
    @endif
@empty
    <p class="pro-mnav-rowlink text-muted small mb-0 px-3 py-2">{{ __('No menu items yet.') }}</p>
@endforelse
