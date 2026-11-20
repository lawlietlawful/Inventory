# Integrated Inventory Management System (IMS)
### Centralized Enterprise Hub with Canteen Management System & Employee Management System Integrations Using PostgreSQL

---

## 1. Project Overview

The **Integrated Inventory Management System (IMS)** is a modern, enterprise-grade platform developed to serve as the single source of truth for inventory control, catalog management, batch tracking, procurement, and valuation.

The system connects three critical operations through a centralized **PostgreSQL** database:
1. **Central Inventory Core**: Products, categories, units, suppliers, purchase orders, stock-in receiving, stock-out dispatches, physical adjustments, transfers, and FEFO expiration batch monitoring.
2. **Canteen Management System (CMS / POS)**: Real-time cashier checkout, automatic Bill of Materials (BOM) recipe-based ingredient deductions (e.g., *Iced Coffee* $\rightarrow$ cup, lid, coffee portion, milk portion, syrup), idempotent event processing to prevent duplicate deductions, real-time product availability indicators, and returns/refunds.
3. **Employee Management System (EMS)**: Reference-based employee identity management linking every purchase order, receipt, stock adjustment, and cashier sale to a responsible employee; account status synchronization that immediately locks out inactive employees from restricted operations.

---

## 2. System Architecture

```
+---------------------------------------------------------------------------------------------------+
|                                          CLIENT LAYER                                             |
|  [Enterprise Admin Dashboard]   [Inventory Staff / Manager UI]   [Interactive Canteen POS Terminal] |
+---------------------------------------------------------------------------------------------------+
                                                  | RESTful APIs
+---------------------------------------------------------------------------------------------------+
|                                      APPLICATION GATEWAY (Node.js)                                |
|  - Role-Based Access Control (RBAC Middleware)                                                    |
|  - Idempotency & Event Bus (integration_events)                                                   |
|  - Bill of Materials Recipe Deduction Engine                                                      |
|  - First-Expired, First-Out (FEFO) Batch Allocator                                                |
|  - Weighted Average Cost (WAC) Valuation Engine                                                    |
|  - Comprehensive Activity Audit Logger                                                            |
+---------------------------------------------------------------------------------------------------+
                                                  |
+---------------------------------------------------------------------------------------------------+
|                               CENTRAL POSTGRESQL DATABASE (23 TABLES)                             |
|  integrated_management_db                                                                         |
|  - Users & Roles: users, roles, permissions, role_permissions, departments                        |
|  - Product Catalog: categories, units, products                                                   |
|  - Inventory & Batches: stock_batches, inventory_transactions, stock_adjustments                   |
|  - Recipe & BOM: product_recipes, product_recipe_items                                            |
|  - Procurement: suppliers, supplier_products, purchase_orders, purchase_order_items               |
|  - Logistics & Auditing: stock_transfers, stock_transfer_items, integration_events,               |
|                         inventory_notifications, activity_logs, system_settings                    |
+---------------------------------------------------------------------------------------------------+
```

---

## 3. Database Architecture (23 Relational Tables)

The system utilizes PostgreSQL with strict referential integrity, foreign keys, row-level locking for concurrency protection, check constraints, and performance indexes.

### Table Summary:
1. `users` – Synchronized user accounts with `employee_id`, bcrypt password hashes, and `account_status`.
2. `roles` – Administrator, Inventory Manager, Inventory Staff, Canteen Cashier, Employee.
3. `permissions` – Granular functional privileges (14 defined permissions).
4. `role_permissions` – Many-to-many relationship mapping roles to permissions.
5. `categories` – Product classifications (Beverages, Food Products, Ingredients, Snacks, Packaging, etc.).
6. `units` – Measurement units (pcs, kg, g, L, ml, packs, boxes, cans, rolls).
7. `products` – Master product records with cost price (WAC), selling price, stock levels, barcodes, and recipe flags.
8. `suppliers` – Vendor directory with contact information and performance history.
9. `supplier_products` – Vendor catalog mapping with purchase prices and lead times.
10. `purchase_orders` – Procurement lifecycle (`DRAFT`, `PENDING APPROVAL`, `APPROVED`, `ORDERED`, `PARTIALLY RECEIVED`, `COMPLETED`, `REJECTED`, `CANCELED`).
11. `purchase_order_items` – Line items with ordered quantities, received quantities, and unit costs.
12. `stock_batches` – Perishable inventory batches with batch numbers, remaining quantities, and expiration dates.
13. `inventory_transactions` – Immutable ledger of all movements (`STOCK_IN`, `STOCK_OUT`, `SALE`, `RETURN`, `DAMAGE`, `EXPIRED`, `ADJUSTMENT`).
14. `stock_adjustments` – Physical reconciliation discrepancy requests with dual-approval workflow.
15. `product_recipes` – Finished product recipes.
16. `product_recipe_items` – Bill of Materials (BOM) specifying required ingredient products, quantities, and units.
17. `departments` – Enterprise organizational units (referenced by EMS).
18. `stock_transfers` – Inter-location stock transfers.
19. `stock_transfer_items` – Items transferred between storage locations.
20. `inventory_notifications` – System alerts (low stock, out of stock, expiring batches, pending PO approvals).
21. `activity_logs` – Detailed audit logs capturing employee ID, module, action, description, and IP address.
22. `integration_events` – Event bus table for idempotent POS transactions and external sync payloads.
23. `system_settings` – Operational parameters (WAC valuation, FEFO enforcement, negative stock prevention).

### PostgreSQL Views Included:
- `v_canteen_product_availability`: Real-time stock availability status (`AVAILABLE`, `LOW_STOCK`, `OUT_OF_STOCK`) consumed by POS.
- `v_inventory_valuation`: Weighted Average Cost valuation per product and total inventory value.
- `v_expiring_batches`: Batches sorted by expiration risk (`EXPIRED`, `CRITICAL_3_DAYS`, `WARNING_7_DAYS`, `ATTENTION_15_DAYS`, `NOTICE_30_DAYS`, `FRESH`).

---

## 4. Key Subsystem Integrations

### A. Canteen Management System (POS) Integration
- **Endpoint**: `POST /api/integration/canteen/sales`
- **Recipe & BOM Deduction**:
  When a finished product (e.g. *Signature Iced Coffee*) is purchased:
  1. The integration engine queries `product_recipes` and `product_recipe_items`.
  2. Resolves ingredients: 1 Cup (16oz), 1 Dome Lid, 50ml Espresso Concentrate, 150ml Fresh Milk, 20ml Simple Syrup.
  3. Deducts required quantities $\times$ sale quantity from each ingredient's inventory row.
  4. Deducts from batches using **First-Expired, First-Out (FEFO)** to prevent perishable waste.
- **Direct Items**: Non-recipe items (e.g. *Bottled Mineral Water 500ml*) are deducted directly.
- **Idempotency Protection**: Every sale requires an `idempotency_key` or `transaction_id`. Retrying or resending the exact same request will return the previous success response without deducting stock a second time.
- **Concurrency & Atomicity**: Database transactions with row-level locks prevent race conditions and overselling.
- **Cancellations & Refunds**:
  - `POST /api/integration/canteen/cancel`: Reverses sale and returns stock to inventory.
  - `POST /api/integration/canteen/refund`: Restores inventory only when physical restock is verified (`restock_inventory: true`).

### B. Employee Management System (EMS) Integration
- **Endpoint**: `POST /api/integration/employees/sync`
- Synchronizes employee name, department, role, and account status (`ACTIVE`, `INACTIVE`, `SUSPENDED`).
- **Inactive Account Enforcement**: If an employee is marked `INACTIVE` in EMS, their account status is updated and all future system actions (PO creation, approvals, receiving, adjustments) are rejected immediately.
- **Audit Accountability**: Every inventory movement, stock-in, stock-out, and adjustment references `employee_id` for accountability.

---

## 5. Technology Stack

- **Database**: PostgreSQL 14+ (`integrated_management_db`) with dual-mode connection support (Live PostgreSQL + automated in-memory relational engine conforming strictly to the PostgreSQL schema).
- **Backend**: Node.js v26 / Express.js RESTful API, `pg` connection pool, bcryptjs password hashing.
- **Frontend**: Responsive Single-Page Application (HTML5, modern CSS3 with enterprise dashboard themes, vanilla JavaScript ES6, Chart.js for real-time analytics).
- **Testing**: Automated Integration Test Suite (`tests/integration.test.js`).

---

## 6. Installation and Execution

### Step 1: Install Dependencies
```bash
npm install
```

### Step 2: Configure Environment
Copy `.env.example` to `.env` and configure your PostgreSQL database credentials:
```bash
# Windows PowerShell
Copy-Item .env.example .env
```
Default connection parameters:
```env
PORT=3000
PGHOST=127.0.0.1
PGPORT=5432
PGUSER=postgres
PGPASSWORD=postgres
PGDATABASE=integrated_management_db
```

### Step 3: Run Database Migrations & Seeds
Execute the automated migration runner to apply `database/schema.sql` and `database/seed.sql`:
```bash
node database/init.js
```
*(If PostgreSQL is currently offline, the runner automatically initializes the local PostgreSQL-compliant storage engine, enabling full functionality immediately).*

### Step 4: Run the Automated Test Suite
Verify that all 7 core subsystems pass:
```bash
node tests/integration.test.js
```
Expected output:
```
====================================================
   INTEGRATED IMS - AUTOMATED TEST SUITE            
====================================================
• Testing: Product Catalog & Availability View... ✓ PASSED
• Testing: Canteen POS Recipe-Based Ingredient Deduction... ✓ PASSED
• Testing: Idempotency: Resending Same POS Event Does NOT Deduct Stock Twice... ✓ PASSED
• Testing: Insufficient Stock Prevention & Atomic Rollback... ✓ PASSED
• Testing: Employee Management System Sync & Deactivation Access Control... ✓ PASSED
• Testing: Stock-In & Weighted Average Cost (WAC) Updating... ✓ PASSED
• Testing: Reports Engine: Valuation & Expiration (FEFO) Reports... ✓ PASSED
====================================================
Results: 7 PASSED, 0 FAILED
====================================================
```

### Step 5: Start the Application Server
```bash
node src/server.js
```
Open your browser and navigate to:
```
http://localhost:3000
```

---

## 7. User Personas & Credentials for Testing

Switch roles interactively via the top navigation dropdown:

| Persona | Employee ID | Username | Password | Role | Permissions |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **System Admin** | `EMP-001` | `admin` | `admin123` | Administrator | Full access to all modules, settings, and logs |
| **Inventory Manager** | `EMP-002` | `inv_mgr` | `admin123` | Inventory Manager | Product management, PO approvals, adjustments |
| **Inventory Staff** | `EMP-003` | `inv_staff` | `admin123` | Inventory Staff | Stock-in receiving, dispatch logs, physical counting |
| **Canteen Cashier** | `EMP-004` | `cashier1` | `admin123` | Canteen Cashier | Canteen POS sales, availability lookup, returns |
| **Employee** | `EMP-005` | `john_doe` | `admin123` | Employee | View inventory levels, submit requests |

---

## 8. Reports & Analytics (All 15 Required Reports)

Access via the **Reports & Analytics** tab in the portal, with CSV export and printing support:
1. **Current Inventory Report** (`/api/reports/current-inventory`)
2. **Stock-In Report** (`/api/reports/stock-in`)
3. **Stock-Out Report** (`/api/reports/stock-out`)
4. **Inventory Movement Report** (`/api/reports/movement`)
5. **Low Stock Report** (`/api/reports/low-stock`)
6. **Out-of-Stock Report** (`/api/reports/out-of-stock`)
7. **Expiration Report** (`/api/reports/expiration`) – Color-coded FEFO risk tags
8. **Damaged Products Report** (`/api/reports/damaged`)
9. **Supplier Performance Report** (`/api/reports/suppliers`)
10. **Purchase Order Report** (`/api/reports/purchase-orders`)
11. **Inventory Valuation Report** (`/api/reports/valuation`) – Weighted Average Cost
12. **Canteen Sales Deductions Report** (`/api/reports/canteen-deductions`)
13. **Employee Activity Report** (`/api/reports/employee-activities`)
14. **Stock Adjustment Report** (`/api/reports/adjustments`)
15. **Product Usage Report** (`/api/reports/product-usage`)

---

## 9. Backup and Disaster Recovery

Refer to `database/backup_and_recovery.sql` for production backup commands:
```bash
# Logical Plain Text Dump
pg_dump -U postgres -h 127.0.0.1 -d integrated_management_db -F p -f "backup.sql"

# Custom Compressed Production Archive
pg_dump -U postgres -h 127.0.0.1 -d integrated_management_db -F c -f "backup.dump"

# Restore Database
pg_restore -U postgres -h 127.0.0.1 -d integrated_management_db -v -c "backup.dump"
```
