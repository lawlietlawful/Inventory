<div class="container-fluid px-2 px-lg-3 pt-2">
    <!-- Top Error Alert -->
    @if ($errorMessage)
        <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center mb-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div class="flex-grow-1">{{ $errorMessage }}</div>
            <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
        </div>
    @endif

    <div class="row g-3 pos-container">
        <!-- ===================================================================
             LEFT PANEL: PRODUCT CATALOG (SEARCH, CATEGORY TABS, GRID)
             =================================================================== -->
        <div class="col-12 col-lg-7 col-xl-8 d-flex flex-column h-100">
            <!-- Search & Filter Controls -->
            <div class="card border-0 shadow-sm p-3 mb-3 bg-white">
                <!-- POS Controls Toolbar -->
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge badge-neutral px-2 py-1">
                            <i class="bi bi-terminal me-1"></i> POS Register
                        </span>
                        <span class="text-muted small d-none d-sm-inline">&bull; <strong>{{ $products->count() }}</strong> items</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <!-- Sound Toggle -->
                        <div class="form-check form-switch mb-0 d-flex align-items-center gap-1" title="Toggle audio feedback">
                            <input class="form-check-input" type="checkbox" id="soundEffectsToggle" checked>
                            <label class="form-check-label small text-muted" for="soundEffectsToggle">
                                <span class="d-none d-md-inline">Audio</span>
                            </label>
                        </div>
                        <!-- Shortcuts Modal Trigger -->
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 small d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#shortcutsModal" title="View keyboard shortcuts (F1 or ?)">
                            <i class="bi bi-keyboard"></i> <span class="d-none d-md-inline">Shortcuts</span> <span class="badge badge-neutral small">F1</span>
                        </button>
                    </div>
                </div>

                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" 
                                   id="posSearchInput"
                                   wire:model.live.debounce.250ms="search" 
                                   class="form-control border-start-0 ps-0" 
                                   placeholder="Search menu items [F2 or /]..." 
                                   autocomplete="off">
                            @if (!empty($search))
                                <button class="btn btn-outline-secondary border-start-0" type="button" wire:click="$set('search', '')">
                                    <i class="bi bi-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-12 col-md-5 text-md-end text-muted small">
                        <span>Active Category: <strong>{{ $selectedCategory === 'all' ? 'All Items' : ($categories->firstWhere('category_id', $selectedCategory)->category_name ?? 'Filtered') }}</strong></span>
                    </div>
                </div>

                <!-- Category Tabs -->
                <div class="mt-2 pt-2 border-top">
                    <div class="nav nav-pills category-pills flex-nowrap overflow-auto py-1">
                        <a href="#" 
                           wire:click.prevent="setCategory('all')" 
                           class="nav-link category-tab {{ $selectedCategory === 'all' ? 'active' : '' }}">
                            <i class="bi bi-grid-fill me-1"></i> All Categories
                        </a>
                        @foreach ($categories as $cat)
                            <a href="#" 
                               wire:click.prevent="setCategory('{{ $cat->category_id }}')" 
                               class="nav-link category-tab {{ $selectedCategory == $cat->category_id ? 'active' : '' }}">
                                {{ $cat->category_name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Product Grid (Scrollable) -->
            <div class="flex-grow-1 product-scroll-area">
                <div class="row g-2 g-sm-3">
                    @forelse ($products as $p)
                        @php
                            $isUnavailable = (!$p->is_available || $p->stock_qty <= 0);
                        @endphp
                        <div class="col-6 col-sm-4 col-md-4 col-xl-3">
                            <div class="product-card p-3 shadow-sm {{ $isUnavailable ? 'disabled' : '' }}"
                                 @if (!$isUnavailable) wire:click="addToCart({{ $p->product_id }})" onclick="window.playScannerBeep && window.playScannerBeep()" @endif
                                 title="{{ $isUnavailable ? 'Unavailable or Out of Stock' : 'Click to add to cart' }}">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-1 gap-1">
                                        <span class="badge badge-neutral text-truncate" style="max-width: 90px; font-size: 0.7rem;">
                                            {{ $p->category->category_name ?? 'General' }}
                                        </span>
                                        @if (!$p->is_available)
                                            <span class="badge bg-light text-muted border small">Unavailable</span>
                                        @elseif ($p->stock_qty <= 0)
                                            <span class="badge bg-light text-muted border small">Out of Stock</span>
                                        @elseif ($p->stock_qty <= 5)
                                            <span class="badge badge-danger-subtle small fw-semibold" title="Critical low stock">
                                                {{ $p->stock_qty }} left
                                            </span>
                                        @elseif ($p->stock_qty <= 10)
                                            <span class="badge badge-warning-subtle small fw-semibold" title="Low stock warning">
                                                Low: {{ $p->stock_qty }}
                                            </span>
                                        @else
                                            <span class="text-muted small" style="font-size: 0.72rem;">
                                                Stock: {{ $p->stock_qty }}
                                            </span>
                                        @endif
                                    </div>

                                    <h6 class="fw-semibold text-dark text-truncate-2 mb-2" style="font-size: 0.92rem; min-height: 2.3em; line-height: 1.25;">
                                        {{ $p->product_name }}
                                    </h6>
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                    <span class="product-card-price">₱{{ number_format($p->price, 2) }}</span>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary rounded-circle d-flex align-items-center justify-content-center p-0" 
                                            style="width: 28px; height: 28px;" 
                                            {{ $isUnavailable ? 'disabled' : '' }}>
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-5">
                            <i class="bi bi-search text-muted display-4 d-block mb-2"></i>
                            <h5 class="text-muted">No menu items found.</h5>
                            <p class="text-muted small">Try a different search term or category filter.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ===================================================================
             RIGHT PANEL: ORDER CART & CHECKOUT
             =================================================================== -->
        <div class="col-12 col-lg-5 col-xl-4 h-100">
            <div class="cart-panel shadow-sm">
                <!-- Cart Header -->
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-cart3 text-primary fs-5"></i>
                        <h6 class="fw-bold text-dark mb-0">Current Order</h6>
                        <span class="badge bg-primary rounded-pill small">{{ $itemCount }} {{ Str::plural('item', $itemCount) }}</span>
                    </div>
                    <button type="button" 
                            wire:click="clearCart" 
                            class="btn btn-outline-danger btn-sm py-1 px-2 small" 
                            {{ empty($cart) ? 'disabled' : '' }} 
                            title="Clear all cart items"
                            onclick="return confirm('Clear entire cart?') || event.stopImmediatePropagation()">
                        <i class="bi bi-trash3 me-1"></i> Clear
                    </button>
                </div>

                <!-- Customer Name (Optional) -->
                <div class="px-3 pt-2 pb-1 bg-white border-bottom">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" wire:model="customerName" class="form-control" placeholder="Customer Name (Optional)">
                    </div>
                </div>

                <!-- Cart Items List (Scrollable) -->
                <div class="cart-scroll-area">
                    @forelse ($cart as $productId => $item)
                        <div class="cart-item d-flex align-items-center justify-content-between gap-2" wire:key="cart-item-{{ $productId }}">
                            <div class="flex-grow-1 text-truncate pe-1">
                                <div class="fw-semibold text-truncate small mb-1" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                <div class="text-muted small">₱{{ number_format($item['price'], 2) }} each</div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <button type="button" 
                                        wire:click="decreaseQty({{ $productId }})" 
                                        onclick="window.playScannerBeep && window.playScannerBeep()"
                                        class="btn btn-outline-secondary btn-sm qty-btn" 
                                        title="Decrease">&minus;</button>
                                
                                <input type="number" 
                                       wire:change="updateQty({{ $productId }}, $event.target.value)" 
                                       class="qty-input form-control form-control-sm text-center" 
                                       value="{{ $item['qty'] }}" 
                                       min="1" 
                                       max="{{ $item['stock'] }}">
                                
                                <button type="button" 
                                        wire:click="increaseQty({{ $productId }})" 
                                        onclick="window.playScannerBeep && window.playScannerBeep()"
                                        class="btn btn-outline-secondary btn-sm qty-btn" 
                                        {{ $item['qty'] >= $item['stock'] ? 'disabled' : '' }} 
                                        title="Increase">&plus;</button>
                            </div>
                            <div class="text-end ps-2" style="min-width: 75px;">
                                <div class="fw-bold text-dark small">₱{{ number_format($item['price'] * $item['qty'], 2) }}</div>
                                <button type="button" 
                                        wire:click="removeFromCart({{ $productId }})" 
                                        class="btn btn-link text-danger p-0 border-0 small text-decoration-none" 
                                        title="Remove">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 my-auto px-3">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle mb-2" style="width: 56px; height: 56px; font-size: 1.6rem;">
                                <i class="bi bi-basket2"></i>
                            </div>
                            <h6 class="fw-semibold text-secondary mb-1">Cart is Empty</h6>
                            <p class="text-muted small mb-0">Select items from the menu to start an order.</p>
                        </div>
                    @endforelse
                </div>

                <!-- Cart Summary & Checkout -->
                <div class="cart-summary">
                    <!-- Grand Total -->
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <span class="text-muted fw-bold">TOTAL AMOUNT</span>
                        <span class="grand-total-display">₱{{ number_format($total, 2) }}</span>
                    </div>

                    <!-- Payment Method Toggle -->
                    <div class="mb-3">
                        <label class="tracking-wide-caps mb-1 d-block">Payment Method</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" 
                                    id="payMethodCashBtn"
                                    wire:click="setPaymentMethod('cash')" 
                                    class="btn {{ $paymentMethod === 'cash' ? 'btn-primary' : 'btn-outline-primary' }} py-2 d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-cash-stack"></i> CASH <span class="badge {{ $paymentMethod === 'cash' ? 'badge-neutral bg-white text-dark' : 'badge-neutral' }} small ms-1">F4</span>
                            </button>
                            <button type="button" 
                                    id="payMethodGcashBtn"
                                    wire:click="setPaymentMethod('gcash')" 
                                    class="btn {{ $paymentMethod === 'gcash' ? 'btn-primary' : 'btn-outline-primary' }} py-2 d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-phone"></i> GCASH <span class="badge {{ $paymentMethod === 'gcash' ? 'badge-neutral bg-white text-dark' : 'badge-neutral' }} small ms-1">F8</span>
                            </button>
                        </div>
                    </div>

                    <!-- Cash Payment Section -->
                    @if ($paymentMethod === 'cash')
                        <div class="mb-2">
                            <label for="tenderedInput" class="tracking-wide-caps mb-1 d-block">
                                Amount Tendered (₱) <span class="text-muted fw-normal lowercase ms-1">[Enter to Pay]</span>
                            </label>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-white fw-bold text-dark border-end-0">₱</span>
                                <input type="number" 
                                       id="tenderedInput" 
                                       wire:model.live.debounce.150ms="amountTendered" 
                                       wire:keydown.enter="checkout"
                                       class="form-control form-control-lg fw-bold border-start-0 ps-0 tabular-nums" 
                                       placeholder="0.00" 
                                       step="any" 
                                       min="0">
                            </div>

                            <!-- Quick Cash Buttons -->
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <button type="button" wire:click="setQuickCash('exact')" class="btn btn-sm quick-cash-btn flex-fill">Exact</button>
                                <button type="button" wire:click="setQuickCash('20')" class="btn btn-sm quick-cash-btn flex-fill">+20</button>
                                <button type="button" wire:click="setQuickCash('50')" class="btn btn-sm quick-cash-btn flex-fill">+50</button>
                                <button type="button" wire:click="setQuickCash('100')" class="btn btn-sm quick-cash-btn flex-fill">+100</button>
                                <button type="button" wire:click="setQuickCash('500')" class="btn btn-sm quick-cash-btn flex-fill">+500</button>
                                <button type="button" wire:click="setQuickCash('1000')" class="btn btn-sm quick-cash-btn flex-fill">+1000</button>
                            </div>
                        </div>

                        <!-- Live Change Display -->
                        <div class="d-flex justify-content-between align-items-center p-2 mb-3 bg-white rounded border">
                            <span class="tracking-wide-caps">Change Due:</span>
                            @php
                                $tenderedFloat = (float)$amountTendered;
                            @endphp
                            @if ($tenderedFloat >= $total && $total > 0)
                                <span class="fw-bold text-dark fs-5 tabular-nums">₱{{ number_format($change, 2) }}</span>
                            @elseif ($tenderedFloat > 0 && $tenderedFloat < $total)
                                <span class="fw-bold text-danger fs-6 tabular-nums">Short: ₱{{ number_format($total - $tenderedFloat, 2) }}</span>
                            @else
                                <span class="fw-bold text-muted fs-5 tabular-nums">₱0.00</span>
                            @endif
                        </div>
                    @else
                        <!-- GCash Digital Payment Card with QR -->
                        <div class="card border shadow-sm mb-3 overflow-hidden">
                            <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-phone"></i>
                                    <span class="fw-semibold small">GCash QR Stand</span>
                                </div>
                                <span class="badge badge-neutral">Scan to Pay</span>
                            </div>
                            <div class="card-body p-3 text-center bg-white">
                                <div class="bg-white p-2 d-inline-block rounded border mb-2">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode('GCASH://PAY?merchant=CANTEEN_DAWN&amount=' . number_format($total, 2, '.', '')) }}" 
                                         alt="GCash QR Code" 
                                         class="img-fluid" 
                                         style="width: 120px; height: 120px;"
                                         onerror="this.onerror=null; this.src='data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'120\' height=\'120\' viewBox=\'0 0 100 100\'><rect width=\'100\' height=\'100\' fill=\'%23f8f9fa\'/><text x=\'50\' y=\'55\' font-size=\'10\' text-anchor=\'middle\' fill=\'%230f172a\'>GCash QR</text></svg>'">
                                </div>
                                
                                <div class="fw-semibold text-dark small">CANTEEN DAWN</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Account: <strong>0917-888-DAWN</strong></div>
                                
                                <div class="badge badge-neutral fs-6 mt-2 px-3 py-1 tabular-nums">
                                    ₱{{ number_format($total, 2) }}
                                </div>

                                <div class="mt-2 text-muted" style="font-size: 0.72rem;">
                                    Exact amount recorded automatically. Zero change.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Checkout Button -->
                    @php
                        $canCheckout = !empty($cart) && $total > 0 && ($paymentMethod === 'gcash' || (float)$amountTendered >= $total);
                    @endphp
                    <button type="button" 
                            wire:click="checkout" 
                            wire:loading.attr="disabled" 
                            class="btn btn-primary w-100 btn-touch shadow-sm d-flex align-items-center justify-content-center gap-2" 
                            {{ !$canCheckout ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="checkout">
                            <i class="bi bi-check2-circle fs-5"></i> Complete Order
                        </span>
                        <span wire:loading wire:target="checkout">
                            <span class="spinner-border spinner-border-sm me-2"></span> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         STEP 1: SEAMLESS RECEIPT & NEXT CUSTOMER MODAL
         =================================================================== -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 440px;">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white py-2.5 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <h6 class="modal-title fw-bold mb-0 text-white" id="receiptModalLabel">Order Completed Successfully</h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" wire:click="closeReceiptModal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-3 bg-light">
                    @if ($completedOrder)
                        <div id="modalReceiptArea" class="receipt-wrapper p-3 bg-white border rounded shadow-sm mx-auto">
                            <!-- Store Header -->
                            <div class="text-center mb-2">
                                <div class="receipt-title fw-bold text-dark fs-5">CANTEEN DAWN</div>
                                <div class="small text-muted text-uppercase" style="font-size: 0.72rem;">School Canteen &bull; Point of Sale</div>
                                <div class="small text-muted" style="font-size: 0.72rem;">Manila, Philippines</div>
                            </div>

                            <div class="receipt-divider my-2"></div>

                            <!-- Details -->
                            <div class="small lh-sm mb-2" style="font-size: 0.8rem;">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Receipt No:</span>
                                    <span class="fw-bold">#ORD-{{ str_pad($completedOrder->order_id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Date & Time:</span>
                                    <span>{{ $completedOrder->order_date ? $completedOrder->order_date->format('M d, Y h:i A') : now()->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Cashier:</span>
                                    <span>{{ $completedOrder->user->name ?? 'Cashier' }} (ID: {{ $completedOrder->idno }})</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Customer:</span>
                                    <span>{{ $completedOrder->customer_name ?: 'Walk-in' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status:</span>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase">{{ $completedOrder->status }}</span>
                                </div>
                            </div>

                            <div class="receipt-divider my-2"></div>

                            <!-- Items Table -->
                            <table class="w-100 receipt-table small mb-2" style="font-size: 0.8rem;">
                                <thead>
                                    <tr class="border-bottom border-dark">
                                        <th class="text-start pb-1">Item</th>
                                        <th class="text-center pb-1">Qty</th>
                                        <th class="text-end pb-1">Price</th>
                                        <th class="text-end pb-1">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($completedOrder->orderItems as $item)
                                        <tr>
                                            <td class="text-start pe-1">{{ $item->product->product_name ?? 'Product #' . $item->product_id }}</td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-end fw-semibold">{{ number_format($item->line_total ?? ($item->unit_price * $item->quantity), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="receipt-divider my-2"></div>

                            <!-- Financial Summary -->
                            <div class="small lh-base" style="font-size: 0.82rem;">
                                <div class="d-flex justify-content-between fs-6 fw-bold text-dark my-1">
                                    <span>TOTAL AMOUNT:</span>
                                    <span class="text-success">₱{{ number_format($completedOrder->total_amount, 2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between text-muted">
                                    <span>Payment Method:</span>
                                    <span class="text-uppercase fw-semibold text-dark">{{ $completedOrder->payment->payment_method ?? 'cash' }}</span>
                                </div>

                                <div class="d-flex justify-content-between text-muted">
                                    <span>Amount Paid:</span>
                                    <span class="fw-semibold text-dark">₱{{ number_format($completedOrder->payment->amount_paid ?? $completedOrder->total_amount, 2) }}</span>
                                </div>

                                <div class="d-flex justify-content-between text-muted">
                                    <span>Change Given:</span>
                                    <span class="fw-bold text-primary">₱{{ number_format($completedOrder->payment->change_amount ?? 0, 2) }}</span>
                                </div>
                            </div>

                            <div class="receipt-divider my-2"></div>

                            <!-- Receipt Verification QR Code -->
                            <div class="text-center my-2">
                                <div class="bg-white p-1 d-inline-block border">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode(route('receipt.show', $completedOrder->order_id)) }}" 
                                         alt="Receipt QR" 
                                         style="width: 95px; height: 95px;" 
                                         class="img-fluid"
                                         onerror="this.style.display='none'">
                                </div>
                                <div class="text-muted mt-1" style="font-size: 0.68rem;">
                                    Scan to verify digital receipt<br>
                                    <span class="font-monospace text-secondary">#ORD-{{ str_pad($completedOrder->order_id, 5, '0', STR_PAD_LEFT) }}</span>
                                </div>
                            </div>

                            <div class="receipt-divider my-2"></div>

                            <div class="text-center text-muted small mt-2" style="font-size: 0.72rem;">
                                <p class="mb-0 fw-bold">THANK YOU FOR YOUR PURCHASE!</p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <span class="spinner-border text-primary"></span>
                            <p class="text-muted small mt-2">Loading receipt...</p>
                        </div>
                    @endif

                    <!-- Printing & Full page options -->
                    <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="autoPrintReceipt">
                            <label class="form-check-label small text-muted" for="autoPrintReceipt">
                                Auto-print on checkout
                            </label>
                        </div>
                        @if ($completedOrder)
                            <a href="{{ route('receipt.show', $completedOrder->order_id) }}" target="_blank" class="small text-decoration-none">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Full page view
                            </a>
                        @endif
                    </div>
                </div>

                <div class="modal-footer bg-white border-top p-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary d-flex align-items-center gap-1" onclick="printReceiptModal()">
                        <i class="bi bi-printer-fill"></i> Print Thermal Receipt
                    </button>
                    <button type="button" 
                            class="btn btn-success d-flex align-items-center gap-1 fw-bold px-4" 
                            data-bs-dismiss="modal" 
                            wire:click="closeReceiptModal">
                        Next Customer <i class="bi bi-arrow-right-circle-fill"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         KEYBOARD SHORTCUTS REFERENCE MODAL
         =================================================================== -->
    <div class="modal fade" id="shortcutsModal" tabindex="-1" aria-labelledby="shortcutsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow border-0">
                <div class="modal-header bg-dark text-white py-2 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-keyboard-fill fs-5 text-warning"></i>
                        <h6 class="modal-title fw-bold mb-0" id="shortcutsModalLabel">Cashier POS Keyboard Shortcuts</h6>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 bg-light">
                    <p class="text-muted small mb-3">Operate the canteen cashier terminal quickly without reaching for the mouse:</p>
                    <div class="list-group list-group-flush shadow-sm rounded overflow-hidden">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                            <div>
                                <i class="bi bi-search text-primary me-2"></i>
                                <span class="fw-semibold small">Focus Search Menu Bar</span>
                            </div>
                            <div>
                                <kbd class="bg-primary text-white px-2 py-1">F2</kbd> <span class="text-muted small">or</span> <kbd class="bg-light text-dark border px-2 py-1">/</kbd>
                            </div>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                            <div>
                                <i class="bi bi-cash-stack text-success me-2"></i>
                                <span class="fw-semibold small">Select Cash & Focus Tendered</span>
                            </div>
                            <kbd class="bg-success text-white px-2 py-1">F4</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                            <div>
                                <i class="bi bi-phone-fill text-primary me-2"></i>
                                <span class="fw-semibold small">Select GCash Digital Payment</span>
                            </div>
                            <kbd class="bg-primary text-white px-2 py-1">F8</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                            <div>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <span class="fw-semibold small">Submit / Complete Order</span>
                            </div>
                            <kbd class="bg-dark text-white px-2 py-1">Enter</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                            <div>
                                <i class="bi bi-x-circle text-danger me-2"></i>
                                <span class="fw-semibold small">Close Modals / Dismiss</span>
                            </div>
                            <kbd class="bg-secondary text-white px-2 py-1">Esc</kbd>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-3 py-2 bg-white">
                            <div>
                                <i class="bi bi-question-circle text-secondary me-2"></i>
                                <span class="fw-semibold small">Show This Shortcuts Help</span>
                            </div>
                            <div>
                                <kbd class="bg-secondary text-white px-2 py-1">F1</kbd> <span class="text-muted small">or</span> <kbd class="bg-light text-dark border px-2 py-1">?</kbd>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-2 bg-white border-top">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // =========================================================================
    // WEB AUDIO API SOUND SYNTHESIS (Zero External Audio Dependencies)
    // =========================================================================
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    let posAudioCtx = null;

    function isPosSoundEnabled() {
        const toggle = document.getElementById('soundEffectsToggle');
        return toggle ? toggle.checked : true;
    }

    function getPosAudioContext() {
        if (!posAudioCtx && AudioContextClass) {
            posAudioCtx = new AudioContextClass();
        }
        if (posAudioCtx && posAudioCtx.state === 'suspended') {
            posAudioCtx.resume();
        }
        return posAudioCtx;
    }

    // Cashier scanner chirp on item addition or quantity change
    window.playScannerBeep = function() {
        if (!isPosSoundEnabled()) return;
        try {
            const ctx = getPosAudioContext();
            if (!ctx) return;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // 880Hz crisp beep
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.08);
        } catch (e) {
            console.warn('Audio synthesis error:', e);
        }
    };

    // Melodious two-tone chime upon checkout completion
    window.playSuccessChime = function() {
        if (!isPosSoundEnabled()) return;
        try {
            const ctx = getPosAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;

            // Note 1: E5 (659Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'triangle';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.15, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.22);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.22);

            // Note 2: A5 (880Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'triangle';
            osc2.frequency.setValueAtTime(880, now + 0.12);
            gain2.gain.setValueAtTime(0.18, now + 0.12);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.40);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.12);
            osc2.stop(now + 0.40);
        } catch (e) {
            console.warn('Chime synthesis error:', e);
        }
    };

    // =========================================================================
    // SEAMLESS THERMAL RECEIPT PRINTING
    // =========================================================================
    function printReceiptModal() {
        const receiptContent = document.getElementById('modalReceiptArea');
        if (!receiptContent) return;

        const printFrame = document.createElement('iframe');
        printFrame.style.position = 'fixed';
        printFrame.style.right = '0';
        printFrame.style.bottom = '0';
        printFrame.style.width = '0';
        printFrame.style.height = '0';
        printFrame.style.border = '0';
        document.body.appendChild(printFrame);

        const doc = printFrame.contentWindow.document;
        doc.open();
        doc.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Receipt</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    @page { size: 80mm auto; margin: 3mm; }
                    body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 5px; color: #000; }
                    .receipt-divider { border-top: 1px dashed #444; margin: 6px 0; }
                    table { width: 100%; border-collapse: collapse; font-size: 11px; }
                    th, td { padding: 2px 0; }
                    .text-end { text-align: right; }
                    .text-center { text-align: center; }
                    .fw-bold { font-weight: bold; }
                    .badge { border: 1px solid #000; padding: 1px 4px; font-size: 9px; }
                </style>
            </head>
            <body onload="window.print(); setTimeout(() => { window.frameElement.remove(); }, 1200);">
                ${receiptContent.innerHTML}
            </body>
            </html>
        `);
        doc.close();
    }

    // =========================================================================
    // KEYBOARD SHORTCUTS & EVENT LISTENERS
    // =========================================================================
    document.addEventListener('keydown', function(e) {
        const activeEl = document.activeElement;
        const isInput = activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT');

        // F1 or ? (outside input): Show shortcuts modal
        if (e.key === 'F1' || (e.key === '?' && !isInput)) {
            e.preventDefault();
            const modalEl = document.getElementById('shortcutsModal');
            if (modalEl) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            return;
        }

        // F2 or / (outside input): Focus POS search box
        if (e.key === 'F2' || (e.key === '/' && !isInput)) {
            e.preventDefault();
            const searchInput = document.getElementById('posSearchInput');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
            return;
        }

        // F4: Cash payment & focus amount tendered
        if (e.key === 'F4') {
            e.preventDefault();
            const cashBtn = document.getElementById('payMethodCashBtn');
            if (cashBtn) cashBtn.click();
            setTimeout(() => {
                const tendered = document.getElementById('tenderedInput');
                if (tendered) {
                    tendered.focus();
                    tendered.select();
                }
            }, 120);
            return;
        }

        // F8: GCash payment
        if (e.key === 'F8') {
            e.preventDefault();
            const gcashBtn = document.getElementById('payMethodGcashBtn');
            if (gcashBtn) gcashBtn.click();
            return;
        }

        // Escape: Close modals or blur inputs
        if (e.key === 'Escape') {
            const receiptModalEl = document.getElementById('receiptModal');
            if (receiptModalEl && receiptModalEl.classList.contains('show')) {
                const inst = bootstrap.Modal.getInstance(receiptModalEl);
                if (inst) inst.hide();
                return;
            }
            const shortcutsModalEl = document.getElementById('shortcutsModal');
            if (shortcutsModalEl && shortcutsModalEl.classList.contains('show')) {
                const inst = bootstrap.Modal.getInstance(shortcutsModalEl);
                if (inst) inst.hide();
                return;
            }
            if (isInput) {
                activeEl.blur();
            }
        }
    });

    // =========================================================================
    // INITIALIZATION & LIVEWIRE HOOKS
    // =========================================================================
    document.addEventListener('livewire:init', () => {
        // Auto-print preference persistence
        const autoPrintCheckbox = document.getElementById('autoPrintReceipt');
        if (autoPrintCheckbox) {
            autoPrintCheckbox.checked = localStorage.getItem('canteen_autoprint') === 'true';
            autoPrintCheckbox.addEventListener('change', (e) => {
                localStorage.setItem('canteen_autoprint', e.target.checked);
            });
        }

        // Sound effects preference persistence
        const soundToggle = document.getElementById('soundEffectsToggle');
        if (soundToggle) {
            soundToggle.checked = localStorage.getItem('canteen_sound_enabled') !== 'false';
            soundToggle.addEventListener('change', (e) => {
                localStorage.setItem('canteen_sound_enabled', e.target.checked);
            });
        }

        // Handle completed order event
        Livewire.on('order-completed', (data) => {
            // Play melodious success chime
            window.playSuccessChime();

            // Display receipt modal
            const modalEl = document.getElementById('receiptModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
                if (autoPrintCheckbox && autoPrintCheckbox.checked) {
                    setTimeout(() => {
                        printReceiptModal();
                    }, 500);
                }
            }
        });
    });
</script>

