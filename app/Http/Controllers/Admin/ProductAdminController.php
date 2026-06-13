<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\ProductStorefrontService;
use App\Support\MenuItemTree;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Services\StockLedgerService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProductAdminController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeAdminProductFilters($request);
        $products = $this->paginateAdminProducts($request);

        $stats = [
            'total' => Product::count(),
            'active' => Product::where('is_active', true)->count(),
            'featured' => Product::where('is_featured', true)->count(),
        ];

        $menuOptions = MenuItemTree::selectOptions();
        $brands = Product::query()
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand');

        return view('admin.products', compact('products', 'stats', 'menuOptions', 'brands'));
    }

    /**
     * JSON fragment for AJAX pagination (admin products catalog).
     */
    public function table(Request $request): JsonResponse
    {
        $request->validate([
            'page' => 'sometimes|integer|min:1|max:100000',
            'per_page' => 'sometimes|integer|in:10,25,50,100',
            'q' => 'nullable|string|max:255',
            'menu_item_id' => 'nullable|integer|exists:menu_items,id',
            'brand' => 'nullable|string|max:120',
            'stock_filter' => 'nullable|in:all,low,out,in_stock',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort' => 'nullable|in:created_at_desc,created_at_asc,name_asc,name_desc,price_asc,price_desc',
        ]);

        $this->normalizeAdminProductFilters($request);
        $products = $this->paginateAdminProducts($request);

        return response()->json([
            'html' => view('admin.products.partials.catalog-panel', ['products' => $products])->render(),
            'meta' => $this->adminProductsPaginationMeta($products),
        ]);
    }

    protected function normalizeAdminProductFilters(Request $request): void
    {
        $allowedPer = [10, 25, 50, 100];
        $perPage = (int) $request->input('per_page', 20);
        if (! in_array($perPage, $allowedPer, true)) {
            $perPage = 20;
        }

        $allowedSort = ['created_at_desc', 'created_at_asc', 'name_asc', 'name_desc', 'price_asc', 'price_desc'];
        $sort = (string) $request->input('sort', 'created_at_desc');
        if (! in_array($sort, $allowedSort, true)) {
            $sort = 'created_at_desc';
        }

        $stock = (string) $request->input('stock_filter', 'all');
        $allowedStock = ['all', 'low', 'out', 'in_stock'];
        if ($request->boolean('low_stock')) {
            $stock = 'low';
        } elseif (! in_array($stock, $allowedStock, true)) {
            $stock = 'all';
        }

        $request->merge([
            'per_page' => $perPage,
            'sort' => $sort,
            'stock_filter' => $stock,
        ]);
    }

    protected function paginateAdminProducts(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->input('per_page', 20);

        return $this->adminProductsQuery($request)
            ->paginate($perPage)
            ->withQueryString();
    }

    protected function adminProductsQuery(Request $request): Builder
    {
        $q = Product::query()
            ->select([
                'products.id',
                'products.vendor_id',
                'products.menu_item_id',
                'products.name',
                'products.slug',
                'products.sku',
                'products.brand',
                'products.base_price',
                'products.is_active',
                'products.is_featured',
                'products.created_at',
            ])
            ->with([
                'vendor:id,shop_name',
                'menuItem:id,title',
                'primaryImage:id,product_id,path,sort_order',
                'variants' => function ($vq) {
                    $vq->select('id', 'product_id', 'stock_qty', 'size', 'color', 'sku')
                        ->orderBy('id');
                },
            ])
            ->withSum('variants as total_stock', 'stock_qty')
            ->withCount([
                'variants as low_stock_variants_count' => function ($vq) {
                    $vq->where('stock_qty', '>', 0)->where('stock_qty', '<', 5);
                },
            ]);

        if ($request->filled('q')) {
            $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $request->get('q')).'%';
            $q->where(function (Builder $qq) use ($term) {
                $qq->where('products.name', 'like', $term)
                    ->orWhere('products.sku', 'like', $term)
                    ->orWhere('products.brand', 'like', $term)
                    ->orWhereHas('variants', function (Builder $vq) use ($term) {
                        $vq->where('sku', 'like', $term);
                    });
            });
        }

        if ($request->filled('menu_item_id')) {
            $q->where('products.menu_item_id', (int) $request->input('menu_item_id'));
        }

        if ($request->filled('brand')) {
            $q->where('products.brand', $request->input('brand'));
        }

        $stock = (string) $request->input('stock_filter', 'all');
        if ($stock === 'low') {
            $q->whereHas('variants', function (Builder $vq) {
                $vq->where('stock_qty', '>', 0)->where('stock_qty', '<', 5);
            });
        } elseif ($stock === 'out') {
            $q->whereRaw('COALESCE((SELECT SUM(pv.stock_qty) FROM product_variants pv WHERE pv.product_id = products.id), 0) = 0');
        } elseif ($stock === 'in_stock') {
            $q->whereHas('variants', function (Builder $vq) {
                $vq->where('stock_qty', '>', 0);
            });
        }

        if ($request->filled('date_from')) {
            $q->whereDate('products.created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $q->whereDate('products.created_at', '<=', $request->input('date_to'));
        }

        $sort = (string) $request->input('sort', 'created_at_desc');
        switch ($sort) {
            case 'created_at_asc':
                $q->oldest('products.created_at');
                break;
            case 'name_asc':
                $q->orderBy('products.name')->orderByDesc('products.id');
                break;
            case 'name_desc':
                $q->orderByDesc('products.name')->orderByDesc('products.id');
                break;
            case 'price_asc':
                $q->orderBy('products.base_price')->orderByDesc('products.id');
                break;
            case 'price_desc':
                $q->orderByDesc('products.base_price')->orderByDesc('products.id');
                break;
            default:
                $q->latest('products.created_at');
                break;
        }

        return $q;
    }

    /**
     * @return array<string, mixed>
     */
    protected function adminProductsPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'has_pages' => $paginator->hasPages(),
        ];
    }

    public function variantSalesReport(Request $request)
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.payment_status', 'paid')
            ->selectRaw('
                product_variants.id as variant_id,
                products.id as product_id,
                products.slug as product_slug,
                products.name as product_name,
                product_variants.sku,
                SUM(order_items.qty) as qty_sold,
                SUM(order_items.line_total) as revenue
            ')
            ->groupBy(
                'product_variants.id',
                'products.id',
                'products.slug',
                'products.name',
                'product_variants.sku'
            )
            ->orderByDesc('qty_sold')
            ->paginate(50)
            ->withQueryString();

        return view('admin.reports.variant-sales', compact('rows'));
    }

    public function create()
    {
        $menuOptions = MenuItemTree::selectOptions();
        $vendors = Vendor::where('status', 'approved')->orderBy('shop_name')->get();
        return view('admin.products.form', [
            'product' => null,
            'menuOptions' => $menuOptions,
            'vendors' => $vendors,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedProduct($request);
        $slug = Str::slug($data['name']).'-'.Str::lower(Str::random(4));

        $product = Product::create([
            'vendor_id' => $data['vendor_id'],
            'menu_item_id' => $data['menu_item_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'weight_kg' => $data['weight_kg'],
            'barcode' => $data['barcode'] ?? null,
            'brand' => $data['brand'] ?? null,
            'gst' => $data['gst'] ?? null,
            'hsn' => $data['hsn'] ?? null,
            'sku' => $data['sku'] ?? null,
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'compare_price' => $data['compare_price'],
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured'),
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'meta_keywords' => $data['meta_keywords'],
            'canonical_url' => $data['canonical_url'],
            'og_image' => $data['og_image'],
        ]);

        try {
            $this->storeUploadedImages($request, $product);
        } catch (ValidationException $e) {
            $product->delete();
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $product->delete();

            return back()
                ->withErrors(['images' => __('Could not save images. Ensure storage/app/public exists and is writable, then run: php artisan storage:link')])
                ->withInput();
        }

        $this->ensureDefaultVariant($product);

        $this->bustCache();

        return redirect()->route('admin.products.index')->with('status', __('Product created.'));
    }

    public function edit(Product $product)
    {
        $product->load(['images']);
        $menuOptions = MenuItemTree::selectOptions();
        $vendors = Vendor::query()
            ->where(function ($q) use ($product) {
                $q->where('status', 'approved')->orWhere('id', $product->vendor_id);
            })
            ->orderBy('shop_name')
            ->get();

        return view('admin.products.form', compact('product', 'menuOptions', 'vendors'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validatedProduct($request);

        $product->update([
            'vendor_id' => $data['vendor_id'],
            'menu_item_id' => $data['menu_item_id'],
            'name' => $data['name'],
            'weight_kg' => $data['weight_kg'],
            'barcode' => $data['barcode'] ?? null,
            'brand' => $data['brand'] ?? null,
            'gst' => $data['gst'] ?? null,
            'hsn' => $data['hsn'] ?? null,
            'sku' => $data['sku'] ?? null,
            'short_description' => $data['short_description'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'compare_price' => $data['compare_price'],
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
            'meta_title' => $data['meta_title'],
            'meta_description' => $data['meta_description'],
            'meta_keywords' => $data['meta_keywords'],
            'canonical_url' => $data['canonical_url'],
            'og_image' => $data['og_image'],
        ]);

        $this->removeProductImages($request, $product);

        try {
            $this->storeUploadedImages($request, $product);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['images' => __('Could not save new images. Check storage permissions and php artisan storage:link')])
                ->withInput();
        }

        $this->ensureDefaultVariant($product);

        $this->bustCache();

        return redirect()->route('admin.products.index')->with('status', __('Product updated.'));
    }

    public function destroy(Product $product)
    {
        $product->load('variants');
        foreach ($product->images as $im) {
            $this->deleteStoredProductImageIfLocal($im->path);
        }
        foreach ($product->variants as $v) {
            $this->deleteStoredProductImageIfLocal($v->image);
        }
        $product->delete();
        $this->bustCache();

        return redirect()->route('admin.products.index')->with('status', __('Product deleted.'));
    }

    public function toggle(Request $request, Product $product)
    {
        $product->update(['is_active' => $request->boolean('is_active')]);
        $this->bustCache();

        return back()->with('status', __('Product status updated.'));
    }

    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => ! $product->is_featured]);
        $this->bustCache();

        return back()->with('status', __('Featured flag updated.'));
    }

    public function adjustStock(Request $request, Product $product, StockLedgerService $ledger): JsonResponse
    {
        $data = $request->validate([
            'delta' => 'required|integer|in:-1,1',
            'variant_id' => 'nullable|integer',
        ]);

        $product->load('variants');

        if ($product->variants->isEmpty()) {
            $this->ensureDefaultVariant($product);
            $product->load('variants');
        }

        $variant = null;
        if (! empty($data['variant_id'])) {
            $variant = $product->variants->firstWhere('id', (int) $data['variant_id']);
        } elseif ($product->variants->count() === 1) {
            $variant = $product->variants->first();
        } else {
            $variant = $product->variants->sortBy('id')->first();
        }

        if (! $variant) {
            return response()->json([
                'ok' => false,
                'message' => __('No variant found for this product.'),
            ], 422);
        }

        try {
            $variantStock = $ledger->adjustVariantStock(
                $variant,
                (int) $data['delta'],
                StockMovement::TYPE_ADJUSTMENT,
                null,
                $request->user()?->id,
                __('Quick stock adjust from admin products list')
            );
        } catch (\RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $ledger->recordInventoryAction(
            'inventory.adjustment',
            $request->user()?->id,
            ProductVariant::class,
            $variant->id,
            __('Quick stock adjust from admin products list'),
            ['sku' => $variant->sku, 'qty_delta' => (int) $data['delta']]
        );

        $this->bustCache();

        $totalStock = (int) $product->variants()->sum('stock_qty');

        return response()->json([
            'ok' => true,
            'stock' => $totalStock,
            'variant_stock' => $variantStock,
            'variant_id' => $variant->id,
            'low_stock' => $totalStock > 0 && $totalStock < 5,
            'out_of_stock' => $totalStock === 0,
        ]);
    }

    /**
     * AJAX: build variant rows from selected sizes × colors (unique SKUs).
     */
    public function generateVariantMatrix(Request $request)
    {
        $colorOnly = $request->boolean('color_only');

        if ($colorOnly) {
            $request->validate([
                'menu_item_id' => 'required|exists:menu_items,id',
            ]);
            $menu = MenuItem::query()->find((int) $request->input('menu_item_id'));
            if (! $menu || ! $menu->isColorOnlyMenu()) {
                throw ValidationException::withMessages([
                    'color_only' => __('Color-only combinations are only available for the Saree category.'),
                ]);
            }
        }

        $rules = [
            'colors' => 'required|array|min:1',
            'colors.*' => 'string|max:64',
            'base_code' => 'required|string|max:120',
        ];
        if (! $colorOnly) {
            $rules['sizes'] = 'required|array|min:1';
            $rules['sizes.*'] = 'string|max:64';
        }

        $data = $request->validate($rules);

        $slugSku = Str::upper(preg_replace('/[^A-Za-z0-9]+/', '-', trim($data['base_code'])));
        $slugSku = trim($slugSku, '-') ?: 'SKU';
        $token = Str::lower(Str::random(5));
        $rows = [];
        $i = 0;
        $sizesList = $colorOnly ? [null] : $data['sizes'];

        foreach ($data['colors'] as $color) {
            foreach ($sizesList as $size) {
                $c = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $color), 0, 4)) ?: 'CLR';
                if ($size === null || $size === '') {
                    $s = 'NA';
                } else {
                    $s = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $size), 0, 4)) ?: 'SZ';
                }
                $rows[] = [
                    'color' => $color,
                    'size' => $size === null || $size === '' ? '' : $size,
                    'sku' => $slugSku.'-'.$c.'-'.$s.'-'.$token.'-'.$i,
                    'stock_qty' => 0,
                    'price' => '',
                    'status' => ProductVariant::STATUS_ACTIVE,
                ];
                $i++;
            }
        }

        return response()->json(['rows' => $rows]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function validatedVariantRows(Request $request, ?Product $product): array
    {
        $data = $request->validate([
            'variants' => 'required|array|min:1',
            'variants.*.id' => 'nullable|integer',
            'variants.*.color' => 'nullable|string|max:64',
            'variants.*.size' => 'nullable|string|max:64',
            'variants.*.sku' => 'required|string|max:64',
            'variants.*.stock_qty' => 'required|integer|min:0',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.status' => 'required|in:active,inactive',
            'variants.*.clear_image' => 'nullable|boolean',
            'variants.*.image' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], [
            'variants.required' => __('Add at least one product variant.'),
            'variants.min' => __('Add at least one product variant.'),
        ]);

        $rows = [];
        foreach ($data['variants'] as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = $row;
        }

        if ($rows === []) {
            throw ValidationException::withMessages(['variants' => __('Add at least one product variant.')]);
        }

        $isSaree = $this->menuIsColorOnlyFromRequest($request);

        $skus = [];
        foreach ($rows as $idx => $row) {
            $skuKey = Str::upper(trim($row['sku']));
            if (isset($skus[$skuKey])) {
                throw ValidationException::withMessages([
                    "variants.$idx.sku" => __('Each variant SKU must be unique in this form.'),
                ]);
            }
            $skus[$skuKey] = true;

            if ($isSaree) {
                if (trim((string) ($row['color'] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        "variants.$idx.color" => __('Color is required for each Saree variant.'),
                    ]);
                }
            } else {
                if (trim((string) ($row['size'] ?? '')) === '') {
                    throw ValidationException::withMessages([
                        "variants.$idx.size" => __('Size is required for each variant in this category.'),
                    ]);
                }
            }

            if (! empty($row['id']) && $product) {
                $ok = ProductVariant::where('product_id', $product->id)
                    ->where('id', (int) $row['id'])
                    ->exists();
                if (! $ok) {
                    throw ValidationException::withMessages([
                        "variants.$idx.id" => __('Invalid variant for this product.'),
                    ]);
                }
            } elseif (! empty($row['id']) && ! $product) {
                throw ValidationException::withMessages([
                    "variants.$idx.id" => __('Invalid variant.'),
                ]);
            }

            $dup = ProductVariant::query()->where('sku', trim($row['sku']));
            if (! empty($row['id'])) {
                $dup->where('id', '!=', (int) $row['id']);
            }
            if ($dup->exists()) {
                throw ValidationException::withMessages([
                    "variants.$idx.sku" => __('This SKU is already used by another variant.'),
                ]);
            }
        }

        $combos = [];
        foreach ($rows as $idx => $row) {
            if ($isSaree) {
                $ck = mb_strtolower(trim((string) ($row['color'] ?? '')));
            } else {
                $ck = mb_strtolower(trim((string) ($row['color'] ?? ''))).'|'.mb_strtolower(trim((string) ($row['size'] ?? '')));
            }
            if (isset($combos[$ck])) {
                $msg = $isSaree
                    ? __('Each color must be unique for Saree variants (:combo).', ['combo' => $ck])
                    : __('Each color + size combination must be unique (:combo).', ['combo' => $ck]);

                throw ValidationException::withMessages([
                    'variants' => $msg,
                ]);
            }
            $combos[$ck] = true;
        }

        return $rows;
    }

    protected function menuIsColorOnlyFromRequest(Request $request): bool
    {
        $id = (int) $request->input('menu_item_id');

        return $id > 0 && (MenuItem::query()->find($id)?->isColorOnlyMenu() ?? false);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    protected function syncVariants(Product $product, array $rows, Request $request): void
    {
        $keepIds = [];
        $isSaree = MenuItem::query()->find((int) $product->menu_item_id)?->isColorOnlyMenu() ?? false;

        foreach ($rows as $i => $row) {
            $priceRaw = $row['price'] ?? null;
            $price = ($priceRaw === '' || $priceRaw === null) ? null : $priceRaw;

            $payload = [
                'color' => ($row['color'] ?? null) !== '' ? ($row['color'] ?? null) : null,
                'size' => $isSaree ? null : (($row['size'] ?? null) !== '' ? ($row['size'] ?? null) : null),
                'sku' => trim($row['sku']),
                'stock_qty' => (int) $row['stock_qty'],
                'price' => $price,
                'status' => $row['status'],
            ];

            $variant = null;
            if (! empty($row['id'])) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('id', (int) $row['id'])
                    ->first();
            }

            if ($variant) {
                $variant->update($payload);
            } else {
                $variant = $product->variants()->create($payload);
            }

            $keepIds[] = $variant->id;

            $file = $request->file('variants.'.$i.'.image');
            $savedNewVariantImage = false;
            if ($file instanceof UploadedFile) {
                if (! $file->isValid()) {
                    if ($file->getError() !== UPLOAD_ERR_NO_FILE) {
                        throw ValidationException::withMessages([
                            "variants.$i.image" => $this->translateUploadError($file->getError()),
                        ]);
                    }
                } else {
                    $this->deleteStoredProductImageIfLocal($variant->image);
                    $stored = $file->store('product-variants', 'public');
                    if (! $stored) {
                        throw ValidationException::withMessages([
                            "variants.$i.image" => __('Could not save variant image. Check that :dir is writable.', ['dir' => storage_path('app/public')]),
                        ]);
                    }
                    $variant->update(['image' => $stored]);
                    $savedNewVariantImage = true;
                }
            }
            if (! $savedNewVariantImage && ! empty($row['clear_image'])) {
                $this->deleteStoredProductImageIfLocal($variant->image);
                $variant->update(['image' => null]);
            }
        }

        $toRemove = $product->variants()->whereNotIn('id', $keepIds)->get();
        foreach ($toRemove as $v) {
            $this->deleteStoredProductImageIfLocal($v->image);
            $v->delete();
        }
    }

    protected function storeUploadedImages(Request $request, Product $product): void
    {
        $raw = $request->file('images');
        if ($raw === null) {
            return;
        }
        $files = is_array($raw) ? $raw : [$raw];
        $maxOrder = (int) $product->images()->max('sort_order');
        foreach ($files as $idx => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }
            if (! $file->isValid()) {
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                throw ValidationException::withMessages([
                    'images.'.$idx => $this->translateUploadError($file->getError()),
                ]);
            }
            $stored = $file->store('products', 'public');
            if (! $stored) {
                throw ValidationException::withMessages([
                    'images' => __('Could not write gallery images. Make sure :dir exists and the web server can write to it (chmod 775 or 755). Link public/storage via php artisan storage:link.', ['dir' => storage_path('app/public')]),
                ]);
            }
            $maxOrder++;
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $stored,
                'sort_order' => $maxOrder,
            ]);
        }
    }

    protected function translateUploadError(int $code): string
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                return __('This file is larger than the server allows (upload_max_filesize). Ask your host to raise it or use a smaller image.');
            case UPLOAD_ERR_FORM_SIZE:
                return __('This file is larger than the form allows. Use files up to 5 MB each.');
            case UPLOAD_ERR_PARTIAL:
                return __('The file was only partially uploaded. Try again or use a smaller file.');
            case UPLOAD_ERR_NO_FILE:
                return __('No file was uploaded.');
            case UPLOAD_ERR_NO_TMP_DIR:
                return __('The server is missing a temporary upload folder. Contact your host.');
            case UPLOAD_ERR_CANT_WRITE:
                return __('Could not write the file to disk. Check storage folder permissions on the server.');
            case UPLOAD_ERR_EXTENSION:
                return __('A PHP extension stopped this upload. Contact your host.');
            default:
                return __('Upload failed (error :code).', ['code' => $code]);
        }
    }

    protected function removeProductImages(Request $request, Product $product): void
    {
        $ids = array_filter((array) $request->input('remove_image_ids', []));
        foreach ($ids as $rid) {
            $img = ProductImage::query()
                ->where('id', (int) $rid)
                ->where('product_id', $product->id)
                ->first();
            if ($img) {
                $this->deleteStoredProductImageIfLocal($img->path);
                $img->delete();
            }
        }
    }

    protected function validatedProduct(Request $request): array
    {
        $data = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'menu_item_id' => 'required|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'weight_kg' => 'nullable|numeric|min:0|max:99999.999',
            'barcode' => 'nullable|string|max:64',
            'brand' => 'nullable|string|max:120',
            'gst' => 'nullable|numeric|min:0|max:100',
            'hsn' => 'nullable|string|max:32',
            'sku' => 'nullable|string|max:128',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:2048',
            'og_image' => 'nullable|string|max:2048',
            'images' => 'nullable|array|max:30',
            'images.*' => ['file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'remove_image_ids' => 'nullable|array',
            'remove_image_ids.*' => 'integer|exists:product_images,id',
        ], [
            'images.max' => __('You can upload at most 30 images at once.'),
            'images.*.mimes' => __('Use JPEG, PNG, GIF, or WebP for all images.'),
            'images.*.max' => __('Each image must be 5 MB or smaller.'),
        ]);

        if (! isset($data['weight_kg']) || $data['weight_kg'] === '' || $data['weight_kg'] === null) {
            $data['weight_kg'] = null;
        } else {
            $data['weight_kg'] = round((float) $data['weight_kg'], 3);
        }

        if (! isset($data['gst']) || $data['gst'] === '' || $data['gst'] === null) {
            $data['gst'] = null;
        } else {
            $data['gst'] = round((float) $data['gst'], 2);
        }

        $data['meta_title'] = trim((string) ($data['meta_title'] ?? '')) ?: $data['name'];
        $data['meta_description'] = trim(strip_tags((string) ($data['meta_description'] ?? ''))) ?: null;
        $data['meta_keywords'] = trim((string) ($data['meta_keywords'] ?? '')) ?: null;
        $data['canonical_url'] = trim((string) ($data['canonical_url'] ?? '')) ?: null;
        $data['og_image'] = trim((string) ($data['og_image'] ?? '')) ?: null;
        $data['hsn'] = trim((string) ($data['hsn'] ?? '')) ?: null;
        $data['sku'] = trim((string) ($data['sku'] ?? '')) ?: null;

        return $data;
    }

    protected function ensureDefaultVariant(Product $product): void
    {
        $variant = $product->variants()->first();
        $sku = 'PRD-'.$product->id.'-'.Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $product->slug), 0, 12));

        if ($variant) {
            $variant->update([
                'sku' => $variant->sku ?: $sku,
                'status' => ProductVariant::STATUS_ACTIVE,
            ]);

            return;
        }

        $product->variants()->create([
            'sku' => $sku,
            'stock_qty' => 100,
            'price' => null,
            'color' => null,
            'size' => null,
            'status' => ProductVariant::STATUS_ACTIVE,
        ]);
    }

    protected function deleteStoredProductImageIfLocal(?string $path): void
    {
        if ($path === null || trim($path) === '') {
            return;
        }
        $path = trim($path);
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return;
        }
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function bustCache(): void
    {
        app(ProductStorefrontService::class)->flushHomeCaches();
    }
}
