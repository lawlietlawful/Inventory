/**
 * Supplier Routes
 */
const express = require('express');
const router = express.Router();
const supplierService = require('../services/supplierService');
const { authenticate, requirePermission } = require('../middleware/auth');

router.get('/', authenticate, async (req, res) => {
    try {
        const suppliers = await supplierService.getSuppliers(req.query);
        res.json({ success: true, count: suppliers.length, suppliers });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/:id', authenticate, async (req, res) => {
    try {
        const supplier = await supplierService.getSupplierById(req.params.id);
        res.json({ success: true, supplier });
    } catch (err) {
        res.status(404).json({ success: false, error: err.message });
    }
});

router.post('/', authenticate, requirePermission('manage_suppliers'), async (req, res) => {
    try {
        const result = await supplierService.createSupplier(req.body, req.user.employee_id);
        res.status(201).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.put('/:id', authenticate, requirePermission('manage_suppliers'), async (req, res) => {
    try {
        const result = await supplierService.updateSupplier(req.params.id, req.body, req.user.employee_id);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

module.exports = router;
