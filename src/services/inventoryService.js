/**
 * Core Inventory Management Service
 * Handles Stock-In, Stock-Out, FEFO Batch Allocations, Adjustments, and Transfers
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');

class InventoryService {
    /**
     * Record Incoming Inventory (Stock-In)
     */
    async stockIn(data, employeeId = 'EMP-001') {
        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            const {
                product_id, quantity, unit_cost, batch_number,
                expiration_date, reference_type, reference_id, remarks
            } = data;

            const qty = parseFloat(quantity);
            const cost = parseFloat(unit_cost || 0);

            if (isNaN(qty) || qty <= 0) {
                throw new Error('Stock-in quantity must be a positive number.');
            }

            // Lock and fetch current product state
            const prodRes = await client.query(
                `SELECT id, product_code, product_name, current_stock, cost_price, expiration_tracking 
                 FROM products WHERE id = $1 FOR UPDATE`,
                [product_id]
            );

            if (prodRes.rows.length === 0) {
                throw new Error(`Product ID ${product_id} not found.`);
            }

            const product = prodRes.rows[0];
            const prevStock = parseFloat(product.current_stock || 0);
            const currentWAC = parseFloat(product.cost_price || 0);
            const newStock = prevStock + qty;

            // Recalculate Weighted Average Cost (WAC)
            let newWAC = currentWAC;
            if (cost > 0 && newStock > 0) {
                newWAC = ((prevStock * currentWAC) + (qty * cost)) / newStock;
                newWAC = Math.round(newWAC * 10000) / 10000;
            }

            // Update Product
            await client.query(
                `UPDATE products SET current_stock = $1, cost_price = $2, updated_at = NOW() WHERE id = $3`,
                [newStock, newWAC, product_id]
            );

            // Record Batch if provided or if product tracks expiration
            const finalBatchNumber = batch_number || `BAT-${Date.now()}`;
            await client.query(
                `INSERT INTO stock_batches (
                    product_id, batch_number, quantity_received, remaining_quantity,
                    expiration_date, received_date, created_at
                ) VALUES ($1, $2, $3, $4, $5, CURRENT_DATE, NOW())`,
                [
                    product_id, finalBatchNumber, qty, qty,
                    expiration_date || null
                ]
            );

            // Generate Transaction Number
            const txnNumber = `TXN-IN-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
            await client.query(
                `INSERT INTO inventory_transactions (
                    transaction_number, product_id, transaction_type, quantity,
                    previous_stock, new_stock, reference_type, reference_id,
                    employee_id, remarks, transaction_date, created_at
                ) VALUES ($1, $2, 'STOCK_IN', $3, $4, $5, $6, $7, $8, $9, NOW(), NOW())`,
                [
                    txnNumber, product_id, qty, prevStock, newStock,
                    reference_type || 'MANUAL_STOCK_IN', reference_id || null,
                    employeeId, remarks || 'Stock received'
                ]
            );

            await client.query('COMMIT');
            await logActivity(employeeId, 'STOCK_IN', 'INVENTORY', `Stock-in of ${qty} units for ${product.product_code}. New stock: ${newStock}`);

            return {
                success: true,
                transaction_number: txnNumber,
                previous_stock: prevStock,
                new_stock: newStock,
                weighted_average_cost: newWAC
            };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }

    /**
     * Record Outgoing Inventory (Stock-Out)
     */
    async stockOut(data, employeeId = 'EMP-001') {
        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            const {
                product_id, quantity, reason, remarks,
                reference_type, reference_id
            } = data;

            const qty = parseFloat(quantity);
            if (isNaN(qty) || qty <= 0) {
                throw new Error('Stock-out quantity must be a positive number.');
            }

            const prodRes = await client.query(
                `SELECT id, product_code, product_name, current_stock 
                 FROM products WHERE id = $1 FOR UPDATE`,
                [product_id]
            );

            if (prodRes.rows.length === 0) {
                throw new Error(`Product ID ${product_id} not found.`);
            }

            const product = prodRes.rows[0];
            const prevStock = parseFloat(product.current_stock || 0);

            if (prevStock < qty) {
                throw new Error(`Insufficient stock for ${product.product_name}. Available: ${prevStock}, Requested: ${qty}`);
            }

            const newStock = prevStock - qty;

            // Update Product
            await client.query(
                `UPDATE products SET current_stock = $1, updated_at = NOW() WHERE id = $2`,
                [newStock, product_id]
            );

            // Deduct batches using FEFO
            await this.deductBatchesFEFO(client, product_id, qty);

            // Map reason to transaction_type
            let txnType = 'STOCK_OUT';
            const rUpper = (reason || '').toUpperCase();
            if (rUpper.includes('DAMAGE')) txnType = 'DAMAGE';
            else if (rUpper.includes('EXPIRED')) txnType = 'EXPIRED';
            else if (rUpper.includes('TRANSFER')) txnType = 'TRANSFER_OUT';

            const txnNumber = `TXN-OUT-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
            await client.query(
                `INSERT INTO inventory_transactions (
                    transaction_number, product_id, transaction_type, quantity,
                    previous_stock, new_stock, reference_type, reference_id,
                    employee_id, remarks, transaction_date, created_at
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, NOW(), NOW())`,
                [
                    txnNumber, product_id, txnType, -qty, prevStock, newStock,
                    reference_type || 'MANUAL_STOCK_OUT', reference_id || null,
                    employeeId, `${reason}: ${remarks || ''}`
                ]
            );

            await client.query('COMMIT');
            await logActivity(employeeId, 'STOCK_OUT', 'INVENTORY', `Stock-out of ${qty} units for ${product.product_code}. Reason: ${reason}`);

            return {
                success: true,
                transaction_number: txnNumber,
                previous_stock: prevStock,
                new_stock: newStock
            };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }

    /**
     * Deducts quantity across active batches using First-Expired, First-Out (FEFO)
     */
    async deductBatchesFEFO(client, productId, requiredQty) {
        // Retrieve available batches ordered by expiration_date ASC, then received_date ASC
        const batchRes = await client.query(
            `SELECT id, batch_number, remaining_quantity, expiration_date 
             FROM stock_batches 
             WHERE product_id = $1 AND remaining_quantity > 0
             ORDER BY 
                CASE WHEN expiration_date IS NULL THEN 1 ELSE 0 END,
                expiration_date ASC,
                received_date ASC`,
            [productId]
        );

        let qtyToDeduct = requiredQty;

        for (const batch of batchRes.rows) {
            if (qtyToDeduct <= 0) break;

            const remaining = parseFloat(batch.remaining_quantity);
            const deduction = Math.min(remaining, qtyToDeduct);
            const newRemaining = remaining - deduction;

            await client.query(
                `UPDATE stock_batches SET remaining_quantity = $1 WHERE id = $2`,
                [newRemaining, batch.id]
            );

            qtyToDeduct -= deduction;
        }

        return true;
    }

    /**
     * Stock Adjustment Workflow
     */
    async requestAdjustment(data, employeeId = 'EMP-001') {
        const { product_id, actual_quantity, reason } = data;
        const actual = parseFloat(actual_quantity);

        const prodRes = await db.query(`SELECT current_stock, product_name FROM products WHERE id = $1`, [product_id]);
        if (prodRes.rows.length === 0) throw new Error('Product not found.');

        const currentStock = parseFloat(prodRes.rows[0].current_stock);
        const difference = actual - currentStock;
        const adjNumber = `ADJ-${Date.now()}`;

        await db.query(
            `INSERT INTO stock_adjustments (
                adjustment_number, product_id, system_quantity, actual_quantity,
                difference, reason, requested_by, status, created_at, updated_at
            ) VALUES ($1, $2, $3, $4, $5, $6, $7, 'PENDING', NOW(), NOW())`,
            [adjNumber, product_id, currentStock, actual, difference, reason, employeeId]
        );

        await logActivity(employeeId, 'ADJUSTMENT_REQUESTED', 'INVENTORY', `Adjustment requested for ${prodRes.rows[0].product_name}: Diff ${difference}`);
        return { success: true, adjustment_number: adjNumber, difference };
    }

    async approveAdjustment(adjustmentId, approverEmployeeId = 'EMP-001') {
        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            const adjRes = await client.query(
                `SELECT * FROM stock_adjustments WHERE id = $1 FOR UPDATE`,
                [adjustmentId]
            );
            if (adjRes.rows.length === 0) throw new Error('Adjustment record not found.');
            const adj = adjRes.rows[0];

            if (adj.status !== 'PENDING') {
                throw new Error(`Adjustment has already been ${adj.status}.`);
            }

            const prodRes = await client.query(
                `SELECT current_stock, product_code FROM products WHERE id = $1 FOR UPDATE`,
                [adj.product_id]
            );
            const prevStock = parseFloat(prodRes.rows[0].current_stock);
            const newStock = parseFloat(adj.actual_quantity);

            // Update product stock to match actual count
            await client.query(
                `UPDATE products SET current_stock = $1, updated_at = NOW() WHERE id = $2`,
                [newStock, adj.product_id]
            );

            // Record transaction
            const txnNumber = `TXN-ADJ-${Date.now()}`;
            await client.query(
                `INSERT INTO inventory_transactions (
                    transaction_number, product_id, transaction_type, quantity,
                    previous_stock, new_stock, reference_type, reference_id,
                    employee_id, remarks, transaction_date, created_at
                ) VALUES ($1, $2, 'ADJUSTMENT', $3, $4, $5, 'STOCK_ADJUSTMENT', $6, $7, $8, NOW(), NOW())`,
                [
                    txnNumber, adj.product_id, adj.difference, prevStock, newStock,
                    adj.adjustment_number, approverEmployeeId, `Approved: ${adj.reason}`
                ]
            );

            // Update adjustment record
            await client.query(
                `UPDATE stock_adjustments SET status = 'APPROVED', approved_by = $1, updated_at = NOW() WHERE id = $2`,
                [approverEmployeeId, adjustmentId]
            );

            await client.query('COMMIT');
            await logActivity(approverEmployeeId, 'ADJUSTMENT_APPROVED', 'INVENTORY', `Approved adjustment ${adj.adjustment_number}`);

            return { success: true, message: 'Stock adjustment applied.' };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }

    async getTransactions(limit = 100) {
        const res = await db.query(
            `SELECT t.*, p.product_name, p.product_code, u.abbreviation AS unit
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             ORDER BY t.created_at DESC
             LIMIT $1`,
            [limit]
        );
        return res.rows;
    }

    async getBatches() {
        const res = await db.query(
            `SELECT b.*, p.product_name, p.product_code, c.category_name, u.abbreviation AS unit
             FROM stock_batches b
             JOIN products p ON b.product_id = p.id
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             ORDER BY b.expiration_date ASC NULLS LAST, b.id DESC`
        );
        return res.rows;
    }

    async getAdjustments() {
        const res = await db.query(
            `SELECT a.*, p.product_name, p.product_code, u.abbreviation AS unit
             FROM stock_adjustments a
             JOIN products p ON a.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             ORDER BY a.created_at DESC`
        );
        return res.rows;
    }
}

module.exports = new InventoryService();
