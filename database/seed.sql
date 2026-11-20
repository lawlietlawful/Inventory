-- ==============================================================================
-- SEED DATA: integrated_management_db
-- Integrated Inventory Management System (IMS)
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 17. DEPARTMENTS
-- ------------------------------------------------------------------------------
INSERT INTO departments (id, department_name, description) VALUES
(1, 'Executive & Administration', 'Executive management and general administration'),
(2, 'Food & Beverage (Canteen)', 'Canteen food preparation, service, and POS operations'),
(3, 'Warehouse & Inventory Logistics', 'Inventory storage, receiving, stock dispatch, and auditing'),
(4, 'Human Resources', 'Employee records, onboarding, departments, and personnel management'),
(5, 'Finance & Procurement', 'Purchasing, supplier accounts, and inventory valuation'),
(6, 'IT & Systems Operations', 'System administration, POS integration, and network maintenance')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 2. ROLES
-- ------------------------------------------------------------------------------
INSERT INTO roles (id, role_name, description) VALUES
(1, 'Administrator', 'Full system access to all modules, users, settings, and logs'),
(2, 'Inventory Manager', 'Manages products, suppliers, purchase orders, approvals, and reports'),
(3, 'Inventory Staff', 'Receives stock, records movements, logs damaged/expired goods'),
(4, 'Canteen Cashier', 'Operates POS terminal, views product availability, executes sales'),
(5, 'Employee', 'Regular employee with viewing rights and self-service requests')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 3. PERMISSIONS
-- ------------------------------------------------------------------------------
INSERT INTO permissions (id, permission_name, description) VALUES
(1, 'manage_users', 'Create, update, and manage user accounts'),
(2, 'manage_products', 'Create, update, and archive products'),
(3, 'view_products', 'View product details and stock status'),
(4, 'manage_inventory', 'Process stock-in, stock-out, adjustments, and transfers'),
(5, 'view_inventory', 'View inventory levels, batches, and movements'),
(6, 'manage_purchase_orders', 'Create and modify purchase orders'),
(7, 'approve_purchase_orders', 'Approve purchase orders for ordering'),
(8, 'receive_purchase_orders', 'Receive deliveries and update stock batches'),
(9, 'manage_suppliers', 'Manage supplier records and contracts'),
(10, 'manage_recipes', 'Create and configure product recipes and BOM items'),
(11, 'operate_pos', 'Process canteen POS transactions and lookups'),
(12, 'approve_adjustments', 'Authorize stock discrepancies and write-offs'),
(13, 'view_reports', 'Generate and export inventory, financial, and integration reports'),
(14, 'manage_settings', 'Modify system parameters, alerts, and integration hooks')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 4. ROLE_PERMISSIONS
-- ------------------------------------------------------------------------------
-- Administrator: All permissions (1..14)
INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14),
-- Inventory Manager: (2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 13)
(2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10), (2, 12), (2, 13),
-- Inventory Staff: (3, 4, 5, 6, 8, 10)
(3, 3), (3, 4), (3, 5), (3, 6), (3, 8), (3, 10),
-- Canteen Cashier: (3, 5, 11)
(4, 3), (4, 5), (4, 11),
-- Employee: (3, 5)
(5, 3), (5, 5)
ON CONFLICT (role_id, permission_id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 1. USERS (BCrypt hashed password for 'admin123' and 'password123')
-- $2a$10$wK1V312a0wI4n/j8p.B4pOU7sE9UHQXnsu5oH8GZ6s1G7XkUu22eO = admin123
-- ------------------------------------------------------------------------------
INSERT INTO users (id, employee_id, username, password, role_id, department_id, full_name, email, phone, account_status) VALUES
(1, 'EMP-001', 'admin', '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.', 1, 1, 'System Administrator', 'admin@canteen-ims.local', '555-0101', 'ACTIVE'),
(2, 'EMP-002', 'inv_mgr', '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.', 2, 3, 'Sarah Connor', 's.connor@canteen-ims.local', '555-0102', 'ACTIVE'),
(3, 'EMP-003', 'inv_staff', '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.', 3, 3, 'Marcus Vance', 'm.vance@canteen-ims.local', '555-0103', 'ACTIVE'),
(4, 'EMP-004', 'cashier1', '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.', 4, 2, 'Elena Rostova', 'e.rostova@canteen-ims.local', '555-0104', 'ACTIVE'),
(5, 'EMP-005', 'john_doe', '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.', 5, 4, 'John Doe', 'j.doe@canteen-ims.local', '555-0105', 'ACTIVE'),
(6, 'EMP-006', 'inactive_user', '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.', 5, 5, 'Thomas Wright', 't.wright@canteen-ims.local', '555-0106', 'INACTIVE')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 5. CATEGORIES
-- ------------------------------------------------------------------------------
INSERT INTO categories (id, category_name, description) VALUES
(1, 'Beverages', 'Prepared and pre-packaged drinks, sodas, and juices'),
(2, 'Food Products', 'Ready-to-eat meals, sandwiches, and hot snacks'),
(3, 'Ingredients', 'Raw materials, dairy, syrups, coffee, and culinary supplies'),
(4, 'Snacks & Confectionery', 'Packaged chips, biscuits, cookies, and candy'),
(5, 'Packaging Materials', 'Cups, lids, straws, paper wraps, and bags'),
(6, 'Cleaning Supplies', 'Detergents, disinfectants, sanitizers, and towels'),
(7, 'Office Supplies', 'POS receipt rolls, register supplies, and stationery')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 6. UNITS
-- ------------------------------------------------------------------------------
INSERT INTO units (id, unit_name, abbreviation) VALUES
(1, 'Piece', 'pcs'),
(2, 'Kilogram', 'kg'),
(3, 'Gram', 'g'),
(4, 'Liter', 'L'),
(5, 'Milliliter', 'ml'),
(6, 'Pack', 'pack'),
(7, 'Box', 'box'),
(8, 'Bottle', 'btl'),
(9, 'Can', 'can'),
(10, 'Roll', 'roll')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 7. PRODUCTS (Finished items, raw ingredients, and direct sales)
-- ------------------------------------------------------------------------------
INSERT INTO products (id, product_code, barcode, product_name, category_id, unit_id, description, cost_price, selling_price, minimum_stock, maximum_stock, current_stock, reorder_level, expiration_tracking, is_recipe_item, is_ingredient, status) VALUES
-- Finished Canteen Items (Recipe-based)
(1, 'PRD-BEV-001', '8901001001', 'Signature Iced Coffee', 1, 1, 'Cold brew coffee with fresh milk and caramel syrup', 35.0000, 75.0000, 10.00, 200.00, 50.00, 20.00, FALSE, TRUE, FALSE, 'ACTIVE'),
(2, 'PRD-FOD-001', '8901001002', 'Deluxe Club Sandwich', 2, 1, 'Toasted sandwich with smoked ham, cheddar cheese, and mayo', 45.0000, 95.0000, 5.00, 100.00, 30.00, 15.00, FALSE, TRUE, FALSE, 'ACTIVE'),
(3, 'PRD-BEV-002', '8901001003', 'Classic Milk Tea', 1, 1, 'Black tea with fresh milk and chewy tapioca pearls', 32.0000, 70.0000, 10.00, 150.00, 40.00, 20.00, FALSE, TRUE, FALSE, 'ACTIVE'),

-- Direct Canteen Items (Non-recipe)
(4, 'PRD-BEV-003', '8901002001', 'Bottled Mineral Water 500ml', 1, 8, 'Pure mountain mineral spring water', 10.0000, 20.0000, 24.00, 300.00, 120.00, 48.00, TRUE, FALSE, FALSE, 'ACTIVE'),
(5, 'PRD-BEV-004', '8901002002', 'Cola Soda Can 330ml', 1, 9, 'Chilled carbonated soft drink', 18.0000, 35.0000, 24.00, 240.00, 72.00, 36.00, TRUE, FALSE, FALSE, 'ACTIVE'),
(6, 'PRD-SNK-001', '8901003001', 'Artisan Potato Chips 60g', 4, 6, 'Crispy salted potato chips', 22.0000, 40.0000, 15.00, 120.00, 45.00, 25.00, TRUE, FALSE, FALSE, 'ACTIVE'),

-- Raw Ingredients & Packaging
(7, 'ING-COF-001', '8902001001', 'Espresso Roast Concentrate', 3, 5, 'High intensity cold drip coffee base', 0.2500, 0.0000, 500.00, 5000.00, 2500.00, 1000.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(8, 'ING-MLK-001', '8902001002', 'Pasteurized Whole Milk', 3, 5, 'Fresh homogenized dairy milk', 0.0800, 0.0000, 1000.00, 10000.00, 6000.00, 2000.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(9, 'ING-SYR-001', '8902001003', 'Natural Simple Cane Syrup', 3, 5, 'Sweetener syrup for cold drinks', 0.0500, 0.0000, 500.00, 5000.00, 3000.00, 1000.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(10, 'PKG-CUP-001', '8903001001', 'Cold Cup 16oz Clear PET', 5, 1, 'Clear recyclable beverage cups', 2.5000, 0.0000, 50.00, 1000.00, 350.00, 100.00, FALSE, FALSE, TRUE, 'ACTIVE'),
(11, 'PKG-LID-001', '8903001002', 'Dome Cup Lid 16oz', 5, 1, 'Matching tight-seal dome lids', 1.0000, 0.0000, 50.00, 1000.00, 350.00, 100.00, FALSE, FALSE, TRUE, 'ACTIVE'),
(12, 'ING-BRD-001', '8902002001', 'Whole Wheat Bread Slices', 3, 1, 'Fresh bakery sandwich bread slices', 1.5000, 0.0000, 20.00, 300.00, 100.00, 40.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(13, 'ING-HAM-001', '8902002002', 'Smoked Deli Ham Slices', 3, 1, 'Cured deli ham slices', 8.0000, 0.0000, 20.00, 200.00, 80.00, 40.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(14, 'ING-CHS-001', '8902002003', 'Cheddar Cheese Slices', 3, 1, 'Aged yellow cheddar cheese slices', 6.0000, 0.0000, 20.00, 200.00, 75.00, 40.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(15, 'ING-MAY-001', '8902002004', 'Classic Mayonnaise Sauce', 3, 3, 'Creamy sandwich spread', 0.0500, 0.0000, 200.00, 3000.00, 1200.00, 500.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(16, 'PKG-STR-001', '8903001003', 'Boba Straw Eco-Paper', 5, 1, 'Wide diameter biodegradable paper straws', 0.8000, 0.0000, 50.00, 1000.00, 400.00, 100.00, FALSE, FALSE, TRUE, 'ACTIVE'),
(17, 'ING-TEA-001', '8902003001', 'Assam Black Tea Leaves', 3, 3, 'Premium loose tea leaves for milk tea', 0.4000, 0.0000, 200.00, 2000.00, 800.00, 400.00, TRUE, FALSE, TRUE, 'ACTIVE'),
(18, 'ING-PRL-001', '8902003002', 'Brown Sugar Tapioca Pearls', 3, 3, 'Cooked brown sugar boba pearls', 0.1500, 0.0000, 500.00, 4000.00, 1500.00, 600.00, TRUE, FALSE, TRUE, 'ACTIVE'),

-- Operational and Maintenance Supplies
(19, 'SUP-CLN-001', '8904001001', 'Food Grade Sanitizer 5L', 6, 4, 'Antibacterial surface sanitizer for food prep', 250.0000, 0.0000, 2.00, 20.00, 8.00, 4.00, FALSE, FALSE, FALSE, 'ACTIVE'),
(20, 'SUP-POS-001', '8904002001', 'Thermal Receipt Paper 80mm', 7, 10, 'BPA-free thermal POS rolls', 35.0000, 0.0000, 10.00, 100.00, 25.00, 15.00, FALSE, FALSE, FALSE, 'ACTIVE')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 15. PRODUCT_RECIPES
-- ------------------------------------------------------------------------------
INSERT INTO product_recipes (id, finished_product_id, recipe_name, serving_size, status) VALUES
(1, 1, 'Standard Iced Coffee Recipe', 1.00, 'ACTIVE'),
(2, 2, 'Deluxe Club Sandwich Recipe', 1.00, 'ACTIVE'),
(3, 3, 'Classic Milk Tea Recipe', 1.00, 'ACTIVE')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 16. PRODUCT_RECIPE_ITEMS
-- ------------------------------------------------------------------------------
-- Recipe 1: Iced Coffee (1 cup, 1 lid, 50ml coffee, 150ml milk, 20ml syrup)
INSERT INTO product_recipe_items (recipe_id, ingredient_product_id, quantity_required, unit_id) VALUES
(1, 10, 1.0000, 1),  -- 1 Cup
(1, 11, 1.0000, 1),  -- 1 Lid
(1, 7, 50.0000, 5),  -- 50 ml Coffee Concentrate
(1, 8, 150.0000, 5), -- 150 ml Milk
(1, 9, 20.0000, 5),  -- 20 ml Syrup

-- Recipe 2: Club Sandwich (2 bread, 2 ham, 1 cheese, 15g mayo)
(2, 12, 2.0000, 1),  -- 2 slices Bread
(2, 13, 2.0000, 1),  -- 2 slices Ham
(2, 14, 1.0000, 1),  -- 1 slice Cheese
(2, 15, 15.0000, 3), -- 15 g Mayo

-- Recipe 3: Classic Milk Tea (1 cup, 1 lid, 1 straw, 20g tea, 100ml milk, 30g pearls)
(3, 10, 1.0000, 1),  -- 1 Cup
(3, 11, 1.0000, 1),  -- 1 Lid
(3, 16, 1.0000, 1),  -- 1 Boba Straw
(3, 17, 20.0000, 3), -- 20 g Black Tea
(3, 8, 100.0000, 5), -- 100 ml Milk
(3, 18, 30.0000, 3)  -- 30 g Tapioca Pearls
ON CONFLICT (recipe_id, ingredient_product_id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 8. SUPPLIERS
-- ------------------------------------------------------------------------------
INSERT INTO suppliers (id, supplier_code, supplier_name, contact_person, contact_number, email, address, status) VALUES
(1, 'SUP-001', 'Golden Roast Coffee Roasters', 'Arthur Dent', '+63 917 111 2233', 'orders@goldenroast.ph', 'Bldg 4, Coffee Trade Zone, Pasig City', 'ACTIVE'),
(2, 'SUP-002', 'Metro Dairy & Fresh Foods Corp', 'Rachel Green', '+63 920 333 4455', 'sales@metrodairy.com', 'Warehouse 12, Food Terminal, Taguig City', 'ACTIVE'),
(3, 'SUP-003', 'Apex Eco Packaging Solutions', 'Walter White', '+63 918 555 6677', 'inquiries@apexpack.ph', 'Lot 8, Industrial Park, Laguna', 'ACTIVE'),
(4, 'SUP-004', 'Universal Beverage & Snack Dist.', 'Diane Prince', '+63 905 777 8899', 'dispatch@unidist.com', 'KM 14 West Service Rd, Parañaque City', 'ACTIVE')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 9. SUPPLIER_PRODUCTS
-- ------------------------------------------------------------------------------
INSERT INTO supplier_products (supplier_id, product_id, supplier_product_code, purchase_price, lead_time_days) VALUES
(1, 7, 'GR-COF-5000', 0.2300, 2),
(1, 9, 'GR-SYR-5000', 0.0450, 2),
(1, 17, 'GR-TEA-1000', 0.3800, 3),
(2, 8, 'MD-MLK-10L', 0.0750, 1),
(2, 12, 'MD-BRD-50', 1.4000, 1),
(2, 13, 'MD-HAM-100', 7.5000, 2),
(2, 14, 'MD-CHS-100', 5.6000, 2),
(2, 15, 'MD-MAY-5K', 0.0450, 2),
(3, 10, 'AP-CUP-16', 2.3000, 3),
(3, 11, 'AP-LID-16', 0.9000, 3),
(3, 16, 'AP-STR-BOBA', 0.7000, 3),
(4, 4, 'UB-WTR-500', 9.2000, 2),
(4, 5, 'UB-COLA-CAN', 16.5000, 2),
(4, 6, 'UB-CHIP-60G', 20.0000, 2)
ON CONFLICT (supplier_id, product_id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 10. PURCHASE_ORDERS
-- ------------------------------------------------------------------------------
INSERT INTO purchase_orders (id, purchase_order_number, supplier_id, requested_by, approved_by, order_date, expected_delivery_date, total_amount, status, remarks) VALUES
(1, 'PO-2026-0001', 1, 'EMP-002', 'EMP-001', '2026-11-01', '2026-11-03', 1550.00, 'COMPLETED', 'Initial stock replenishment for coffee and tea bases'),
(2, 'PO-2026-0002', 2, 'EMP-002', 'EMP-001', '2026-11-10', '2026-11-12', 2840.00, 'COMPLETED', 'Fresh dairy, cheese, and deli meats for sandwiches'),
(3, 'PO-2026-0003', 4, 'EMP-003', 'EMP-002', '2026-11-18', '2026-11-20', 1850.00, 'PENDING APPROVAL', 'Weekly beverage and snack replenishment')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 11. PURCHASE_ORDER_ITEMS
-- ------------------------------------------------------------------------------
INSERT INTO purchase_order_items (id, purchase_order_id, product_id, quantity_ordered, quantity_received, unit_cost, subtotal) VALUES
(1, 1, 7, 5000.00, 5000.00, 0.2300, 1150.00),
(2, 1, 9, 5000.00, 5000.00, 0.0450, 225.00),
(3, 1, 17, 450.00, 450.00, 0.3800, 171.00),
(4, 2, 8, 8000.00, 8000.00, 0.0750, 600.00),
(5, 2, 13, 150.00, 150.00, 7.5000, 1125.00),
(6, 2, 14, 150.00, 150.00, 5.6000, 840.00),
(7, 3, 4, 100.00, 0.00, 9.2000, 920.00),
(8, 3, 5, 50.00, 0.00, 16.5000, 825.00)
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 12. STOCK_BATCHES (Perishable batches with FEFO dates)
-- ------------------------------------------------------------------------------
INSERT INTO stock_batches (id, product_id, batch_number, purchase_order_item_id, quantity_received, remaining_quantity, expiration_date, received_date) VALUES
-- Milk: Batch expiring in 3 days (CRITICAL FEFO), Batch expiring in 15 days
(1, 8, 'BAT-MLK-202611-01', 4, 3000.00, 1500.00, '2026-11-22', '2026-11-12'),
(2, 8, 'BAT-MLK-202611-02', 4, 5000.00, 4500.00, '2026-12-05', '2026-11-15'),

-- Coffee concentrate: Fresh
(3, 7, 'BAT-COF-202611-01', 1, 5000.00, 2500.00, '2027-02-15', '2026-11-03'),

-- Ham: Deli perishable
(4, 13, 'BAT-HAM-202611-01', 5, 150.00, 80.00, '2026-11-28', '2026-11-12'),

-- Cheese
(5, 14, 'BAT-CHS-202611-01', 6, 150.00, 75.00, '2026-12-20', '2026-11-12'),

-- Bottled Water
(6, 4, 'BAT-WTR-202610-01', NULL, 150.00, 120.00, '2027-10-01', '2026-10-25'),

-- Chips
(7, 6, 'BAT-CHP-202610-01', NULL, 60.00, 45.00, '2027-04-10', '2026-10-25')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 13. INVENTORY_TRANSACTIONS (Initial audit trail)
-- ------------------------------------------------------------------------------
INSERT INTO inventory_transactions (id, transaction_number, product_id, transaction_type, quantity, previous_stock, new_stock, reference_type, reference_id, employee_id, remarks) VALUES
(1, 'TXN-2026-0001', 7, 'STOCK_IN', 5000.00, 0.00, 5000.00, 'PURCHASE_ORDER', 'PO-2026-0001', 'EMP-003', 'Initial PO receiving from Golden Roast'),
(2, 'TXN-2026-0002', 8, 'STOCK_IN', 8000.00, 0.00, 8000.00, 'PURCHASE_ORDER', 'PO-2026-0002', 'EMP-003', 'Milk delivery received and inspected'),
(3, 'TXN-2026-0003', 4, 'STOCK_IN', 150.00, 0.00, 150.00, 'STOCK_IN', 'RECEIPT-099', 'EMP-003', 'Mineral water delivery from Universal Dist'),
(4, 'TXN-2026-0004', 4, 'SALE', -30.00, 150.00, 120.00, 'CANTEEN_SALE', 'POS-TXN-1001', 'EMP-004', 'Canteen cashier sales transaction'),
(5, 'TXN-2026-0005', 8, 'SALE', -2000.00, 8000.00, 6000.00, 'CANTEEN_SALE', 'POS-TXN-1002', 'EMP-004', 'Deducted for Iced Coffee sales via Recipe BOM')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 20. INVENTORY_NOTIFICATIONS
-- ------------------------------------------------------------------------------
INSERT INTO inventory_notifications (id, notification_type, product_id, message, priority, status) VALUES
(1, 'EXPIRING_SOON', 8, 'Fresh Whole Milk Batch BAT-MLK-202611-01 expires in 3 days! FEFO will prioritize this batch.', 'HIGH', 'UNREAD'),
(2, 'PO_APPROVAL', NULL, 'Purchase Order PO-2026-0003 requires review and approval from Inventory Manager.', 'MEDIUM', 'UNREAD'),
(3, 'LOW_STOCK', 20, 'Thermal Receipt Paper 80mm current stock (25 rolls) is approaching reorder level (15 rolls).', 'LOW', 'READ')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 23. SYSTEM_SETTINGS
-- ------------------------------------------------------------------------------
INSERT INTO system_settings (id, setting_name, setting_value, description) VALUES
(1, 'COMPANY_NAME', 'Apex Enterprise Canteen & IMS', 'Organization trading name'),
(2, 'VALUATION_METHOD', 'WEIGHTED_AVERAGE_COST', 'Default financial inventory valuation algorithm'),
(3, 'FEFO_ENFORCEMENT', 'TRUE', 'Automatically deduct from earliest expiring batches first'),
(4, 'EXPIRATION_ALERT_DAYS', '30,15,7,3', 'Comma separated thresholds for expiration warning notifications'),
(5, 'AUTO_DEACTIVATE_SYNC', 'TRUE', 'Instantly block IMS access when employee is deactivated in EMS'),
(6, 'ALLOW_NEGATIVE_STOCK', 'FALSE', 'Strictly prevent stock-out or sales when stock reaches zero')
ON CONFLICT (id) DO NOTHING;

-- ------------------------------------------------------------------------------
-- 21. ACTIVITY_LOGS
-- ------------------------------------------------------------------------------
INSERT INTO activity_logs (employee_id, action, module, description, ip_address) VALUES
('EMP-001', 'SYSTEM_INITIALIZATION', 'SYSTEM', 'Integrated management system initialized with schema v1.0', '127.0.0.1'),
('EMP-002', 'PURCHASE_ORDER_CREATED', 'PURCHASE_ORDERS', 'Created PO-2026-0001 for Golden Roast Coffee Roasters', '127.0.0.1'),
('EMP-003', 'STOCK_IN_PROCESSED', 'INVENTORY', 'Received 8000ml Milk under PO-2026-0002', '127.0.0.1'),
('EMP-004', 'CANTEEN_POS_LOGIN', 'POS', 'Cashier Elena Rostova logged into POS Terminal 01', '127.0.0.1');
