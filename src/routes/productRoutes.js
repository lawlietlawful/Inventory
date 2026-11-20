/**
 * Product Routes
 */
const express = require('express');
const router = express.Router();
const productService = require('../services/productService');
const { authenticate, requirePermission } = require('../middleware/auth');

router.get('/', authenticate, async (req, res) => {
    try {
        const products = await productService.getProducts(req.query);
        res.json({ success: true, count: products.length, products });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/categories', authenticate, async (req, res) => {
    try {
        const categories = await productService.getCategories();
        res.json({ success: true, categories });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/units', authenticate, async (req, res) => {
    try {
        const units = await productService.getUnits();
        res.json({ success: true, units });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/barcode/:barcode', authenticate, async (req, res) => {
    try {
        const product = await productService.getProductByBarcode(req.params.barcode);
        res.json({ success: true, product });
    } catch (err) {
        res.status(404).json({ success: false, error: err.message });
    }
});

router.get('/:id', authenticate, async (req, res) => {
    try {
        const product = await productService.getProductById(req.params.id);
        res.json({ success: true, product });
    } catch (err) {
        res.status(404).json({ success: false, error: err.message });
    }
});

router.post('/', authenticate, requirePermission('manage_products'), async (req, res) => {
    try {
        const result = await productService.createProduct(req.body, req.user.employee_id);
        res.status(201).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.put('/:id', authenticate, requirePermission('manage_products'), async (req, res) => {
    try {
        const result = await productService.updateProduct(req.params.id, req.body, req.user.employee_id);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.delete('/:id', authenticate, requirePermission('manage_products'), async (req, res) => {
    try {
        const result = await productService.archiveProduct(req.params.id, req.user.employee_id);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

module.exports = router;
