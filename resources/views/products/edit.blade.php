@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="container py-4" style="max-width: 600px;">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <div>
            <h3 class="fw-bold mb-0 text-dark" style="font-size: 1.4rem; letter-spacing: -0.02em;">Edit Menu Item</h3>
            <p class="text-muted small mb-0">Update details for {{ $product->product_name }}</p>
        </div>
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm">
            &larr; Back to List
        </a>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('products.update', $product->product_id) }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="product_name" class="tracking-wide-caps mb-1 d-block">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name', $product->product_name) }}" required autofocus>
                @error('product_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="category_id" class="tracking-wide-caps mb-1 d-block">Category <span class="text-danger">*</span></label>
                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->category_id }}" {{ old('category_id', $product->category_id) == $cat->category_id ? 'selected' : '' }}>
                            {{ $cat->category_name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="price" class="tracking-wide-caps mb-1 d-block">Price (₱) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted">₱</span>
                        <input type="number" class="form-control tabular-nums @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $product->price) }}" step="0.25" min="0" required>
                    </div>
                    @error('price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>
                <div class="col-sm-6">
                    <label for="stock_qty" class="tracking-wide-caps mb-1 d-block">Current Inventory <span class="text-danger">*</span></label>
                    <input type="number" class="form-control tabular-nums @error('stock_qty') is-invalid @enderror" id="stock_qty" name="stock_qty" value="{{ old('stock_qty', $product->stock_qty) }}" min="0" required>
                    @error('stock_qty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" role="switch" id="is_available" name="is_available" value="1" {{ old('is_available', $product->is_available) ? 'checked' : '' }}>
                <label class="form-check-label small fw-semibold text-dark" for="is_available">Available for ordering</label>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
