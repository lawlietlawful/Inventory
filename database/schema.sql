-- ==============================================================================
-- DATABASE: integrated_management_db
-- Integrated Inventory Management System (IMS)
-- Integrations: Canteen Management System (CMS) & Employee Management System (EMS)
-- Dialect: PostgreSQL 14+
-- ==============================================================================

-- Drop tables in reverse order of foreign key dependencies if re-running
DROP TABLE IF EXISTS system_settings CASCADE;
DROP TABLE IF EXISTS integration_events CASCADE;
DROP TABLE IF EXISTS activity_logs CASCADE;
DROP TABLE IF EXISTS inventory_notifications CASCADE;
DROP TABLE IF EXISTS stock_transfer_items CASCADE;
DROP TABLE IF EXISTS stock_transfers CASCADE;
DROP TABLE IF EXISTS product_recipe_items CASCADE;
DROP TABLE IF EXISTS product_recipes CASCADE;
DROP TABLE IF EXISTS stock_adjustments CASCADE;
DROP TABLE IF EXISTS inventory_transactions CASCADE;
DROP TABLE IF EXISTS stock_batches CASCADE;
DROP TABLE IF EXISTS purchase_order_items CASCADE;
DROP TABLE IF EXISTS purchase_orders CASCADE;
DROP TABLE IF EXISTS supplier_products CASCADE;
DROP TABLE IF EXISTS suppliers CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS units CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS role_permissions CASCADE;
DROP TABLE IF EXISTS permissions CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS departments CASCADE;
DROP TABLE IF EXISTS roles CASCADE;

-- ------------------------------------------------------------------------------
-- 17. DEPARTMENTS (Referenced by Employee Management System)
-- ------------------------------------------------------------------------------
CREATE TABLE departments (
    id SERIAL PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 2. ROLES
-- ------------------------------------------------------------------------------
CREATE TABLE roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 3. PERMISSIONS
-- ------------------------------------------------------------------------------
CREATE TABLE permissions (
    id SERIAL PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- ------------------------------------------------------------------------------
-- 4. ROLE_PERMISSIONS
-- ------------------------------------------------------------------------------
CREATE TABLE role_permissions (
    id SERIAL PRIMARY KEY,
    role_id INTEGER NOT NULL REFERENCES roles(id) ON DELETE CASCADE,
    permission_id INTEGER NOT NULL REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT uq_role_permission UNIQUE (role_id, permission_id)
);

-- ------------------------------------------------------------------------------
-- 1. USERS (Synchronized with Employee Management System)
-- ------------------------------------------------------------------------------
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    employee_id VARCHAR(50) NOT NULL UNIQUE,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER NOT NULL REFERENCES roles(id),
    department_id INTEGER REFERENCES departments(id),
    full_name VARCHAR(150),
    email VARCHAR(150),
    phone VARCHAR(50),
    account_status VARCHAR(20) DEFAULT 'ACTIVE' CHECK (account_status IN ('ACTIVE', 'INACTIVE', 'SUSPENDED')),
    last_login TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_employee_id ON users(employee_id);
CREATE INDEX idx_users_account_status ON users(account_status);

-- ------------------------------------------------------------------------------
-- 5. CATEGORIES
-- ------------------------------------------------------------------------------
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 6. UNITS
-- ------------------------------------------------------------------------------
CREATE TABLE units (
    id SERIAL PRIMARY KEY,
    unit_name VARCHAR(50) NOT NULL UNIQUE,
    abbreviation VARCHAR(20) NOT NULL UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 7. PRODUCTS
-- ------------------------------------------------------------------------------
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    product_code VARCHAR(50) NOT NULL UNIQUE,
    barcode VARCHAR(100) UNIQUE,
    product_name VARCHAR(150) NOT NULL,
    category_id INTEGER NOT NULL REFERENCES categories(id),
    unit_id INTEGER NOT NULL REFERENCES units(id),
    description TEXT,
    cost_price NUMERIC(12, 4) NOT NULL DEFAULT 0.0000 CHECK (cost_price >= 0),
    selling_price NUMERIC(12, 4) NOT NULL DEFAULT 0.0000 CHECK (selling_price >= 0),
    minimum_stock NUMERIC(12, 2) NOT NULL DEFAULT 0.00 CHECK (minimum_stock >= 0),
    maximum_stock NUMERIC(12, 2) NOT NULL DEFAULT 1000.00 CHECK (maximum_stock >= minimum_stock),
    current_stock NUMERIC(12, 2) NOT NULL DEFAULT 0.00 CHECK (current_stock >= 0),
    reorder_level NUMERIC(12, 2) NOT NULL DEFAULT 10.00 CHECK (reorder_level >= 0),
    expiration_tracking BOOLEAN DEFAULT FALSE,
    product_image TEXT,
    is_recipe_item BOOLEAN DEFAULT FALSE, -- finished product made with recipe
    is_ingredient BOOLEAN DEFAULT FALSE,  -- raw ingredient item
    status VARCHAR(20) DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE', 'INACTIVE', 'ARCHIVED')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_products_code ON products(product_code);
CREATE INDEX idx_products_barcode ON products(barcode);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
CREATE INDEX idx_products_current_stock ON products(current_stock);

-- ------------------------------------------------------------------------------
-- 8. SUPPLIERS
-- ------------------------------------------------------------------------------
CREATE TABLE suppliers (
    id SERIAL PRIMARY KEY,
    supplier_code VARCHAR(50) NOT NULL UNIQUE,
    supplier_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    contact_number VARCHAR(50),
    email VARCHAR(100),
    address TEXT,
    status VARCHAR(20) DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE', 'INACTIVE', 'ARCHIVED')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_suppliers_code ON suppliers(supplier_code);
CREATE INDEX idx_suppliers_status ON suppliers(status);

-- ------------------------------------------------------------------------------
-- 9. SUPPLIER_PRODUCTS
-- ------------------------------------------------------------------------------
CREATE TABLE supplier_products (
    id SERIAL PRIMARY KEY,
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    supplier_product_code VARCHAR(50),
    purchase_price NUMERIC(12, 4) NOT NULL CHECK (purchase_price >= 0),
    lead_time_days INTEGER DEFAULT 3 CHECK (lead_time_days >= 0),
    CONSTRAINT uq_supplier_product UNIQUE (supplier_id, product_id)
);

-- ------------------------------------------------------------------------------
-- 10. PURCHASE_ORDERS
-- ------------------------------------------------------------------------------
CREATE TABLE purchase_orders (
    id SERIAL PRIMARY KEY,
    purchase_order_number VARCHAR(50) NOT NULL UNIQUE,
    supplier_id INTEGER NOT NULL REFERENCES suppliers(id),
    requested_by VARCHAR(50) NOT NULL, -- references users.employee_id
    approved_by VARCHAR(50),           -- references users.employee_id
    order_date DATE NOT NULL DEFAULT CURRENT_DATE,
    expected_delivery_date DATE,
    total_amount NUMERIC(12, 2) NOT NULL DEFAULT 0.00 CHECK (total_amount >= 0),
    status VARCHAR(30) DEFAULT 'DRAFT' CHECK (status IN (
        'DRAFT', 'PENDING APPROVAL', 'APPROVED', 'ORDERED', 
        'PARTIALLY RECEIVED', 'COMPLETED', 'REJECTED', 'CANCELED'
    )),
    remarks TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_purchase_orders_number ON purchase_orders(purchase_order_number);
CREATE INDEX idx_purchase_orders_status ON purchase_orders(status);
CREATE INDEX idx_purchase_orders_supplier ON purchase_orders(supplier_id);

-- ------------------------------------------------------------------------------
-- 11. PURCHASE_ORDER_ITEMS
-- ------------------------------------------------------------------------------
CREATE TABLE purchase_order_items (
    id SERIAL PRIMARY KEY,
    purchase_order_id INTEGER NOT NULL REFERENCES purchase_orders(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id),
    quantity_ordered NUMERIC(12, 2) NOT NULL CHECK (quantity_ordered > 0),
    quantity_received NUMERIC(12, 2) NOT NULL DEFAULT 0.00 CHECK (quantity_received >= 0),
    unit_cost NUMERIC(12, 4) NOT NULL CHECK (unit_cost >= 0),
    subtotal NUMERIC(12, 2) NOT NULL CHECK (subtotal >= 0)
);

CREATE INDEX idx_po_items_po_id ON purchase_order_items(purchase_order_id);
CREATE INDEX idx_po_items_product_id ON purchase_order_items(product_id);

-- ------------------------------------------------------------------------------
-- 12. STOCK_BATCHES
-- ------------------------------------------------------------------------------
CREATE TABLE stock_batches (
    id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL REFERENCES products(id),
    batch_number VARCHAR(100) NOT NULL,
    purchase_order_item_id INTEGER REFERENCES purchase_order_items(id),
    quantity_received NUMERIC(12, 2) NOT NULL CHECK (quantity_received > 0),
    remaining_quantity NUMERIC(12, 2) NOT NULL CHECK (remaining_quantity >= 0),
    expiration_date DATE,
    received_date DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_product_batch UNIQUE (product_id, batch_number)
);

CREATE INDEX idx_stock_batches_product ON stock_batches(product_id);
CREATE INDEX idx_stock_batches_expiration ON stock_batches(expiration_date);
CREATE INDEX idx_stock_batches_remaining ON stock_batches(remaining_quantity);

-- ------------------------------------------------------------------------------
-- 13. INVENTORY_TRANSACTIONS
-- ------------------------------------------------------------------------------
CREATE TABLE inventory_transactions (
    id SERIAL PRIMARY KEY,
    transaction_number VARCHAR(50) NOT NULL UNIQUE,
    product_id INTEGER NOT NULL REFERENCES products(id),
    transaction_type VARCHAR(30) NOT NULL CHECK (transaction_type IN (
        'STOCK_IN', 'STOCK_OUT', 'SALE', 'RETURN', 'DAMAGE', 
        'EXPIRED', 'ADJUSTMENT', 'TRANSFER_IN', 'TRANSFER_OUT'
    )),
    quantity NUMERIC(12, 2) NOT NULL,
    previous_stock NUMERIC(12, 2) NOT NULL,
    new_stock NUMERIC(12, 2) NOT NULL,
    reference_type VARCHAR(50), -- e.g. 'PURCHASE_ORDER', 'CANTEEN_SALE', 'ADJUSTMENT', 'TRANSFER'
    reference_id VARCHAR(100),  -- e.g. POS Transaction ID, PO number
    employee_id VARCHAR(50),    -- employee responsible
    remarks TEXT,
    transaction_date TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_transactions_product ON inventory_transactions(product_id);
CREATE INDEX idx_transactions_type ON inventory_transactions(transaction_type);
CREATE INDEX idx_transactions_date ON inventory_transactions(transaction_date);
CREATE INDEX idx_transactions_reference ON inventory_transactions(reference_type, reference_id);

-- ------------------------------------------------------------------------------
-- 14. STOCK_ADJUSTMENTS
-- ------------------------------------------------------------------------------
CREATE TABLE stock_adjustments (
    id SERIAL PRIMARY KEY,
    adjustment_number VARCHAR(50) NOT NULL UNIQUE,
    product_id INTEGER NOT NULL REFERENCES products(id),
    system_quantity NUMERIC(12, 2) NOT NULL,
    actual_quantity NUMERIC(12, 2) NOT NULL,
    difference NUMERIC(12, 2) NOT NULL,
    reason TEXT NOT NULL,
    requested_by VARCHAR(50) NOT NULL, -- references users.employee_id
    approved_by VARCHAR(50),           -- references users.employee_id
    status VARCHAR(20) DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'APPROVED', 'REJECTED')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_stock_adj_product ON stock_adjustments(product_id);
CREATE INDEX idx_stock_adj_status ON stock_adjustments(status);

-- ------------------------------------------------------------------------------
-- 15. PRODUCT_RECIPES
-- ------------------------------------------------------------------------------
CREATE TABLE product_recipes (
    id SERIAL PRIMARY KEY,
    finished_product_id INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    recipe_name VARCHAR(150) NOT NULL,
    serving_size NUMERIC(8, 2) DEFAULT 1.00 CHECK (serving_size > 0),
    status VARCHAR(20) DEFAULT 'ACTIVE' CHECK (status IN ('ACTIVE', 'INACTIVE')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_finished_product_recipe UNIQUE (finished_product_id)
);

-- ------------------------------------------------------------------------------
-- 16. PRODUCT_RECIPE_ITEMS (Ingredients / Bill of Materials)
-- ------------------------------------------------------------------------------
CREATE TABLE product_recipe_items (
    id SERIAL PRIMARY KEY,
    recipe_id INTEGER NOT NULL REFERENCES product_recipes(id) ON DELETE CASCADE,
    ingredient_product_id INTEGER NOT NULL REFERENCES products(id),
    quantity_required NUMERIC(12, 4) NOT NULL CHECK (quantity_required > 0),
    unit_id INTEGER NOT NULL REFERENCES units(id),
    CONSTRAINT uq_recipe_ingredient UNIQUE (recipe_id, ingredient_product_id)
);

CREATE INDEX idx_recipe_items_recipe ON product_recipe_items(recipe_id);
CREATE INDEX idx_recipe_items_ingredient ON product_recipe_items(ingredient_product_id);

-- ------------------------------------------------------------------------------
-- 18. STOCK_TRANSFERS
-- ------------------------------------------------------------------------------
CREATE TABLE stock_transfers (
    id SERIAL PRIMARY KEY,
    transfer_number VARCHAR(50) NOT NULL UNIQUE,
    source_location VARCHAR(100) NOT NULL,
    destination_location VARCHAR(100) NOT NULL,
    requested_by VARCHAR(50) NOT NULL,
    approved_by VARCHAR(50),
    status VARCHAR(20) DEFAULT 'PENDING' CHECK (status IN ('PENDING', 'APPROVED', 'IN TRANSIT', 'COMPLETED', 'CANCELED')),
    transfer_date DATE NOT NULL DEFAULT CURRENT_DATE,
    remarks TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------------------------
-- 19. STOCK_TRANSFER_ITEMS
-- ------------------------------------------------------------------------------
CREATE TABLE stock_transfer_items (
    id SERIAL PRIMARY KEY,
    transfer_id INTEGER NOT NULL REFERENCES stock_transfers(id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES products(id),
    quantity NUMERIC(12, 2) NOT NULL CHECK (quantity > 0)
);

-- ------------------------------------------------------------------------------
-- 20. INVENTORY_NOTIFICATIONS
-- ------------------------------------------------------------------------------
CREATE TABLE inventory_notifications (
    id SERIAL PRIMARY KEY,
    notification_type VARCHAR(50) NOT NULL, -- 'LOW_STOCK', 'OUT_OF_STOCK', 'EXPIRING_SOON', 'EXPIRED', 'PO_APPROVAL', 'INTEGRATION_ERROR'
    product_id INTEGER REFERENCES products(id) ON DELETE SET NULL,
    message TEXT NOT NULL,
    priority VARCHAR(20) DEFAULT 'MEDIUM' CHECK (priority IN ('LOW', 'MEDIUM', 'HIGH', 'CRITICAL')),
    status VARCHAR(20) DEFAULT 'UNREAD' CHECK (status IN ('UNREAD', 'READ', 'RESOLVED')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_notifications_status ON inventory_notifications(status);
CREATE INDEX idx_notifications_type ON inventory_notifications(notification_type);

-- ------------------------------------------------------------------------------
-- 21. ACTIVITY_LOGS
-- ------------------------------------------------------------------------------
CREATE TABLE activity_logs (
    id SERIAL PRIMARY KEY,
    employee_id VARCHAR(50),
    action VARCHAR(100) NOT NULL,
    module VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_activity_logs_employee ON activity_logs(employee_id);
CREATE INDEX idx_activity_logs_created ON activity_logs(created_at);

-- ------------------------------------------------------------------------------
-- 22. INTEGRATION_EVENTS
-- ------------------------------------------------------------------------------
CREATE TABLE integration_events (
    id SERIAL PRIMARY KEY,
    source_system VARCHAR(50) NOT NULL, -- 'CANTEEN_POS', 'EMPLOYEE_SYSTEM', 'IMS'
    event_type VARCHAR(100) NOT NULL,   -- 'SALE_COMPLETED', 'SALE_REFUNDED', 'SALE_CANCELED', 'EMPLOYEE_SYNC'
    reference_id VARCHAR(100) NOT NULL, -- POS Transaction ID, Employee ID
    idempotency_key VARCHAR(150) UNIQUE,
    payload JSONB NOT NULL,
    processing_status VARCHAR(20) DEFAULT 'PENDING' CHECK (processing_status IN ('PENDING', 'PROCESSED', 'FAILED', 'RETRYING')),
    error_message TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP WITH TIME ZONE
);

CREATE INDEX idx_integration_events_status ON integration_events(processing_status);
CREATE INDEX idx_integration_events_key ON integration_events(idempotency_key);

-- ------------------------------------------------------------------------------
-- 23. SYSTEM_SETTINGS
-- ------------------------------------------------------------------------------
CREATE TABLE system_settings (
    id SERIAL PRIMARY KEY,
    setting_name VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    description TEXT,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

-- ==============================================================================
-- DATABASE VIEWS
-- ==============================================================================

-- 1. Product Availability for Canteen POS
CREATE OR REPLACE VIEW v_canteen_product_availability AS
SELECT 
    p.id AS product_id,
    p.product_code,
    p.barcode,
    p.product_name,
    c.category_name,
    u.abbreviation AS unit,
    p.selling_price,
    p.current_stock,
    p.minimum_stock,
    p.is_recipe_item,
    CASE 
        WHEN p.current_stock <= 0 THEN 'OUT_OF_STOCK'
        WHEN p.current_stock <= p.minimum_stock THEN 'LOW_STOCK'
        ELSE 'AVAILABLE'
    END AS availability_status,
    p.status AS product_status
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN units u ON p.unit_id = u.id
WHERE p.status = 'ACTIVE';

-- 2. Inventory Valuation (Weighted Average Cost)
CREATE OR REPLACE VIEW v_inventory_valuation AS
SELECT 
    p.id AS product_id,
    p.product_code,
    p.product_name,
    c.category_name,
    p.current_stock,
    u.abbreviation AS unit,
    p.cost_price AS unit_cost_wac,
    ROUND((p.current_stock * p.cost_price), 2) AS total_inventory_value
FROM products p
JOIN categories c ON p.category_id = c.id
JOIN units u ON p.unit_id = u.id
WHERE p.status = 'ACTIVE';

-- 3. Expiring Batches Monitor
CREATE OR REPLACE VIEW v_expiring_batches AS
SELECT 
    b.id AS batch_id,
    p.id AS product_id,
    p.product_code,
    p.product_name,
    c.category_name,
    b.batch_number,
    b.remaining_quantity,
    b.expiration_date,
    CURRENT_DATE AS today_date,
    (b.expiration_date - CURRENT_DATE) AS days_until_expiration,
    CASE 
        WHEN b.expiration_date < CURRENT_DATE THEN 'EXPIRED'
        WHEN (b.expiration_date - CURRENT_DATE) <= 3 THEN 'CRITICAL_3_DAYS'
        WHEN (b.expiration_date - CURRENT_DATE) <= 7 THEN 'WARNING_7_DAYS'
        WHEN (b.expiration_date - CURRENT_DATE) <= 15 THEN 'ATTENTION_15_DAYS'
        WHEN (b.expiration_date - CURRENT_DATE) <= 30 THEN 'NOTICE_30_DAYS'
        ELSE 'FRESH'
    END AS expiration_risk
FROM stock_batches b
JOIN products p ON b.product_id = p.id
JOIN categories c ON p.category_id = c.id
WHERE b.remaining_quantity > 0 AND b.expiration_date IS NOT NULL
ORDER BY b.expiration_date ASC;
