@extends('layouts.admin')

@section('title', __('Header menu'))

@section('page_subtitle')
    {{ __('Build your header menu and product departments here. Products are assigned to any menu item in the product form. Child items appear as a simple dropdown under their parent.') }}
@endsection

@section('content')
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="alert alert-warning py-2 small mb-3">
        <strong>{{ __('This store') }}:</strong>
        <code>{{ config('app.url') }}</code>
        · {{ __('Database') }}: <code>{{ config('database.connections.'.config('database.default').'.database') }}</code>
        — {{ __('Changes here only affect this site. Open the storefront using the same URL before checking the header menu.') }}
    </div>

    <div class="alert alert-info py-2 small mb-3">
        <strong>{{ __('Website header') }}:</strong> {{ __('Only top-level items (Parent = “Top level”) appear as main nav tabs. Add children under a tab for a simple dropdown. Keep Active checked and set a slug (auto-filled from title if empty).') }}
    </div>

    <form method="post" action="{{ route('admin.menu-items.store') }}" class="card p-3 mb-4">
        @csrf
        <div class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Title') }}</label>
                <input name="title" class="form-control form-control-sm" required maxlength="255">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">{{ __('Parent') }}</label>
                <select name="parent_id" class="form-select form-select-sm">
                    <option value="">— {{ __('Top level') }} —</option>
                    @foreach($parentChoices as $pc)
                        <option value="{{ $pc->id }}">
                            @if($pc->parent)↳ @endif{{ $pc->title }}@if($pc->parent) ({{ $pc->parent->title }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small mb-0">{{ __('Sort') }}</label>
                <input name="sort_order" type="number" class="form-control form-control-sm" value="0" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('URL') }}</label>
                <input name="url" class="form-control form-control-sm" placeholder="/search?sort=newest">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Route name') }}</label>
                <input name="route_name" class="form-control form-control-sm" placeholder="market.home">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">{{ __('Slug') }}</label>
                <input name="slug" class="form-control form-control-sm" placeholder="honey-ghee">
            </div>
            <div class="col-md-3 d-flex flex-wrap gap-2 align-items-center pt-3">
                <input type="hidden" name="is_active" value="0">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="na" checked>
                    <label class="form-check-label small" for="na">{{ __('Active') }}</label>
                </div>
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="target_blank" value="1" id="ntb">
                    <label class="form-check-label small" for="ntb">{{ __('New tab') }}</label>
                </div>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary btn-sm w-100">{{ __('Add') }}</button>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm admin-data-card mb-4">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <span class="admin-data-card__title d-block">{{ __('Menu structure') }}</span>
                <span class="admin-data-card__meta">{{ __('Header links and sort order.') }}</span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fw-semibold border" style="background: var(--admin-teal-soft); color: var(--admin-teal-hover); border-color: rgba(13, 148, 136, 0.25) !important;">{{ $items->count() }}</span>
        </div>
        <form id="menuReorderForm" method="post" action="{{ route('admin.menu-items.reorder') }}" class="d-none">@csrf</form>
        <div class="card-body p-0">
            <div class="table-responsive">
    <table class="table table-hover align-middle mb-0 admin-table">
        <thead>
            <tr>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Parent') }}</th>
                <th>{{ __('Target') }}</th>
                <th>{{ __('Flags') }}</th>
                <th>{{ __('Sort') }}</th>
                <th class="text-end" style="width:9rem;">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $row)
                <tr class="{{ $row->parent_id ? 'table-light' : '' }}">
                    <td>{{ $row->title }}</td>
                    <td>{{ $row->parent?->title ?? '—' }}</td>
                    <td class="small text-break">
                        @if($row->slug)
                            <code>{{ $row->slug }}</code>
                        @elseif($row->route_name)
                            <code>{{ $row->route_name }}</code>
                        @elseif($row->url)
                            {{ \Illuminate\Support\Str::limit($row->url, 48) }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="small">
                        @if(!$row->is_active)<span class="badge bg-warning text-dark">{{ __('Off') }}</span>@else<span class="text-muted">—</span>@endif
                    </td>
                    <td style="width:5rem;">
                        <input type="number" form="menuReorderForm" name="sort[{{ $row->id }}]" value="{{ $row->sort_order }}" min="0" class="form-control form-control-sm" aria-label="{{ __('Sort order for :title', ['title' => $row->title]) }}">
                    </td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editMenu{{ $row->id }}">{{ __('Edit') }}</button>
                        <form method="post" action="{{ route('admin.menu-items.destroy', $row) }}" class="d-inline" onsubmit="return confirm(@json(__('Delete this item?')));">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
            </div>
            @if($items->isNotEmpty())
                <div class="border-top p-3 d-flex justify-content-end">
                    <button type="submit" form="menuReorderForm" class="btn btn-outline-primary btn-sm">{{ __('Save sort order') }}</button>
                </div>
            @endif
        </div>
    </div>

    @foreach($items as $row)
        <div class="modal fade" id="editMenu{{ $row->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form method="post" action="{{ route('admin.menu-items.update', $row) }}">
                        @csrf @method('PATCH')
                        <div class="modal-header">
                            <h2 class="modal-title h5">{{ __('Edit menu item') }}</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        </div>
                        <div class="modal-body row g-2">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Title') }}</label>
                                <input name="title" class="form-control" value="{{ $row->title }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Parent') }}</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">— {{ __('Top level') }} —</option>
                                    @foreach($parentChoices as $pc)
                                        @if($pc->id !== $row->id)
                                            <option value="{{ $pc->id }}" @if($row->parent_id == $pc->id) selected @endif>
                                                @if($pc->parent)↳ @endif{{ $pc->title }}@if($pc->parent) ({{ $pc->parent->title }})@endif
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('URL') }}</label>
                                <input name="url" class="form-control" value="{{ $row->url }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Route name') }}</label>
                                <input name="route_name" class="form-control" value="{{ $row->route_name }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Slug') }}</label>
                                <input name="slug" class="form-control" value="{{ $row->slug }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">{{ __('Sort') }}</label>
                                <input name="sort_order" type="number" class="form-control" value="{{ $row->sort_order }}" min="0">
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-3">
                                <input type="hidden" name="is_active" value="0">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="e-a-{{ $row->id }}" @if($row->is_active) checked @endif>
                                    <label class="form-check-label" for="e-a-{{ $row->id }}">{{ __('Active') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="target_blank" value="1" id="e-t-{{ $row->id }}" @if($row->target_blank) checked @endif>
                                    <label class="form-check-label" for="e-t-{{ $row->id }}">{{ __('New tab') }}</label>
                                </div>
                            </div>
                            <div class="col-12"><hr class="my-1"><p class="small fw-semibold mb-2">{{ __('SEO (category page)') }}</p></div>
                            <div class="col-md-6">
                                <label class="form-label small">{{ __('Meta title') }}</label>
                                <input name="meta_title" class="form-control form-control-sm" value="{{ $row->meta_title }}" maxlength="255">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">{{ __('Canonical URL') }}</label>
                                <input name="canonical_url" class="form-control form-control-sm" value="{{ $row->canonical_url }}" maxlength="2048">
                            </div>
                            <div class="col-12">
                                <label class="form-label small">{{ __('Meta description') }}</label>
                                <textarea name="meta_description" class="form-control form-control-sm" rows="2" maxlength="500">{{ $row->meta_description }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">{{ __('Meta keywords') }}</label>
                                <input name="meta_keywords" class="form-control form-control-sm" value="{{ $row->meta_keywords }}" maxlength="500">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">{{ __('OG image URL') }}</label>
                                <input name="og_image" class="form-control form-control-sm" value="{{ $row->og_image }}" maxlength="2048">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button class="btn btn-primary">{{ __('Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    @if($items->isEmpty())
        <p class="text-muted">{{ __('No items yet. Run') }} <code>php artisan db:seed --class=HeaderMenuSeeder</code> {{ __('or add rows above.') }}</p>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
