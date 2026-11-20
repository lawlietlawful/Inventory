@extends('layouts.app')

@section('title', 'Add Warehouse Product')

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
            <h2 class="fw-bold mb-0 mt-1" style="font-size: 1.5rem; letter-spacing: -0.02em;">Add Warehouse Product</h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-semibold text-dark mb-0">Product Specifications & Inventory Control</h6>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('inventory.products.store') }}">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="sku" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control font-monospace @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}" placeholder="e.g. SNK-003, BEV-004" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateSKU()" title="Auto-generate SKU"><i class="bi bi-magic"></i> Auto</button>
                                </div>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="barcode" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Barcode (UPC / EAN)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-upc"></i></span>
                                    <input type="text" class="form-control font-monospace @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode') }}" placeholder="Optional barcode digits">
                                </div>
                                @error('barcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Piattos Cheese 85g, Bottled Iced Tea" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="category_id" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Category <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="" disabled selected>Select Category...</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
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
                                        <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
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
                                <input type="text" class="form-control @error('unit') is-invalid @enderror" id="unit" name="unit" value="{{ old('unit', 'pc') }}" placeholder="e.g. pc, pack, can, bottle, kg" required>
                                @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="cost_price" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Cost Price (₱) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">₱</span>
                                    <input type="number" step="0.01" class="form-control tabular-nums @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', '0.00') }}" min="0" required>
                                </div>
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="selling_price" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Selling Price (₱) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">₱</span>
                                    <input type="number" step="0.01" class="form-control tabular-nums @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', '0.00') }}" min="0" required>
                                </div>
                                @error('selling_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="quantity_on_hand" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Initial Stock on Hand <span class="text-danger">*</span></label>
                                <input type="number" class="form-control tabular-nums @error('quantity_on_hand') is-invalid @enderror" id="quantity_on_hand" name="quantity_on_hand" value="{{ old('quantity_on_hand', '0') }}" min="0" required>
                                <div class="form-text small">Initial stock recorded as an intake movement.</div>
                                @error('quantity_on_hand')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="reorder_level" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Reorder Level Threshold <span class="text-danger">*</span></label>
                                <input type="number" class="form-control tabular-nums @error('reorder_level') is-invalid @enderror" id="reorder_level" name="reorder_level" value="{{ old('reorder_level', '10') }}" min="0" required>
                                <div class="form-text small">Triggers low stock alert when inventory reaches this level.</div>
                                @error('reorder_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="status" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="description" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Notes / Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="1" placeholder="Product details or storage requirements...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function generateSKU() {
    const cat = document.getElementById('category_id');
    const selectedText = cat.options[cat.selectedIndex]?.text || 'PRD';
    let prefix = 'PRD';
    if (selectedText.toLowerCase().includes('bev')) prefix = 'BEV';
    else if (selectedText.toLowerCase().includes('snk') || selectedText.toLowerCase().includes('snack')) prefix = 'SNK';
    else if (selectedText.toLowerCase().includes('meal')) prefix = 'MEL';
    else if (selectedText.toLowerCase().includes('dessert')) prefix = 'DES';

    const rand = Math.floor(100 + Math.random() * 900);
    document.getElementById('sku').value = prefix + '-' + rand;
}
</script>
@endpush
