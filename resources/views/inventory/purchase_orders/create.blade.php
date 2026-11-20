@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Breadcrumb / Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inventory.purchase-orders.index') }}" class="text-decoration-none text-muted small">&larr; Back to Purchase Orders</a>
                <span class="text-muted small">&bull;</span>
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-0.5 small fw-semibold">Procurement</span>
            </div>
            <h2 class="fw-bold mb-0 mt-1" style="font-size: 1.5rem; letter-spacing: -0.02em;">Create Purchase Order</h2>
        </div>
    </div>

    <form method="POST" action="{{ route('inventory.purchase-orders.store') }}" id="poForm">
        @csrf

        <div class="row g-4">
            <!-- Left Side: Order Meta & Supplier -->
            <div class="col-12 col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 px-3 border-bottom">
                        <h6 class="fw-semibold text-dark mb-0">Order Information</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <label for="supplier_id" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Target Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Select Supplier...</option>
                                @foreach ($suppliers as $sup)
                                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="order_date" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Order Date <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" id="order_date" class="form-control @error('order_date') is-invalid @enderror" value="{{ old('order_date', date('Y-m-d')) }}" required>
                            @error('order_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="expected_date" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Expected Delivery Date</label>
                            <input type="date" name="expected_date" id="expected_date" class="form-control @error('expected_date') is-invalid @enderror" value="{{ old('expected_date', date('Y-m-d', strtotime('+3 days'))) }}">
                            @error('expected_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Special Instructions / Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Delivery notes, terms of payment, or packing instructions...">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Order Summary Card -->
                <div class="card shadow-sm border-0" style="background: #0f172a; color: #fff;">
                    <div class="card-body p-4">
                        <div class="small text-uppercase text-secondary fw-semibold mb-1" style="letter-spacing: 0.05em;">Estimated Total</div>
                        <div class="fs-2 fw-bold font-monospace text-white tabular-nums mb-3" id="grandTotalDisplay">₱0.00</div>
                        <div class="d-flex justify-content-between text-secondary small pt-2 border-top border-secondary border-opacity-25">
                            <span>Line Items:</span>
                            <span id="lineCountDisplay" class="fw-bold text-white">0 items</span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary small pt-1">
                            <span>Total Units:</span>
                            <span id="totalUnitsDisplay" class="fw-bold text-white">0 units</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Line Items Repeater -->
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center border-bottom">
                        <div>
                            <h6 class="fw-semibold text-dark mb-0">Order Line Items</h6>
                            <small class="text-muted">Select warehouse products and enter quantities to procure.</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2.5" onclick="addLineItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item Row
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0" id="itemsTable" style="font-size: 0.88rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 45%;">Warehouse Product</th>
                                        <th style="width: 20%;" class="text-center">Quantity</th>
                                        <th style="width: 20%;" class="text-end">Unit Cost (₱)</th>
                                        <th style="width: 15%;" class="text-end pe-3">Subtotal</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Dynamic rows inserted by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addLineItem()">
                            <i class="bi bi-plus-lg me-1"></i> Add Another Item
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Purchase Order</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const availableProducts = @json($products);
let rowIndex = 0;

function addLineItem(defaultProductId = null, defaultQty = 10, defaultCost = null) {
    const tbody = document.getElementById('itemsTableBody');
    const tr = document.createElement('tr');
    tr.id = 'row-' + rowIndex;

    let productOptions = '<option value="" disabled selected>Choose Product...</option>';
    availableProducts.forEach(p => {
        const selected = (defaultProductId && p.id == defaultProductId) ? 'selected' : '';
        productOptions += `<option value="${p.id}" data-cost="${p.cost_price}" data-unit="${p.unit}" ${selected}>${p.name} (${p.sku}) [${p.unit}]</option>`;
    });

    tr.innerHTML = `
        <td class="ps-3">
            <select name="items[${rowIndex}][product_id]" class="form-select form-select-sm product-select" required onchange="onProductSelect(${rowIndex})">
                ${productOptions}
            </select>
        </td>
        <td class="text-center">
            <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm text-center tabular-nums qty-input" min="1" value="${defaultQty}" required oninput="calculateTotals()">
        </td>
        <td class="text-end">
            <input type="number" step="0.01" name="items[${rowIndex}][unit_cost]" class="form-control form-control-sm text-end tabular-nums cost-input" min="0" value="${defaultCost !== null ? defaultCost : '0.00'}" required oninput="calculateTotals()">
        </td>
        <td class="text-end pe-3 fw-bold tabular-nums subtotal-display">
            ₱0.00
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm p-1 border-0" onclick="removeLineItem(${rowIndex})" title="Remove item">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    if (defaultProductId) {
        onProductSelect(rowIndex);
    }
    rowIndex++;
    calculateTotals();
}

function onProductSelect(index) {
    const row = document.getElementById('row-' + index);
    if (!row) return;
    const select = row.querySelector('.product-select');
    const costInput = row.querySelector('.cost-input');
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.dataset.cost) {
        costInput.value = parseFloat(selectedOption.dataset.cost).toFixed(2);
    }
    calculateTotals();
}

function removeLineItem(index) {
    const row = document.getElementById('row-' + index);
    if (row) {
        row.remove();
        calculateTotals();
    }
}

function calculateTotals() {
    let grandTotal = 0;
    let totalUnits = 0;
    let lineCount = 0;

    const rows = document.querySelectorAll('#itemsTableBody tr');
    rows.forEach(r => {
        const qtyInput = r.querySelector('.qty-input');
        const costInput = r.querySelector('.cost-input');
        const subtotalDisplay = r.querySelector('.subtotal-display');

        if (qtyInput && costInput && subtotalDisplay) {
            const qty = parseInt(qtyInput.value) || 0;
            const cost = parseFloat(costInput.value) || 0;
            const sub = qty * cost;

            subtotalDisplay.textContent = '₱' + sub.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            grandTotal += sub;
            totalUnits += qty;
            lineCount++;
        }
    });

    document.getElementById('grandTotalDisplay').textContent = '₱' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    document.getElementById('lineCountDisplay').textContent = lineCount + ' item' + (lineCount === 1 ? '' : 's');
    document.getElementById('totalUnitsDisplay').textContent = totalUnits + ' unit' + (totalUnits === 1 ? '' : 's');
}

document.addEventListener('DOMContentLoaded', function() {
    // Add 2 initial blank rows
    addLineItem();
    addLineItem();
});
</script>
@endpush
