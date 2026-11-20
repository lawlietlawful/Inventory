@extends('layouts.app')

@section('title', 'Stock Movements')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-1 small fw-semibold">Audit Ledger</span>
            </div>
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Stock Movements Ledger</h2>
            <p class="text-muted mb-0 small">Immutable audit trail of all warehouse stock intakes, point-of-sale dispatches, and physical adjustments.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#newMovementModal">
                <i class="bi bi-plus-circle"></i> Record Stock Movement
            </button>
            <a href="{{ route('inventory.products.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-boxes"></i> Warehouse Catalog
            </a>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Total Inflow (Received)</span>
                    <span class="text-success small"><i class="bi bi-arrow-down-left"></i></span>
                </div>
                <div class="metric-value text-success tabular-nums">+{{ number_format($totalIn) }}</div>
                <div class="metric-sub">Units added across all records</div>
            </div>
        </div>

        <div class="col-12 col-sm-4">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Total Outflow (Dispatched)</span>
                    <span class="text-danger small"><i class="bi bi-arrow-up-right"></i></span>
                </div>
                <div class="metric-value text-danger tabular-nums">-{{ number_format($totalOut) }}</div>
                <div class="metric-sub">Units dispensed or deducted</div>
            </div>
        </div>

        <div class="col-12 col-sm-4">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Total Audit Events</span>
                    <span class="text-muted small"><i class="bi bi-list-check"></i></span>
                </div>
                <div class="metric-value text-dark tabular-nums">{{ number_format($totalMovementsCount) }}</div>
                <div class="metric-sub">Logged movements in database</div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('inventory.movements.index') }}" class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <select name="movement_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Movement Types</option>
                        <option value="in" {{ request('movement_type') === 'in' ? 'selected' : '' }}>Stock In (+)</option>
                        <option value="out" {{ request('movement_type') === 'out' ? 'selected' : '' }}>Stock Out (-)</option>
                        <option value="adjustment" {{ request('movement_type') === 'adjustment' ? 'selected' : '' }}>Adjustment (+/-)</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="product_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Products</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" placeholder="Start Date">
                </div>
                <div class="col-6 col-md-2">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" placeholder="End Date">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">Filter</button>
                    @if (request()->hasAny(['movement_type', 'product_id', 'start_date', 'end_date']))
                        <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Ledger Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="fw-semibold text-dark">
                Movement Audit Trail <span class="badge bg-light text-secondary border ms-1">{{ $movements->total() }} events</span>
            </div>
            <div class="small text-muted">
                Showing {{ $movements->firstItem() ?? 0 }} - {{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if ($movements->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-arrow-left-right fs-1 d-block mb-2 text-secondary"></i>
                    No stock movement events recorded matching the current filter.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Log #</th>
                                <th>Movement Type</th>
                                <th>Product Details</th>
                                <th class="text-center">Quantity Delta</th>
                                <th>Reference / Reason</th>
                                <th>Recorded By</th>
                                <th class="text-end pe-3">Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $mv)
                                <tr>
                                    <td class="ps-3 font-monospace text-muted small">
                                        #{{ $mv->id }}
                                    </td>
                                    <td>
                                        @if ($mv->movement_type === 'in')
                                            <span class="badge bg-success-subtle text-success px-2 py-1 d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-arrow-down-left"></i> Stock In
                                            </span>
                                        @elseif ($mv->movement_type === 'out')
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-arrow-up-right"></i> Stock Out
                                            </span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1 d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-sliders"></i> Adjustment
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $mv->product->name ?? 'Deleted/Archived SKU' }}</div>
                                        <div class="text-muted small font-monospace" style="font-size: 0.72rem;">
                                            SKU: {{ $mv->product->sku ?? 'N/A' }} &bull; {{ $mv->product->category->name ?? 'General' }}
                                        </div>
                                    </td>
                                    <td class="text-center font-monospace fw-bold fs-6 tabular-nums {{ $mv->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }} <span class="small fw-normal text-muted">{{ $mv->product->unit ?? 'units' }}</span>
                                    </td>
                                    <td>
                                        <div class="text-dark small">{{ $mv->reason }}</div>
                                        @if ($mv->reference_type)
                                            <div class="text-muted small" style="font-size: 0.72rem;">
                                                Ref: <span class="badge bg-light text-secondary border font-monospace">{{ $mv->reference_type }} {{ $mv->reference_id ? '#' . $mv->reference_id : '' }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border small">Staff #{{ $mv->performed_by }}</span>
                                    </td>
                                    <td class="text-end pe-3 font-monospace text-muted small">
                                        {{ $mv->created_at ? $mv->created_at->format('M d, Y h:i A') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($movements->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Record Stock Movement Modal -->
<div class="modal fade" id="newMovementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('inventory.movements.store') }}" class="modal-content shadow border-0">
            @csrf
            <input type="hidden" name="reference_type" value="manual">

            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Record Manual Stock Movement</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label for="prodSelect" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Target Product <span class="text-danger">*</span></label>
                    <select name="product_id" id="prodSelect" class="form-select" required>
                        <option value="" disabled selected>Select Warehouse Product...</option>
                        @foreach ($products as $p)
                            <option value="{{ $p->id }}" data-stock="{{ $p->quantity_on_hand }}" data-unit="{{ $p->unit }}">
                                {{ $p->name }} ({{ $p->sku }}) &bull; Stock: {{ $p->quantity_on_hand }} {{ $p->unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Movement Action <span class="text-danger">*</span></label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="movement_type" id="newTypeIn" value="in" checked>
                            <label class="btn btn-outline-success w-100 py-1.5 small fw-semibold" for="newTypeIn">
                                <i class="bi bi-arrow-down-left"></i> Stock In
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="movement_type" id="newTypeOut" value="out">
                            <label class="btn btn-outline-danger w-100 py-1.5 small fw-semibold" for="newTypeOut">
                                <i class="bi bi-arrow-up-right"></i> Stock Out
                            </label>
                        </div>
                        <div class="col-4">
                            <input type="radio" class="btn-check" name="movement_type" id="newTypeAdj" value="adjustment">
                            <label class="btn btn-outline-primary w-100 py-1.5 small fw-semibold" for="newTypeAdj">
                                <i class="bi bi-sliders"></i> Adjust
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="newQty" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Quantity Amount <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="newQty" class="form-control form-control-lg fw-bold text-center tabular-nums" min="1" value="10" required>
                    <div class="form-text small" id="newHelpText">Enter positive units to receive into stock.</div>
                </div>

                <div class="mb-2">
                    <label for="newReason" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Audit Reason / Note <span class="text-danger">*</span></label>
                    <input type="text" name="reason" id="newReason" class="form-control" placeholder="e.g. Delivery replenishment, spoilage write-off, count correction" required>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Confirm Movement</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tin = document.getElementById('newTypeIn');
    const tout = document.getElementById('newTypeOut');
    const tadj = document.getElementById('newTypeAdj');
    const htext = document.getElementById('newHelpText');

    function updateNewHelp() {
        if (tin.checked) {
            htext.textContent = 'Enter units to ADD to warehouse stock.';
        } else if (tout.checked) {
            htext.textContent = 'Enter units to DEDUCT from warehouse stock.';
        } else if (tadj.checked) {
            htext.textContent = 'Enter delta units (+ to increase, - to decrease).';
        }
    }

    if (tin && tout && tadj) {
        tin.addEventListener('change', updateNewHelp);
        tout.addEventListener('change', updateNewHelp);
        tadj.addEventListener('change', updateNewHelp);
    }
});
</script>
@endpush
