{{-- Catalog card + table + pagination (used by full page and AJAX JSON fragment). --}}
@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator $products */
    $paginator = $products;
@endphp
<div class="card border-0 shadow-sm admin-data-card admin-prod-table-card overflow-hidden admin-prod-catalog-card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
        <div class="admin-prod-results-info">
            <span class="admin-data-card__title d-block">{{ __('All products') }}</span>
            <span class="admin-data-card__meta text-muted small admin-prod-results-range">
                @if ($paginator->total() === 0)
                    {{ __('No results match your filters.') }}
                @else
                    {{ __('Showing :from to :to of :total products', [
                        'from' => $paginator->firstItem(),
                        'to' => $paginator->lastItem(),
                        'total' => $paginator->total(),
                    ]) }}
                @endif
            </span>
        </div>
        <span class="badge rounded-pill px-3 py-2 fw-semibold border admin-prod-total-badge" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">
            {{ trans_choice(':count product|:count products', $paginator->total(), ['count' => $paginator->total()]) }}
        </span>
    </div>
    <div class="card-body p-0 position-relative">
        <div class="table-responsive admin-prod-table-scroll">
            <table class="table table-hover align-middle mb-0 admin-prod-table admin-table admin-prod-table--sticky">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 56px;">{{ __('Img') }}</th>
                        <th>{{ __('Product') }}</th>
                        <th class="d-none d-xl-table-cell" style="min-width: 100px;">{{ __('SKU') }}</th>
                        <th>{{ __('Menu') }}</th>
                        <th class="d-none d-lg-table-cell">{{ __('Brand') }}</th>
                        <th style="min-width: 88px;">{{ __('Price') }}</th>
                        <th style="min-width: 96px;">{{ __('Stock') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="d-none d-md-table-cell" style="min-width: 104px;">{{ __('Created') }}</th>
                        <th class="text-end pe-4" style="min-width: 168px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $p)
                        @php
                            $thumbUrl = \App\Models\Product::publicImageUrl($p->primaryImage?->path) ?? $p->namedPlaceholderUrl();
                            $price = (float) $p->base_price;
                            $stock = (int) ($p->total_stock ?? 0);
                            $variantList = $p->variants ?? collect();
                            $variantCount = $variantList->count();
                            $activeVariant = $variantList->first();
                            $displayStock = $variantCount === 1 ? $stock : (int) ($activeVariant->stock_qty ?? 0);
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <img src="{{ $thumbUrl }}" alt="" class="admin-prod-thumb" width="44" height="44" loading="lazy">
                            </td>
                            <td>
                                <a href="{{ route('product.show', $p) }}" target="_blank" rel="noopener" class="fw-semibold text-decoration-none d-inline-block" style="color: var(--admin-teal);">{{ \Illuminate\Support\Str::limit($p->name, 42) }}</a>
                                <div class="small text-muted d-xl-none font-monospace">{{ $p->sku }}</div>
                            </td>
                            <td class="d-none d-xl-table-cell small font-monospace">{{ $p->sku }}</td>
                            <td>
                                <span class="badge rounded-pill text-wrap text-start" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); font-weight: 600; max-width: 140px;">{{ $p->menuItem->title ?? '—' }}</span>
                            </td>
                            <td class="d-none d-lg-table-cell small">{{ $p->brand ? \Illuminate\Support\Str::limit($p->brand, 24) : '—' }}</td>
                            <td class="fw-semibold">₹{{ number_format($price, 0) }}</td>
                            <td>
                                @if ($variantCount > 0)
                                    <div
                                        class="admin-stock-ctrl"
                                        data-adjust-url="{{ route('admin.products.stock-adjust', $p) }}"
                                        data-variant-id="{{ $activeVariant->id }}"
                                        data-multi-variant="{{ $variantCount > 1 ? '1' : '0' }}"
                                    >
                                        @if ($variantCount > 1)
                                            <select class="form-select form-select-sm admin-stock-variant mb-1" aria-label="{{ __('Variant') }}">
                                                @foreach ($variantList as $v)
                                                    <option value="{{ $v->id }}" data-stock="{{ (int) $v->stock_qty }}" @selected($loop->first)>{{ $v->label() }} ({{ (int) $v->stock_qty }})</option>
                                                @endforeach
                                            </select>
                                            <div class="small text-muted mb-1">{{ __('Total') }}: <span class="admin-stock-total">{{ $stock }}</span></div>
                                        @endif
                                        <div class="d-inline-flex align-items-center gap-1 admin-stock-stepper">
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary admin-stock-btn px-2"
                                                data-delta="-1"
                                                title="{{ __('Decrease stock') }}"
                                                @if ($displayStock <= 0) disabled @endif
                                            >−</button>
                                            <span class="admin-stock-qty fw-semibold text-center" style="min-width: 2rem;">{{ $displayStock }}</span>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-secondary admin-stock-btn px-2"
                                                data-delta="1"
                                                title="{{ __('Increase stock') }}"
                                            >+</button>
                                        </div>
                                        @if ($variantCount === 1 && $stock > 0 && $stock < 5)
                                            <span class="badge rounded-pill text-bg-warning ms-1 admin-stock-low-badge" title="{{ __('Low stock') }}">!</span>
                                        @else
                                            <span class="badge rounded-pill text-bg-warning ms-1 admin-stock-low-badge d-none" title="{{ __('Low stock') }}">!</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($p->is_active)
                                    <span class="admin-prod-badge admin-prod-badge--active">{{ __('Active') }}</span>
                                @else
                                    <span class="admin-prod-badge admin-prod-badge--inactive">{{ __('Inactive') }}</span>
                                @endif
                                @if (($p->low_stock_variants_count ?? 0) > 0)
                                    <span class="badge rounded-pill text-bg-warning ms-1" title="{{ __('Variant low stock') }}">{{ __('Low') }}</span>
                                @endif
                                @if ($p->is_featured)
                                    <span class="badge rounded-pill ms-1" style="background: linear-gradient(135deg, #e7276d, #ff6b35); color: #fff;">★</span>
                                @endif
                            </td>
                            <td class="d-none d-md-table-cell small text-muted">
                                {{ optional($p->created_at)->format('d M Y') }}
                                <span class="d-none d-lg-inline small">{{ optional($p->created_at)->format('H:i') }}</span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-end admin-prod-actions">
                                    <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-outline-primary rounded-pill" title="{{ __('Edit') }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="{{ route('admin.products.toggle', $p) }}" class="d-inline">@csrf
                                        <input type="hidden" name="is_active" value="{{ $p->is_active ? 0 : 1 }}">
                                        <button type="submit" class="btn btn-sm rounded-pill {{ $p->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $p->is_active ? __('Deactivate') : __('Activate') }}">
                                            <i class="bi {{ $p->is_active ? 'bi-eye-slash' : 'bi-eye' }}"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.products.featured', $p) }}" class="d-inline">@csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill" title="{{ $p->is_featured ? __('Unfeature') : __('Feature') }}">
                                            <i class="bi {{ $p->is_featured ? 'bi-star-fill text-warning' : 'bi-star' }}"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.products.destroy', $p) }}" class="d-inline" onsubmit="return confirm(@json(__('Delete this product permanently?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                {{ __('No products match these filters.') }}
                                <div class="mt-3">
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-primary rounded-pill">{{ __('Add product') }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if ($paginator->total() > 0)
        <div class="card-footer bg-white border-top py-3">
            <div class="d-flex flex-column flex-xl-row align-items-stretch align-items-xl-center justify-content-between gap-3">
                <div class="small text-muted text-center text-xl-start order-2 order-xl-1">
                    {{ __('Page :current of :last', ['current' => $paginator->currentPage(), 'last' => max(1, $paginator->lastPage())]) }}
                </div>
                <div class="order-1 order-xl-2 flex-grow-1 d-flex justify-content-center justify-content-xl-end">
                    @if ($paginator->hasPages())
                        {{ $products->links('admin.components.pagination-advanced') }}
                    @else
                        <span class="small text-muted">{{ __('Single page') }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
