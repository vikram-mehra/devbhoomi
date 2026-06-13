@extends('layouts.admin')

@section('title', $product ? __('Edit product') : __('Add product'))

@section('page_subtitle')
    {{ $product ? __('Update listing, SEO, and images.') : __('Assign to an approved vendor and menu/service.') }}
@endsection

@section('content')
    @php
        $oldFormToken = old('_token');
        $isActiveChecked = $oldFormToken !== null
            ? (string) old('is_active', '0') === '1'
            : ($product ? (bool) $product->is_active : true);
        $isFeaturedChecked = $oldFormToken !== null
            ? (string) old('is_featured', '0') === '1'
            : ($product ? (bool) $product->is_featured : false);

        $parseIniSize = static function (string $s): int {
            $s = trim($s);
            if ($s === '') {
                return 0;
            }
            if (! preg_match('/^([\d.]+)\s*([kmg]?)$/i', $s, $m)) {
                return (int) $s;
            }
            $n = (float) $m[1];
            $u = strtoupper($m[2] ?? '');
            $mul = match ($u) {
                'G' => 1073741824,
                'M' => 1048576,
                'K' => 1024,
                default => 1,
            };

            return (int) round($n * $mul);
        };
        $postMaxBytes = $parseIniSize(ini_get('post_max_size'));
        $uploadMaxBytes = $parseIniSize(ini_get('upload_max_filesize'));
        $maxFileUploadsIni = max(1, (int) ini_get('max_file_uploads'));
        $effectiveUploadCapBytes = min(
            5242880,
            $uploadMaxBytes > 0 ? $uploadMaxBytes : 5242880,
            $postMaxBytes > 0 ? $postMaxBytes : 5242880
        );
    @endphp
    @if($vendors->isEmpty())
        <div class="admin-alert admin-alert--danger mb-4">
            {{ __('No approved vendors yet. Approve a vendor first, then you can assign products.') }}
            <a href="{{ route('admin.vendors.index') }}" class="alert-link">{{ __('Go to vendors') }}</a>
        </div>
    @endif
    <div class="admin-form-hero card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="admin-form-hero__strip"></div>
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h2 class="h5 fw-bold mb-1">{{ $product ? __('Edit product') : __('New product') }}</h2>
                    <p class="text-muted small mb-0">{{ __('Fields marked * are required.') }}</p>
                </div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i>{{ __('Back to list') }}
                </a>
            </div>

            <form method="post" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="row g-3 g-lg-4" id="adminProductForm" data-upload-cap="{{ $effectiveUploadCapBytes }}" style="max-width: 1100px;" @if($vendors->isEmpty()) aria-disabled="true" @endif>
                @csrf
                @if($product)
                    @method('PATCH')
                @endif

                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Vendor') }} *</label>
                    <select name="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                        <option value="">{{ __('Select vendor') }}</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" @if((string) old('vendor_id', $product->vendor_id ?? '') === (string) $v->id) selected @endif>{{ $v->shop_name }}</option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">{{ __('Menu / Service') }} *</label>
                    <select name="menu_item_id" id="admProductMenuSelect" class="form-select @error('menu_item_id') is-invalid @enderror" required>
                        <option value="" data-color-only="0">{{ __('Select menu or service') }}</option>
                        @foreach($menuOptions as $opt)
                            <option value="{{ $opt['id'] }}" data-color-only="{{ $opt['color_only'] ? '1' : '0' }}" @if((string) old('menu_item_id', $product->menu_item_id ?? '') === (string) $opt['id']) selected @endif>{{ $opt['label'] }}</option>
                        @endforeach
                    </select>
                    <p class="form-text small mb-0">{{ __('Shows on the shop page for this menu. In the header, it appears under that menu dropdown (e.g. Our Products) when there are no sub-menu links — or on the sub-menu page if you picked a child menu.') }}</p>
                    @error('menu_item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-8">
                    <label class="form-label fw-semibold">{{ __('Product name') }} *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required maxlength="255" value="{{ old('name', $product->name ?? '') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Weight (kg)') }}</label>
                    <input type="number" name="weight_kg" id="admProductWeightKg" class="form-control @error('weight_kg') is-invalid @enderror" min="0" step="0.001" inputmode="decimal" value="{{ old('weight_kg', $product->weight_kg ?? '') }}" placeholder="{{ __('e.g. 0.5, 1, 2.25') }}">
                    @error('weight_kg')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Barcode / EAN') }}</label>
                    <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" maxlength="64" value="{{ old('barcode', $product->barcode ?? '') }}" placeholder="{{ __('Optional') }}">
                    @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Brand') }}</label>
                    <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror" maxlength="120" value="{{ old('brand', $product->brand ?? '') }}" placeholder="{{ __('Optional') }}">
                    @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('GST') }} (%)</label>
                    <div class="input-group @error('gst') is-invalid @enderror">
                        <input type="number" name="gst" class="form-control @error('gst') is-invalid @enderror" min="0" max="100" step="0.01" inputmode="decimal" value="{{ old('gst', $product->gst ?? '') }}" placeholder="{{ __('Optional') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('gst')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('HSN') }}</label>
                    <input type="text" name="hsn" class="form-control @error('hsn') is-invalid @enderror" maxlength="32" value="{{ old('hsn', $product->hsn ?? '') }}" placeholder="{{ __('Optional') }}">
                    @error('hsn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('SKU') }}</label>
                    <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" maxlength="128" value="{{ old('sku', $product->sku ?? '') }}" placeholder="{{ __('Optional') }}">
                    @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Short description') }}</label>
                    <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" rows="3" placeholder="{{ __('Short summary shown under the price on the product page') }}">{{ old('short_description', $product->short_description ?? '') }}</textarea>
                    @error('short_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">{{ __('Full description') }}</label>
                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="8" placeholder="{{ __('Fabric, size & fit, care, details — shown in the Description tab') }}">{{ old('description', $product->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('Base price') }} (₹) *</label>
                    <input type="number" step="0.01" name="base_price" class="form-control @error('base_price') is-invalid @enderror" required value="{{ old('base_price', $product->base_price ?? '') }}">
                    @error('base_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('Selling price shown on the storefront.') }}</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">{{ __('MRP / Compare at') }} (₹)</label>
                    <input type="number" step="0.01" name="compare_price" class="form-control @error('compare_price') is-invalid @enderror" value="{{ old('compare_price', $product->compare_price ?? '') }}">
                    @error('compare_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">{{ __('Optional strike-through price on storefront.') }}</div>
                </div>

                <div class="col-12">
                    <div class="alert alert-light border mb-3 shadow-sm">
                        <div class="fw-semibold small mb-2"><i class="bi bi-hdd-network me-1"></i>{{ __('Server upload limits (shared hosting)') }}</div>
                        <ul class="small mb-2 ps-3 text-body-secondary">
                            <li>{{ __('upload_max_filesize: :v', ['v' => ini_get('upload_max_filesize')]) }}</li>
                            <li>{{ __('post_max_size: :v — must be larger than all files + form fields in one save', ['v' => ini_get('post_max_size')]) }}</li>
                            <li>{{ __('max_file_uploads: :n', ['n' => $maxFileUploadsIni]) }}</li>
                        </ul>
                        @if($effectiveUploadCapBytes < 5242880)
                            <div class="small text-warning-emphasis fw-semibold mb-2">
                                {{ __('This server caps each image at about :mb MB. Raise limits in cPanel → MultiPHP INI Editor (or php.ini) if uploads fail silently.', ['mb' => max(0.1, round($effectiveUploadCapBytes / 1048576, 1))]) }}
                            </div>
                        @endif
                        <p class="small text-muted mb-0">{{ __('Images save under storage/app/public — folder must be writable. Run php artisan storage:link so URLs like /storage/products/… work.') }}</p>
                    </div>
                    <label class="form-label fw-semibold">{{ __('Product images') }}</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="{{ $effectiveUploadCapBytes }}">
                    <input type="file" id="admProductImagesInput" name="images[]" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" multiple>
                    @error('images')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @error('images.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div id="admProductImagesWarn" class="alert alert-warning py-2 small d-none mb-1" role="alert"></div>
                    <p class="form-text mb-1 d-none fw-semibold text-primary" id="admProductImagesCount" role="status" aria-live="polite"></p>
                    <div class="form-text">{{ __('JPEG, PNG, GIF, WebP — up to 5 MB each (or lower if your host limits PHP).') }}</div>
                    @if($product && $product->images->isNotEmpty())
                        <div class="mt-3 border rounded-3 p-3 bg-light">
                            <div class="fw-semibold small mb-2">{{ __('Current images') }}</div>
                            <div class="row g-2">
                                @foreach($product->images as $im)
                                    @php $u = \App\Models\Product::publicImageUrl($im->path); @endphp
                                    @if($u)
                                        <div class="col-6 col-sm-4 col-md-3">
                                            <div class="position-relative border rounded overflow-hidden bg-white">
                                                <img src="{{ $u }}" alt="" class="w-100" style="height: 88px; object-fit: cover;">
                                                <div class="p-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="remove_image_ids[]" value="{{ $im->id }}" id="rm-img-{{ $im->id }}">
                                                        <label class="form-check-label small text-danger" for="rm-img-{{ $im->id }}">{{ __('Remove') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12">
                    <div class="card border shadow-sm">
                        <div class="card-header bg-white py-3">
                            <span class="fw-bold">{{ __('SEO') }}</span>
                            <span class="text-muted small d-block">{{ __('Search engine title, description, and keywords for this product page.') }}</span>
                        </div>
                        <div class="card-body row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="adm-meta-title">{{ __('Product SEO Title') }}</label>
                                <input type="text" name="meta_title" id="adm-meta-title" class="form-control @error('meta_title') is-invalid @enderror" maxlength="255" value="{{ old('meta_title', $product?->meta_title ?? '') }}" placeholder="{{ __('Leave empty to use product name') }}">
                                @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="adm-meta-desc">{{ __('Meta Description') }}</label>
                                <textarea name="meta_description" id="adm-meta-desc" class="form-control @error('meta_description') is-invalid @enderror" rows="3" maxlength="500" placeholder="{{ __('Short summary for search results (recommended ~160 characters)') }}">{{ old('meta_description', $product?->meta_description ?? '') }}</textarea>
                                @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold" for="adm-meta-keywords">{{ __('Meta Keywords') }}</label>
                                <input type="text" name="meta_keywords" id="adm-meta-keywords" class="form-control @error('meta_keywords') is-invalid @enderror" maxlength="500" value="{{ old('meta_keywords', $product?->meta_keywords ?? '') }}" placeholder="{{ __('e.g. organic honey, uttarakhand, natural') }}">
                                @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">{{ __('Comma-separated keywords (optional).') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="adm-canonical">{{ __('Canonical URL') }}</label>
                                <input type="url" name="canonical_url" id="adm-canonical" class="form-control" maxlength="2048" value="{{ old('canonical_url', $product?->canonical_url ?? '') }}" placeholder="{{ __('Leave empty for default product URL') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="adm-og-image">{{ __('OG image URL') }}</label>
                                <input type="text" name="og_image" id="adm-og-image" class="form-control" maxlength="2048" value="{{ old('og_image', $product?->og_image ?? '') }}" placeholder="{{ __('Leave empty to use primary product image') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="admin-form-toggles p-3 rounded-3 border">
                        <input type="hidden" name="is_active" value="0">
                        <input type="hidden" name="is_featured" value="0">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="adm-p-active" @if($isActiveChecked) checked @endif>
                            <label class="form-check-label fw-semibold" for="adm-p-active">{{ __('Active on storefront') }}</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="adm-p-feat" @if($isFeaturedChecked) checked @endif>
                            <label class="form-check-label fw-semibold" for="adm-p-feat">{{ __('Featured on home') }}</label>
                        </div>
                    </div>
                </div>

                <div class="col-12 pt-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" @if($vendors->isEmpty()) disabled @endif>
                        <i class="bi bi-check2-circle me-1"></i>{{ $product ? __('Save changes') : __('Create product') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var formEl = document.getElementById('adminProductForm');
    var uploadCap = formEl ? parseInt(formEl.getAttribute('data-upload-cap') || '5242880', 10) : 5242880;
    if (uploadCap < 1024) uploadCap = 5242880;
    var input = document.getElementById('admProductImagesInput');
    var countEl = document.getElementById('admProductImagesCount');
    var warnEl = document.getElementById('admProductImagesWarn');
    if (input && countEl) {
        var msg1 = @json(__('1 image selected — will upload on save.'));
        var msgN = @json(__('images selected — will upload on save.'));
        var msgGalleryTooBig = @json(__('One or more gallery files exceed this server’s PHP size limit. Compress images or increase upload_max_filesize / post_max_size in hosting.'));
        input.addEventListener('change', function () {
            var n = input.files ? input.files.length : 0;
            if (warnEl) { warnEl.classList.add('d-none'); warnEl.textContent = ''; }
            if (n < 1) { countEl.classList.add('d-none'); countEl.textContent = ''; return; }
            var bad = false, total = 0;
            for (var i = 0; i < input.files.length; i++) {
                total += input.files[i].size;
                if (input.files[i].size > uploadCap) bad = true;
            }
            countEl.classList.remove('d-none');
            countEl.textContent = (n === 1 ? msg1 : (n + ' ' + msgN)) + ' (~' + Math.round(total / 1024) + ' KB)';
            if (bad && warnEl) { warnEl.textContent = msgGalleryTooBig; warnEl.classList.remove('d-none'); }
        });
    }
})();
</script>
@endpush
