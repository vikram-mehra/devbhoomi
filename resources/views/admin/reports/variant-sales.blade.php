@extends('layouts.admin')

@section('title', __('Variant sales'))

@section('page_subtitle')
    {{ __('Paid orders only — quantity and revenue by SKU.') }}
@endsection

@section('content')
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="admin-data-card__title">{{ __('Variant performance') }}</span>
            <span class="badge rounded-pill px-3 py-2 border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover);">{{ $rows->total() }}</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 admin-table">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">{{ __('Product') }}</th>
                        <th>{{ __('SKU') }}</th>
                        <th class="text-end">{{ __('Qty sold') }}</th>
                        <th class="text-end pe-4">{{ __('Revenue') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td class="ps-4">
                                <a href="{{ route('admin.products.edit', $r->product_slug) }}" class="fw-semibold text-decoration-none" style="color: var(--admin-teal);">{{ \Illuminate\Support\Str::limit($r->product_name, 42) }}</a>
                            </td>
                            <td><code class="small">{{ $r->sku }}</code></td>
                            <td class="text-end fw-semibold">{{ number_format($r->qty_sold) }}</td>
                            <td class="text-end pe-4">₹{{ number_format($r->revenue, 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">{{ __('No paid order data yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
            <div class="card-footer border-0 pt-0">{{ $rows->links() }}</div>
        @endif
    </div>
@endsection
