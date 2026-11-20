@extends('layouts.app')

@section('title', 'Edit Warehouse Product')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Breadcrumb / Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inventory.products.index') }}" class="text-decoration-none text-muted small">&larr; Back to Products</a>
                <span class="text-muted small">&bull;</span>
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-0.5 small fw-semibold">Inventory Hub</span>
            </div>
            <h2 class="fw-bold mb-0 mt-1" style="font-size: 1.5rem; letter-spacing: -0.02em;">Edit Product: {{ $product->name }}</h2>
        </div>
        <div class="text-muted small font-monospace">
            SKU: <strong class="text-dark">{{ $product->sku }}</strong> &bull; Current Stock: <strong class="text-dark">{{ $product->quantity_on_hand }} {{ $product->unit }}</strong>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-semibold text-dark mb-0">Product Details & Control</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('inventory.products.update', $product->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="sku" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="barcode" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Barcode (UPC / EAN)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-upc"></i></span>
                                    <input type="text" class="form-control font-monospace @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
                                </div>
                                @error('barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="category_id" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="supplier_id" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Default Supplier</label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id">
                                    <option value="">Unassigned</option>
                                    @foreach ($suppliers as $sup)
                                        <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label for="unit" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Unit of Measurement <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', $product->unit) }}" required>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="cost_price" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Cost Price (₱) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">₱</span>
                                    <input type="number" step="0.01" class="form-control tabular-nums @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" required>
                                </div>
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="selling_price" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Selling Price (₱) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">₱</span>
                                    <input type="number" step="0.01" class="form-control tabular-nums @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" min="0" required>
                                </div>
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="reorder_level" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Reorder Level Threshold <span class="text-danger">*</span></label>
                                <input type="number" class="form-control tabular-nums @error('reorder_level') is-invalid @enderror" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', $product->reorder_level) }}" min="0" required>
                                <div class="form-text small">Triggers low stock alert when quantity reaches this number.</div>
                                @error('reorder_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="status" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Notes / Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $product->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <span class="text-muted small">To adjust current quantity on hand, please use the <a href="{{ route('inventory.movements.index') }}">Stock Movements Ledger</a>.</span>
                            <div class="d-flex gap-2">
                                <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">Update Product</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
