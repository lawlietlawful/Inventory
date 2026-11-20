<?php

namespace App\Services;

use App\Models\InventoryProduct;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shift;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * Execute POS order checkout inside a single atomic database transaction.
     * Integrates with Group 2's inventory.products and logs stock movements.
     *
     * @param  array   $items        Array of ['product_id' => int, 'quantity' => int]
     * @param  string  $paymentMethod 'cash' or 'gcash'
     * @param  float   $amountPaid   Amount tendered
     * @param  int|string $cashierIdentifier Cashier ID or username
     * @param  string|null $customerName Optional customer name/notes
     * @return Order
     * @throws Exception
     */
    public function checkout(
        array $items,
        string $paymentMethod,
        float $amountPaid,
        $cashierIdentifier,
        ?string $customerName = null
    ): Order {
        if (empty($items)) {
            throw new Exception("Cart is empty. Please add products before checking out.");
        }

        $paymentMethod = strtolower(trim($paymentMethod));
        if (!in_array($paymentMethod, ['cash', 'gcash'], true)) {
            throw new Exception("Invalid payment method. Only Cash and GCash are accepted.");
        }

        return DB::transaction(function () use ($items, $paymentMethod, $amountPaid, $cashierIdentifier, $customerName) {
            // 1. Resolve Cashier
            $cashier = User::where('id', $cashierIdentifier)
                ->orWhere('username', $cashierIdentifier)
                ->first();

            $cashierEmpId = $cashier && $cashier->employee_id ? $cashier->employee_id : 1;

            // 2. Ensure an active open shift exists
            $shift = Shift::where('cashier_id', $cashierEmpId)
                ->where('status', 'open')
                ->latest('id')
                ->first();

            if (!$shift) {
                $shift = Shift::create([
                    'cashier_id'   => $cashierEmpId,
                    'opening_cash' => 0.00,
                    'status'       => 'open',
                    'opened_at'    => now(),
                ]);
            }

            $verifiedItems = [];
            $subtotal = 0.0;

            // 3. Verify each menu item & lock inventory product with pessimistic locking
            foreach ($items as $item) {
                $menuItemId = (int)($item['product_id'] ?? 0);
                $qty = (int)($item['quantity'] ?? 0);

                if ($menuItemId <= 0 || $qty <= 0) {
                    throw new Exception("Invalid item or quantity in order payload.");
                }

                $menuItem = Product::where('id', $menuItemId)
                    ->lockForUpdate()
                    ->first();

                if (!$menuItem) {
                    throw new Exception("Menu item #{$menuItemId} not found.");
                }

                if (!$menuItem->is_available) {
                    throw new Exception("'{$menuItem->name}' is currently marked unavailable.");
                }

                // If linked to Group 2's inventory product, lock and check stock
                $invProduct = null;
                if ($menuItem->product_id) {
                    $invProduct = InventoryProduct::where('id', $menuItem->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$invProduct) {
                        throw new Exception("Warehouse inventory record missing for '{$menuItem->name}'.");
                    }

                    if ($invProduct->quantity_on_hand < $qty) {
                        throw new Exception("Insufficient stock for '{$menuItem->name}'. Available: {$invProduct->quantity_on_hand}, Requested: {$qty}.");
                    }
                }

                $unitPrice = (float)$menuItem->price;
                $lineTotal = round($unitPrice * $qty, 2);
                $subtotal += $lineTotal;

                $verifiedItems[] = [
                    'menu_item'   => $menuItem,
                    'inv_product' => $invProduct,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                ];
            }

            $subtotal = round($subtotal, 2);
            $discount = 0.00;
            $totalAmount = $subtotal - $discount;

            // 4. Validate Tendered Amount
            $finalAmountPaid = 0.0;
            $changeAmount = 0.0;

            if ($paymentMethod === 'cash') {
                if ($amountPaid < $totalAmount) {
                    throw new Exception("Amount tendered (₱" . number_format($amountPaid, 2) . ") is less than the total required (₱" . number_format($totalAmount, 2) . ").");
                }
                $finalAmountPaid = round($amountPaid, 2);
                $changeAmount = round($finalAmountPaid - $totalAmount, 2);
            } else {
                // GCash: Exact amount paid, zero change
                $finalAmountPaid = $totalAmount;
                $changeAmount = 0.00;
            }

            // 5. Generate unique receipt number (e.g. RCP-20261119-0001)
            $todayPrefix = 'RCP-' . date('Ymd') . '-';
            $latestReceipt = DB::table('canteen.sales')
                ->where('receipt_no', 'LIKE', $todayPrefix . '%')
                ->count();
            $receiptNo = $todayPrefix . str_pad($latestReceipt + 1, 4, '0', STR_PAD_LEFT);

            // 6. Create Sale in canteen.sales
            // Note: 'total' is a PostgreSQL GENERATED ALWAYS AS (subtotal - discount) STORED column
            $order = Order::create([
                'receipt_no' => $receiptNo,
                'shift_id'   => $shift->id,
                'cashier_id' => $cashierEmpId,
                'order_type' => 'dine_in',
                'subtotal'   => $subtotal,
                'discount'   => $discount,
                'status'     => 'completed',
                'notes'      => trim($customerName ?: 'Walk-in Customer'),
                'sold_at'    => now(),
            ]);

            // 7. Create Sale Items and Deduct Inventory Stock
            foreach ($verifiedItems as $v) {
                // Note: 'line_total' is GENERATED ALWAYS AS (unit_price * quantity) STORED
                OrderItem::create([
                    'sale_id'      => $order->id,
                    'menu_item_id' => $v['menu_item']->id,
                    'item_name'    => $v['menu_item']->name,
                    'unit_price'   => $v['unit_price'],
                    'quantity'     => $v['quantity'],
                ]);

                // Deduct stock from Group 2's inventory.products and log movement
                if ($v['inv_product']) {
                    $v['inv_product']->decrement('quantity_on_hand', $v['quantity']);

                    // Log stock movement for Group 2
                    DB::table('inventory.stock_movements')->insert([
                        'product_id'     => $v['inv_product']->id,
                        'movement_type'  => 'out',
                        'quantity'       => -$v['quantity'],
                        'reference_type' => 'canteen_sale',
                        'reference_id'   => $order->id,
                        'reason'         => "POS Sale #{$receiptNo}",
                        'performed_by'   => 1, // System staff ID in inventory.users
                        'created_at'     => now(),
                    ]);
                }
            }

            // 8. Create Payment Record in canteen.payments
            Payment::create([
                'sale_id'         => $order->id,
                'method'          => $paymentMethod,
                'amount_tendered' => $finalAmountPaid,
                'change_given'    => $changeAmount,
                'reference_no'    => $paymentMethod === 'gcash' ? 'GCASH-' . strtoupper(Str::random(8)) : null,
                'paid_at'         => now(),
            ]);

            return $order;
        });
    }
}
