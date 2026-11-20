/**
 * Purchase Order Routes
 */
const express = require('express');
const router = express.Router();
const purchaseOrderService = require('../services/purchaseOrderService');
const { authenticate, requirePermission } = require('../middleware/auth');

router.get('/', authenticate, async (req, res) => {
    try {
        const orders = await purchaseOrderService.getPurchaseOrders(req.query);
        res.json({ success: true, count: orders.length, orders });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/:id', authenticate, async (req, res) => {
    try {
        const po = await purchaseOrderService.getPurchaseOrderById(req.params.id);
        res.json({ success: true, purchase_order: po });
    } catch (err) {
        res.status(404).json({ success: false, error: err.message });
    }
});

router.post('/', authenticate, requirePermission('manage_purchase_orders'), async (req, res) => {
    try {
        const result = await purchaseOrderService.createPurchaseOrder(req.body, req.user.employee_id);
        res.status(201).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.post('/:id/approve', authenticate, requirePermission('approve_purchase_orders'), async (req, res) => {
    try {
        const result = await purchaseOrderService.updateStatus(req.params.id, 'APPROVED', req.user.employee_id);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.post('/:id/receive', authenticate, requirePermission('receive_purchase_orders'), async (req, res) => {
    try {
        const result = await purchaseOrderService.receiveDelivery(req.params.id, req.body, req.user.employee_id);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

module.exports = router;
