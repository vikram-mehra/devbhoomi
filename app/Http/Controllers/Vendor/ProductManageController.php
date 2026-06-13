<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Support\MenuItemTree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductManageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:'.(\App\Models\User::ROLE_VENDOR)]);
    }

    protected function vendor()
    {
        $v = auth()->user()->vendor;
        abort_unless($v, 403);

        return $v;
    }

    public function index()
    {
        $vendor = $this->vendor();
        if ($vendor->status !== 'approved') {
            return view('vendor.pending', compact('vendor'))->with('vendorPendingBanner', true);
        }
        $products = Product::with(['menuItem', 'variants'])->where('vendor_id', $vendor->id)->latest()->paginate(20);

        return view('vendor.products.index', compact('products', 'vendor'));
    }

    public function create()
    {
        $vendor = $this->vendor();
        $this->ensureApproved($vendor);
        $menuOptions = MenuItemTree::selectOptions();

        return view('vendor.products.form', ['product' => null, 'menuOptions' => $menuOptions, 'vendor' => $vendor]);
    }

    public function store(Request $request)
    {
        $vendor = $this->vendor();
        $this->ensureApproved($vendor);

        $data = $this->validated($request);
        $slug = Str::slug($data['name']).'-'.Str::lower(Str::random(4));

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'menu_item_id' => $data['menu_item_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'sku' => $data['sku'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'compare_price' => $data['compare_price'],
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => false,
            'meta_title' => $data['name'],
            'meta_description' => Str::limit(strip_tags($data['description']), 160),
        ]);

        if (! empty($data['image_url'])) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => $data['image_url'],
                'sort_order' => 0,
            ]);
        }

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $data['sku'].'-A',
            'size' => $data['size'],
            'color' => $data['color'],
            'stock_qty' => $data['stock_qty'],
        ]);

        $this->bustCache();

        return redirect()->route('vendor.products.index')->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        $vendor = $this->vendor();
        $this->ensureApproved($vendor);
        abort_unless($product->vendor_id === $vendor->id, 403);
        $product->load(['images', 'variants']);
        $menuOptions = MenuItemTree::selectOptions();

        return view('vendor.products.form', compact('product', 'menuOptions', 'vendor'));
    }

    public function update(Request $request, Product $product)
    {
        $vendor = $this->vendor();
        $this->ensureApproved($vendor);
        abort_unless($product->vendor_id === $vendor->id, 403);

        $data = $this->validated($request);
        $product->update([
            'menu_item_id' => $data['menu_item_id'],
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'compare_price' => $data['compare_price'],
            'is_active' => $request->boolean('is_active'),
            'meta_description' => Str::limit(strip_tags($data['description']), 160),
        ]);

        $v = $product->variants()->first();
        if ($v) {
            $v->update([
                'size' => $data['size'],
                'color' => $data['color'],
                'stock_qty' => $data['stock_qty'],
            ]);
        }

        if (! empty($data['image_url']) && $product->images()->count() === 0) {
            ProductImage::create(['product_id' => $product->id, 'path' => $data['image_url'], 'sort_order' => 0]);
        }

        $this->bustCache();

        return redirect()->route('vendor.products.index')->with('status', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $vendor = $this->vendor();
        abort_unless($product->vendor_id === $vendor->id, 403);
        $product->delete();
        $this->bustCache();

        return back()->with('status', 'Product removed.');
    }

    public function importCsv(Request $request)
    {
        $vendor = $this->vendor();
        $this->ensureApproved($vendor);
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:2048']);

        $path = $request->file('file')->getRealPath();
        $fh = fopen($path, 'r');
        fgetcsv($fh);
        $count = 0;
        while (($row = fgetcsv($fh)) !== false) {
            if (count($row) < 4) {
                continue;
            }
            $name = $row[0];
            $menuSlug = $row[1];
            $price = (float) $row[2];
            $stock = (int) ($row[3] ?? 0);
            $menu = MenuItem::where('slug', $menuSlug)->where('is_active', true)->first();
            if (! $menu || $price <= 0) {
                continue;
            }
            $slug = Str::slug($name).'-'.Str::lower(Str::random(3));
            $p = Product::create([
                'vendor_id' => $vendor->id,
                'menu_item_id' => $menu->id,
                'name' => $name,
                'slug' => $slug,
                'sku' => 'CSV-'.Str::upper(Str::random(6)),
                'description' => $name,
                'base_price' => $price,
                'compare_price' => null,
                'is_active' => true,
                'is_featured' => false,
                'meta_title' => $name,
            ]);
            ProductImage::create([
                'product_id' => $p->id,
                'path' => 'https://picsum.photos/seed/'.Str::random(6).'/600/600',
                'sort_order' => 0,
            ]);
            ProductVariant::create([
                'product_id' => $p->id,
                'sku' => $p->sku.'-A',
                'stock_qty' => $stock,
            ]);
            $count++;
        }
        fclose($fh);
        $this->bustCache();

        return back()->with('status', "Imported {$count} products.");
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:64',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'required|integer|min:0',
            'size' => 'nullable|string|max:32',
            'color' => 'nullable|string|max:32',
            'image_url' => 'nullable|string|max:2048',
        ]);
    }

    protected function ensureApproved($vendor): void
    {
        abort_if($vendor->status !== 'approved', 403);
    }

    protected function bustCache(): void
    {
        Cache::forget('home.featured');
        Cache::forget('home.trending');
        Cache::forget('home.newest');
    }
}
