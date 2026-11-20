/**
 * Inventory Operations Routes
 */
const express = require('express');
const router = express.Router();
const inventoryService = require('../services/inventoryService');
const { authenticate, requirePermission } = require('../middleware/auth');

router.get('/transactions', authenticate, async (req, res) => {
    try {
        const transactions = await inventoryService.getTransactions(parseInt(req.query.limit || '100', 10));
        res.json({ success: true, count: transactions.length, transactions });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/batches', authenticate, async (req, res) => {
    try {
        const batches = await inventoryService.getBatches();
        res.json({ success: true, count: batches.length, batches });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/adjustments', authenticate, async (req, res) => {
    try {
        const adjustments = await inventoryService.getAdjustments();
        res.json({ success: true, adjustments });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.post('/stock-in', authenticate, requirePermission('manage_inventory'), async (req, res) => {
    try {
        const result = await inventoryService.stockIn(req.body, req.user.employee_id);
        res.status(201).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.post('/stock-out', authenticate, requirePermission('manage_inventory'), async (req, res) => {
    try {
        const result = await inventoryService.stockOut(req.body, req.user.employee_id);
        res.status(200).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.post('/adjustments', authenticate, async (req, res) => {
    try {
        const result = await inventoryService.requestAdjustment(req.body, req.user.employee_id);
        res.status(201).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.post('/adjustments/:id/approve', authenticate, requirePermission('approve_adjustments'), async (req, res) => {
    try {
        const result = await inventoryService.approveAdjustment(req.params.id, req.user.employee_id);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

module.exports = router;
