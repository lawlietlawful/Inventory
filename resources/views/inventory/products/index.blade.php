@extends('layouts.app')

@section('title', 'Warehouse Products')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-1 small fw-semibold">Inventory Hub</span>
                @if ($lowStockTotal > 0)
                    <span class="badge rounded-pill bg-danger-subtle text-danger px-2 py-1 small fw-semibold">{{ $lowStockTotal }} Low Stock Alerts</span>
                @endif
            </div>
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Warehouse Product Catalog</h2>
            <p class="text-muted mb-0 small">Centralized inventory stock master, cost margins, and SKU replenishment thresholds.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.products.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
            <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left-right"></i> Stock Ledger
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('inventory.products.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, SKU, or barcode..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="stock_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Stock Status</option>
                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock (&le; Reorder)</option>
                        <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock (0)</option>
                        <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>Adequate Stock</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Suppliers</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ Str::limit($sup->name, 22) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">Filter</button>
                    @if (request()->hasAny(['search', 'category_id', 'stock_status', 'supplier_id', 'status']))
                        <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="fw-semibold text-dark">
                Products List <span class="badge bg-light text-secondary border ms-1">{{ $products->total() }} items</span>
            </div>
            <div class="small text-muted">
                Showing {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} of {{ $products->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if ($products->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-boxes fs-1 d-block mb-2 text-secondary"></i>
                    No warehouse products match your filter criteria.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">SKU / Barcode</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th class="text-end">Cost Price</th>
                                <th class="text-end">Selling Price</th>
                                <th class="text-center">Stock Level</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $prod)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold font-monospace text-dark">{{ $prod->sku }}</div>
                                        @if ($prod->barcode)
                                            <div class="text-muted small font-monospace" style="font-size: 0.72rem;">
                                                <i class="bi bi-upc"></i> {{ $prod->barcode }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $prod->name }}</div>
                                        @if ($prod->description)
                                            <div class="text-muted small" style="font-size: 0.75rem;">{{ Str::limit($prod->description, 45) }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral">{{ $prod->category->name ?? 'General' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ Str::limit($prod->supplier->name ?? 'None', 22) }}</span>
                                    </td>
                                    <td class="text-end tabular-nums text-muted">
                                        ₱{{ number_format($prod->cost_price, 2) }}
                                    </td>
                                    <td class="text-end tabular-nums fw-semibold text-dark">
                                        ₱{{ number_format($prod->selling_price, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($prod->quantity_on_hand <= 0)
                                            <span class="badge bg-danger text-white px-2 py-1">0 {{ $prod->unit }} (Out)</span>
                                        @elseif ($prod->quantity_on_hand <= $prod->reorder_level)
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1">
                                                {{ $prod->quantity_on_hand }} {{ $prod->unit }} (Low)
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success-emphasis px-2 py-1">
                                                {{ $prod->quantity_on_hand }} {{ $prod->unit }}
                                            </span>
                                        @endif
                                        <div class="text-muted" style="font-size: 0.7rem;">Reorder: {{ $prod->reorder_level }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if ($prod->status === 'active')
                                            <span class="badge bg-success-subtle text-success" style="font-size: 0.72rem;">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Quick Adjust Stock Modal Trigger -->
                                            <button type="button" 
                                                    class="btn btn-outline-secondary py-1 px-2" 
                                                    title="Quick Adjust Stock"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#quickAdjustModal" 
                                                    data-id="{{ $prod->id }}" 
                                                    data-sku="{{ $prod->sku }}" 
                                                    data-name="{{ $prod->name }}" 
                                                    data-stock="{{ $prod->quantity_on_hand }}" 
                                                    data-unit="{{ $prod->unit }}">
                                                <i class="bi bi-sliders"></i>
                                            </button>
                                            <!-- Edit Product -->
                                            <a href="{{ route('inventory.products.edit', $prod->id) }}" class="btn btn-outline-primary py-1 px-2" title="Edit Product">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <!-- Toggle Status -->
                                            <form method="POST" action="{{ route('inventory.products.destroy', $prod->id) }}" class="d-inline" onsubmit="return confirm('Change status for {{ $prod->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-{{ $prod->status === 'active' ? 'danger' : 'success' }} py-1 px-2" title="{{ $prod->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($products->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Quick Adjust Stock Modal -->
<div class="modal fade" id="quickAdjustModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('inventory.movements.store') }}" class="modal-content shadow border-0">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="reference_type" value="manual">

            <div class="modal-header border-bottom py-2.5 px-3">
                <div>
                    <h6 class="modal-title fw-bold text-dark mb-0">Record Stock Movement</h6>
                    <small class="text-muted" id="modalProductTitle"></small>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="p-2 mb-3 rounded bg-light border d-flex justify-content-between align-items-center">
                    <span class="small text-muted">Current Stock on Hand:</span>
                    <strong class="fs-6 text-dark font-monospace" id="modalCurrentStock">0</strong>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Action Type</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="movement_type" id="typeIn" value="in" checked>
                            <label class="btn btn-outline-success w-100 py-1.5 small fw-semibold" for="typeIn">
                                <i class="bi bi-arrow-down-left"></i> Stock In
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="movement_type" id="typeOut" value="out">
                            <label class="btn btn-outline-danger w-100 py-1.5 small fw-semibold" for="typeOut">
                                <i class="bi bi-arrow-up-right"></i> Stock Out
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="movement_type" id="typeAdj" value="adjustment">
                            <label class="btn btn-outline-primary w-100 py-1.5 small fw-semibold" for="typeAdj">
                                <i class="bi bi-sliders"></i> Adjust
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="modalQty" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Quantity</label>
                    <input type="number" name="quantity" id="modalQty" class="form-control form-control-lg fw-bold text-center tabular-nums" min="1" value="10" required>
                    <div class="form-text small" id="qtyHelpText">Enter positive units to add to warehouse stock.</div>
                </div>

                <div class="mb-2">
                    <label for="modalReason" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Reason / Memo</label>
                    <input type="text" name="reason" id="modalReason" class="form-control" placeholder="e.g. Regular delivery, damaged goods, physical count correction" required>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Movement</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('quickAdjustModal');
    if (modal) {
        modal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const id = btn.getAttribute('data-id');
            const sku = btn.getAttribute('data-sku');
            const name = btn.getAttribute('data-name');
            const stock = btn.getAttribute('data-stock');
            const unit = btn.getAttribute('data-unit');

            document.getElementById('modalProductId').value = id;
            document.getElementById('modalProductTitle').textContent = name + ' (' + sku + ')';
            document.getElementById('modalCurrentStock').textContent = stock + ' ' + unit;
            document.getElementById('modalQty').value = 10;
            document.getElementById('modalReason').value = '';
        });
    }

    const typeIn = document.getElementById('typeIn');
    const typeOut = document.getElementById('typeOut');
    const typeAdj = document.getElementById('typeAdj');
    const qtyHelp = document.getElementById('qtyHelpText');

    function updateHelp() {
        if (typeIn.checked) {
            qtyHelp.textContent = 'Enter units to ADD to warehouse stock.';
        } else if (typeOut.checked) {
            qtyHelp.textContent = 'Enter units to DEDUCT from warehouse stock.';
        } else if (typeAdj.checked) {
            qtyHelp.textContent = 'Enter delta units (+ to increase, - to decrease).';
        }
    }

    if (typeIn && typeOut && typeAdj) {
        typeIn.addEventListener('change', updateHelp);
        typeOut.addEventListener('change', updateHelp);
        typeAdj.addEventListener('change', updateHelp);
    }
});
</script>
@endpush
