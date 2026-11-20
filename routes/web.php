<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Inventory\InventoryDashboardController;
use App\Http\Controllers\Inventory\WarehouseProductController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\SupplierController;
use App\Http\Controllers\Inventory\PurchaseOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Integrated Inventory Management System (Group 2)
|--------------------------------------------------------------------------
*/

// Authentication Routes (Guest Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('login.quick');
});

// Logout Route (Authenticated)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Inventory Application Routes
Route::middleware('auth')->group(function () {
    // Root Redirect
    Route::get('/', function () {
        return redirect()->route('inventory.dashboard');
    });
    Route::get('/dashboard', [InventoryDashboardController::class, 'index'])->name('dashboard');

    Route::name('inventory.')->group(function () {
        Route::get('/overview', [InventoryDashboardController::class, 'index'])->name('dashboard');
        Route::resource('products', WarehouseProductController::class)->except(['show']);
        Route::get('/movements', [StockMovementController::class, 'index'])->name('movements.index');
        Route::post('/movements', [StockMovementController::class, 'store'])->name('movements.store');
        Route::resource('suppliers', SupplierController::class)->except(['create', 'show', 'edit']);
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });
});
