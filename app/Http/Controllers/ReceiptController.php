<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    /**
     * Display printable receipt for an order
     */
    public function show(Order $order): View
    {
        $order->load(['user', 'payment', 'orderItems.product']);

        return view('receipt.show', compact('order'));
    }
}

