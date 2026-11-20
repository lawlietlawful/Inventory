<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryProduct;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items.product']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        $orders = $query->orderBy('order_date', 'desc')->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();

        $pendingCount = PurchaseOrder::where('status', 'pending')->count();
        $receivedCount = PurchaseOrder::where('status', 'received')->count();

        return view('inventory.purchase_orders.index', compact('orders', 'suppliers', 'pendingCount', 'receivedCount'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->orderBy('name', 'asc')->get();
        $products = InventoryProduct::where('status', 'active')->orderBy('name', 'asc')->get();

        return view('inventory.purchase_orders.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'   => 'required|integer|exists:inventory.suppliers,id',
            'order_date'    => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes'         => 'nullable|string|max:1000',
            'items'         => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:inventory.products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.unit_cost'  => 'required|numeric|min:0',
        ]);

        $po = DB::transaction(function () use ($validated) {
            // Generate unique PO Number: PO-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $rand = strtoupper(Str::random(4));
            $poNumber = "PO-{$datePrefix}-{$rand}";

            // Ensure uniqueness
            while (PurchaseOrder::where('po_number', $poNumber)->exists()) {
                $poNumber = "PO-{$datePrefix}-" . strtoupper(Str::random(4));
            }

            $createdBy = (auth()->check() && auth()->user() instanceof \App\Models\InventoryUser)
                ? auth()->id()
                : (\App\Models\InventoryUser::where('username', '!=', 'canteen_system')->value('id') ?? 1);

            $order = PurchaseOrder::create([
                'po_number'     => $poNumber,
                'supplier_id'   => $validated['supplier_id'],
                'order_date'    => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'status'        => 'pending',
                'notes'         => $validated['notes'] ?? null,
                'created_by'    => $createdBy,
            ]);

            // Track inserted product IDs to prevent duplicates in the same PO
            $seenProductIds = [];

            foreach ($validated['items'] as $item) {
                $pId = (int)$item['product_id'];
                if (in_array($pId, $seenProductIds, true)) {
                    continue; // Skip duplicate line items
                }
                $seenProductIds[] = $pId;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'product_id'        => $pId,
                    'quantity'          => (int)$item['quantity'],
                    'unit_cost'         => (float)$item['unit_cost'],
                ]);
            }

            return $order;
        });

        return redirect()->route('inventory.purchase-orders.show', $po->id)
            ->with('success', "Purchase Order <strong>{$po->po_number}</strong> created successfully.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product.category']);

        return view('inventory.purchase_orders.show', compact('purchaseOrder'));
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()->with('error', "Purchase Order {$purchaseOrder->po_number} is already marked as '{$purchaseOrder->status}'.");
        }

        try {
            DB::transaction(function () use ($purchaseOrder) {
                // Lock PO
                $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->firstOrFail();
                $po->status = 'received';
                $po->save();

                // Increment stock on hand and record stock movement for each item
                $items = PurchaseOrderItem::where('purchase_order_id', $po->id)->get();

                foreach ($items as $item) {
                    $product = InventoryProduct::where('id', $item->product_id)->lockForUpdate()->first();
                    if ($product) {
                        $product->quantity_on_hand += (int)$item->quantity;
                        $product->cost_price = (float)$item->unit_cost; // Update latest cost price
                        $product->save();

                        $receivedBy = (auth()->check() && auth()->user() instanceof \App\Models\InventoryUser)
                            ? auth()->id()
                            : (\App\Models\InventoryUser::where('username', '!=', 'canteen_system')->value('id') ?? 1);

                        StockMovement::create([
                            'product_id'     => $product->id,
                            'movement_type'  => 'in',
                            'quantity'       => (int)$item->quantity, // Constraint: in requires quantity > 0
                            'reference_type' => 'purchase_order',
                            'reference_id'   => $po->id,
                            'reason'         => "Received PO #{$po->po_number}",
                            'performed_by'   => $receivedBy,
                            'created_at'     => now(),
                        ]);
                    }
                }
            });

            return redirect()->route('inventory.purchase-orders.show', $purchaseOrder->id)
                ->with('success', "Purchase Order <strong>{$purchaseOrder->po_number}</strong> received. Warehouse stock levels and movements have been automatically updated.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Failed to receive order: " . $e->getMessage());
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'pending') {
            return redirect()->back()->with('error', "Only 'pending' purchase orders can be cancelled.");
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return redirect()->route('inventory.purchase-orders.show', $purchaseOrder->id)
            ->with('info', "Purchase Order <strong>{$purchaseOrder->po_number}</strong> has been cancelled.");
    }
}
