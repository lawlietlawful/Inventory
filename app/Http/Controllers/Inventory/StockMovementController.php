<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::with(['product.category']);

        // Filter by movement type
        if ($type = $request->input('movement_type')) {
            $query->where('movement_type', $type);
        }

        // Filter by product
        if ($prodId = $request->input('product_id')) {
            $query->where('product_id', $prodId);
        }

        // Filter by date range
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        $products = InventoryProduct::where('status', 'active')->orderBy('name', 'asc')->get();

        // Summary counts
        $totalIn = StockMovement::where('movement_type', 'in')->sum('quantity');
        $totalOut = abs((int) StockMovement::where('movement_type', 'out')->sum('quantity'));
        $totalMovementsCount = StockMovement::count();

        return view('inventory.movements.index', compact('movements', 'products', 'totalIn', 'totalOut', 'totalMovementsCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id'    => 'required|integer|exists:inventory.products,id',
            'movement_type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'quantity'      => 'required|integer',
            'reason'        => 'required|string|max:255',
            'reference_type'=> 'nullable|string|max:50',
        ]);

        $productId = (int)$validated['product_id'];
        $movementType = $validated['movement_type'];
        $rawQty = (int)$validated['quantity'];
        $reason = $validated['reason'];
        $refType = $validated['reference_type'] ?: 'manual';

        try {
            DB::transaction(function () use ($productId, $movementType, $rawQty, $reason, $refType) {
                // Lock row for update
                $product = InventoryProduct::where('id', $productId)->lockForUpdate()->firstOrFail();

                $currentQty = (int)$product->quantity_on_hand;
                $dbQty = 0;
                $newStock = $currentQty;

                if ($movementType === 'in') {
                    if ($rawQty <= 0) {
                        throw new \Exception("Stock-in quantity must be greater than zero.");
                    }
                    $dbQty = $rawQty; // Must be positive (> 0)
                    $newStock = $currentQty + $rawQty;

                } elseif ($movementType === 'out') {
                    $positiveAmount = abs($rawQty);
                    if ($positiveAmount <= 0) {
                        throw new \Exception("Stock-out quantity must be greater than zero.");
                    }
                    if ($currentQty < $positiveAmount) {
                        throw new \Exception("Insufficient stock on hand ({$currentQty} {$product->unit}). Cannot deduct {$positiveAmount} {$product->unit}.");
                    }
                    $dbQty = -$positiveAmount; // Constraint: out requires quantity < 0
                    $newStock = $currentQty - $positiveAmount;

                } elseif ($movementType === 'adjustment') {
                    // For adjustment, rawQty is either the difference or user directly provides delta
                    if ($rawQty === 0) {
                        throw new \Exception("Adjustment quantity cannot be zero.");
                    }
                    if ($currentQty + $rawQty < 0) {
                        throw new \Exception("Stock adjustment would result in negative quantity on hand ({$currentQty} + {$rawQty}).");
                    }
                    $dbQty = $rawQty; // Constraint: adjustment requires quantity <> 0
                    $newStock = $currentQty + $rawQty;
                }

                // Update product quantity on hand
                $product->quantity_on_hand = $newStock;
                $product->save();

                // Insert into inventory.stock_movements
                $performedBy = (auth()->check() && auth()->user() instanceof \App\Models\InventoryUser)
                    ? auth()->id()
                    : (\App\Models\InventoryUser::where('username', '!=', 'canteen_system')->value('id') ?? 1);

                StockMovement::create([
                    'product_id'     => $productId,
                    'movement_type'  => $movementType,
                    'quantity'       => $dbQty,
                    'reference_type' => $refType,
                    'reference_id'   => null,
                    'reason'         => $reason,
                    'performed_by'   => $performedBy,
                    'created_at'     => now(),
                ]);
            });

            return redirect()->route('inventory.movements.index')
                ->with('success', "Stock movement for product recorded successfully.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Failed to record movement: " . $e->getMessage());
        }
    }
}
