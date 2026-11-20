/**
 * Integrated Inventory Management System (IMS) Server
 * Central Application Gateway & RESTful API Service
 */
const express = require('express');
const path = require('path');
const db = require('../database/pg-client');

// Import Route Handlers
const authRoutes = require('./routes/authRoutes');
const productRoutes = require('./routes/productRoutes');
const inventoryRoutes = require('./routes/inventoryRoutes');
const canteenRoutes = require('./routes/canteenRoutes');
const employeeRoutes = require('./routes/employeeRoutes');
const purchaseOrderRoutes = require('./routes/purchaseOrderRoutes');
const supplierRoutes = require('./routes/supplierRoutes');
const recipeRoutes = require('./routes/recipeRoutes');
const reportRoutes = require('./routes/reportRoutes');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Serve frontend static assets
app.use(express.static(path.join(__dirname, '../public')));

// Request logger
app.use((req, res, next) => {
    const start = Date.now();
    res.on('finish', () => {
        const duration = Date.now() - start;
        if (req.originalUrl.startsWith('/api')) {
            console.log(`[API] ${req.method} ${req.originalUrl} -> ${res.statusCode} (${duration}ms)`);
        }
    });
    next();
});

// API Routes
app.use('/api/auth', authRoutes);
app.use('/api/products', productRoutes);
app.use('/api/inventory', inventoryRoutes);
app.use('/api/integration/canteen', canteenRoutes);
app.use('/api/integration/employees', employeeRoutes);
app.use('/api/purchase-orders', purchaseOrderRoutes);
app.use('/api/suppliers', supplierRoutes);
app.use('/api/recipes', recipeRoutes);
app.use('/api/reports', reportRoutes);

// System Status and Settings Endpoints
app.get('/api/system/status', async (req, res) => {
    try {
        const isLive = db.isLive();
        const settingsRes = await db.query(`SELECT * FROM system_settings`);
        res.json({
            success: true,
            system_name: 'Integrated Inventory Management System (IMS)',
            version: '2.0.0-enterprise',
            database: {
                engine: isLive ? 'Live PostgreSQL (pg)' : 'Local PostgreSQL-Compliant Relational Store',
                status: isLive ? 'CONNECTED_LIVE' : 'FALLBACK_LOCAL_ACTIVE',
                config: db.getConfig()
            },
            settings: settingsRes.rows,
            timestamp: new Date().toISOString()
        });
    } catch (err) {
        res.status(500).json({ success: false, error: err.message });
    }
});

// Global Fallback for Single Page Application
app.use((req, res) => {
    if (!req.originalUrl.startsWith('/api')) {
        res.sendFile(path.join(__dirname, '../public/index.html'));
    } else {
        res.status(404).json({ success: false, error: 'Endpoint not found.' });
    }
});

// Global Error Handler
app.use((err, req, res, next) => {
    console.error('[Unhandled Server Error]', err.stack || err.message);
    res.status(err.status || 500).json({
        success: false,
        error: err.message || 'Internal Server Error'
    });
});

if (require.main === module) {
    app.listen(PORT, () => {
        console.log(`========================================================`);
        console.log(`   INTEGRATED INVENTORY MANAGEMENT SYSTEM (IMS)`);
        console.log(`   Server running on http://localhost:${PORT}`);
        console.log(`   Database Engine: ${db.isLive() ? 'Live PostgreSQL' : 'Local Relational Store'}`);
        console.log(`========================================================`);
    });
}

module.exports = app;
