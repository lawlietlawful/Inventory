<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InventoryProduct;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of menu items with search and category filters
     */
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with(['category', 'inventoryProduct'])->withCount('orderItems');

        if ($request->filled('category')) {
            $query->where('category_id', (int)$request->category);
        }

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            $query->where('name', 'ilike', $term);
        }

        $products = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Show form to create a new menu item
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created menu item and link to inventory
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'  => ['required', Rule::exists(Category::class, 'id')],
            'product_name' => 'required|string|max:150',
            'price'        => 'required|numeric|min:0',
            'stock_qty'    => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $isAvailable = $request->has('is_available');

        // Create in inventory.products
        $sku = 'CN-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['product_name']), 0, 3)) . '-' . rand(100, 999);
        $invProduct = InventoryProduct::create([
            'sku'              => $sku,
            'name'             => $validated['product_name'],
            'category_id'      => 4, // Meals/General
            'cost_price'       => max(0.00, $validated['price'] * 0.7),
            'selling_price'    => $validated['price'],
            'quantity_on_hand' => (int)$validated['stock_qty'],
            'reorder_level'    => 10,
            'status'           => 'active',
        ]);

        // Create in canteen.menu_items
        Product::create([
            'category_id'  => $validated['category_id'],
            'product_id'   => $invProduct->id,
            'name'         => $validated['product_name'],
            'price'        => $validated['price'],
            'is_available' => $isAvailable,
        ]);

        return redirect()->route('products.index')
            ->with('success', "Menu item \"{$validated['product_name']}\" added successfully.");
    }

    /**
     * Show form to edit an existing menu item
     */
    public function edit(Product $product): View
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update an existing menu item
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id'  => ['required', Rule::exists(Category::class, 'id')],
            'product_name' => 'required|string|max:150',
            'price'        => 'required|numeric|min:0',
            'stock_qty'    => 'required|integer|min:0',
            'is_available' => 'nullable|boolean',
        ]);

        $product->update([
            'category_id'  => $validated['category_id'],
            'name'         => $validated['product_name'],
            'price'        => $validated['price'],
            'is_available' => $request->has('is_available'),
        ]);

        if ($product->inventoryProduct) {
            $product->inventoryProduct->update([
                'name'             => $validated['product_name'],
                'selling_price'    => $validated['price'],
                'quantity_on_hand' => (int)$validated['stock_qty'],
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', "Menu item \"{$product->name}\" updated successfully.");
    }

    /**
     * Remove the specified menu item (Prevent delete if sale_items exist!)
     */
    public function destroy(Product $product): RedirectResponse
    {
        if ($product->orderItems()->exists()) {
            return back()->with('warning', "Cannot delete \"{$product->name}\" because it is referenced in past sales records. You can toggle it to 'Unavailable' instead.");
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Menu item \"{$name}\" has been deleted.");
    }

    /**
     * Toggle availability status
     */
    public function toggleAvailability(Product $product): RedirectResponse
    {
        $product->update([
            'is_available' => !$product->is_available,
        ]);

        $status = $product->is_available ? 'Available' : 'Unavailable';
        return back()->with('success', "\"{$product->name}\" is now marked as {$status}.");
    }

    /**
     * Restock inventory for a menu item
     */
    public function restock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'add_qty' => 'required|integer|min:1',
        ]);

        $qty = (int)$validated['add_qty'];

        if ($product->inventoryProduct) {
            $product->inventoryProduct->increment('quantity_on_hand', $qty);

            DB::table('inventory.stock_movements')->insert([
                'product_id'     => $product->inventoryProduct->id,
                'movement_type'  => 'in',
                'quantity'       => $qty,
                'reference_type' => 'manual_restock',
                'reference_id'   => null,
                'reason'         => "Restocked from Canteen POS",
                'performed_by'   => 1,
                'created_at'     => now(),
            ]);
        }

        return back()->with('success', "Added +{$qty} units to \"{$product->name}\". Current stock: {$product->stock_qty}.");
    }
}
