<?php

namespace App\Http\Controllers;

use App\Models\InventoryProduct;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the main management dashboard
     */
    public function index(): View
    {
        // 1. Today's sales total and orders count
        $today = Carbon::today('Asia/Manila')->toDateString();

        $todayStats = Order::where('status', 'completed')
            ->whereDate('sold_at', $today)
            ->selectRaw('COALESCE(SUM(total), 0) as total_sales, COUNT(*) as order_count')
            ->first();

        $todaySales = (float)($todayStats->total_sales ?? 0);
        $todayOrders = (int)($todayStats->order_count ?? 0);

        // 2. Active products and low stock count from inventory
        $activeProducts = Product::where('is_available', true)->count();
        $lowStockCount = InventoryProduct::where('quantity_on_hand', '<=', 10)->count();

        // 3. Low-stock list from Group 2's inventory
        $lowStockProducts = Product::with(['category', 'inventoryProduct'])
            ->whereHas('inventoryProduct', function ($q) {
                $q->where('quantity_on_hand', '<=', 10);
            })
            ->get();

        // 4. Top 5 selling products (by total quantity sold)
        $topProducts = DB::table('canteen.sale_items as si')
            ->join('canteen.sales as s', 'si.sale_id', '=', 's.id')
            ->join('canteen.menu_items as m', 'si.menu_item_id', '=', 'm.id')
            ->leftJoin('canteen.menu_categories as c', 'm.category_id', '=', 'c.id')
            ->where('s.status', 'completed')
            ->select(
                'm.id as product_id',
                'm.name as product_name',
                DB::raw("COALESCE(c.name, 'General') as category_name"),
                DB::raw('SUM(si.quantity) as total_sold'),
                DB::raw('SUM(si.unit_price * si.quantity) as total_revenue')
            )
            ->groupBy('m.id', 'm.name', 'c.name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        // 5. Sales for the last 7 days (continuous series)
        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today('Asia/Manila')->subDays($i);
            $dateStr = $date->toDateString();
            $label = $date->format('M d');

            $dayStats = Order::where('status', 'completed')
                ->whereDate('sold_at', $dateStr)
                ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as count')
                ->first();

            $last7Days[] = [
                'date'        => $dateStr,
                'label'       => $label,
                'sales'       => (float)($dayStats->total ?? 0),
                'order_count' => (int)($dayStats->count ?? 0),
            ];
        }

        // 6. Payment breakdown (Cash vs GCash)
        $paymentStats = DB::table('canteen.payments as p')
            ->join('canteen.sales as s', 'p.sale_id', '=', 's.id')
            ->where('s.status', 'completed')
            ->select(
                'p.method as payment_method',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(s.total), 0) as total_amount')
            )
            ->groupBy('p.method')
            ->get()
            ->keyBy('payment_method');

        $cashSales = (float)($paymentStats->get('cash')->total_amount ?? 0);
        $gcashSales = (float)($paymentStats->get('gcash')->total_amount ?? 0);
        $cashOrders = (int)($paymentStats->get('cash')->total_orders ?? 0);
        $gcashOrders = (int)($paymentStats->get('gcash')->total_orders ?? 0);

        return view('dashboard', compact(
            'todaySales',
            'todayOrders',
            'activeProducts',
            'lowStockCount',
            'lowStockProducts',
            'topProducts',
            'last7Days',
            'cashSales',
            'gcashSales',
            'cashOrders',
            'gcashOrders'
        ));
    }
}
