<?php

namespace App\Http\Controllers;

use App\Models\InventoryProduct;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    /**
     * Display a listing of sales with date, status, and cashier filters
     */
    public function index(Request $request): View
    {
        $cashiers = User::orderBy('name')->get(['id', 'name']);

        $query = Order::with(['user', 'payment', 'orderItems.product'])
            ->withCount('orderItems');

        if ($request->filled('date_from')) {
            $query->whereDate('sold_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sold_at', '<=', $request->date_to);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('cashier') && $request->cashier !== 'all') {
            $query->where('cashier_id', $request->cashier);
        }

        $orders = $query->orderBy('sold_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders', 'cashiers'));
    }

    /**
     * Show order details
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'payment', 'orderItems.product']);
        return view('orders.show', compact('order'));
    }

    /**
     * Void a sale and restore warehouse inventory stock within a transaction
     */
    public function cancel(Order $order): RedirectResponse
    {
        try {
            $restoredCount = 0;

            DB::transaction(function () use ($order, &$restoredCount) {
                // Lock sale for update
                $lockedOrder = Order::where('id', $order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedOrder->status === 'voided') {
                    throw new Exception("Sale #{$order->receipt_no} has already been voided.");
                }

                $cashierEmpId = Auth::user() && Auth::user()->employee_id ? Auth::user()->employee_id : 1;

                // Restore stock for all items
                foreach ($lockedOrder->orderItems as $item) {
                    $menuItem = $item->product;
                    if ($menuItem && $menuItem->product_id) {
                        $invProduct = InventoryProduct::where('id', $menuItem->product_id)
                            ->lockForUpdate()
                            ->first();

                        if ($invProduct) {
                            $invProduct->increment('quantity_on_hand', $item->quantity);
                            $restoredCount += $item->quantity;

                            // Log stock return in Group 2's inventory
                            DB::table('inventory.stock_movements')->insert([
                                'product_id'     => $invProduct->id,
                                'movement_type'  => 'in',
                                'quantity'       => $item->quantity,
                                'reference_type' => 'canteen_sale_void',
                                'reference_id'   => $lockedOrder->id,
                                'reason'         => "Voided Sale #{$lockedOrder->receipt_no}",
                                'performed_by'   => 1,
                                'created_at'     => now(),
                            ]);
                        }
                    }
                }

                // Update status to 'voided' in canteen.sales
                $lockedOrder->update([
                    'status'      => 'voided',
                    'voided_by'   => $cashierEmpId,
                    'voided_at'   => now(),
                    'void_reason' => 'Cashier Void & Refund',
                ]);
            });

            return back()->with('success', "Sale #{$order->receipt_no} was successfully voided. {$restoredCount} item unit(s) were returned to inventory.");

        } catch (Exception $e) {
            return back()->with('danger', "Failed to void sale: " . $e->getMessage());
        }
    }
}
