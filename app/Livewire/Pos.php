<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CheckoutService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Pos extends Component
{
    public string $search = '';
    public string $selectedCategory = 'all';
    public array $cart = [];
    public string $customerName = '';
    public string $paymentMethod = 'cash';
    public string $amountTendered = '';
    public string $errorMessage = '';
    public ?int $completedOrderId = null;

    /**
     * Render the Livewire POS interface
     */
    public function render()
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        $query = Product::with(['category', 'inventoryProduct'])
            ->orderBy('is_available', 'desc')
            ->orderBy('name');

        if ($this->selectedCategory !== 'all') {
            $query->where('category_id', (int)$this->selectedCategory);
        }

        if (!empty(trim($this->search))) {
            $term = '%' . trim($this->search) . '%';
            $query->where('name', 'ilike', $term);
        }

        $products = $query->get();

        $completedOrder = null;
        if ($this->completedOrderId) {
            $completedOrder = \App\Models\Order::with(['user', 'payment', 'orderItems.product'])
                ->find($this->completedOrderId);
        }

        return view('livewire.pos', [
            'categories'     => $categories,
            'products'       => $products,
            'total'          => $this->total,
            'itemCount'      => $this->itemCount,
            'change'         => $this->change,
            'completedOrder' => $completedOrder,
        ]);
    }

    /**
     * Filter by Category
     */
    public function setCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    /**
     * Add product to cart
     */
    public function addToCart(int $productId): void
    {
        $this->errorMessage = '';

        $product = Product::with('inventoryProduct')->find($productId);
        if (!$product || !$product->is_available || $product->stock_qty <= 0) {
            $this->errorMessage = "Selected product is unavailable or out of stock.";
            return;
        }

        if (isset($this->cart[$productId])) {
            if ($this->cart[$productId]['qty'] + 1 > $product->stock_qty) {
                $this->errorMessage = "Cannot add more \"{$product->product_name}\". Maximum available stock is {$product->stock_qty}.";
                return;
            }
            $this->cart[$productId]['qty'] += 1;
        } else {
            $this->cart[$productId] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => (float)$product->price,
                'stock' => (int)$product->stock_qty,
                'qty'   => 1,
            ];
        }

        $this->syncPaymentAmount();
    }

    /**
     * Increment cart item quantity
     */
    public function increaseQty(int $productId): void
    {
        $this->errorMessage = '';

        if (!isset($this->cart[$productId])) {
            return;
        }

        $item = &$this->cart[$productId];
        if ($item['qty'] + 1 > $item['stock']) {
            $this->errorMessage = "Cannot exceed available stock ({$item['stock']}) for \"{$item['name']}\".";
            return;
        }

        $item['qty'] += 1;
        $this->syncPaymentAmount();
    }

    /**
     * Decrement cart item quantity
     */
    public function decreaseQty(int $productId): void
    {
        $this->errorMessage = '';

        if (!isset($this->cart[$productId])) {
            return;
        }

        if ($this->cart[$productId]['qty'] > 1) {
            $this->cart[$productId]['qty'] -= 1;
        } else {
            unset($this->cart[$productId]);
        }

        $this->syncPaymentAmount();
    }

    /**
     * Update item quantity directly
     */
    public function updateQty(int $productId, $newQty): void
    {
        $this->errorMessage = '';

        if (!isset($this->cart[$productId])) {
            return;
        }

        $newQty = (int)$newQty;

        if ($newQty <= 0) {
            unset($this->cart[$productId]);
            $this->syncPaymentAmount();
            return;
        }

        $item = &$this->cart[$productId];
        if ($newQty > $item['stock']) {
            $this->errorMessage = "Stock limit reached ({$item['stock']}) for \"{$item['name']}\".";
            $item['qty'] = $item['stock'];
        } else {
            $item['qty'] = $newQty;
        }

        $this->syncPaymentAmount();
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
        $this->syncPaymentAmount();
    }

    /**
     * Clear all items in cart
     */
    public function clearCart(): void
    {
        $this->cart = [];
        $this->amountTendered = '';
        $this->customerName = '';
        $this->errorMessage = '';
    }

    /**
     * Change payment method
     */
    public function setPaymentMethod(string $method): void
    {
        $this->paymentMethod = in_array($method, ['cash', 'gcash'], true) ? $method : 'cash';
        $this->syncPaymentAmount();
    }

    /**
     * Quick cash buttons (+20, +50, +100, +500, +1000, exact)
     */
    public function setQuickCash(string $val): void
    {
        $currentTotal = $this->total;

        if ($val === 'exact') {
            $this->amountTendered = number_format($currentTotal, 2, '.', '');
        } else {
            $add = (float)$val;
            $current = (float)$this->amountTendered;
            if ($current < $currentTotal) {
                $current = 0;
            }
            $this->amountTendered = number_format($current + $add, 2, '.', '');
        }
    }

    /**
     * Sync tendered amount when GCash is selected
     */
    protected function syncPaymentAmount(): void
    {
        if ($this->paymentMethod === 'gcash') {
            $this->amountTendered = number_format($this->total, 2, '.', '');
        }
    }

    /**
     * Total amount computed property
     */
    public function getTotalProperty(): float
    {
        $sum = 0.0;
        foreach ($this->cart as $item) {
            $sum += $item['price'] * $item['qty'];
        }
        return round($sum, 2);
    }

    /**
     * Total items count computed property
     */
    public function getItemCountProperty(): int
    {
        $count = 0;
        foreach ($this->cart as $item) {
            $count += $item['qty'];
        }
        return $count;
    }

    /**
     * Change due computed property
     */
    public function getChangeProperty(): float
    {
        if ($this->paymentMethod === 'gcash') {
            return 0.00;
        }

        $tendered = (float)$this->amountTendered;
        return max(0.00, round($tendered - $this->total, 2));
    }

    /**
     * Process checkout via CheckoutService
     */
    public function checkout(CheckoutService $service)
    {
        $this->errorMessage = '';

        if (empty($this->cart)) {
            $this->errorMessage = "Cannot checkout: your cart is empty.";
            return;
        }

        $total = $this->total;
        $tendered = (float)$this->amountTendered;

        if ($this->paymentMethod === 'cash' && $tendered < $total) {
            $this->errorMessage = "Amount tendered (₱" . number_format($tendered, 2) . ") is less than the total amount (₱" . number_format($total, 2) . ").";
            return;
        }

        // Format items array for CheckoutService
        $items = [];
        foreach ($this->cart as $item) {
            $items[] = [
                'product_id' => $item['id'],
                'quantity'   => $item['qty'],
            ];
        }

        $cashierId = Auth::user()->id;

        try {
            $order = $service->checkout(
                items: $items,
                paymentMethod: $this->paymentMethod,
                amountPaid: $this->paymentMethod === 'gcash' ? $total : $tendered,
                cashierIdentifier: $cashierId,
                customerName: $this->customerName
            );

            // Set completed order for receipt modal display
            $this->completedOrderId = $order->id;

            // Reset cart and customer name for the next order
            $this->clearCart();
            $this->customerName = '';

            // Dispatch event for UI modal display & optional sound/auto-print
            $this->dispatch('order-completed', orderId: $order->id);

        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    /**
     * Close the receipt modal and prepare for the next customer
     */
    public function closeReceiptModal(): void
    {
        $this->completedOrderId = null;
    }
}
