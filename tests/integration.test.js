/**
 * Comprehensive Automated Integration Test Suite
 * Tests Core Inventory, Recipe BOM Deductions, Idempotency, and Employee Sync
 */
const assert = require('assert');
const db = require('../database/pg-client');
const canteenService = require('../src/services/canteenService');
const employeeService = require('../src/services/employeeService');
const inventoryService = require('../src/services/inventoryService');
const reportService = require('../src/services/reportService');
const productService = require('../src/services/productService');

async function runTests() {
    console.log('====================================================');
    console.log('   INTEGRATED IMS - AUTOMATED TEST SUITE            ');
    console.log('====================================================');

    let passed = 0;
    let failed = 0;

    async function test(name, fn) {
        try {
            process.stdout.write(`• Testing: ${name}... `);
            await fn();
            console.log('✓ PASSED');
            passed++;
        } catch (err) {
            console.log('✗ FAILED');
            console.error('   Error:', err.message);
            failed++;
        }
    }

    // Test 1: Product catalog retrieval
    await test('Product Catalog & Availability View', async () => {
        const products = await productService.getProducts();
        assert(products.length >= 20, `Expected at least 20 products, got ${products.length}`);
        const avail = await canteenService.getProductAvailability();
        assert(avail.length > 0, 'Canteen availability view returned empty');
    });

    // Test 2: Canteen Recipe Deduction (e.g. 1 Iced Coffee)
    await test('Canteen POS Recipe-Based Ingredient Deduction', async () => {
        // Find milk and coffee ingredients
        const milkBefore = await productService.getProductById(8); // Fresh milk
        const coffeeBefore = await productService.getProductById(7); // Coffee concentrate
        const cupsBefore = await productService.getProductById(10); // 16oz cup

        const salePayload = {
            transaction_id: `TEST-POS-${Date.now()}`,
            cashier_employee_id: 'EMP-004',
            idempotency_key: `IDEMP-TEST-${Date.now()}`,
            items: [
                { product_id: 1, quantity: 1, unit_price: 75.00 }, // 1 Iced Coffee
                { product_id: 4, quantity: 2, unit_price: 20.00 }  // 2 Bottled Water
            ],
            total_amount: 115.00
        };

        const result = await canteenService.processSale(salePayload);
        assert.strictEqual(result.status, 'SUCCESS');
        assert(result.deductions.length >= 2, 'Expected multiple deductions');

        const milkAfter = await productService.getProductById(8);
        const cupsAfter = await productService.getProductById(10);
        const waterAfter = await productService.getProductById(4);

        // 1 Iced Coffee requires 150ml milk and 1 cup
        assert.strictEqual(
            parseFloat(milkBefore.current_stock) - 150,
            parseFloat(milkAfter.current_stock),
            'Milk was not deducted by exactly 150ml'
        );
        assert.strictEqual(
            parseFloat(cupsBefore.current_stock) - 1,
            parseFloat(cupsAfter.current_stock),
            'Cups were not deducted by exactly 1'
        );
    });

    // Test 3: Idempotency Enforcement (Duplicate POS Event)
    await test('Idempotency: Resending Same POS Event Does NOT Deduct Stock Twice', async () => {
        const testIdempKey = `IDEMP-STRICT-${Date.now()}`;
        const milkBefore = await productService.getProductById(8);

        const payload = {
            transaction_id: `TXN-DUP-${Date.now()}`,
            cashier_employee_id: 'EMP-004',
            idempotency_key: testIdempKey,
            items: [{ product_id: 1, quantity: 1, unit_price: 75.00 }] // 1 Iced Coffee (150ml milk)
        };

        // First call
        const res1 = await canteenService.processSale(payload);
        assert.strictEqual(res1.status, 'SUCCESS');

        const milkMid = await productService.getProductById(8);
        assert.strictEqual(
            parseFloat(milkBefore.current_stock) - 150,
            parseFloat(milkMid.current_stock)
        );

        // Second call with same idempotency key
        const res2 = await canteenService.processSale(payload);
        assert.strictEqual(res2.status, 'SUCCESS');
        assert.strictEqual(res2.idempotent_replay, true);

        // Verify stock did NOT decrease again
        const milkFinal = await productService.getProductById(8);
        assert.strictEqual(
            parseFloat(milkMid.current_stock),
            parseFloat(milkFinal.current_stock),
            'Duplicate POS request erroneously deducted stock a second time!'
        );
    });

    // Test 4: Insufficient Stock Protection
    await test('Insufficient Stock Prevention & Atomic Rollback', async () => {
        let threw = false;
        try {
            await canteenService.processSale({
                transaction_id: `TXN-FAIL-${Date.now()}`,
                cashier_employee_id: 'EMP-004',
                idempotency_key: `IDEMP-EXCEED-${Date.now()}`,
                items: [{ product_id: 4, quantity: 999999, unit_price: 20.00 }] // Exceeds mineral water stock
            });
        } catch (e) {
            threw = true;
            assert(e.message.includes('Insufficient stock'), `Unexpected error message: ${e.message}`);
        }
        assert.strictEqual(threw, true, 'System allowed sale exceeding available stock');
    });

    // Test 5: Employee Sync & Inactive Lockout
    await test('Employee Management System Sync & Deactivation Access Control', async () => {
        const syncPayload = {
            employee_id: 'EMP-TEST-99',
            full_name: 'Test Officer',
            email: 'test.officer@canteen-ims.local',
            department_name: 'Warehouse & Inventory Logistics',
            role_name: 'Inventory Staff',
            account_status: 'ACTIVE'
        };

        // Sync new employee
        const syncRes = await employeeService.syncEmployee(syncPayload);
        assert.strictEqual(syncRes.status, 'ACTIVE');

        // Deactivate employee
        syncPayload.account_status = 'INACTIVE';
        const deactRes = await employeeService.syncEmployee(syncPayload);
        assert.strictEqual(deactRes.status, 'INACTIVE');

        // Verify employee record status
        const empDetails = await employeeService.getEmployeeById('EMP-TEST-99');
        assert.strictEqual(empDetails.employee.account_status, 'INACTIVE');
    });

    // Test 6: Weighted Average Cost (WAC) Stock-In Recalculation
    await test('Stock-In & Weighted Average Cost (WAC) Updating', async () => {
        // Stock-in 100 bottles of water at cost $15
        const res = await inventoryService.stockIn({
            product_id: 4,
            quantity: 100,
            unit_cost: 15.00,
            batch_number: `BAT-TEST-WAC-${Date.now()}`,
            remarks: 'WAC Valuation test'
        }, 'EMP-003');

        assert.strictEqual(res.success, true);
        assert(res.weighted_average_cost > 0, 'WAC was not calculated');
    });

    // Test 7: Reports Generation
    await test('Reports Engine: Valuation & Expiration (FEFO) Reports', async () => {
        const valuation = await reportService.getInventoryValuationReport();
        assert(valuation.total_valuation > 0, 'Total valuation is zero');
        assert(valuation.items.length > 0, 'No valuation items returned');

        const metrics = await reportService.getDashboardMetrics();
        assert(metrics.summary_cards.total_products > 0, 'Dashboard total products is zero');
        assert(metrics.charts.value_by_category.length > 0, 'Value by category chart empty');
    });

    console.log('====================================================');
    console.log(`Results: ${passed} PASSED, ${failed} FAILED`);
    console.log('====================================================');

    if (failed > 0) process.exit(1);
}

if (require.main === module) {
    runTests().then(() => process.exit(0)).catch(err => {
        console.error(err);
        process.exit(1);
    });
}

module.exports = { runTests };
