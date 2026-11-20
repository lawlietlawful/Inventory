@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-1 small fw-semibold">Procurement</span>
                @if ($pendingCount > 0)
                    <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis px-2 py-1 small fw-semibold">{{ $pendingCount }} Pending Approval/Receipt</span>
                @endif
            </div>
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Purchase Orders (Procurement)</h2>
            <p class="text-muted mb-0 small">Official supplier purchase orders, delivery tracking, and automated stock receipt processing.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.purchase-orders.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-plus"></i> Create Purchase Order
            </a>
            <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-truck"></i> Suppliers Directory
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('inventory.purchase-orders.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search PO # or supplier..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All PO Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Suppliers</option>
                        @foreach ($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ Str::limit($sup->name, 20) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">Filter</button>
                    @if (request()->hasAny(['search', 'status', 'supplier_id']))
                        <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="fw-semibold text-dark">
                Purchase Orders List <span class="badge bg-light text-secondary border ms-1">{{ $orders->total() }} records</span>
            </div>
            <div class="small text-muted">
                Showing {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if ($orders->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-file-earmark-x fs-1 d-block mb-2 text-secondary"></i>
                    No purchase orders found matching the specified parameters.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">PO Number</th>
                                <th>Supplier</th>
                                <th>Order Date</th>
                                <th>Expected Delivery</th>
                                <th class="text-center">Items Count</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td class="ps-3">
                                        <a href="{{ route('inventory.purchase-orders.show', $order->id) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                            {{ $order->po_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $order->supplier->name ?? 'Unassigned' }}</div>
                                        @if ($order->supplier && $order->supplier->contact_person)
                                            <div class="text-muted small" style="font-size: 0.75rem;">Attn: {{ $order->supplier->contact_person }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-dark">{{ $order->order_date->format('M d, Y') }}</span>
                                    </td>
                                    <td>
                                        @if ($order->expected_date)
                                            <span class="text-muted font-monospace small">{{ $order->expected_date->format('M d, Y') }}</span>
                                        @else
                                            <span class="text-muted small">&mdash;</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-secondary border">{{ $order->items->count() }} items ({{ $order->total_quantity }} units)</span>
                                    </td>
                                    <td class="text-end fw-bold tabular-nums text-dark">
                                        ₱{{ number_format($order->total_amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if ($order->status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-1">
                                                <i class="bi bi-hourglass-split me-1"></i> Pending
                                            </span>
                                        @elseif ($order->status === 'received')
                                            <span class="badge bg-success-subtle text-success px-2 py-1">
                                                <i class="bi bi-check-circle me-1"></i> Received
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                                <i class="bi bi-x-circle me-1"></i> Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('inventory.purchase-orders.show', $order->id) }}" class="btn btn-outline-primary btn-sm py-1 px-2.5">
                                            View PO &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($orders->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
