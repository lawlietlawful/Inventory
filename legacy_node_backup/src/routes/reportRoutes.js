/**
 * Report and Analytics Routes
 * Exposes all 15 required enterprise reports + dashboard analytics
 */
const express = require('express');
const router = express.Router();
const reportService = require('../services/reportService');
const { authenticate, requirePermission } = require('../middleware/auth');

// Dashboard High-Level Metrics
router.get('/dashboard', authenticate, async (req, res) => {
    try {
        const data = await reportService.getDashboardMetrics();
        res.json({ success: true, ...data });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 1: Current Inventory
router.get('/current-inventory', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getCurrentInventoryReport(req.query);
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 2: Stock-In
router.get('/stock-in', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getStockInReport(req.query);
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 3: Stock-Out
router.get('/stock-out', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getStockOutReport(req.query);
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 4: Inventory Movement
router.get('/movement', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getInventoryMovementReport(req.query);
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 5: Low Stock
router.get('/low-stock', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getLowStockReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 6: Out of Stock
router.get('/out-of-stock', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getOutOfStockReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 7: Expiration & Batches (FEFO)
router.get('/expiration', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getExpirationReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 8: Damaged Products
router.get('/damaged', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getDamagedProductsReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 9: Supplier Performance
router.get('/suppliers', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getSupplierReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 10: Purchase Orders
router.get('/purchase-orders', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getPurchaseOrderReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 11: Inventory Valuation (WAC)
router.get('/valuation', authenticate, async (req, res) => {
    try {
        const result = await reportService.getInventoryValuationReport();
        res.json({ success: true, ...result });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 12: Canteen Sales Deductions
router.get('/canteen-deductions', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getCanteenSalesDeductionReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 13: Employee Activity
router.get('/employee-activities', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getEmployeeActivityReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 14: Stock Adjustments
router.get('/adjustments', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getStockAdjustmentReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Report 15: Product Usage
router.get('/product-usage', authenticate, async (req, res) => {
    try {
        const rows = await reportService.getProductUsageReport();
        res.json({ success: true, count: rows.length, rows });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

module.exports = router;
