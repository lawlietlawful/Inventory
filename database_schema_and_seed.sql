-- ==============================================================================
-- CANTEEN MANAGEMENT SYSTEM (POS) - POSTGRESQL DATABASE SCHEMA & SEED
-- Compatible with PostgreSQL 15, 16, 17, 18
-- Schemas:
--   1. app1.users         (Shared student user authentication table)
--   2. canteen_pos.*      (Canteen Management System domain tables)
-- ==============================================================================

-- 1. Create Schemas
CREATE SCHEMA IF NOT EXISTS app1;
CREATE SCHEMA IF NOT EXISTS canteen_pos;

-- Set search path
SET search_path TO canteen_pos, app1, public;

-- ==============================================================================
-- 2. Shared Auth Table: app1.users
-- Note: Do NOT alter or drop this table in shared multi-student environments.
-- ==============================================================================
CREATE TABLE IF NOT EXISTS app1.users (
    idno VARCHAR(50) PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    pwd VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL
);

-- ==============================================================================
-- 3. Canteen Domain Tables: canteen_pos
-- ==============================================================================

-- Categories Table
CREATE TABLE IF NOT EXISTS canteen_pos.categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- Products Table
CREATE TABLE IF NOT EXISTS canteen_pos.products (
    product_id SERIAL PRIMARY KEY,
    category_id INTEGER REFERENCES canteen_pos.categories(category_id) ON DELETE SET NULL,
    product_name VARCHAR(150) NOT NULL,
    price NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    stock_qty INTEGER NOT NULL DEFAULT 0,
    is_available BOOLEAN NOT NULL DEFAULT true
);

-- Orders Table
CREATE TABLE IF NOT EXISTS canteen_pos.orders (
    order_id SERIAL PRIMARY KEY,
    idno VARCHAR(50) NOT NULL REFERENCES app1.users(idno) ON DELETE RESTRICT,
    customer_name VARCHAR(100),
    order_date TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW(),
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'paid', 'cancelled')),
    total_amount NUMERIC(10, 2) NOT NULL DEFAULT 0.00
);

-- Order Items Table (with PostgreSQL GENERATED line_total)
CREATE TABLE IF NOT EXISTS canteen_pos.order_items (
    order_item_id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL REFERENCES canteen_pos.orders(order_id) ON DELETE CASCADE,
    product_id INTEGER NOT NULL REFERENCES canteen_pos.products(product_id) ON DELETE RESTRICT,
    quantity INTEGER NOT NULL CHECK (quantity > 0),
    unit_price NUMERIC(10, 2) NOT NULL CHECK (unit_price >= 0),
    line_total NUMERIC(10, 2) GENERATED ALWAYS AS ((quantity::NUMERIC) * unit_price) STORED
);

-- Payments Table (1-to-1 with orders)
CREATE TABLE IF NOT EXISTS canteen_pos.payments (
    payment_id SERIAL PRIMARY KEY,
    order_id INTEGER UNIQUE NOT NULL REFERENCES canteen_pos.orders(order_id) ON DELETE CASCADE,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'cash' CHECK (payment_method IN ('cash', 'gcash')),
    amount_paid NUMERIC(10, 2) NOT NULL,
    change_amount NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
    paid_at TIMESTAMP WITHOUT TIME ZONE NOT NULL DEFAULT NOW()
);

-- Indexes for Query Performance
CREATE INDEX IF NOT EXISTS idx_orders_date ON canteen_pos.orders(order_date);
CREATE INDEX IF NOT EXISTS idx_orders_status ON canteen_pos.orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_cashier ON canteen_pos.orders(idno);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON canteen_pos.order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_products_category ON canteen_pos.products(category_id);

-- ==============================================================================
-- 4. Sample Seed Data
-- ==============================================================================

-- Default Cashier Account in app1.users
-- Username: admin@school.com
-- Password: password123 (bcrypt hash: $2y$12$eAn74tB5iO8sLq6X.rLg/u2zTj1vYlX7ZqJg9q6rK.8k8nL3j0r.2)
INSERT INTO app1.users (idno, username, pwd, name)
VALUES 
    ('1001', 'admin@school.com', '$2y$12$wMpKH.Eahg.ODvg0dY90/uO3YdV63sse33air6IRCfTLXXR0SVKFa', 'Admin Cashier'),
    ('1002', 'cashier1@school.com', '$2y$12$wMpKH.Eahg.ODvg0dY90/uO3YdV63sse33air6IRCfTLXXR0SVKFa', 'Canteen Staff')
ON CONFLICT (idno) DO NOTHING;

-- Initial Product Categories
INSERT INTO canteen_pos.categories (category_id, category_name) VALUES
    (1, 'Meals'),
    (2, 'Snacks'),
    (3, 'Beverages'),
    (4, 'Desserts')
ON CONFLICT (category_id) DO NOTHING;

-- Reset sequence to prevent collision
SELECT setval('canteen_pos.categories_category_id_seq', (SELECT COALESCE(MAX(category_id), 1) FROM canteen_pos.categories));

-- Initial Products
INSERT INTO canteen_pos.products (category_id, product_name, price, stock_qty, is_available) VALUES
    (1, 'Pork Sinigang with Rice', 80.00, 25, true),
    (1, 'Chicken Adobo with Rice', 75.00, 30, true),
    (1, 'Beef Caldereta with Rice', 95.00, 15, true),
    (2, 'Cheesy Lumpiang Shanghai (5 pcs)', 35.00, 40, true),
    (2, 'Crispy French Fries (Large)', 45.00, 20, true),
    (3, 'House Blend Iced Tea (16 oz)', 20.00, 50, true),
    (3, 'Bottled Mineral Water (500ml)', 15.00, 60, true),
    (4, 'Special Halo-Halo', 50.00, 20, true),
    (4, 'Creamy Leche Flan', 30.00, 15, true)
ON CONFLICT DO NOTHING;

-- Reset sequence
SELECT setval('canteen_pos.products_product_id_seq', (SELECT COALESCE(MAX(product_id), 1) FROM canteen_pos.products));

