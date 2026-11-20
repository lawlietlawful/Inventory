/**
 * Authentication Routes
 */
const express = require('express');
const router = express.Router();
const authService = require('../services/authService');
const { authenticate } = require('../middleware/auth');

router.post('/login', async (req, res) => {
    try {
        const { username, password } = req.body;
        const result = await authService.login(username, password, req.ip);
        res.json({ success: true, ...result });
    } catch (err) {
        res.status(401).json({ success: false, error: err.message });
    }
});

router.get('/me', authenticate, async (req, res) => {
    try {
        res.json({ success: true, user: req.user });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/users', authenticate, async (req, res) => {
    try {
        const users = await authService.listUsers();
        res.json({ success: true, users });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

module.exports = router;
