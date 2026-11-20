/**
 * Canteen POS Integration Service
 * Manages Sales Synchronization, Recipe-Based BOM Deduction,
 * FEFO Batch Allocation, Idempotency, Cancellations, and Refunds.
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');
const inventoryService = require('./inventoryService');

class CanteenService {
    /**
     * Process Completed Canteen POS Sale
     */
    async processSale(payload) {
        const {
            transaction_id,
            cashier_employee_id,
            idempotency_key,
            items, // Array of { product_id, quantity, unit_price }
            payment_method,
            total_amount
        } = payload;

        if (!transaction_id || !items || !Array.isArray(items) || items.length === 0) {
            throw new Error('Invalid POS transaction payload: Missing transaction_id or items array.');
        }

        const effectiveKey = idempotency_key || `POS_SALE_${transaction_id}`;

        // Step 1: Check Idempotency in integration_events
        const existingEvent = await db.query(
            `SELECT * FROM integration_events WHERE idempotency_key = $1`,
            [effectiveKey]
        );

        if (existingEvent.rows.length > 0) {
            const event = existingEvent.rows[0];
            if (event.processing_status === 'PROCESSED') {
                return {
                    status: 'SUCCESS',
                    idempotent_replay: true,
                    message: 'Transaction was already processed successfully.',
                    transaction_id: event.reference_id,
                    processed_at: event.processed_at
                };
            }
        }

        const client = await db.getClient();
        const deductionSummary = [];

        try {
            await client.query('BEGIN');

            // Log event as PENDING
            await client.query(
                `INSERT INTO integration_events (
                    source_system, event_type, reference_id, idempotency_key, payload,
                    processing_status, created_at
                ) VALUES ('CANTEEN_POS', 'SALE_COMPLETED', $1, $2, $3, 'PENDING', NOW())
                ON CONFLICT (idempotency_key) DO UPDATE 
                SET processing_status = 'PENDING', error_message = NULL`,
                [transaction_id, effectiveKey, JSON.stringify(payload)]
            );

            // Step 2: Process each sold item
            for (const item of items) {
                const productId = parseInt(item.product_id, 10);
                const soldQty = parseFloat(item.quantity);

                if (isNaN(soldQty) || soldQty <= 0) {
                    throw new Error(`Invalid quantity ${item.quantity} for item ${productId}`);
                }

                // Check if product has a recipe (Bill of Materials)
                const recipeRes = await client.query(
                    `SELECT r.id AS recipe_id, r.recipe_name, r.serving_size, p.product_name, p.product_code
                     FROM product_recipes r
                     JOIN products p ON r.finished_product_id = p.id
                     WHERE r.finished_product_id = $1 AND r.status = 'ACTIVE'`,
                    [productId]
                );

                if (recipeRes.rows.length > 0) {
                    // Recipe-based Item (e.g. Iced Coffee, Club Sandwich, Milk Tea)
                    const recipe = recipeRes.rows[0];
                    const itemsRes = await client.query(
                        `SELECT ri.*, p.product_name, p.product_code, p.current_stock, u.abbreviation AS unit
                         FROM product_recipe_items ri
                         JOIN products p ON ri.ingredient_product_id = p.id
                         JOIN units u ON ri.unit_id = u.id
                         WHERE ri.recipe_id = $1`,
                        [recipe.recipe_id]
                    );

                    if (itemsRes.rows.length === 0) {
                        throw new Error(`Recipe for '${recipe.product_name}' has no configured ingredients.`);
                    }

                    // Deduct each ingredient
                    for (const ing of itemsRes.rows) {
                        const neededQty = parseFloat(ing.quantity_required) * soldQty;
                        const currentStock = parseFloat(ing.current_stock);

                        if (currentStock < neededQty) {
                            throw new Error(
                                `Insufficient ingredient stock: '${ing.product_name}' requires ${neededQty} ${ing.unit}, but only ${currentStock} ${ing.unit} is available in stock.`
                            );
                        }

                        const newStock = currentStock - neededQty;

                        // Deduct product stock
                        await client.query(
                            `UPDATE products SET current_stock = $1, updated_at = NOW() WHERE id = $2`,
                            [newStock, ing.ingredient_product_id]
                        );

                        // Deduct batches using FEFO
                        await inventoryService.deductBatchesFEFO(client, ing.ingredient_product_id, neededQty);

                        // Record inventory transaction
                        const txnNum = `TXN-RECIPE-${Date.now()}-${ing.ingredient_product_id}`;
                        await client.query(
                            `INSERT INTO inventory_transactions (
                                transaction_number, product_id, transaction_type, quantity,
                                previous_stock, new_stock, reference_type, reference_id,
                                employee_id, remarks, transaction_date, created_at
                            ) VALUES ($1, $2, 'SALE', $3, $4, $5, 'CANTEEN_SALE', $6, $7, $8, NOW(), NOW())`,
                            [
                                txnNum, ing.ingredient_product_id, -neededQty, currentStock, newStock,
                                transaction_id, cashier_employee_id || 'CASHIER',
                                `Ingredient deduction for ${soldQty}x ${recipe.product_name} (${recipe.recipe_name})`
                            ]
                        );

                        deductionSummary.push({
                            type: 'INGREDIENT',
                            product_id: ing.ingredient_product_id,
                            product_name: ing.product_name,
                            deducted_quantity: neededQty,
                            remaining_stock: newStock,
                            unit: ing.unit
                        });
                    }
                } else {
                    // Direct Product Item (e.g. Bottled Water, Soda Can, Chips)
                    const prodRes = await client.query(
                        `SELECT id, product_code, product_name, current_stock, minimum_stock
                         FROM products WHERE id = $1 FOR UPDATE`,
                        [productId]
                    );

                    if (prodRes.rows.length === 0) {
                        throw new Error(`Product with ID ${productId} not found.`);
                    }

                    const product = prodRes.rows[0];
                    const currentStock = parseFloat(product.current_stock);

                    if (currentStock < soldQty) {
                        throw new Error(
                            `Insufficient stock for '${product.product_name}'. Available: ${currentStock}, Requested: ${soldQty}.`
                        );
                    }

                    const newStock = currentStock - soldQty;

                    // Deduct product stock
                    await client.query(
                        `UPDATE products SET current_stock = $1, updated_at = NOW() WHERE id = $2`,
                        [newStock, productId]
                    );

                    // Deduct batches using FEFO
                    await inventoryService.deductBatchesFEFO(client, productId, soldQty);

                    // Record inventory transaction
                    const txnNum = `TXN-SALE-${Date.now()}-${productId}`;
                    await client.query(
                        `INSERT INTO inventory_transactions (
                            transaction_number, product_id, transaction_type, quantity,
                            previous_stock, new_stock, reference_type, reference_id,
                            employee_id, remarks, transaction_date, created_at
                        ) VALUES ($1, $2, 'SALE', $3, $4, $5, 'CANTEEN_SALE', $6, $7, $8, NOW(), NOW())`,
                        [
                            txnNum, productId, -soldQty, currentStock, newStock,
                            transaction_id, cashier_employee_id || 'CASHIER',
                            `Direct canteen sale for transaction ${transaction_id}`
                        ]
                    );

                    deductionSummary.push({
                        type: 'DIRECT_PRODUCT',
                        product_id: productId,
                        product_name: product.product_name,
                        deducted_quantity: soldQty,
                        remaining_stock: newStock
                    });
                }
            }

            // Step 3: Mark integration event as PROCESSED
            await client.query(
                `UPDATE integration_events 
                 SET processing_status = 'PROCESSED', processed_at = NOW() 
                 WHERE idempotency_key = $1`,
                [effectiveKey]
            );

            await client.query('COMMIT');

            await logActivity(
                cashier_employee_id || 'CASHIER',
                'CANTEEN_SALE_PROCESSED',
                'POS_INTEGRATION',
                `POS sale ${transaction_id} completed: ${items.length} items sold`
            );

            return {
                status: 'SUCCESS',
                message: 'Canteen POS sale processed and inventory deducted successfully.',
                transaction_id,
                deductions: deductionSummary,
                processed_at: new Date().toISOString()
            };
        } catch (err) {
            await client.query('ROLLBACK');

            // Record failure in integration_events
            await db.query(
                `UPDATE integration_events 
                 SET processing_status = 'FAILED', error_message = $1 
                 WHERE idempotency_key = $2`,
                [err.message, effectiveKey]
            );

            await logActivity(
                cashier_employee_id || 'CASHIER',
                'CANTEEN_SALE_FAILED',
                'POS_INTEGRATION',
                `POS sale ${transaction_id} failed: ${err.message}`
            );

            throw err;
        } finally {
            client.release();
        }
    }

    /**
     * Process POS Transaction Cancellation
     */
    async cancelSale(payload) {
        const { transaction_id, cashier_employee_id, reason } = payload;
        if (!transaction_id) throw new Error('Missing transaction_id for cancellation.');

        // Find original sale transactions
        const txns = await db.query(
            `SELECT * FROM inventory_transactions 
             WHERE reference_type = 'CANTEEN_SALE' AND reference_id = $1`,
            [transaction_id]
        );

        if (txns.rows.length === 0) {
            throw new Error(`No inventory deduction records found for transaction ${transaction_id}`);
        }

        const client = await db.getClient();
        try {
            await client.query('BEGIN');

            for (const t of txns.rows) {
                const returnQty = Math.abs(parseFloat(t.quantity));

                const prodRes = await client.query(
                    `SELECT current_stock FROM products WHERE id = $1 FOR UPDATE`,
                    [t.product_id]
                );
                const prevStock = parseFloat(prodRes.rows[0].current_stock);
                const newStock = prevStock + returnQty;

                await client.query(
                    `UPDATE products SET current_stock = $1, updated_at = NOW() WHERE id = $2`,
                    [newStock, t.product_id]
                );

                const revTxnNum = `TXN-CANCEL-${Date.now()}-${t.product_id}`;
                await client.query(
                    `INSERT INTO inventory_transactions (
                        transaction_number, product_id, transaction_type, quantity,
                        previous_stock, new_stock, reference_type, reference_id,
                        employee_id, remarks, transaction_date, created_at
                    ) VALUES ($1, $2, 'RETURN', $3, $4, $5, 'SALE_CANCELED', $6, $7, $8, NOW(), NOW())`,
                    [
                        revTxnNum, t.product_id, returnQty, prevStock, newStock,
                        transaction_id, cashier_employee_id || 'CASHIER',
                        `Canceled sale reversal: ${reason || 'Customer canceled'}`
                    ]
                );
            }

            await client.query(
                `INSERT INTO integration_events (
                    source_system, event_type, reference_id, idempotency_key, payload,
                    processing_status, created_at, processed_at
                ) VALUES ('CANTEEN_POS', 'SALE_CANCELED', $1, $2, $3, 'PROCESSED', NOW(), NOW())`,
                [transaction_id, `CANCEL_${transaction_id}_${Date.now()}`, JSON.stringify(payload)]
            );

            await client.query('COMMIT');
            await logActivity(cashier_employee_id || 'CASHIER', 'CANTEEN_SALE_CANCELED', 'POS_INTEGRATION', `Sale ${transaction_id} canceled and stock restored.`);

            return { success: true, message: 'Sale canceled and stock successfully restored.' };
        } catch (err) {
            await client.query('ROLLBACK');
            throw err;
        } finally {
            client.release();
        }
    }

    /**
     * Process POS Refund with Optional Physical Return Restocking
     */
    async refundSale(payload) {
        const { transaction_id, cashier_employee_id, reason, restock_inventory } = payload;
        if (!transaction_id) throw new Error('Missing transaction_id for refund.');

        if (!restock_inventory) {
            // Refund issued financially without inventory return (e.g. food damaged/consumed)
            await logActivity(
                cashier_employee_id || 'CASHIER',
                'CANTEEN_REFUND_FINANCIAL_ONLY',
                'POS_INTEGRATION',
                `Refund for ${transaction_id} processed without inventory restock. Reason: ${reason}`
            );
            return {
                success: true,
                restocked: false,
                message: 'Refund recorded financially. Inventory was not restored per policy.'
            };
        }

        // Reversal with physical return
        return this.cancelSale(payload);
    }

    /**
     * Retrieve Live Product Availability for POS
     */
    async getProductAvailability() {
        const res = await db.query(`SELECT * FROM v_canteen_product_availability`);
        return res.rows;
    }
}

module.exports = new CanteenService();
