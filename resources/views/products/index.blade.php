@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Top Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Products</h2>
            <p class="text-muted mb-0 small">Create, restock, and manage cafeteria food and beverage catalog items.</p>
        </div>
        <div>
            <a href="{{ route('products.create') }}" class="btn btn-primary d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="card mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by product name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->category_id }}" {{ request('category') == $cat->category_id ? 'selected' : '' }}>
                                {{ $cat->category_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    @if (request()->hasAny(['search', 'category']))
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark">Menu Items</span>
            <span class="badge badge-neutral">{{ $products->total() }} items</span>
        </div>
        <div class="card-body p-0">
            @if ($products->isEmpty())
                <div class="text-center py-5 text-muted small">
                    No products found matching your search.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">ID</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Inventory</th>
                                <th>Status</th>
                                <th>Order History</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $p)
                                <tr>
                                    <td class="ps-3 text-muted small tabular-nums">#{{ $p->product_id }}</td>
                                    <td class="fw-semibold text-dark">{{ $p->product_name }}</td>
                                    <td>
                                        <span class="badge badge-neutral">{{ $p->category->category_name ?? 'General' }}</span>
                                    </td>
                                    <td class="fw-semibold text-dark tabular-nums">
                                        ₱{{ number_format($p->price, 2) }}
                                    </td>
                                    <td>
                                        @if ($p->stock_qty <= 0)
                                            <span class="badge bg-secondary text-white">0 Out of Stock</span>
                                        @elseif ($p->stock_qty <= 5)
                                            <span class="badge badge-danger-subtle">{{ $p->stock_qty }} Critical</span>
                                        @elseif ($p->stock_qty <= 10)
                                            <span class="badge badge-warning-subtle">{{ $p->stock_qty }} Low</span>
                                        @else
                                            <span class="text-secondary small tabular-nums">{{ $p->stock_qty }} units</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('products.toggle', $p->product_id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm py-0.5 px-2.5 border {{ $p->is_available ? 'bg-light text-dark' : 'bg-light text-muted' }}" style="font-size: 0.76rem;" title="Toggle availability">
                                                {!! $p->is_available ? '<i class="bi bi-check-circle text-success me-1"></i>Available' : '<i class="bi bi-dash-circle text-muted me-1"></i>Unavailable' !!}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="small text-muted tabular-nums">
                                        {{ number_format($p->order_items_count) }} orders
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-primary" 
                                                    style="font-size: 0.78rem;"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#restockModal" 
                                                    data-id="{{ $p->product_id }}" 
                                                    data-name="{{ $p->product_name }}" 
                                                    data-stock="{{ $p->stock_qty }}" 
                                                    title="Add stock quantity">
                                                Restock
                                            </button>

                                            <a href="{{ route('products.edit', $p->product_id) }}" class="btn btn-outline-primary" style="font-size: 0.78rem;" title="Edit product">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <form method="POST" action="{{ route('products.destroy', $p->product_id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($p->product_name) }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-primary text-danger" style="font-size: 0.78rem;" title="Delete product">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="p-3 border-top">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div class="modal fade" id="restockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <form method="POST" id="restockForm" class="modal-content shadow border-0">
            @csrf
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Restock Item</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <p class="small text-muted mb-1">Item: <strong id="restockName" class="text-dark"></strong></p>
                <p class="small text-muted mb-3">Current Inventory: <span id="currentStock" class="fw-semibold text-dark"></span></p>

                <div class="mb-3">
                    <label for="add_qty" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Quantity to Add</label>
                    <input type="number" class="form-control form-control-lg fw-bold text-center tabular-nums" id="add_qty" name="add_qty" min="1" value="10" required autofocus>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Add to Stock</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const restockModal = document.getElementById('restockModal');
    if (restockModal) {
        restockModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const stock = button.getAttribute('data-stock');

            document.getElementById('restockName').textContent = name;
            document.getElementById('currentStock').textContent = stock + ' units';
            document.getElementById('restockForm').action = '/products/' + id + '/restock';
            document.getElementById('add_qty').value = 10;
        });
    }
</script>
@endpush
