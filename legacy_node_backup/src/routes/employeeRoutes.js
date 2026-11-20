/**
 * Employee Management System Integration Routes
 */
const express = require('express');
const router = express.Router();
const employeeService = require('../services/employeeService');
const { authenticate, requirePermission } = require('../middleware/auth');

router.get('/', authenticate, async (req, res) => {
    try {
        const employees = await employeeService.getEmployees();
        res.json({ success: true, count: employees.length, employees });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

router.get('/:id', authenticate, async (req, res) => {
    try {
        const details = await employeeService.getEmployeeById(req.params.id);
        res.json({ success: true, ...details });
    } catch (err) {
        res.status(404).json({ success: false, error: err.message });
    }
});

router.post('/sync', authenticate, async (req, res) => {
    try {
        const result = await employeeService.syncEmployee(req.body, req.user ? req.user.employee_id : 'EMS_SYNC');
        res.json({ success: true, ...result });
    } catch (err) {
        res.status(400).json({ success: false, error: err.message });
    }
});

router.get('/:id/activities', authenticate, async (req, res) => {
    try {
        const activities = await employeeService.getEmployeeActivities(req.params.id);
        res.json({ success: true, activities });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

module.exports = router;
