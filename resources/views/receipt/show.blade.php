@extends('layouts.app')

@section('title', 'Receipt #ORD-' . str_pad($order->order_id, 5, '0', STR_PAD_LEFT))

@section('content')
<div class="container py-3">
    <!-- Non-printable Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print mx-auto" style="max-width: 420px;">
        <a href="{{ route('pos') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <i class="bi bi-cart-plus"></i> New Sale (POS)
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-receipt me-1"></i> Orders
            </a>
            <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1 fw-bold shadow-sm" onclick="window.print();">
                <i class="bi bi-printer-fill"></i> Print Receipt
            </button>
        </div>
    </div>

    <!-- Official Thermal Receipt Wrapper -->
    <div class="receipt-wrapper">
        <!-- Store Header -->
        <div class="text-center mb-3">
            <div class="receipt-title text-dark">CANTEEN DAWN</div>
            <div class="small text-muted text-uppercase">School Canteen &bull; Point of Sale</div>
            <div class="small text-muted">Manila, Philippines</div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Receipt Details -->
        <div class="small lh-sm mb-2">
            <div class="d-flex justify-content-between">
                <span>Receipt No:</span>
                <span class="fw-bold">#ORD-{{ str_pad($order->order_id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Date & Time:</span>
                <span>{{ $order->order_date ? $order->order_date->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Cashier:</span>
                <span>{{ $order->user->name ?? 'Cashier' }} (ID: {{ $order->idno }})</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Customer:</span>
                <span>{{ $order->customer_name ?: 'Walk-in' }}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Status:</span>
                <span class="fw-bold text-uppercase">{{ $order->status }}</span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Items Table -->
        <table class="w-100 receipt-table small mb-2">
            <thead>
                <tr class="border-bottom border-dark">
                    <th class="text-start pb-1">Item</th>
                    <th class="text-center pb-1">Qty</th>
                    <th class="text-end pb-1">Price</th>
                    <th class="text-end pb-1">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderItems as $item)
                    <tr>
                        <td class="text-start pe-1">{{ $item->product->product_name ?? 'Product #' . $item->product_id }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format($item->line_total ?? ($item->unit_price * $item->quantity), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="receipt-divider"></div>

        <!-- Financial Summary -->
        <div class="small lh-base">
            <div class="d-flex justify-content-between fs-6 fw-bold text-dark my-1">
                <span>TOTAL AMOUNT:</span>
                <span>₱{{ number_format($order->total_amount, 2) }}</span>
            </div>

            <div class="d-flex justify-content-between text-muted">
                <span>Payment Method:</span>
                <span class="text-uppercase fw-semibold text-dark">{{ $order->payment->payment_method ?? 'cash' }}</span>
            </div>

            <div class="d-flex justify-content-between text-muted">
                <span>Amount Paid:</span>
                <span class="fw-semibold text-dark">₱{{ number_format($order->payment->amount_paid ?? $order->total_amount, 2) }}</span>
            </div>

            <div class="d-flex justify-content-between text-muted">
                <span>Change Given:</span>
                <span class="fw-semibold text-dark">₱{{ number_format($order->payment->change_amount ?? 0, 2) }}</span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Verification QR Code -->
        <div class="text-center my-2">
            <div class="bg-white p-1 d-inline-block border">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode(route('receipt.show', $order->order_id)) }}" 
                     alt="Receipt Verification QR" 
                     style="width: 95px; height: 95px;"
                     class="img-fluid"
                     onerror="this.style.display='none'">
            </div>
            <div class="text-muted mt-1" style="font-size: 0.68rem; letter-spacing: 0.3px;">
                Scan to verify digital receipt<br>
                <span class="font-monospace text-secondary">#ORD-{{ str_pad($order->order_id, 5, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>

        <div class="receipt-divider"></div>

        <!-- Receipt Footer -->
        <div class="text-center text-muted small mt-2">
            <p class="mb-1 fw-bold">THANK YOU FOR YOUR PURCHASE!</p>
            <p class="mb-0" style="font-size: 0.72rem;">Please keep this receipt for your records.<br>Have a wonderful day!</p>
        </div>
    </div>
</div>
@endsection

