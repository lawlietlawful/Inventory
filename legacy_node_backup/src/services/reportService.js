/**
 * Inventory Reports & Analytics Service
 * Generates all 15 required enterprise reports from live PostgreSQL records
 */
const db = require('../../database/pg-client');

class ReportService {
    /**
     * Report 1: Current Inventory Report
     */
    async getCurrentInventoryReport(filters = {}) {
        let sql = `
            SELECT p.id, p.product_code, p.barcode, p.product_name, c.category_name, 
                   u.unit_name, u.abbreviation AS unit, p.cost_price, p.selling_price, 
                   p.current_stock, p.minimum_stock, p.maximum_stock, p.reorder_level,
                   ROUND((p.current_stock * p.cost_price), 2) AS total_value,
                   p.status
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN units u ON p.unit_id = u.id
            WHERE p.status = 'ACTIVE'
        `;
        const params = [];
        if (filters.category_id) {
            sql += ` AND p.category_id = $1`;
            params.push(filters.category_id);
        }
        sql += ` ORDER BY c.category_name ASC, p.product_name ASC`;
        const res = await db.query(sql, params);
        return res.rows;
    }

    /**
     * Report 2: Stock-In Report
     */
    async getStockInReport(filters = {}) {
        const res = await db.query(
            `SELECT t.*, p.product_code, p.product_name, u.abbreviation AS unit,
                    t.employee_id, t.reference_type, t.reference_id, t.remarks
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             WHERE t.transaction_type = 'STOCK_IN'
             ORDER BY t.created_at DESC`
        );
        return res.rows;
    }

    /**
     * Report 3: Stock-Out Report
     */
    async getStockOutReport(filters = {}) {
        const res = await db.query(
            `SELECT t.*, p.product_code, p.product_name, u.abbreviation AS unit,
                    t.employee_id, t.reference_type, t.reference_id, t.remarks
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             WHERE t.transaction_type IN ('STOCK_OUT', 'SALE', 'DAMAGE', 'EXPIRED')
             ORDER BY t.created_at DESC`
        );
        return res.rows;
    }

    /**
     * Report 4: Inventory Movement Report
     */
    async getInventoryMovementReport(filters = {}) {
        const res = await db.query(
            `SELECT t.*, p.product_code, p.product_name, c.category_name, u.abbreviation AS unit
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             ORDER BY t.created_at DESC
             LIMIT 200`
        );
        return res.rows;
    }

    /**
     * Report 5: Low Stock Report
     */
    async getLowStockReport() {
        const res = await db.query(
            `SELECT p.id, p.product_code, p.product_name, c.category_name, 
                    p.current_stock, p.minimum_stock, p.reorder_level, u.abbreviation AS unit,
                    p.cost_price, (p.minimum_stock - p.current_stock) AS replenishment_needed
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             WHERE p.current_stock <= p.minimum_stock AND p.current_stock > 0 AND p.status = 'ACTIVE'
             ORDER BY p.current_stock ASC`
        );
        return res.rows;
    }

    /**
     * Report 6: Out-of-Stock Report
     */
    async getOutOfStockReport() {
        const res = await db.query(
            `SELECT p.id, p.product_code, p.product_name, c.category_name, 
                    p.minimum_stock, p.reorder_level, u.abbreviation AS unit,
                    p.cost_price, p.selling_price
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             WHERE p.current_stock <= 0 AND p.status = 'ACTIVE'
             ORDER BY p.product_name ASC`
        );
        return res.rows;
    }

    /**
     * Report 7: Expiration Report (FEFO Risk)
     */
    async getExpirationReport() {
        const res = await db.query(`SELECT * FROM v_expiring_batches`);
        return res.rows;
    }

    /**
     * Report 8: Damaged Products Report
     */
    async getDamagedProductsReport() {
        const res = await db.query(
            `SELECT t.*, p.product_code, p.product_name, u.abbreviation AS unit
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             WHERE t.transaction_type = 'DAMAGE'
             ORDER BY t.created_at DESC`
        );
        return res.rows;
    }

    /**
     * Report 9: Supplier Report
     */
    async getSupplierReport() {
        const res = await db.query(
            `SELECT s.*, 
                    (SELECT COUNT(*) FROM purchase_orders po WHERE po.supplier_id = s.id) AS total_orders,
                    (SELECT COALESCE(SUM(po.total_amount), 0) FROM purchase_orders po WHERE po.supplier_id = s.id) AS total_spend,
                    (SELECT COUNT(*) FROM supplier_products sp WHERE sp.supplier_id = s.id) AS supplied_products_count
             FROM suppliers s
             ORDER BY s.supplier_name ASC`
        );
        return res.rows;
    }

    /**
     * Report 10: Purchase Order Report
     */
    async getPurchaseOrderReport() {
        const res = await db.query(
            `SELECT po.*, s.supplier_name, s.supplier_code
             FROM purchase_orders po
             JOIN suppliers s ON po.supplier_id = s.id
             ORDER BY po.created_at DESC`
        );
        return res.rows;
    }

    /**
     * Report 11: Inventory Valuation Report (Weighted Average Cost)
     */
    async getInventoryValuationReport() {
        const res = await db.query(`SELECT * FROM v_inventory_valuation`);
        const totalVal = res.rows.reduce((sum, r) => sum + parseFloat(r.total_inventory_value || 0), 0);
        return {
            items: res.rows,
            total_valuation: Math.round(totalVal * 100) / 100,
            valuation_method: 'Weighted Average Cost (WAC)'
        };
    }

    /**
     * Report 12: Canteen Sales and Inventory Deduction Report
     */
    async getCanteenSalesDeductionReport() {
        const res = await db.query(
            `SELECT t.*, p.product_code, p.product_name, c.category_name, u.abbreviation AS unit
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             WHERE t.reference_type = 'CANTEEN_SALE' OR t.transaction_type = 'SALE'
             ORDER BY t.created_at DESC`
        );
        return res.rows;
    }

    /**
     * Report 13: Employee Inventory Activity Report
     */
    async getEmployeeActivityReport() {
        const res = await db.query(
            `SELECT a.*, u.full_name, u.username, r.role_name
             FROM activity_logs a
             LEFT JOIN users u ON a.employee_id = u.employee_id
             LEFT JOIN roles r ON u.role_id = r.id
             ORDER BY a.created_at DESC
             LIMIT 200`
        );
        return res.rows;
    }

    /**
     * Report 14: Stock Adjustment Report
     */
    async getStockAdjustmentReport() {
        const res = await db.query(
            `SELECT a.*, p.product_code, p.product_name, u.abbreviation AS unit
             FROM stock_adjustments a
             JOIN products p ON a.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             ORDER BY a.created_at DESC`
        );
        return res.rows;
    }

    /**
     * Report 15: Product Usage Report
     */
    async getProductUsageReport() {
        const res = await db.query(
            `SELECT p.id, p.product_code, p.product_name, c.category_name, u.abbreviation AS unit,
                    ABS(COALESCE(SUM(t.quantity), 0)) AS total_quantity_used,
                    COUNT(t.id) AS usage_events_count
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             LEFT JOIN inventory_transactions t ON p.id = t.product_id AND t.quantity < 0
             GROUP BY p.id, p.product_code, p.product_name, c.category_name, u.abbreviation
             ORDER BY total_quantity_used DESC`
        );
        return res.rows;
    }

    /**
     * Dashboard Summary Metrics
     */
    async getDashboardMetrics() {
        const productsRes = await db.query(`SELECT COUNT(*) AS total_products FROM products WHERE status = 'ACTIVE'`);
        const categoriesRes = await db.query(`SELECT COUNT(*) AS total_categories FROM categories`);
        const suppliersRes = await db.query(`SELECT COUNT(*) AS total_suppliers FROM suppliers WHERE status = 'ACTIVE'`);
        
        const valRes = await db.query(`SELECT * FROM v_inventory_valuation`);
        const totalValue = valRes.rows.reduce((sum, r) => sum + parseFloat(r.total_inventory_value || 0), 0);

        const lowStockRes = await db.query(
            `SELECT COUNT(*) AS low_stock FROM products WHERE current_stock <= minimum_stock AND current_stock > 0 AND status = 'ACTIVE'`
        );
        const outStockRes = await db.query(
            `SELECT COUNT(*) AS out_of_stock FROM products WHERE current_stock <= 0 AND status = 'ACTIVE'`
        );
        const expiringRes = await db.query(
            `SELECT COUNT(*) AS expiring_soon FROM stock_batches WHERE remaining_quantity > 0 AND expiration_date <= CURRENT_DATE + INTERVAL '15 days'`
        );
        const pendingPoRes = await db.query(
            `SELECT COUNT(*) AS pending_pos FROM purchase_orders WHERE status = 'PENDING APPROVAL'`
        );

        // Chart Data 1 & 2: Computed dynamically across active products and categories
        const allProducts = await db.query(`SELECT * FROM products WHERE status = 'ACTIVE'`);
        const allCategories = await db.query(`SELECT * FROM categories`);
        const allTxns = await db.query(`SELECT * FROM inventory_transactions`);

        const valueByCategoryMap = {};
        const movementByCategoryMap = {};

        allCategories.rows.forEach(c => {
            valueByCategoryMap[c.category_name] = 0;
            movementByCategoryMap[c.category_name] = { stock_in: 0, stock_out: 0 };
        });

        allProducts.rows.forEach(p => {
            const cat = allCategories.rows.find(c => c.id == p.category_id);
            const cName = cat ? cat.category_name : 'General';
            if (!valueByCategoryMap[cName]) valueByCategoryMap[cName] = 0;
            valueByCategoryMap[cName] += (parseFloat(p.current_stock || 0) * parseFloat(p.cost_price || 0));
        });

        allTxns.rows.forEach(t => {
            const p = allProducts.rows.find(x => x.id == t.product_id);
            if (p) {
                const cat = allCategories.rows.find(c => c.id == p.category_id);
                const cName = cat ? cat.category_name : 'General';
                if (!movementByCategoryMap[cName]) movementByCategoryMap[cName] = { stock_in: 0, stock_out: 0 };
                const qty = parseFloat(t.quantity || 0);
                if (qty > 0) movementByCategoryMap[cName].stock_in += qty;
                else if (qty < 0) movementByCategoryMap[cName].stock_out += Math.abs(qty);
            }
        });

        const valueByCategory = Object.keys(valueByCategoryMap).map(k => ({
            category_name: k,
            category_value: Math.round(valueByCategoryMap[k] * 100) / 100
        }));

        const movementByCategory = Object.keys(movementByCategoryMap).map(k => ({
            category_name: k,
            stock_in: movementByCategoryMap[k].stock_in,
            stock_out: movementByCategoryMap[k].stock_out
        }));

        // Top 10 Most Used Products
        const usageMap = {};
        allTxns.rows.forEach(t => {
            const qty = parseFloat(t.quantity || 0);
            if (qty < 0) {
                const p = allProducts.rows.find(x => x.id == t.product_id);
                const pName = p ? p.product_name : `Product #${t.product_id}`;
                usageMap[pName] = (usageMap[pName] || 0) + Math.abs(qty);
            }
        });

        const topUsed = Object.keys(usageMap).map(k => ({
            product_name: k,
            total_used: usageMap[k]
        })).sort((a, b) => b.total_used - a.total_used).slice(0, 10);

        // Recent Activities
        const recentTxns = await db.query(
            `SELECT * FROM inventory_transactions ORDER BY created_at DESC LIMIT 10`
        );

        // Notifications
        const notifications = await db.query(
            `SELECT * FROM inventory_notifications WHERE status = 'UNREAD' ORDER BY id DESC LIMIT 10`
        );

        return {
            summary_cards: {
                total_products: parseInt(productsRes.rows[0]?.total_products || 0, 10),
                total_categories: parseInt(categoriesRes.rows[0]?.total_categories || 0, 10),
                total_suppliers: parseInt(suppliersRes.rows[0]?.total_suppliers || 0, 10),
                total_inventory_value: Math.round(totalValue * 100) / 100,
                low_stock_products: parseInt(lowStockRes.rows[0]?.low_stock || 0, 10),
                out_of_stock_products: parseInt(outStockRes.rows[0]?.out_of_stock || 0, 10),
                expiring_products: parseInt(expiringRes.rows[0]?.expiring_soon || 0, 10),
                pending_purchase_orders: parseInt(pendingPoRes.rows[0]?.pending_pos || 0, 10)
            },
            charts: {
                movement_by_category: movementByCategory,
                value_by_category: valueByCategory,
                top_used_products: topUsed
            },
            recent_activities: recentTxns.rows,
            notifications: notifications.rows
        };
    }
}

module.exports = new ReportService();
