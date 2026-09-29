@extends('layouts.vendor')

@section('title', $product ? 'Edit product' : 'New product')

@section('content')
    <h1 class="h4 mb-3">{{ $product ? 'Edit' : 'Create' }} product</h1>
    <form method="post" action="{{ $product ? route('vendor.products.update', $product) : route('vendor.products.store') }}" class="card p-4" style="max-width:640px">
        @csrf
        @if($product) @method('PATCH') @endif
        <div class="mb-2">
            <label class="form-label">{{ __('Menu / Service') }}</label>
            <select name="menu_item_id" class="form-select" required>
                <option value="">{{ __('Select menu or service') }}</option>
                @foreach($menuOptions as $opt)
                    <option value="{{ $opt['id'] }}" @if((string) old('menu_item_id', $product->menu_item_id ?? '') === (string) $opt['id']) selected @endif>{{ $opt['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" required value="{{ old('name', $product->name ?? '') }}"></div>
        <div class="mb-2"><label class="form-label">SKU</label><input name="sku" class="form-control" required value="{{ old('sku', $product->sku ?? '') }}"></div>
        <div class="mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $product->description ?? '') }}</textarea></div>
        <div class="row g-2">
            <div class="col-md-4"><label class="form-label">Base price</label><input type="number" step="0.01" name="base_price" class="form-control" required value="{{ old('base_price', $product->base_price ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Compare at</label><input type="number" step="0.01" name="compare_price" class="form-control" value="{{ old('compare_price', $product->compare_price ?? '') }}"></div>
            <div class="col-md-4"><label class="form-label">Stock</label><input type="number" name="stock_qty" class="form-control" required value="{{ old('stock_qty', $product->variants->first()->stock_qty ?? 0) }}"></div>
        </div>
        <div class="row g-2 mt-1">
            <div class="col-md-6"><label class="form-label">Size</label><input name="size" class="form-control" value="{{ old('size', $product->variants->first()->size ?? '') }}"></div>
            <div class="col-md-6"><label class="form-label">Color</label><input name="color" class="form-control" value="{{ old('color', $product->variants->first()->color ?? '') }}"></div>
        </div>
        <div class="mb-2 mt-2"><label class="form-label">Image URL</label><input name="image_url" class="form-control" value="{{ old('image_url', $product->images->first()->path ?? '') }}" placeholder="https://…"></div>
        @if($product)
            <input type="hidden" name="is_active" value="0">
            <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="a" @if(old('is_active', $product->is_active)) checked @endif><label class="form-check-label" for="a">Active</label></div>
        @endif
        <button class="btn btn-primary mt-3" type="submit">Save</button>
    </form>
@endsection
