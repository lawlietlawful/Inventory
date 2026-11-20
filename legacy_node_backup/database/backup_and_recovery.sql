-- ==============================================================================
-- POSTGRESQL BACKUP AND DISASTER RECOVERY PROCEDURES
-- Database: integrated_management_db
-- ==============================================================================

-- ------------------------------------------------------------------------------
-- 1. FULL LOGICAL BACKUP COMMANDS (Execute in Terminal / Command Prompt)
-- ------------------------------------------------------------------------------

-- Plain SQL Dump (Human-readable, portable across PostgreSQL versions):
-- pg_dump -U postgres -h 127.0.0.1 -p 5432 -d integrated_management_db -F p -b -v -f "database/backups/backup_full_%date:~10,4%%date:~4,2%%date:~7,2%.sql"

-- Custom Compressed Archive (Recommended for production, supports multi-threaded restore):
-- pg_dump -U postgres -h 127.0.0.1 -p 5432 -d integrated_management_db -F c -b -v -f "database/backups/backup_compressed.dump"

-- Schema-Only Backup:
-- pg_dump -U postgres -h 127.0.0.1 -p 5432 -d integrated_management_db -s -F p -f "database/backups/schema_only.sql"

-- Data-Only Backup:
-- pg_dump -U postgres -h 127.0.0.1 -p 5432 -d integrated_management_db -a -F p -f "database/backups/data_only.sql"

-- ------------------------------------------------------------------------------
-- 2. RESTORATION PROCEDURES
-- ------------------------------------------------------------------------------

-- Step A: Terminate active client connections to allow database drop/re-creation:
SELECT pg_terminate_backend(pid) 
FROM pg_stat_activity 
WHERE datname = 'integrated_management_db' AND pid <> pg_backend_pid();

-- Step B: Recreate clean database:
DROP DATABASE IF EXISTS integrated_management_db;
CREATE DATABASE integrated_management_db WITH ENCODING 'UTF8';

-- Step C: Restore from Plain SQL Backup:
-- psql -U postgres -h 127.0.0.1 -p 5432 -d integrated_management_db -f "database/backups/backup_full.sql"

-- Step D: Restore from Custom Compressed Archive:
-- pg_restore -U postgres -h 127.0.0.1 -p 5432 -d integrated_management_db -v -c "database/backups/backup_compressed.dump"

-- ------------------------------------------------------------------------------
-- 3. AUTOMATED DATABASE INTEGRITY & HEALTH CHECKS
-- ------------------------------------------------------------------------------

-- Check row count integrity across critical tables:
SELECT 'products' AS table_name, COUNT(*) AS row_count FROM products
UNION ALL
SELECT 'stock_batches', COUNT(*) FROM stock_batches
UNION ALL
SELECT 'inventory_transactions', COUNT(*) FROM inventory_transactions
UNION ALL
SELECT 'purchase_orders', COUNT(*) FROM purchase_orders
UNION ALL
SELECT 'integration_events', COUNT(*) FROM integration_events;

-- Identify negative stock anomalies (Rule: current_stock >= 0):
SELECT id, product_code, product_name, current_stock 
FROM products 
WHERE current_stock < 0;

-- Identify unreferenced batches:
SELECT b.id, b.batch_number, b.product_id 
FROM stock_batches b 
LEFT JOIN products p ON b.product_id = p.id 
WHERE p.id IS NULL;
