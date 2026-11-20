@extends('layouts.app')

@section('title', 'Orders History')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Top Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Orders</h2>
            <p class="text-muted mb-0 small">Review past customer transactions, reprint thermal receipts, and audit cashier sales.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pos') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-cart4"></i> Open POS
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('orders.index') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-2">
                    <label class="tracking-wide-caps mb-1 d-block">From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="tracking-wide-caps mb-1 d-block">To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-6 col-md-2">
                    <label class="tracking-wide-caps mb-1 d-block">Status</label>
                    <select name="status" class="form-select">
                        <option value="all">All</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="tracking-wide-caps mb-1 d-block">Cashier</label>
                    <select name="cashier" class="form-select">
                        <option value="all">All Cashiers</option>
                        @foreach ($cashiers as $c)
                            <option value="{{ $c->idno }}" {{ request('cashier') == $c->idno ? 'selected' : '' }}>
                                {{ $c->name }} (ID: {{ $c->idno }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    @if (request()->hasAny(['date_from', 'date_to', 'status', 'cashier']))
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-primary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark">Transaction Log</span>
            <span class="badge badge-neutral">{{ $orders->total() }} records</span>
        </div>
        <div class="card-body p-0">
            @if ($orders->isEmpty())
                <div class="text-center py-5 text-muted small">
                    No orders match your filter criteria.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Order No.</th>
                                <th>Date & Time</th>
                                <th>Cashier</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Method</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $ord)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark font-monospace" style="font-size: 0.85rem;">
                                        #ORD-{{ str_pad($ord->order_id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $ord->order_date ? $ord->order_date->format('M d, Y h:i A') : '—' }}
                                    </td>
                                    <td class="small fw-semibold text-dark">
                                        {{ $ord->user->name ?? 'N/A' }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $ord->customer_name ?: 'Walk-in' }}
                                    </td>
                                    <td class="small tabular-nums">
                                        {{ $ord->order_items_count }} item(s)
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral text-uppercase" style="font-size: 0.72rem;">
                                            {{ $ord->payment->payment_method ?? 'cash' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark tabular-nums">
                                        ₱{{ number_format($ord->total_amount, 2) }}
                                    </td>
                                    <td>
                                        @if ($ord->status === 'paid' || $ord->status === 'completed')
                                            <span class="badge badge-success-subtle">Completed</span>
                                        @elseif ($ord->status === 'cancelled' || $ord->status === 'voided')
                                            <span class="badge badge-danger-subtle">Voided</span>
                                        @else
                                            <span class="badge badge-warning-subtle">{{ ucfirst($ord->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('orders.show', $ord->order_id) }}" class="btn btn-outline-primary" style="font-size: 0.78rem;" title="View details">
                                                View
                                            </a>

                                            <a href="{{ route('receipt.show', $ord->order_id) }}" class="btn btn-outline-primary" style="font-size: 0.78rem;" title="Print Receipt">
                                                Receipt
                                            </a>

                                            @if ($ord->status !== 'cancelled' && $ord->status !== 'voided')
                                                <button type="button" 
                                                        class="btn btn-outline-primary text-danger" 
                                                        style="font-size: 0.78rem;" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#cancelOrderModal"
                                                        data-order-id="{{ $ord->order_id }}"
                                                        data-order-code="#ORD-{{ str_pad($ord->order_id, 5, '0', STR_PAD_LEFT) }}"
                                                        data-order-total="₱{{ number_format($ord->total_amount, 2) }}"
                                                        title="Cancel order and restore stock">
                                                    Cancel
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 border-top">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Cancel Order Confirmation Modal -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark fs-6">Void Order & Restore Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelOrderForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger" style="font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Cancel Transaction <span id="modalOrderCode" class="font-monospace"></span>?</h6>
                    <p class="text-muted small mb-3">
                        This will mark the order as <strong>Cancelled</strong>, exclude the total of <strong id="modalOrderTotal" class="tabular-nums"></strong> from sales reports, and immediately return all purchased items back to inventory.
                    </p>
                    <div class="p-2.5 rounded bg-light border text-start small text-muted">
                        <i class="bi bi-info-circle me-1"></i> This action is non-reversible. Please ensure any refund has been handed to the customer.
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-dismiss="modal">Keep Order</button>
                    <button type="submit" class="btn btn-danger btn-sm px-3">Confirm Void & Restore</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cancelModal = document.getElementById('cancelOrderModal');
    if (cancelModal) {
        cancelModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');
            const orderCode = button.getAttribute('data-order-code');
            const orderTotal = button.getAttribute('data-order-total');

            document.getElementById('modalOrderCode').textContent = orderCode;
            document.getElementById('modalOrderTotal').textContent = orderTotal;
            document.getElementById('cancelOrderForm').action = '/orders/' + orderId + '/cancel';
        });
    }
});
</script>
@endpush
