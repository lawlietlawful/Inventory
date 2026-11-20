@extends('layouts.app')

@section('title', 'Sales Reports')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Top Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Sales Reports</h2>
            <p class="text-muted mb-0 small">Analyze canteen revenue, product sales velocity, and export CSV spreadsheets.</p>
        </div>
        <div>
            <a href="{{ route('reports.export', ['date_from' => $dateFrom, 'date_to' => $dateTo]) }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Date Range Filter Form -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="tracking-wide-caps mb-1 d-block">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" required>
                </div>
                <div class="col-12 col-sm-4 col-md-3">
                    <label class="tracking-wide-caps mb-1 d-block">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" required>
                </div>
                <div class="col-12 col-sm-4 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        Filter Report
                    </button>
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-primary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <span class="metric-label">Period Revenue</span>
                <div class="metric-value text-dark">₱{{ number_format($summary->total_revenue ?? 0, 2) }}</div>
                <div class="metric-sub">{{ \Carbon\Carbon::parse($dateFrom)->format('M d') }} &ndash; {{ \Carbon\Carbon::parse($dateTo)->format('M d, Y') }}</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <span class="metric-label">Paid Transactions</span>
                <div class="metric-value text-dark">{{ number_format($summary->total_orders ?? 0) }}</div>
                <div class="metric-sub">Average ticket: <strong>₱{{ number_format($summary->avg_order_value ?? 0, 2) }}</strong></div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <span class="metric-label">Cash Collections</span>
                <div class="metric-value text-dark">₱{{ number_format($summary->total_cash ?? 0, 2) }}</div>
                @php
                    $cashShare = ($summary->total_revenue ?? 0) > 0 ? round(($summary->total_cash / $summary->total_revenue) * 100) : 0;
                @endphp
                <div class="metric-sub">{{ $cashShare }}% of period total</div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="metric-card">
                <span class="metric-label">GCash Collections</span>
                <div class="metric-value text-dark">₱{{ number_format($summary->total_gcash ?? 0, 2) }}</div>
                @php
                    $gcashShare = ($summary->total_revenue ?? 0) > 0 ? round(($summary->total_gcash / $summary->total_revenue) * 100) : 0;
                @endphp
                <div class="metric-sub">{{ $gcashShare }}% of period total</div>
            </div>
        </div>
    </div>

    <!-- Per-Product Sales Breakdown Table -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark">Product Sales Breakdown</span>
            <span class="badge badge-neutral">{{ $productSales->count() }} items sold</span>
        </div>
        <div class="card-body p-0">
            @if ($productSales->isEmpty())
                <div class="text-center py-5 text-muted small">
                    No product sales found in the selected date range.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 50px;">#</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th class="text-center">Units Sold</th>
                                <th class="text-end">Avg Price</th>
                                <th class="text-end">Total Revenue</th>
                                <th class="text-end pe-3" style="width: 180px;">Share of Sales</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $grandRevenue = (float)($summary->total_revenue ?? 0);
                            @endphp
                            @foreach ($productSales as $idx => $prod)
                                @php
                                    $revenue = (float)$prod->total_product_revenue;
                                    $share = $grandRevenue > 0 ? round(($revenue / $grandRevenue) * 100, 1) : 0;
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted small">{{ $idx + 1 }}</td>
                                    <td class="fw-semibold text-dark">{{ $prod->product_name }}</td>
                                    <td><span class="badge badge-neutral">{{ $prod->category_name }}</span></td>
                                    <td class="text-center tabular-nums">{{ number_format($prod->total_qty_sold) }}</td>
                                    <td class="text-end tabular-nums">₱{{ number_format($prod->avg_price, 2) }}</td>
                                    <td class="text-end fw-semibold text-dark tabular-nums">₱{{ number_format($revenue, 2) }}</td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                            <span class="small text-muted tabular-nums" style="min-width: 38px;">{{ $share }}%</span>
                                            <div class="progress flex-grow-1 bg-light border" style="height: 6px;">
                                                <div class="progress-bar bg-dark" role="progressbar" style="width: {{ $share }}%;"></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-top">
                            <tr>
                                <td colspan="3" class="ps-3 fw-bold small text-uppercase text-muted">Total Sales</td>
                                <td class="text-center fw-bold text-dark tabular-nums">{{ number_format($productSales->sum('total_qty_sold')) }}</td>
                                <td></td>
                                <td class="text-end fw-bold text-dark tabular-nums">₱{{ number_format($grandRevenue, 2) }}</td>
                                <td class="text-end pe-3 small text-muted">100.0%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Detailed Orders in Period -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark">Period Transactions</span>
            <span class="badge badge-neutral">{{ $periodOrders->total() }} orders</span>
        </div>
        <div class="card-body p-0">
            @if ($periodOrders->isEmpty())
                <div class="text-center py-5 text-muted small">No orders found for this timeframe.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Order No.</th>
                                <th>Date & Time</th>
                                <th>Cashier</th>
                                <th>Customer</th>
                                <th>Method</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end pe-3">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($periodOrders as $ord)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark font-monospace" style="font-size: 0.85rem;">
                                        #ORD-{{ str_pad($ord->order_id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="small text-muted">{{ $ord->order_date ? $ord->order_date->format('M d, Y h:i A') : '—' }}</td>
                                    <td class="small fw-semibold text-dark">{{ $ord->user->name ?? 'N/A' }}</td>
                                    <td class="small text-muted">{{ $ord->customer_name ?: 'Walk-in' }}</td>
                                    <td><span class="badge badge-neutral text-uppercase" style="font-size: 0.72rem;">{{ $ord->payment->payment_method ?? 'cash' }}</span></td>
                                    <td class="text-end fw-bold text-dark tabular-nums">₱{{ number_format($ord->total_amount, 2) }}</td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('receipt.show', $ord->order_id) }}" class="btn btn-outline-primary btn-sm py-0.5 px-2" style="font-size: 0.78rem;" title="Print Receipt">
                                            Receipt
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $periodOrders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
