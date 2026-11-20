@extends('layouts.app')

@section('title', 'Order #ORD-' . str_pad($order->order_id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <!-- Top Action Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary btn-sm mb-2">
                &larr; Back to Orders
            </a>
            <h3 class="fw-bold text-dark mb-0 font-monospace" style="font-size: 1.4rem;">
                Order #ORD-{{ str_pad($order->order_id, 5, '0', STR_PAD_LEFT) }}
            </h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('receipt.show', $order->order_id) }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> Receipt View
            </a>
            @if ($order->status !== 'cancelled' && $order->status !== 'voided')
                <button type="button" class="btn btn-outline-primary text-danger d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#cancelDetailModal">
                    Void Order
                </button>
            @endif
        </div>
    </div>

    @if ($order->status === 'cancelled' || $order->status === 'voided')
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert" style="border-radius: var(--radius-md);">
            <i class="bi bi-x-octagon-fill fs-5"></i>
            <div>
                <strong>Transaction Voided</strong> &mdash; This order was voided and all items were returned to warehouse inventory.
            </div>
        </div>
    @endif

    <!-- Order Metadata Card -->
    <div class="card p-3.5 mb-4">
        <div class="row g-3">
            <div class="col-sm-6 col-md-3">
                <span class="tracking-wide-caps d-block mb-1">Status</span>
                <div>
                    @if ($order->status === 'paid' || $order->status === 'completed')
                        <span class="badge badge-success-subtle">Completed</span>
                    @elseif ($order->status === 'cancelled' || $order->status === 'voided')
                        <span class="badge badge-danger-subtle">Voided</span>
                    @else
                        <span class="badge badge-warning-subtle">{{ ucfirst($order->status) }}</span>
                    @endif
                </div>
            </div>
            <div class="col-sm-6 col-md-3">
                <span class="tracking-wide-caps d-block mb-1">Date & Time</span>
                <div class="small fw-semibold text-dark">{{ $order->order_date ? $order->order_date->format('M d, Y h:i A') : '—' }}</div>
            </div>
            <div class="col-sm-6 col-md-3">
                <span class="tracking-wide-caps d-block mb-1">Cashier</span>
                <div class="small fw-semibold text-dark">{{ $order->user->name ?? 'N/A' }} <span class="text-muted">({{ $order->idno }})</span></div>
            </div>
            <div class="col-sm-6 col-md-3">
                <span class="tracking-wide-caps d-block mb-1">Customer</span>
                <div class="small fw-semibold text-dark">{{ $order->customer_name ?: 'Walk-in' }}</div>
            </div>
        </div>
    </div>

    <!-- Items Purchased Table -->
    <div class="card mb-4">
        <div class="card-header">
            <span class="fw-semibold text-dark">Purchased Items</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Item</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end pe-3">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->orderItems as $item)
                            <tr>
                                <td class="ps-3 fw-semibold text-dark">{{ $item->product->product_name ?? 'Product #' . $item->product_id }}</td>
                                <td class="text-center tabular-nums">{{ $item->quantity }}</td>
                                <td class="text-end tabular-nums">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end pe-3 fw-bold text-dark tabular-nums">₱{{ number_format($item->line_total ?? ($item->unit_price * $item->quantity), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-top">
                        <tr>
                            <td colspan="3" class="ps-3 fw-bold text-uppercase small text-muted">Grand Total</td>
                            <td class="text-end pe-3 fw-bold text-dark fs-5 tabular-nums">₱{{ number_format($order->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Payment Information Card -->
    @if ($order->payment)
        <div class="card p-3.5">
            <h6 class="fw-semibold text-dark mb-3">Settlement Information</h6>
            <div class="row g-3">
                <div class="col-sm-6 col-md-3">
                    <span class="tracking-wide-caps d-block mb-1">Method</span>
                    <div class="fw-semibold text-uppercase small text-dark">{{ $order->payment->payment_method }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <span class="tracking-wide-caps d-block mb-1">Amount Paid</span>
                    <div class="fw-bold text-dark tabular-nums">₱{{ number_format($order->payment->amount_paid, 2) }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <span class="tracking-wide-caps d-block mb-1">Change Given</span>
                    <div class="fw-bold text-dark tabular-nums">₱{{ number_format($order->payment->change_amount, 2) }}</div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <span class="tracking-wide-caps d-block mb-1">Timestamp</span>
                    <div class="small text-muted">{{ $order->payment->paid_at ? $order->payment->paid_at->format('M d, Y h:i A') : '—' }}</div>
                </div>
            </div>
        </div>
    @endif
</div>

@if ($order->status !== 'cancelled' && $order->status !== 'voided')
<!-- Cancel Order Modal -->
<div class="modal fade" id="cancelDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: var(--radius-lg);">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark fs-6">Void Order & Restore Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('orders.cancel', $order->order_id) }}">
                @csrf
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-danger" style="font-size: 2rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-2">Cancel Order #ORD-{{ str_pad($order->order_id, 5, '0', STR_PAD_LEFT) }}?</h6>
                    <p class="text-muted small mb-3">
                        This will mark this transaction as <strong>Cancelled</strong>, exclude the total of <strong class="tabular-nums">₱{{ number_format($order->total_amount, 2) }}</strong> from revenue, and restore the following item quantities to inventory:
                    </p>
                    <div class="p-2.5 rounded bg-light border text-start small mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($order->orderItems as $item)
                                <li>
                                    <strong>{{ $item->quantity }}x</strong> {{ $item->product->product_name ?? 'Item' }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="p-2.5 rounded bg-light border text-start small text-muted">
                        <i class="bi bi-info-circle me-1"></i> This action cannot be reversed. Please make sure the refund has been completed.
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
@endif
@endsection
