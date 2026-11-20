@extends('layouts.app')

@section('title', 'Categories')

@section('content')
<div class="container-fluid px-3 px-lg-4 pt-3">
    <!-- Top Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold mb-1" style="font-size: 1.6rem; letter-spacing: -0.02em;">Categories</h2>
            <p class="text-muted mb-0 small">Group menu items into clean categories (e.g. Rice Meals, Snacks, Drinks, Desserts).</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="bi bi-plus-lg"></i> Add Category
            </button>
        </div>
    </div>

    <!-- Categories List Table -->
    <div class="card" style="max-width: 900px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-dark">Menu Categories</span>
            <span class="badge badge-neutral">{{ $categories->count() }} total</span>
        </div>
        <div class="card-body p-0">
            @if ($categories->isEmpty())
                <div class="text-center py-5 text-muted small">
                    No categories found. Click "Add Category" above to create one.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 80px;">ID</th>
                                <th>Category Name</th>
                                <th class="text-center">Associated Products</th>
                                <th class="text-end pe-3" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $cat)
                                <tr>
                                    <td class="ps-3 text-muted small tabular-nums">#{{ $cat->category_id }}</td>
                                    <td class="fw-semibold text-dark">{{ $cat->category_name }}</td>
                                    <td class="text-center">
                                        @if ($cat->products_count > 0)
                                            <a href="{{ route('products.index', ['category' => $cat->category_id]) }}" class="badge badge-neutral text-decoration-none">
                                                {{ $cat->products_count }} item(s) &rarr;
                                            </a>
                                        @else
                                            <span class="badge badge-neutral">0 items</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-primary"
                                                    style="font-size: 0.78rem;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editCategoryModal"
                                                    data-id="{{ $cat->category_id }}"
                                                    data-name="{{ $cat->category_name }}"
                                                    title="Edit category">
                                                Edit
                                            </button>

                                            <form method="POST" action="{{ route('categories.destroy', $cat->category_id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($cat->category_name) }}\'?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-primary text-danger" style="font-size: 0.78rem;" title="Delete category">
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
            @endif
        </div>
    </div>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('categories.store') }}" class="modal-content shadow border-0">
            @csrf
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Add New Category</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label for="addCategoryName" class="tracking-wide-caps mb-1 d-block">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="addCategoryName" name="category_name" placeholder="e.g. Snacks, Rice Meals, Drinks" required autofocus>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Save Category</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Category -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="editCategoryForm" class="modal-content shadow border-0">
            @csrf
            @method('PUT')
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0">Edit Category</h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label for="editCategoryName" class="tracking-wide-caps mb-1 d-block">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editCategoryName" name="category_name" required autofocus>
                </div>
            </div>
            <div class="modal-footer border-top p-2 bg-light">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm fw-semibold">Update Category</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const editModal = document.getElementById('editCategoryModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');

            document.getElementById('editCategoryName').value = name;
            document.getElementById('editCategoryForm').action = '/categories/' + id;
        });
    }
</script>
@endpush
