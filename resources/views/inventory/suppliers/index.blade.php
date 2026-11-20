@extends('layouts.app')

@section('title', 'Suppliers Directory')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill bg-info-subtle text-info-emphasis px-2 py-1 small fw-semibold">Procurement Partners</span>
            </div>
            <h2 class="fw-bold mb-1 mt-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Suppliers Directory</h2>
            <p class="text-muted mb-0 small">Vendors, distributors, and ingredient partners providing goods to the canteen and warehouse.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="bi bi-plus-circle"></i> Add New Supplier
            </button>
            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-text"></i> Purchase Orders
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('inventory.suppliers.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by company name, contact person, or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active Suppliers</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1">Filter</button>
                    @if (request()->hasAny(['search', 'status']))
                        <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-outline-secondary btn-sm" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Suppliers Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 px-3 d-flex justify-content-between align-items-center border-bottom">
            <div class="fw-semibold text-dark">
                Suppliers List <span class="badge bg-light text-secondary border ms-1">{{ $suppliers->total() }} partners</span>
            </div>
            <div class="small text-muted">
                Showing {{ $suppliers->firstItem() ?? 0 }} - {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }}
            </div>
        </div>
        <div class="card-body p-0">
            @if ($suppliers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-truck fs-1 d-block mb-2 text-secondary"></i>
                    No suppliers found matching your query.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Company Name</th>
                                <th>Contact Person</th>
                                <th>Phone & Email</th>
                                <th>Office Address</th>
                                <th class="text-center">Products</th>
                                <th class="text-center">POs</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $sup)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $sup->name }}</div>
                                        <div class="text-muted font-monospace small" style="font-size: 0.72rem;">ID: #{{ $sup->id }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark">{{ $sup->contact_person ?? 'Not specified' }}</div>
                                    </td>
                                    <td>
                                        @if ($sup->phone)
                                            <div class="text-dark small"><i class="bi bi-telephone me-1 text-muted"></i>{{ $sup->phone }}</div>
                                        @endif
                                        @if ($sup->email)
                                            <div class="text-muted small"><i class="bi bi-envelope me-1 text-muted"></i>{{ $sup->email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small">{{ Str::limit($sup->address ?? '-', 35) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('inventory.products.index', ['supplier_id' => $sup->id]) }}" class="badge bg-light text-primary border text-decoration-none">
                                            {{ $sup->products_count }} SKUs
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('inventory.purchase-orders.index', ['supplier_id' => $sup->id]) }}" class="badge bg-light text-secondary border text-decoration-none">
                                            {{ $sup->purchase_orders_count }} POs
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        @if ($sup->status === 'active')
                                            <span class="badge bg-success-subtle text-success" style="font-size: 0.72rem;">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <!-- Edit Modal Trigger -->
                                            <button type="button" 
                                                    class="btn btn-outline-primary py-1 px-2" 
                                                    title="Edit Supplier"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSupplierModal" 
                                                    data-id="{{ $sup->id }}" 
                                                    data-name="{{ $sup->name }}" 
                                                    data-contact="{{ $sup->contact_person }}" 
                                                    data-phone="{{ $sup->phone }}" 
                                                    data-email="{{ $sup->email }}" 
                                                    data-address="{{ $sup->address }}" 
                                                    data-status="{{ $sup->status }}">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <!-- Toggle Status -->
                                            <form method="POST" action="{{ route('inventory.suppliers.destroy', $sup->id) }}" class="d-inline" onsubmit="return confirm('Toggle status for {{ $sup->name }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-{{ $sup->status === 'active' ? 'danger' : 'success' }} py-1 px-2" title="{{ $sup->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bi bi-power"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if ($suppliers->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Add Supplier Modal -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('inventory.suppliers.store') }}" class="modal-content shadow border-0">
            @csrf
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Register New Supplier</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label for="supName" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="supName" class="form-control" placeholder="e.g. San Miguel Foods, URC" required>
                </div>

                <div class="mb-3">
                    <label for="supContact" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Contact Person</label>
                    <input type="text" name="contact_person" id="supContact" class="form-control" placeholder="e.g. Juan dela Cruz (Account Manager)">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="supPhone" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Phone Number</label>
                        <input type="text" name="phone" id="supPhone" class="form-control" placeholder="+63 9XX XXX XXXX">
                    </div>
                    <div class="col-6">
                        <label for="supEmail" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Email Address</label>
                        <input type="email" name="email" id="supEmail" class="form-control" placeholder="sales@supplier.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="supAddress" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Address / Location</label>
                    <textarea name="address" id="supAddress" class="form-control" rows="2" placeholder="Warehouse address, City"></textarea>
                </div>

                <div class="mb-2">
                    <label for="supStatus" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Status <span class="text-danger">*</span></label>
                    <select name="status" id="supStatus" class="form-select" required>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="editSupplierForm" class="modal-content shadow border-0">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Edit Supplier Details</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label for="editSupName" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="editSupName" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="editSupContact" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Contact Person</label>
                    <input type="text" name="contact_person" id="editSupContact" class="form-control">
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="editSupPhone" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Phone Number</label>
                        <input type="text" name="phone" id="editSupPhone" class="form-control">
                    </div>
                    <div class="col-6">
                        <label for="editSupEmail" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Email Address</label>
                        <input type="email" name="email" id="editSupEmail" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="editSupAddress" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Address / Location</label>
                    <textarea name="address" id="editSupAddress" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-2">
                    <label for="editSupStatus" class="form-label small fw-semibold text-muted text-uppercase" style="font-size: 0.72rem;">Status <span class="text-danger">*</span></label>
                    <select name="status" id="editSupStatus" class="form-select" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Update Supplier</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editSupplierModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const id = btn.getAttribute('data-id');
            document.getElementById('editSupplierForm').action = '/inventory/suppliers/' + id;
            document.getElementById('editSupName').value = btn.getAttribute('data-name');
            document.getElementById('editSupContact').value = btn.getAttribute('data-contact') || '';
            document.getElementById('editSupPhone').value = btn.getAttribute('data-phone') || '';
            document.getElementById('editSupEmail').value = btn.getAttribute('data-email') || '';
            document.getElementById('editSupAddress').value = btn.getAttribute('data-address') || '';
            document.getElementById('editSupStatus').value = btn.getAttribute('data-status');
        });
    }
});
</script>
@endpush
