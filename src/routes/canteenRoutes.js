/**
 * Canteen POS Integration Routes
 */
const express = require('express');
const router = express.Router();
const canteenService = require('../services/canteenService');
const { authenticate } = require('../middleware/auth');

// Live Product Availability for POS (Public or POS-Authenticated)
router.get('/product-availability', async (req, res) => {
    try {
        const products = await canteenService.getProductAvailability();
        res.json({ success: true, count: products.length, products });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Process Completed POS Sale (Automatic Inventory & Recipe Deduction)
router.post('/sales', async (req, res) => {
    try {
        const result = await canteenService.processSale(req.body);
        res.status(200).json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

// Cancel POS Sale
router.post('/cancel', async (req, res) => {
    try {
        const result = await canteenService.cancelSale(req.body);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

// Refund POS Sale
router.post('/refund', async (req, res) => {
    try {
        const result = await canteenService.refundSale(req.body);
        res.json(result);
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

module.exports = router;
