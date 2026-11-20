<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryProduct;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WarehouseProductController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryProduct::with(['category', 'supplier']);

        // Search by name, SKU, or barcode
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('sku', 'ilike', "%{$search}%")
                  ->orWhere('barcode', 'ilike', "%{$search}%");
            });
        }

        // Filter by category
        if ($catId = $request->input('category_id')) {
            $query->where('category_id', $catId);
        }

        // Filter by supplier
        if ($supId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supId);
        }

        // Filter by stock status
        if ($statusFilter = $request->input('stock_status')) {
            if ($statusFilter === 'low') {
                $query->whereColumn('quantity_on_hand', '<=', 'reorder_level')->where('quantity_on_hand', '>', 0);
            } elseif ($statusFilter === 'out') {
                $query->where('quantity_on_hand', '<=', 0);
            } elseif ($statusFilter === 'normal') {
                $query->whereColumn('quantity_on_hand', '>', 'reorder_level');
            }
        }

        // Filter by active status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $products = $query->orderBy('name', 'asc')->paginate(12)->withQueryString();

        $categories = InventoryCategory::orderBy('name', 'asc')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();

        $lowStockTotal = InventoryProduct::where('status', 'active')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        return view('inventory.products.index', compact('products', 'categories', 'suppliers', 'lowStockTotal'));
    }

    public function create()
    {
        $categories = InventoryCategory::orderBy('name', 'asc')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('inventory.products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku'              => 'required|string|max:50|unique:inventory.products,sku',
            'barcode'          => 'nullable|string|max:100|unique:inventory.products,barcode',
            'name'             => 'required|string|max:255',
            'category_id'      => 'required|integer|exists:inventory.categories,id',
            'supplier_id'      => 'nullable|integer|exists:inventory.suppliers,id',
            'unit'             => 'required|string|max:50',
            'cost_price'       => 'required|numeric|min:0',
            'selling_price'    => 'required|numeric|min:0',
            'quantity_on_hand' => 'required|integer|min:0',
            'reorder_level'    => 'required|integer|min:0',
            'status'           => ['required', Rule::in(['active', 'inactive'])],
            'description'      => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            $product = InventoryProduct::create($validated);

            // If initial stock > 0, log an initial stock movement
            if ($validated['quantity_on_hand'] > 0) {
                $performedBy = (auth()->check() && auth()->user() instanceof \App\Models\InventoryUser)
                    ? auth()->id()
                    : (\App\Models\InventoryUser::where('username', '!=', 'canteen_system')->value('id') ?? 1);

                StockMovement::create([
                    'product_id'     => $product->id,
                    'movement_type'  => 'in',
                    'quantity'       => $validated['quantity_on_hand'],
                    'reference_type' => 'manual',
                    'reference_id'   => null,
                    'reason'         => 'Initial inventory stock intake upon SKU registration',
                    'performed_by'   => $performedBy,
                    'created_at'     => now(),
                ]);
            }
        });

        return redirect()->route('inventory.products.index')->with('success', "Warehouse Product <strong>{$request->name}</strong> ({$request->sku}) was successfully created.");
    }

    public function edit(InventoryProduct $product)
    {
        $categories = InventoryCategory::orderBy('name', 'asc')->get();
        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('inventory.products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, InventoryProduct $product)
    {
        $validated = $request->validate([
            'sku'              => ['required', 'string', 'max:50', Rule::unique('inventory.products', 'sku')->ignore($product->id)],
            'barcode'          => ['nullable', 'string', 'max:100', Rule::unique('inventory.products', 'barcode')->ignore($product->id)],
            'name'             => 'required|string|max:255',
            'category_id'      => 'required|integer|exists:inventory.categories,id',
            'supplier_id'      => 'nullable|integer|exists:inventory.suppliers,id',
            'unit'             => 'required|string|max:50',
            'cost_price'       => 'required|numeric|min:0',
            'selling_price'    => 'required|numeric|min:0',
            'reorder_level'    => 'required|integer|min:0',
            'status'           => ['required', Rule::in(['active', 'inactive'])],
            'description'      => 'nullable|string|max:1000',
        ]);

        $product->update($validated);

        return redirect()->route('inventory.products.index')->with('success', "Warehouse Product <strong>{$product->name}</strong> was updated.");
    }

    public function destroy(InventoryProduct $product)
    {
        // Toggle active status safely to prevent breaking foreign keys in canteen sales or POs
        $newStatus = $product->status === 'active' ? 'inactive' : 'active';
        $product->update(['status' => $newStatus]);

        $label = $newStatus === 'active' ? 'activated' : 'deactivated';
        return redirect()->back()->with('info', "Product <strong>{$product->name}</strong> has been {$label}.");
    }
}
