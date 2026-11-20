@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Top Header: Title & Clean Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Canteen Overview</h2>
            <p class="text-muted mb-0 small">Real-time daily sales, point-of-sale volume, and inventory stock alerts.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-cart4"></i> Open POS Terminal
            </a>
            <a href="{{ route('reports.index') }}" class="btn btn-outline-primary d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-text"></i> Sales Reports
            </a>
        </div>
    </div>

    <!-- Executive Metric Cards (Clean, Minimalist, No Pastel Circles) -->
    <div class="row g-3 mb-4">
        <!-- Today's Sales -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Today's Revenue</span>
                    <span class="text-muted small"><i class="bi bi-calendar-event"></i></span>
                </div>
                <div class="metric-value text-dark">₱{{ number_format($todaySales, 2) }}</div>
                <div class="metric-sub">Paid orders today</div>
            </div>
        </div>

        <!-- Orders Today -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Transactions Today</span>
                    <span class="text-muted small"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="metric-value text-dark">{{ number_format($todayOrders) }}</div>
                <div class="metric-sub">Completed cashier sales</div>
            </div>
        </div>

        <!-- Active Products -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Active Menu Items</span>
                    <span class="text-muted small"><i class="bi bi-grid"></i></span>
                </div>
                <div class="metric-value text-dark">{{ number_format($activeProducts) }}</div>
                <div class="metric-sub">Available for ordering</div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="metric-label">Restock Alerts</span>
                    <span class="text-muted small"><i class="bi bi-bell"></i></span>
                </div>
                <div class="metric-value {{ $lowStockCount > 0 ? 'text-danger' : 'text-dark' }}">{{ number_format($lowStockCount) }}</div>
                <div class="metric-sub">Items &le; 10 units remaining</div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Row -->
    <div class="row g-4 mb-4">
        <!-- Sales for Last 7 Days (Chart.js Bar Chart) -->
        <div class="col-12 col-xl-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Sales Performance</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Daily revenue trend over the past 7 days</div>
                    </div>
                    <span class="badge badge-neutral">Last 7 Days</span>
                </div>
                <div class="card-body p-3">
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Method Channels (Chart.js Doughnut Chart) -->
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Payment Channels</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Breakdown of Cash vs GCash volume</div>
                    </div>
                    <span class="badge badge-neutral">Distribution</span>
                </div>
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div style="position: relative; height: 180px; width: 100%;">
                        <canvas id="paymentChart"></canvas>
                    </div>
                    <div class="row g-2 mt-2 pt-2 border-top text-center">
                        <div class="col-6">
                            <div class="p-2 rounded bg-light border">
                                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.68rem;">Cash Sales</div>
                                <div class="fw-bold text-dark fs-6 tabular-nums">₱{{ number_format($cashSales, 2) }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $cashOrders }} orders</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 rounded bg-light border">
                                <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.68rem;">GCash Sales</div>
                                <div class="fw-bold text-dark fs-6 tabular-nums">₱{{ number_format($gcashSales, 2) }}</div>
                                <div class="text-muted" style="font-size: 0.72rem;">{{ $gcashOrders }} orders</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory & Best Sellers Row -->
    <div class="row g-4 mb-4">
        <!-- Top 5 Products -->
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Top Products</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Highest quantity sold across all orders</div>
                    </div>
                    <span class="badge badge-neutral">Best Sellers</span>
                </div>
                <div class="card-body p-3">
                    @if ($topProducts->isEmpty())
                        <div class="text-center py-5 text-muted small">
                            No product sales recorded yet.
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($topProducts as $idx => $prod)
                                <div class="list-group-item px-0 py-2.5 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="text-muted fw-bold small" style="width: 20px;">
                                            #{{ $idx + 1 }}
                                        </span>
                                        <div>
                                            <div class="fw-semibold text-dark mb-0 small">{{ $prod->product_name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $prod->category_name }}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-semibold text-dark small tabular-nums">{{ number_format($prod->total_sold) }} sold</div>
                                        <div class="text-muted tabular-nums" style="font-size: 0.75rem;">₱{{ number_format($prod->total_revenue, 2) }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Low Stock Inventory Alert Table -->
        <div class="col-12 col-lg-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Inventory Stock Alerts</div>
                        <div class="text-muted small" style="font-size: 0.75rem;">Items requiring attention or replenishment (&le; 10 units)</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if ($lowStockProducts->count() > 0)
                            <span class="badge bg-danger-subtle text-danger">{{ $lowStockProducts->count() }} items</span>
                        @endif
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-sm py-1 px-2.5" style="font-size: 0.78rem;">
                            View Inventory &rarr;
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($lowStockProducts->isEmpty())
                        <div class="text-center py-5 text-muted small">
                            <i class="bi bi-check2 text-success fs-3 d-block mb-1"></i>
                            All inventory items are sufficiently stocked.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Item</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($lowStockProducts as $prod)
                                        <tr>
                                            <td class="ps-3 fw-semibold text-dark">{{ $prod->product_name }}</td>
                                            <td><span class="badge badge-neutral">{{ $prod->category->category_name ?? 'General' }}</span></td>
                                            <td class="tabular-nums">₱{{ number_format($prod->price, 2) }}</td>
                                            <td>
                                                @if ($prod->stock_qty <= 0)
                                                    <span class="badge bg-secondary text-white">0 Out of Stock</span>
                                                @elseif ($prod->stock_qty <= 5)
                                                    <span class="badge badge-danger-subtle">{{ $prod->stock_qty }} Critical</span>
                                                @else
                                                    <span class="badge badge-warning-subtle">{{ $prod->stock_qty }} Low</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                <button type="button" 
                                                        class="btn btn-outline-primary btn-sm py-0.5 px-2" 
                                                        style="font-size: 0.78rem;"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#quickRestockModal" 
                                                        data-id="{{ $prod->product_id }}" 
                                                        data-name="{{ $prod->product_name }}">
                                                    Restock
                                                </button>
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

<!-- Quick Restock Modal -->
<div class="modal fade" id="quickRestockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form method="POST" id="quickRestockForm" class="modal-content shadow border-0">
            @csrf
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Quick Restock</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-2">Item: <strong id="quickRestockName" class="text-dark"></strong></p>
                <div class="mb-3">
                    <label for="quickRestockQty" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Quantity to Add</label>
                    <input type="number" class="form-control form-control-lg fw-bold text-center tabular-nums" id="quickRestockQty" name="add_qty" min="1" value="10" required>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Confirm Restock</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js CDN (Dashboard Only) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Sales Performance (Last 7 Days) - Clean Slate Palette
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($last7Days);

    const labels = salesData.map(d => d.label);
    const dataValues = salesData.map(d => d.sales);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Daily Sales (₱)',
                data: dataValues,
                backgroundColor: '#0f172a',
                hoverBackgroundColor: '#1e293b',
                borderRadius: 4,
                barPercentage: 0.55,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 10,
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            return ' Sales: ₱' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 11 },
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: '#f1f5f9'
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11 }
                    }
                }
            }
        }
    });

    // 2. Payment Method Channels (Cash vs GCash)
    const payCanvas = document.getElementById('paymentChart');
    if (payCanvas) {
        const payCtx = payCanvas.getContext('2d');
        const cashSales = {{ $cashSales }};
        const gcashSales = {{ $gcashSales }};
        const hasSales = (cashSales + gcashSales) > 0;

        new Chart(payCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cash', 'GCash'],
                datasets: [{
                    data: hasSales ? [cashSales, gcashSales] : [1, 1],
                    backgroundColor: hasSales ? ['#0f172a', '#64748b'] : ['#e2e8f0', '#cbd5e1'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 6,
                        enabled: hasSales,
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ₱' + Number(context.parsed).toLocaleString('en-US', { minimumFractionDigits: 2 });
                            }
                        }
                    }
                },
                cutout: '68%'
            }
        });
    }

    // Quick restock modal handler
    const restockModal = document.getElementById('quickRestockModal');
    if (restockModal) {
        restockModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            document.getElementById('quickRestockName').textContent = name;
            document.getElementById('quickRestockForm').action = '/products/' + id + '/restock';
        });
    }
});
</script>
@endpush
