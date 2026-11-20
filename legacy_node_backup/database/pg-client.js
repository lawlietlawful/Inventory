/**
 * Database Layer for Integrated Management System
 * Supports live PostgreSQL connections (via pg.Pool) with automatic transaction support,
 * parameterized queries ($1, $2, ...), and transparent local relational persistence
 * conforming strictly to the PostgreSQL schema.
 */

const { Pool } = require('pg');
const fs = require('fs');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '../.env') });

const dbConfig = {
    host: process.env.PGHOST || '127.0.0.1',
    port: parseInt(process.env.PGPORT || '5432', 10),
    user: process.env.PGUSER || 'postgres',
    password: process.env.PGPASSWORD || 'postgres',
    database: process.env.PGDATABASE || 'integrated_management_db',
    connectionTimeoutMillis: 3000,
    idleTimeoutMillis: 10000,
};

let pool = null;
let isPostgresLive = false;
let memoryStore = null;

const storageFilePath = path.join(__dirname, 'local_storage.json');

function initStorage() {
    if (fs.existsSync(storageFilePath)) {
        try {
            return JSON.parse(fs.readFileSync(storageFilePath, 'utf8'));
        } catch (e) {
            console.warn('[DB] Could not parse local storage file, initializing fresh store.');
        }
    }
    return {
        departments: [],
        roles: [],
        permissions: [],
        role_permissions: [],
        users: [],
        categories: [],
        units: [],
        products: [],
        suppliers: [],
        supplier_products: [],
        purchase_orders: [],
        purchase_order_items: [],
        stock_batches: [],
        inventory_transactions: [],
        stock_adjustments: [],
        product_recipes: [],
        product_recipe_items: [],
        stock_transfers: [],
        stock_transfer_items: [],
        inventory_notifications: [],
        activity_logs: [],
        integration_events: [],
        system_settings: []
    };
}

function persistStorage() {
    if (!isPostgresLive && memoryStore) {
        fs.writeFileSync(storageFilePath, JSON.stringify(memoryStore, null, 2), 'utf8');
    }
}

function parseSqlTuple(str) {
    const result = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < str.length; i++) {
        const char = str[i];
        if (char === "'" && (i === 0 || str[i - 1] !== '\\')) {
            inQuotes = !inQuotes;
        } else if (char === ',' && !inQuotes) {
            result.push(cleanVal(current));
            current = '';
        } else {
            current += char;
        }
    }
    result.push(cleanVal(current));
    return result;
}

function cleanVal(v) {
    if (v === undefined || v === null) return null;
    v = String(v).trim();
    if (v === 'NULL' || v === 'null') return null;
    if (v === 'TRUE' || v === 'true') return true;
    if (v === 'FALSE' || v === 'false') return false;
    if (v.startsWith("'") && v.endsWith("'")) return v.slice(1, -1);
    if (!isNaN(v) && v !== '') return Number(v);
    return v;
}

function seedLocalStore() {
    memoryStore = initStorage();
    const hasData = Object.values(memoryStore).some(arr => arr && arr.length > 0);
    if (hasData) {
        return;
    }

    console.log('[DB] Seeding local memory store from seed.sql...');
    const seedSqlPath = path.join(__dirname, 'seed.sql');
    if (!fs.existsSync(seedSqlPath)) return;

    const content = fs.readFileSync(seedSqlPath, 'utf8');
    const insertRegex = /INSERT\s+INTO\s+(\w+)\s*\(([^)]+)\)\s*VALUES\s*([\s\S]*?)(?:ON CONFLICT|;)/gi;
    let match;

    while ((match = insertRegex.exec(content)) !== null) {
        const table = match[1].toLowerCase();
        const cols = match[2].split(',').map(c => c.trim().toLowerCase());
        const valuesRaw = match[3].trim();

        if (!memoryStore[table]) memoryStore[table] = [];

        const tupleRegex = /\(([^)]+)\)/g;
        let tupleMatch;
        while ((tupleMatch = tupleRegex.exec(valuesRaw)) !== null) {
            const rowVals = parseSqlTuple(tupleMatch[1]);
            const rowObj = {};
            cols.forEach((col, idx) => {
                rowObj[col] = rowVals[idx];
            });
            if (!rowObj.created_at) rowObj.created_at = new Date().toISOString();
            if (!rowObj.id && memoryStore[table].length >= 0) {
                rowObj.id = memoryStore[table].length + 1;
            }
            memoryStore[table].push(rowObj);
        }
    }
    persistStorage();
    console.log('[DB] Local store seeded successfully with all tables.');
}

async function connect() {
    try {
        pool = new Pool(dbConfig);
        const res = await pool.query('SELECT NOW() as now, current_database() as db');
        isPostgresLive = true;
        console.log(`[DB] Successfully connected to Live PostgreSQL (${res.rows[0].db}) at ${dbConfig.host}:${dbConfig.port}`);
    } catch (err) {
        isPostgresLive = false;
        seedLocalStore();
    }
}

async function query(text, params = []) {
    if (isPostgresLive && pool) {
        try {
            return await pool.query(text, params);
        } catch (err) {
            console.error('[DB Query Error]', err.message, '\nQuery:', text);
            throw err;
        }
    } else {
        return executeLocalQuery(text, params);
    }
}

async function getClient() {
    if (isPostgresLive && pool) {
        const client = await pool.connect();
        return {
            query: (text, params) => client.query(text, params),
            release: () => client.release()
        };
    } else {
        let snapshot = null;
        return {
            query: async (text, params) => {
                const upper = text.trim().toUpperCase();
                if (upper === 'BEGIN') {
                    snapshot = JSON.parse(JSON.stringify(memoryStore));
                    return { rowCount: 1, rows: [] };
                } else if (upper === 'COMMIT') {
                    persistStorage();
                    snapshot = null;
                    return { rowCount: 1, rows: [] };
                } else if (upper === 'ROLLBACK') {
                    if (snapshot) memoryStore = snapshot;
                    snapshot = null;
                    return { rowCount: 1, rows: [] };
                }
                return executeLocalQuery(text, params);
            },
            release: () => {
                if (snapshot) {
                    memoryStore = snapshot;
                    snapshot = null;
                }
            }
        };
    }
}

function executeLocalQuery(sql, params = []) {
    if (!memoryStore) seedLocalStore();
    const cleanSql = sql.trim();
    const upper = cleanSql.toUpperCase();

    if (upper.startsWith('SELECT')) return handleLocalSelect(cleanSql, params);
    if (upper.startsWith('INSERT')) return handleLocalInsert(cleanSql, params);
    if (upper.startsWith('UPDATE')) return handleLocalUpdate(cleanSql, params);
    if (upper.startsWith('DELETE')) return handleLocalDelete(cleanSql, params);

    return { rowCount: 0, rows: [] };
}

function handleLocalSelect(sql, params) {
    const tableMatch = sql.match(/FROM\s+([a-zA-Z0-9_]+)/i);
    if (!tableMatch) {
        if (/SELECT\s+NOW\(\)/i.test(sql)) {
            return { rowCount: 1, rows: [{ now: new Date().toISOString() }] };
        }
        return { rowCount: 0, rows: [] };
    }

    let tableName = tableMatch[1].toLowerCase();

    // 1. View: v_canteen_product_availability
    if (tableName === 'v_canteen_product_availability') {
        const rows = (memoryStore.products || []).filter(p => p.status === 'ACTIVE').map(p => {
            const cat = (memoryStore.categories || []).find(c => c.id == p.category_id);
            const unit = (memoryStore.units || []).find(u => u.id == p.unit_id);
            let availability = 'AVAILABLE';
            if (p.current_stock <= 0) availability = 'OUT_OF_STOCK';
            else if (p.current_stock <= p.minimum_stock) availability = 'LOW_STOCK';

            return {
                product_id: p.id,
                product_code: p.product_code,
                barcode: p.barcode,
                product_name: p.product_name,
                category_name: cat ? cat.category_name : 'General',
                unit: unit ? unit.abbreviation : 'pcs',
                selling_price: parseFloat(p.selling_price || 0),
                current_stock: parseFloat(p.current_stock || 0),
                minimum_stock: parseFloat(p.minimum_stock || 0),
                is_recipe_item: Boolean(p.is_recipe_item),
                availability_status: availability,
                product_status: p.status
            };
        });
        return { rowCount: rows.length, rows };
    }

    // 2. View: v_inventory_valuation
    if (tableName === 'v_inventory_valuation') {
        const rows = (memoryStore.products || []).filter(p => p.status === 'ACTIVE').map(p => {
            const cat = (memoryStore.categories || []).find(c => c.id == p.category_id);
            const unit = (memoryStore.units || []).find(u => u.id == p.unit_id);
            const stock = parseFloat(p.current_stock || 0);
            const cost = parseFloat(p.cost_price || 0);
            return {
                product_id: p.id,
                product_code: p.product_code,
                product_name: p.product_name,
                category_name: cat ? cat.category_name : 'General',
                current_stock: stock,
                unit: unit ? unit.abbreviation : 'pcs',
                unit_cost_wac: cost,
                total_inventory_value: Math.round(stock * cost * 100) / 100
            };
        });
        return { rowCount: rows.length, rows };
    }

    // 3. View: v_expiring_batches
    if (tableName === 'v_expiring_batches') {
        const today = new Date();
        const rows = (memoryStore.stock_batches || []).filter(b => b.remaining_quantity > 0 && b.expiration_date).map(b => {
            const prod = (memoryStore.products || []).find(p => p.id == b.product_id) || {};
            const cat = (memoryStore.categories || []).find(c => c.id == prod.category_id) || {};
            const expDate = new Date(b.expiration_date);
            const diffDays = Math.ceil((expDate - today) / (1000 * 60 * 60 * 24));
            let risk = 'FRESH';
            if (diffDays < 0) risk = 'EXPIRED';
            else if (diffDays <= 3) risk = 'CRITICAL_3_DAYS';
            else if (diffDays <= 7) risk = 'WARNING_7_DAYS';
            else if (diffDays <= 15) risk = 'ATTENTION_15_DAYS';
            else if (diffDays <= 30) risk = 'NOTICE_30_DAYS';

            return {
                batch_id: b.id,
                product_id: b.product_id,
                product_code: prod.product_code,
                product_name: prod.product_name,
                category_name: cat.category_name,
                batch_number: b.batch_number,
                remaining_quantity: parseFloat(b.remaining_quantity),
                expiration_date: b.expiration_date,
                days_until_expiration: diffDays,
                expiration_risk: risk
            };
        }).sort((a, b) => new Date(a.expiration_date) - new Date(b.expiration_date));
        return { rowCount: rows.length, rows };
    }

    let rows = (memoryStore[tableName] || []).map(r => ({ ...r }));

    // Handle standard table joins / enrichments
    if (tableName === 'products') {
        rows.forEach(p => {
            const cat = (memoryStore.categories || []).find(c => c.id == p.category_id);
            const unit = (memoryStore.units || []).find(u => u.id == p.unit_id);
            p.category_name = cat ? cat.category_name : null;
            p.unit_name = unit ? unit.unit_name : null;
            p.unit_abbr = unit ? unit.abbreviation : null;
        });
    } else if (tableName === 'users') {
        rows.forEach(u => {
            const role = (memoryStore.roles || []).find(r => r.id == u.role_id);
            const dept = (memoryStore.departments || []).find(d => d.id == u.department_id);
            u.role_name = role ? role.role_name : null;
            u.department_name = dept ? dept.department_name : null;
        });
    } else if (tableName === 'product_recipes') {
        rows.forEach(r => {
            r.recipe_id = r.id;
            const p = (memoryStore.products || []).find(x => x.id == r.finished_product_id);
            if (p) {
                r.product_name = p.product_name;
                r.product_code = p.product_code;
                r.selling_price = p.selling_price;
                r.current_stock = p.current_stock;
            }
        });
    } else if (tableName === 'product_recipe_items') {
        rows.forEach(ri => {
            const p = (memoryStore.products || []).find(x => x.id == ri.ingredient_product_id);
            const u = (memoryStore.units || []).find(x => x.id == ri.unit_id);
            if (p) {
                ri.product_name = p.product_name;
                ri.product_code = p.product_code;
                ri.current_stock = p.current_stock;
                ri.cost_price = p.cost_price;
            }
            if (u) {
                ri.unit_name = u.unit_name;
                ri.unit = u.abbreviation;
            }
        });
    } else if (tableName === 'inventory_transactions') {
        rows.forEach(t => {
            const p = (memoryStore.products || []).find(x => x.id == t.product_id);
            if (p) {
                t.product_name = p.product_name;
                t.product_code = p.product_code;
                const cat = (memoryStore.categories || []).find(c => c.id == p.category_id);
                const u = (memoryStore.units || []).find(x => x.id == p.unit_id);
                t.category_name = cat ? cat.category_name : null;
                t.unit = u ? u.abbreviation : null;
            }
        });
    } else if (tableName === 'purchase_orders') {
        rows.forEach(po => {
            const sup = (memoryStore.suppliers || []).find(s => s.id == po.supplier_id);
            if (sup) {
                po.supplier_name = sup.supplier_name;
                po.supplier_code = sup.supplier_code;
                po.contact_person = sup.contact_person;
                po.contact_number = sup.contact_number;
                po.email = sup.email;
                po.address = sup.address;
            }
        });
    } else if (tableName === 'purchase_order_items') {
        rows.forEach(poi => {
            const p = (memoryStore.products || []).find(x => x.id == poi.product_id);
            if (p) {
                poi.product_name = p.product_name;
                poi.product_code = p.product_code;
                const u = (memoryStore.units || []).find(x => x.id == p.unit_id);
                poi.unit = u ? u.abbreviation : 'pcs';
            }
        });
    } else if (tableName === 'stock_batches') {
        rows.forEach(b => {
            const p = (memoryStore.products || []).find(x => x.id == b.product_id);
            if (p) {
                b.product_name = p.product_name;
                b.product_code = p.product_code;
                const cat = (memoryStore.categories || []).find(c => c.id == p.category_id);
                const u = (memoryStore.units || []).find(x => x.id == p.unit_id);
                b.category_name = cat ? cat.category_name : null;
                b.unit = u ? u.abbreviation : null;
            }
        });
    } else if (tableName === 'stock_adjustments') {
        rows.forEach(a => {
            const p = (memoryStore.products || []).find(x => x.id == a.product_id);
            if (p) {
                a.product_name = p.product_name;
                a.product_code = p.product_code;
                const u = (memoryStore.units || []).find(x => x.id == p.unit_id);
                a.unit = u ? u.abbreviation : null;
            }
        });
    } else if (tableName === 'activity_logs') {
        rows.forEach(l => {
            const u = (memoryStore.users || []).find(x => x.employee_id == l.employee_id);
            if (u) {
                l.full_name = u.full_name;
                l.username = u.username;
                const r = (memoryStore.roles || []).find(x => x.id == u.role_id);
                l.role_name = r ? r.role_name : null;
            }
        });
    }

    // WHERE filter resolution
    if (/WHERE/i.test(sql)) {
        let whereClause = sql.split(/WHERE/i)[1];
        whereClause = whereClause.split(/\s+ORDER\s+BY|\s+LIMIT\b|\s+GROUP\s+BY|\s+FOR\s+UPDATE/i)[0];
        rows = rows.filter(row => evaluateWhere(row, whereClause, params));
    }

    // COUNT(*) Aggregate check
    if (/SELECT\s+COUNT\(\*\)\s+AS\s+([a-zA-Z0-9_]+)/i.test(sql)) {
        const aliasMatch = sql.match(/SELECT\s+COUNT\(\*\)\s+AS\s+([a-zA-Z0-9_]+)/i);
        const alias = aliasMatch[1];
        return { rowCount: 1, rows: [{ [alias]: rows.length }] };
    }

    // ORDER BY
    if (/ORDER\s+BY/i.test(sql)) {
        const orderPart = sql.split(/ORDER\s+BY/i)[1].split(/\s+LIMIT\b|\s+FOR\s+UPDATE/i)[0].trim();
        const parts = orderPart.split(',')[0].trim().split(/\s+/);
        const colClean = parts[0].replace(/[^a-zA-Z0-9_]/g, '').toLowerCase();
        const isDesc = parts[1] && parts[1].toUpperCase() === 'DESC';
        rows.sort((a, b) => {
            if (a[colClean] < b[colClean]) return isDesc ? 1 : -1;
            if (a[colClean] > b[colClean]) return isDesc ? -1 : 1;
            return 0;
        });
    }

    // LIMIT
    if (/LIMIT/i.test(sql)) {
        const limitPart = sql.split(/LIMIT/i)[1].split(/\s+FOR\s+UPDATE/i)[0].trim();
        const match = limitPart.match(/(\d+)|\$([0-9]+)/);
        if (match) {
            let limit = 0;
            if (match[1]) limit = parseInt(match[1], 10);
            else if (match[2]) limit = parseInt(params[parseInt(match[2], 10) - 1], 10);
            if (!isNaN(limit) && limit > 0) rows = rows.slice(0, limit);
        }
    }

    return { rowCount: rows.length, rows };
}

function evaluateWhere(row, whereClause, params) {
    let clause = whereClause.trim();
    if (!clause) return true;

    // Substitute parameters $1, $2, ...
    params.forEach((val, idx) => {
        const placeholder = new RegExp(`\\$${idx + 1}\\b`, 'g');
        const formatted = typeof val === 'string' ? `'${val.replace(/'/g, "\\'")}'` : val;
        clause = clause.replace(placeholder, formatted);
    });

    const andConditions = clause.split(/\s+AND\s+/i);
    return andConditions.every(cond => {
        cond = cond.trim();
        if (cond === '1=1' || cond === '1 = 1') return true;

        if (/OR/i.test(cond)) {
            let inner = cond;
            if (inner.startsWith('(') && inner.endsWith(')')) {
                inner = inner.slice(1, -1);
            }
            const orParts = inner.split(/\s+OR\s+/i);
            return orParts.some(part => evaluateSingleCondition(row, part));
        }

        return evaluateSingleCondition(row, cond);
    });
}

function evaluateSingleCondition(row, cond) {
    cond = cond.trim();
    if (cond === '1=1' || cond === '1 = 1') return true;

    const opMatch = cond.match(/([a-zA-Z0-9_.:]+)\s*(=|!=|<>|>=|<=|>|<|ILIKE|LIKE)\s*(.*)/i);
    if (!opMatch) return true;

    let [, rawField, op, val] = opMatch;
    let field = rawField.split('::')[0].split('.').pop().trim().toLowerCase();
    val = val.trim().replace(/^['"]|['"]$/g, '');

    const rowVal = row[field];
    if (rowVal === undefined || rowVal === null) {
        if (val === 'null' || val === 'NULL') return op === '=';
        return false;
    }

    if (op === '=') return String(rowVal).toLowerCase() === String(val).toLowerCase();
    if (op === '!=' || op === '<>') return String(rowVal).toLowerCase() !== String(val).toLowerCase();
    if (op === '>') return parseFloat(rowVal) > parseFloat(val);
    if (op === '<') return parseFloat(rowVal) < parseFloat(val);
    if (op === '>=') return parseFloat(rowVal) >= parseFloat(val);
    if (op === '<=') return parseFloat(rowVal) <= parseFloat(val);
    if (op.toUpperCase().includes('LIKE')) {
        const pattern = val.replace(/%/g, '.*');
        return new RegExp(pattern, 'i').test(String(rowVal));
    }
    return true;
}

function handleLocalInsert(sql, params) {
    const tableMatch = sql.match(/INSERT\s+INTO\s+([a-zA-Z0-9_]+)\s*\(([^)]+)\)\s*VALUES\s*\(([^)]+)\)/i);
    if (!tableMatch) return { rowCount: 0, rows: [] };

    const tableName = tableMatch[1].toLowerCase();
    const cols = tableMatch[2].split(',').map(c => c.trim().toLowerCase());
    const valPlaceholders = parseSqlTuple(tableMatch[3]);

    if (!memoryStore[tableName]) memoryStore[tableName] = [];

    const newRow = {};
    cols.forEach((col, idx) => {
        const placeholder = valPlaceholders[idx];
        if (typeof placeholder === 'string' && placeholder.startsWith('$')) {
            const pIdx = parseInt(placeholder.substring(1), 10) - 1;
            newRow[col] = params[pIdx] !== undefined ? params[pIdx] : null;
        } else if (typeof placeholder === 'string' && (placeholder === 'NOW()' || placeholder === 'CURRENT_DATE')) {
            newRow[col] = new Date().toISOString();
        } else {
            newRow[col] = placeholder !== undefined ? placeholder : null;
        }
    });

    // Check unique conflict (e.g. idempotency_key)
    if (tableName === 'integration_events' && newRow.idempotency_key) {
        const existingIdx = memoryStore.integration_events.findIndex(x => x.idempotency_key === newRow.idempotency_key);
        if (existingIdx >= 0) {
            memoryStore.integration_events[existingIdx] = {
                ...memoryStore.integration_events[existingIdx],
                ...newRow,
                updated_at: new Date().toISOString()
            };
            persistStorage();
            return { rowCount: 1, rows: [memoryStore.integration_events[existingIdx]] };
        }
    }

    newRow.id = memoryStore[tableName].length > 0
        ? Math.max(...memoryStore[tableName].map(r => r.id || 0)) + 1
        : 1;

    if (!newRow.created_at) newRow.created_at = new Date().toISOString();
    if (!newRow.updated_at) newRow.updated_at = new Date().toISOString();

    memoryStore[tableName].push(newRow);
    persistStorage();

    return { rowCount: 1, rows: [newRow] };
}

function handleLocalUpdate(sql, params) {
    const tableMatch = sql.match(/UPDATE\s+([a-zA-Z0-9_]+)\s+SET\s+([\s\S]*?)\s+WHERE\s+(.*)/i);
    if (!tableMatch) return { rowCount: 0, rows: [] };

    const tableName = tableMatch[1].toLowerCase();
    const setClause = tableMatch[2];
    const whereClause = tableMatch[3];

    if (!memoryStore[tableName]) return { rowCount: 0, rows: [] };

    // Split SET clause safely respecting parentheses e.g. COALESCE($1, full_name)
    const setPairs = splitSetClause(setClause);

    let updatedCount = 0;
    const updatedRows = [];

    memoryStore[tableName].forEach(row => {
        if (evaluateWhere(row, whereClause, params)) {
            setPairs.forEach(pair => {
                const eqIdx = pair.indexOf('=');
                if (eqIdx === -1) return;
                const field = pair.slice(0, eqIdx).trim().toLowerCase();
                const rawVal = pair.slice(eqIdx + 1).trim();

                if (/^COALESCE\s*\(/i.test(rawVal)) {
                    // Extract COALESCE($idx, fallback)
                    const innerMatch = rawVal.match(/COALESCE\s*\(\s*\$(\d+)\s*,\s*([a-zA-Z0-9_]+)\s*\)/i);
                    if (innerMatch) {
                        const pIdx = parseInt(innerMatch[1], 10) - 1;
                        const fallbackField = innerMatch[2].toLowerCase();
                        const pVal = params[pIdx];
                        if (pVal !== null && pVal !== undefined) {
                            row[field] = pVal;
                        } else {
                            row[field] = row[fallbackField];
                        }
                    }
                } else if (rawVal === 'NOW()' || rawVal === 'CURRENT_TIMESTAMP') {
                    row[field] = new Date().toISOString();
                } else {
                    const paramMatch = rawVal.match(/^\$(\d+)$/);
                    if (paramMatch) {
                        const pIdx = parseInt(paramMatch[1], 10) - 1;
                        row[field] = params[pIdx];
                    } else {
                        row[field] = cleanVal(rawVal);
                    }
                }
            });
            row.updated_at = new Date().toISOString();
            updatedCount++;
            updatedRows.push({ ...row });
        }
    });

    persistStorage();
    return { rowCount: updatedCount, rows: updatedRows };
}

function splitSetClause(setClause) {
    const pairs = [];
    let current = '';
    let parenDepth = 0;

    for (let i = 0; i < setClause.length; i++) {
        const char = setClause[i];
        if (char === '(') parenDepth++;
        else if (char === ')') parenDepth--;

        if (char === ',' && parenDepth === 0) {
            pairs.push(current.trim());
            current = '';
        } else {
            current += char;
        }
    }
    if (current.trim()) pairs.push(current.trim());
    return pairs;
}

function handleLocalDelete(sql, params) {
    const tableMatch = sql.match(/DELETE\s+FROM\s+([a-zA-Z0-9_]+)\s+WHERE\s+(.*)/i);
    if (!tableMatch) return { rowCount: 0, rows: [] };

    const tableName = tableMatch[1].toLowerCase();
    const whereClause = tableMatch[2];

    if (!memoryStore[tableName]) return { rowCount: 0, rows: [] };

    const initialLen = memoryStore[tableName].length;
    memoryStore[tableName] = memoryStore[tableName].filter(row => !evaluateWhere(row, whereClause, params));
    const deletedCount = initialLen - memoryStore[tableName].length;

    persistStorage();
    return { rowCount: deletedCount, rows: [] };
}

connect();

module.exports = {
    query,
    getClient,
    isLive: () => isPostgresLive,
    getConfig: () => ({ ...dbConfig, password: '***' }),
    getMemoryStore: () => memoryStore,
    connect
};
