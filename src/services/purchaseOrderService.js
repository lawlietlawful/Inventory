/**
 * Purchase Order Management Service
 * Manages PO Lifecycle, Multi-stage Receiving, and Automatic WAC Valuation Updates
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');
const inventoryService = require('./inventoryService');

class PurchaseOrderService {
    async getPurchaseOrders(filters = {}) {
        let sql = `
            SELECT po.*, s.supplier_name, s.supplier_code, s.contact_person
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            WHERE 1=1
        `;
        const params = [];
        let pIdx = 1;

        if (filters.status) {
            sql += ` AND po.status = $${pIdx}`;
            params.push(filters.status);
            pIdx++;
        }

        if (filters.supplier_id) {
            sql += ` AND po.supplier_id = $${pIdx}`;
            params.push(filters.supplier_id);
            pIdx++;
        }

        sql += ` ORDER BY po.id DESC`;

        const res = await db.query(sql, params);
        return res.rows;
    }

    async getPurchaseOrderById(id) {
        const poRes = await db.query(
            `SELECT po.*, s.supplier_name, s.supplier_code, s.contact_person, s.contact_number, s.email, s.address
             FROM purchase_orders po
             JOIN suppliers s ON po.supplier_id = s.id
             WHERE po.id = $1`,
            [id]
        );

        if (poRes.rows.length === 0) {
            throw new Error(`Purchase Order #${id} not found.`);
        }

        const po = poRes.rows[0];

        // Fetch PO items
        const itemsRes = await db.query(
            `SELECT poi.*, p.product_name, p.product_code, u.abbreviation AS unit
             FROM purchase_order_items poi
             JOIN products p ON poi.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             WHERE poi.purchase_order_id = $1`,
            [id]
        );

        po.items = itemsRes.rows;
        return po;
    }

    async createPurchaseOrder(data, employeeId = 'EMP-002') {
        const {
            supplier_id, expected_delivery_date, items, remarks
        } = data;

        if (!supplier_id || !items || !Array.isArray(items) || items.length === 0) {
            throw new Error('Invalid PO data: Supplier and at least one item are required.');
        }

        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            const poNumber = `PO-${new Date().getFullYear()}-${String(Date.now()).slice(-4)}`;
            let totalAmount = 0;

            // Calculate total amount
            items.forEach(it => {
                const subtotal = parseFloat(it.quantity_ordered) * parseFloat(it.unit_cost);
                totalAmount += subtotal;
            });

            const poRes = await client.query(
                `INSERT INTO purchase_orders (
                    purchase_order_number, supplier_id, requested_by, order_date,
                    expected_delivery_date, total_amount, status, remarks, created_at, updated_at
                ) VALUES ($1, $2, $3, CURRENT_DATE, $4, $5, 'PENDING APPROVAL', $6, NOW(), NOW())
                RETURNING id`,
                [
                    poNumber, supplier_id, employeeId, expected_delivery_date || null,
                    totalAmount, remarks || null
                ]
            );

            const poId = poRes.rows[0].id;

            for (const it of items) {
                const qty = parseFloat(it.quantity_ordered);
                const cost = parseFloat(it.unit_cost);
                const subtotal = qty * cost;

                await client.query(
                    `INSERT INTO purchase_order_items (
                        purchase_order_id, product_id, quantity_ordered, quantity_received,
                        unit_cost, subtotal
                    ) VALUES ($1, $2, $3, 0.00, $4, $5)`,
                    [poId, it.product_id, qty, cost, subtotal]
                );
            }

            await client.query('COMMIT');
            await logActivity(employeeId, 'PO_CREATED', 'PURCHASE_ORDERS', `Created PO ${poNumber} for total $${totalAmount}`);

            return {
                success: true,
                purchase_order_id: poId,
                purchase_order_number: poNumber,
                total_amount: totalAmount
            };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }

    async updateStatus(id, status, employeeId = 'EMP-001') {
        const poRes = await db.query(`SELECT purchase_order_number, status FROM purchase_orders WHERE id = $1`, [id]);
        if (poRes.rows.length === 0) throw new Error('Purchase Order not found.');

        const po = poRes.rows[0];
        let approvedByClause = '';
        const params = [status, id];

        if (status === 'APPROVED') {
            await db.query(
                `UPDATE purchase_orders SET status = $1, approved_by = $2, updated_at = NOW() WHERE id = $3`,
                [status, employeeId, id]
            );
        } else {
            await db.query(
                `UPDATE purchase_orders SET status = $1, updated_at = NOW() WHERE id = $2`,
                [status, id]
            );
        }

        await logActivity(employeeId, 'PO_STATUS_CHANGE', 'PURCHASE_ORDERS', `Changed PO ${po.purchase_order_number} status to ${status}`);
        return { success: true, message: `PO status updated to ${status}.` };
    }

    /**
     * Receive Deliveries against Purchase Order
     */
    async receiveDelivery(id, deliveryData, employeeId = 'EMP-003') {
        const { received_items } = deliveryData; // Array of { po_item_id, quantity_receiving, batch_number, expiration_date }
        if (!received_items || !Array.isArray(received_items) || received_items.length === 0) {
            throw new Error('Missing received items.');
        }

        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            const po = await this.getPurchaseOrderById(id);
            if (!['APPROVED', 'ORDERED', 'PARTIALLY RECEIVED'].includes(po.status)) {
                throw new Error(`Cannot receive delivery for PO in status '${po.status}'.`);
            }

            let allCompleted = true;

            for (const rx of received_items) {
                const rxQty = parseFloat(rx.quantity_receiving);
                if (rxQty <= 0) continue;

                const poiRes = await client.query(
                    `SELECT * FROM purchase_order_items WHERE id = $1 AND purchase_order_id = $2 FOR UPDATE`,
                    [rx.po_item_id, id]
                );

                if (poiRes.rows.length === 0) {
                    throw new Error(`PO item ID ${rx.po_item_id} not found in this order.`);
                }

                const poi = poiRes.rows[0];
                const prevRx = parseFloat(poi.quantity_received || 0);
                const newRx = prevRx + rxQty;
                const orderedQty = parseFloat(poi.quantity_ordered);

                if (newRx < orderedQty) {
                    allCompleted = false;
                }

                // Update PO Item quantity received
                await client.query(
                    `UPDATE purchase_order_items SET quantity_received = $1 WHERE id = $2`,
                    [newRx, poi.id]
                );

                // Update Product Stock & Recalculate WAC
                const prodRes = await client.query(
                    `SELECT id, product_code, product_name, current_stock, cost_price FROM products WHERE id = $1 FOR UPDATE`,
                    [poi.product_id]
                );
                const prod = prodRes.rows[0];
                const prevStock = parseFloat(prod.current_stock || 0);
                const currentWAC = parseFloat(prod.cost_price || 0);
                const itemUnitCost = parseFloat(poi.unit_cost);
                const newStock = prevStock + rxQty;

                let newWAC = currentWAC;
                if (newStock > 0) {
                    newWAC = ((prevStock * currentWAC) + (rxQty * itemUnitCost)) / newStock;
                    newWAC = Math.round(newWAC * 10000) / 10000;
                }

                await client.query(
                    `UPDATE products SET current_stock = $1, cost_price = $2, updated_at = NOW() WHERE id = $3`,
                    [newStock, newWAC, poi.product_id]
                );

                // Insert Stock Batch
                const batchNum = rx.batch_number || `BAT-${po.purchase_order_number}-${poi.id}`;
                await client.query(
                    `INSERT INTO stock_batches (
                        product_id, batch_number, purchase_order_item_id,
                        quantity_received, remaining_quantity, expiration_date,
                        received_date, created_at
                    ) VALUES ($1, $2, $3, $4, $5, $6, CURRENT_DATE, NOW())`,
                    [
                        poi.product_id, batchNum, poi.id, rxQty, rxQty,
                        rx.expiration_date || null
                    ]
                );

                // Log inventory transaction
                const txnNum = `TXN-PO-RX-${Date.now()}-${poi.id}`;
                await client.query(
                    `INSERT INTO inventory_transactions (
                        transaction_number, product_id, transaction_type, quantity,
                        previous_stock, new_stock, reference_type, reference_id,
                        employee_id, remarks, transaction_date, created_at
                    ) VALUES ($1, $2, 'STOCK_IN', $3, $4, $5, 'PURCHASE_ORDER', $6, $7, $8, NOW(), NOW())`,
                    [
                        txnNum, poi.product_id, rxQty, prevStock, newStock,
                        po.purchase_order_number, employeeId,
                        `Received delivery under PO ${po.purchase_order_number}. WAC updated to $${newWAC}`
                    ]
                );
            }

            // Check if any items remain unfulfilled across whole PO
            const remainingItemsRes = await client.query(
                `SELECT COUNT(*) AS unfulfilled FROM purchase_order_items 
                 WHERE purchase_order_id = $1 AND quantity_received < quantity_ordered`,
                [id]
            );

            const hasUnfulfilled = parseInt(remainingItemsRes.rows[0].unfulfilled, 10) > 0;
            const newPoStatus = hasUnfulfilled ? 'PARTIALLY RECEIVED' : 'COMPLETED';

            await client.query(
                `UPDATE purchase_orders SET status = $1, updated_at = NOW() WHERE id = $2`,
                [newPoStatus, id]
            );

            await client.query('COMMIT');
            await logActivity(employeeId, 'PO_DELIVERY_RECEIVED', 'PURCHASE_ORDERS', `Received delivery for PO ${po.purchase_order_number}. Status: ${newPoStatus}`);

            return {
                success: true,
                message: `Delivery successfully processed. PO status is now ${newPoStatus}.`,
                po_status: newPoStatus
            };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }
}

module.exports = new PurchaseOrderService();
