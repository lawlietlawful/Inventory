<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryProduct;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        // 1. Core KPIs
        $totalValuation = (float) InventoryProduct::where('status', 'active')
            ->selectRaw('COALESCE(SUM(quantity_on_hand * cost_price), 0) as total')
            ->value('total');

        $totalActiveProducts = InventoryProduct::where('status', 'active')->count();

        $lowStockCount = InventoryProduct::where('status', 'active')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->count();

        $outOfStockCount = InventoryProduct::where('status', 'active')
            ->where('quantity_on_hand', '<=', 0)
            ->count();

        $activeSuppliersCount = Supplier::where('status', 'active')->count();

        $pendingPOCount = PurchaseOrder::where('status', 'pending')->count();

        // 2. Low Stock Alerts List (top 8 urgently needing reorder)
        $lowStockProducts = InventoryProduct::with(['category', 'supplier'])
            ->where('status', 'active')
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderBy('quantity_on_hand', 'asc')
            ->limit(8)
            ->get();

        // 3. Recent Stock Movements (audit feed)
        $recentMovements = StockMovement::with('product')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // 4. Pending Purchase Orders
        $recentPOs = PurchaseOrder::with(['supplier', 'items'])
            ->orderBy('order_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        // 5. Category Breakdown Data for Chart.js
        $categoryStats = InventoryCategory::withCount(['products' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->map(function ($cat) {
                $val = (float) InventoryProduct::where('category_id', $cat->id)
                    ->where('status', 'active')
                    ->selectRaw('COALESCE(SUM(quantity_on_hand * cost_price), 0) as total')
                    ->value('total');
                return [
                    'name'       => $cat->name,
                    'count'      => $cat->products_count,
                    'valuation'  => $val,
                ];
            });

        return view('inventory.dashboard', compact(
            'totalValuation',
            'totalActiveProducts',
            'lowStockCount',
            'outOfStockCount',
            'activeSuppliersCount',
            'pendingPOCount',
            'lowStockProducts',
            'recentMovements',
            'recentPOs',
            'categoryStats'
        ));
    }
}
