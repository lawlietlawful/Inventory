@extends('layouts.app')

@section('title', 'PO ' . $purchaseOrder->po_number)

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Breadcrumb & Top Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom d-print-none">
        <div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('inventory.purchase-orders.index') }}" class="text-decoration-none text-muted small">&larr; All Purchase Orders</a>
                <span class="text-muted small">&bull;</span>
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-0.5 small fw-semibold">Procurement</span>
            </div>
            <div class="d-flex align-items-center gap-3 mt-1">
                <h2 class="fw-bold mb-0 font-monospace" style="font-size: 1.6rem; letter-spacing: -0.02em;">{{ $purchaseOrder->po_number }}</h2>
                @if ($purchaseOrder->status === 'pending')
                    <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1 fs-6">
                        <i class="bi bi-hourglass-split me-1"></i> Pending Receipt
                    </span>
                @elseif ($purchaseOrder->status === 'received')
                    <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i> Received & Stocked
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 fs-6">
                        <i class="bi bi-x-circle me-1"></i> Cancelled
                    </span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-2" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Purchase Order
            </button>

            @if ($purchaseOrder->status === 'pending')
                <!-- Receive Stock Form -->
                <form method="POST" action="{{ route('inventory.purchase-orders.receive', $purchaseOrder->id) }}" class="d-inline" onsubmit="return confirm('Confirm receipt of this order? This will immediately increment warehouse stock for all {{ $purchaseOrder->items->count() }} items and log stock intake movements.');">
                    @csrf
                    <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam"></i> Receive Stock & Update Inventory
                    </button>
                </form>

                <!-- Cancel Form -->
                <form method="POST" action="{{ route('inventory.purchase-orders.cancel', $purchaseOrder->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this purchase order?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">
                        Cancel PO
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Official Printable Purchase Order Document -->
    <div class="card shadow-sm border-0 mb-4 mx-auto" style="max-width: 900px;">
        <div class="card-body p-4 p-md-5">
            <!-- Document Header -->
            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="brand-mark">
                            <svg width="24" height="24" viewBox="0 0 64 64" fill="none">
                                <rect width="64" height="64" rx="14" fill="#0f172a"/>
                                <path d="M42 22C39.5 19.5 36 18 32 18C24.268 18 18 24.268 18 32C18 39.732 24.268 46 32 46C36 46 39.5 44.5 42 42" stroke="#ffffff" stroke-width="4.5" stroke-linecap="round"/>
                                <circle cx="33" cy="32" r="3.5" fill="#38bdf8"/>
                                <line x1="33" y1="25" x2="33" y2="28" stroke="#38bdf8" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="fw-bold fs-4 text-dark" style="letter-spacing: -0.01em;">CANTEEN DAWN</span>
                    </div>
                    <div class="text-muted small">Centralized School Canteen & Warehouse</div>
                    <div class="text-muted small">San Jose, Antique &bull; Centralized Management Hub</div>
                </div>
                <div class="text-end">
                    <h3 class="fw-bold text-dark mb-1" style="letter-spacing: 0.05em;">PURCHASE ORDER</h3>
                    <div class="font-monospace fw-bold text-primary fs-5">{{ $purchaseOrder->po_number }}</div>
                    <div class="text-muted small">Date Issued: <strong class="text-dark">{{ $purchaseOrder->order_date->format('F d, Y') }}</strong></div>
                </div>
            </div>

            <!-- Supplier & PO Meta Info Box -->
            <div class="row g-4 mb-4 pb-4 border-bottom">
                <div class="col-12 col-md-6">
                    <div class="text-uppercase text-muted fw-bold small mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Vendor / Supplier</div>
                    <h5 class="fw-bold text-dark mb-1">{{ $purchaseOrder->supplier->name ?? 'Unassigned Supplier' }}</h5>
                    @if ($purchaseOrder->supplier)
                        @if ($purchaseOrder->supplier->contact_person)
                            <div class="text-secondary small mb-1">Attention: <strong class="text-dark">{{ $purchaseOrder->supplier->contact_person }}</strong></div>
                        @endif
                        @if ($purchaseOrder->supplier->phone)
                            <div class="text-secondary small"><i class="bi bi-telephone me-1"></i> {{ $purchaseOrder->supplier->phone }}</div>
                        @endif
                        @if ($purchaseOrder->supplier->email)
                            <div class="text-secondary small"><i class="bi bi-envelope me-1"></i> {{ $purchaseOrder->supplier->email }}</div>
                        @endif
                        @if ($purchaseOrder->supplier->address)
                            <div class="text-secondary small mt-1"><i class="bi bi-geo-alt me-1"></i> {{ $purchaseOrder->supplier->address }}</div>
                        @endif
                    @endif
                </div>

                <div class="col-12 col-md-6 text-md-end">
                    <div class="text-uppercase text-muted fw-bold small mb-2" style="font-size: 0.72rem; letter-spacing: 0.05em;">Shipment & Terms</div>
                    <div class="text-secondary small mb-1">
                        Expected Delivery: 
                        <strong class="text-dark font-monospace">{{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('F d, Y') : 'Prompt' }}</strong>
                    </div>
                    <div class="text-secondary small mb-1">
                        Order Status: 
                        <strong class="text-uppercase text-dark">{{ $purchaseOrder->status }}</strong>
                    </div>
                    <div class="text-secondary small">
                        Prepared By: <span class="badge bg-light text-secondary border">Inventory Staff #{{ $purchaseOrder->created_by }}</span>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle mb-0" style="font-size: 0.88rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 5%;">#</th>
                            <th style="width: 20%;">SKU</th>
                            <th style="width: 40%;">Item Description</th>
                            <th class="text-center" style="width: 10%;">Qty</th>
                            <th class="text-end" style="width: 12%;">Unit Cost</th>
                            <th class="text-end" style="width: 13%;">Total (₱)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $idx => $item)
                            <tr>
                                <td class="text-center font-monospace text-muted">{{ $idx + 1 }}</td>
                                <td class="font-monospace fw-bold text-dark">{{ $item->product->sku ?? 'N/A' }}</td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->product->name ?? 'Product #' . $item->product_id }}</div>
                                    @if ($item->product && $item->product->category)
                                        <div class="text-muted small" style="font-size: 0.72rem;">Category: {{ $item->product->category->name }} &bull; Unit: {{ $item->product->unit }}</div>
                                    @endif
                                </td>
                                <td class="text-center font-monospace fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end tabular-nums">₱{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-end tabular-nums fw-bold text-dark">₱{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-semibold">Grand Total:</td>
                            <td class="text-center font-monospace fw-bold">{{ $purchaseOrder->total_quantity }} units</td>
                            <td></td>
                            <td class="text-end font-monospace fw-bold fs-6 text-primary tabular-nums">₱{{ number_format($purchaseOrder->total_amount, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Notes & Signatures -->
            <div class="row g-4 pt-3">
                <div class="col-12 col-md-7">
                    <div class="text-uppercase text-muted fw-bold small mb-1" style="font-size: 0.72rem;">Order Notes / Instructions</div>
                    <div class="p-3 bg-light rounded border small text-secondary">
                        {{ $purchaseOrder->notes ?: 'Standard delivery terms apply. All goods must be in good condition upon warehouse intake inspection.' }}
                    </div>
                </div>
                <div class="col-12 col-md-5 d-flex flex-column justify-content-end text-center">
                    <div class="border-top pt-2 mt-4 text-muted small">
                        Authorized Procurement Officer Signature
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
