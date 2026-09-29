@extends('layouts.vendor')

@section('title', 'Products')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">Products</h1>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary">Add product</a>
    </div>
    <form method="post" action="{{ route('vendor.products.import') }}" enctype="multipart/form-data" class="card p-3 mb-3">@csrf
        <label class="form-label">Bulk CSV (name, category_slug, price, stock)</label>
        <input type="file" name="file" class="form-control mb-2" required accept=".csv,.txt">
        <button class="btn btn-sm btn-outline-primary" type="submit">Import</button>
    </form>
    <table class="table table-bordered bg-white">
        <thead><tr><th>Name</th><th>SKU</th><th>Price</th><th></th></tr></thead>
        <tbody>
            @foreach($products as $p)
                <tr>
                    <td>{{ $p->name }}</td>
                    <td>{{ $p->sku }}</td>
                    <td>₹{{ number_format($p->base_price, 2) }}</td>
                    <td>
                        <a href="{{ route('vendor.products.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('vendor.products.destroy', $p) }}" method="post" class="d-inline">@csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $products->links() }}
@endsection
