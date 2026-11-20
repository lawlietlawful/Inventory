@extends('layouts.app')

@section('title', 'Inventory Overview')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Top Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-1 small fw-semibold">Group 2 &bull; Centralized Database</span>
            </div>
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Warehouse Inventory Hub</h2>
            <p class="text-muted mb-0 small">Real-time stock ledger, procurement purchase orders, and warehouse valuation.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('inventory.products.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle"></i> New Product (SKU)
            </a>
            <a href="{{ route('inventory.purchase-orders.create') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-plus"></i> New Purchase Order
            </a>
            <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left-right"></i> Movements Ledger
            </a>
        </div>
    </div>

    <!-- Executive Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Inventory Valuation -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Warehouse Valuation</span>
                    <span class="text-muted small"><i class="bi bi-cash-stack"></i></span>
                </div>
                <div class="metric-value text-dark tabular-nums">₱{{ number_format($totalValuation, 2) }}</div>
                <div class="metric-sub">Total cost of stock on hand</div>
            </div>
        </div>

        <!-- Total Products / SKUs -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Active Warehouse SKUs</span>
                    <span class="text-muted small"><i class="bi bi-boxes"></i></span>
                </div>
                <div class="metric-value text-dark tabular-nums">{{ number_format($totalActiveProducts) }}</div>
                <div class="metric-sub">Cataloged warehouse items</div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Reorder Alerts</span>
                    <span class="text-muted small"><i class="bi bi-exclamation-triangle"></i></span>
                </div>
                <div class="metric-value tabular-nums {{ $lowStockCount > 0 ? 'text-danger' : 'text-dark' }}">
                    {{ number_format($lowStockCount) }}
                </div>
                <div class="metric-sub">{{ $outOfStockCount }} completely out of stock</div>
            </div>
        </div>

        <!-- Open Purchase Orders -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Pending POs & Suppliers</span>
                    <span class="text-muted small"><i class="bi bi-truck"></i></span>
                </div>
                <div class="metric-value text-dark tabular-nums">{{ number_format($pendingPOCount) }} <span class="fs-6 fw-normal text-muted">POs</span></div>
                <div class="metric-sub">{{ $activeSuppliersCount }} registered suppliers</div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Row -->
    <div class="row g-4 mb-4">
        <!-- Category Stock Valuation Chart -->
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Category Stock Valuation</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Total asset value tied in stock per category</div>
                    </div>
                    <span class="badge badge-neutral">Inventory Assets</span>
                </div>
                <div class="card-body p-3">
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="categoryValuationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Purchase Orders Quick Feed -->
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Recent Purchase Orders</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Procurement requests awaiting receipt</div>
                    </div>
                    <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.75rem;">
                        View All &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    @if ($recentPOs->isEmpty())
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-file-earmark-check fs-3 d-block mb-1 text-secondary"></i>
                            No recent purchase orders found.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($recentPOs as $po)
                                <a href="{{ route('inventory.purchase-orders.show', $po->id) }}" class="list-group-item list-group-item-action px-3 py-2.5 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold font-monospace small text-primary">{{ $po->po_number }}</span>
                                            @if ($po->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning-emphasis" style="font-size: 0.65rem;">Pending</span>
                                            @elseif ($po->status === 'received')
                                                <span class="badge bg-success-subtle text-success-emphasis" style="font-size: 0.65rem;">Received</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;">{{ ucfirst($po->status) }}</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">
                                            Supplier: <strong class="text-dark">{{ $po->supplier->name ?? 'N/A' }}</strong> &bull; {{ $po->order_date->format('M d, Y') }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-dark small tabular-nums">₱{{ number_format($po->total_amount, 2) }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">{{ $po->items->count() }} items</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row: Low Stock Alerts & Recent Movements -->
    <div class="row g-4 mb-4">
        <!-- Low Stock Items Urgent Table -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Low Stock Reorder Alerts</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Items where current stock &le; reorder threshold</div>
                    </div>
                    <a href="{{ route('inventory.products.index', ['stock_status' => 'low']) }}" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.75rem;">
                        All Low Stock &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    @if ($lowStockProducts->isEmpty())
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-check2-circle text-success fs-3 d-block mb-1"></i>
                            All inventory items are currently at safe operating stock levels.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th class="ps-3">SKU & Product</th>
                                        <th>Category</th>
                                        <th class="text-center">Stock</th>
                                        <th class="text-center">Reorder Lvl</th>
                                        <th class="text-end pe-3">Supplier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStockProducts as $prod)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-dark">{{ $prod->name }}</div>
                                                <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.68rem;">{{ $prod->sku }}</span>
                                            </td>
                                            <td><span class="badge badge-neutral">{{ $prod->category->name ?? 'General' }}</span></td>
                                            <td class="text-center">
                                                @if ($prod->quantity_on_hand <= 0)
                                                    <span class="badge bg-danger text-white">0 {{ $prod->unit }}</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning-emphasis">{{ $prod->quantity_on_hand }} {{ $prod->unit }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center tabular-nums text-muted">{{ $prod->reorder_level }} {{ $prod->unit }}</td>
                                            <td class="text-end pe-3 small text-muted">
                                                {{ Str::limit($prod->supplier->name ?? 'Unassigned', 20) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Stock Movement Activity Feed -->
        <div class="col-12 col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Recent Stock Movements</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Latest stock intake, sales deductions, and adjustments</div>
                    </div>
                    <a href="{{ route('inventory.movements.index') }}" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 0.75rem;">
                        View Ledger &rarr;
                    </a>
                </div>
                <div class="card-body p-0">
                    @if ($recentMovements->isEmpty())
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-clock-history fs-3 d-block mb-1 text-secondary"></i>
                            No stock movement records found.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Type</th>
                                        <th>Product</th>
                                        <th class="text-center">Quantity</th>
                                        <th>Reason / Ref</th>
                                        <th class="text-end pe-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentMovements as $mv)
                                        <tr>
                                            <td class="ps-3">
                                                @if ($mv->movement_type === 'in')
                                                    <span class="badge bg-success-subtle text-success d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-arrow-down-left"></i> IN
                                                    </span>
                                                @elseif ($mv->movement_type === 'out')
                                                    <span class="badge bg-danger-subtle text-danger d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-arrow-up-right"></i> OUT
                                                    </span>
                                                @else
                                                    <span class="badge bg-primary-subtle text-primary d-inline-flex align-items-center gap-1">
                                                        <i class="bi bi-sliders"></i> ADJ
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $mv->product->name ?? 'SKU #' . $mv->product_id }}</div>
                                            </td>
                                            <td class="text-center fw-bold tabular-nums {{ $mv->quantity > 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $mv->quantity > 0 ? '+' : '' }}{{ $mv->quantity }}
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ Str::limit($mv->reason, 26) }}</span>
                                            </td>
                                            <td class="text-end pe-3 text-muted small font-monospace" style="font-size: 0.75rem;">
                                                {{ $mv->created_at ? $mv->created_at->format('M d, H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('categoryValuationChart');
    if (!ctx) return;

    const catStats = @json($categoryStats);
    const labels = catStats.map(c => c.name);
    const valuations = catStats.map(c => c.valuation);

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Stock Valuation (₱)',
                data: valuations,
                backgroundColor: '#0f172a',
                hoverBackgroundColor: '#38bdf8',
                borderRadius: 4,
                barPercentage: 0.5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return ' Valuation: ₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 11 },
                        callback: function(v) { return '₱' + v.toLocaleString(); }
                    },
                    grid: { color: '#f1f5f9' },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
});
</script>
@endpush
