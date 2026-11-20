/**
 * Product Management Service
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');

class ProductService {
    async getProducts(filters = {}) {
        let sql = `
            SELECT p.*, c.category_name, u.unit_name, u.abbreviation AS unit_abbr
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN units u ON p.unit_id = u.id
            WHERE 1=1
        `;
        const params = [];
        let pIdx = 1;

        if (filters.search) {
            sql += ` AND (p.product_name ILIKE $${pIdx} OR p.product_code ILIKE $${pIdx} OR p.barcode ILIKE $${pIdx})`;
            params.push(`%${filters.search}%`);
            pIdx++;
        }

        if (filters.category_id) {
            sql += ` AND p.category_id = $${pIdx}`;
            params.push(filters.category_id);
            pIdx++;
        }

        if (filters.status) {
            sql += ` AND p.status = $${pIdx}`;
            params.push(filters.status);
            pIdx++;
        }

        if (filters.availability) {
            if (filters.availability === 'OUT_OF_STOCK') {
                sql += ` AND p.current_stock <= 0`;
            } else if (filters.availability === 'LOW_STOCK') {
                sql += ` AND p.current_stock > 0 AND p.current_stock <= p.minimum_stock`;
            } else if (filters.availability === 'AVAILABLE') {
                sql += ` AND p.current_stock > p.minimum_stock`;
            }
        }

        sql += ` ORDER BY p.id ASC`;

        const res = await db.query(sql, params);
        return res.rows;
    }

    async getProductById(id) {
        const res = await db.query(
            `SELECT p.*, c.category_name, u.unit_name, u.abbreviation AS unit_abbr
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             WHERE p.id = $1`,
            [id]
        );
        if (res.rows.length === 0) throw new Error(`Product with ID ${id} not found.`);
        return res.rows[0];
    }

    async getProductByBarcode(barcode) {
        const res = await db.query(
            `SELECT p.*, c.category_name, u.unit_name, u.abbreviation AS unit_abbr
             FROM products p
             JOIN categories c ON p.category_id = c.id
             JOIN units u ON p.unit_id = u.id
             WHERE p.barcode = $1 OR p.product_code = $1`,
            [barcode]
        );
        if (res.rows.length === 0) throw new Error(`Product with barcode/code '${barcode}' not found.`);
        return res.rows[0];
    }

    async createProduct(data, employeeId = 'EMP-001') {
        const {
            product_code, barcode, product_name, category_id, unit_id,
            description, cost_price, selling_price, minimum_stock, maximum_stock,
            current_stock, reorder_level, expiration_tracking, is_recipe_item, is_ingredient,
            product_image
        } = data;

        // Check duplicate code or barcode
        const existing = await db.query(
            `SELECT id FROM products WHERE product_code = $1 OR (barcode IS NOT NULL AND barcode = $2)`,
            [product_code, barcode || '']
        );
        if (existing.rows.length > 0) {
            throw new Error(`Product code '${product_code}' or barcode already exists.`);
        }

        const res = await db.query(
            `INSERT INTO products (
                product_code, barcode, product_name, category_id, unit_id,
                description, cost_price, selling_price, minimum_stock, maximum_stock,
                current_stock, reorder_level, expiration_tracking, is_recipe_item, is_ingredient,
                product_image, status, created_at, updated_at
            ) VALUES (
                $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15, $16, 'ACTIVE', NOW(), NOW()
            )`,
            [
                product_code, barcode || null, product_name, category_id, unit_id,
                description || null, parseFloat(cost_price || 0), parseFloat(selling_price || 0),
                parseFloat(minimum_stock || 0), parseFloat(maximum_stock || 1000),
                parseFloat(current_stock || 0), parseFloat(reorder_level || 10),
                Boolean(expiration_tracking), Boolean(is_recipe_item), Boolean(is_ingredient),
                product_image || null
            ]
        );

        await logActivity(employeeId, 'CREATE_PRODUCT', 'PRODUCTS', `Created product ${product_code} (${product_name})`);
        return { success: true, message: 'Product created successfully.' };
    }

    async updateProduct(id, data, employeeId = 'EMP-001') {
        const {
            product_name, category_id, unit_id, description, cost_price,
            selling_price, minimum_stock, maximum_stock, reorder_level,
            expiration_tracking, status, product_image
        } = data;

        await db.query(
            `UPDATE products SET 
                product_name = $1, category_id = $2, unit_id = $3, description = $4,
                cost_price = $5, selling_price = $6, minimum_stock = $7,
                maximum_stock = $8, reorder_level = $9, expiration_tracking = $10,
                status = $11, product_image = $12, updated_at = NOW()
             WHERE id = $13`,
            [
                product_name, category_id, unit_id, description || null,
                parseFloat(cost_price || 0), parseFloat(selling_price || 0),
                parseFloat(minimum_stock || 0), parseFloat(maximum_stock || 1000),
                parseFloat(reorder_level || 10), Boolean(expiration_tracking),
                status || 'ACTIVE', product_image || null, id
            ]
        );

        await logActivity(employeeId, 'UPDATE_PRODUCT', 'PRODUCTS', `Updated product ID ${id}`);
        return { success: true, message: 'Product updated successfully.' };
    }

    async archiveProduct(id, employeeId = 'EMP-001') {
        await db.query(`UPDATE products SET status = 'ARCHIVED', updated_at = NOW() WHERE id = $1`, [id]);
        await logActivity(employeeId, 'ARCHIVE_PRODUCT', 'PRODUCTS', `Archived product ID ${id}`);
        return { success: true, message: 'Product archived.' };
    }

    async getCategories() {
        const res = await db.query(`SELECT * FROM categories ORDER BY id ASC`);
        return res.rows;
    }

    async getUnits() {
        const res = await db.query(`SELECT * FROM units ORDER BY id ASC`);
        return res.rows;
    }
}

module.exports = new ProductService();
