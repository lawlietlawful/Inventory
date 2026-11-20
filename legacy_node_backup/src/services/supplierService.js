/**
 * Supplier Management Service
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');

class SupplierService {
    async getSuppliers(filters = {}) {
        let sql = `SELECT * FROM suppliers WHERE 1=1`;
        const params = [];

        if (filters.search) {
            sql += ` AND (supplier_name ILIKE $1 OR supplier_code ILIKE $1 OR contact_person ILIKE $1)`;
            params.push(`%${filters.search}%`);
        }

        sql += ` ORDER BY id ASC`;
        const res = await db.query(sql, params);
        return res.rows;
    }

    async getSupplierById(id) {
        const supRes = await db.query(`SELECT * FROM suppliers WHERE id = $1`, [id]);
        if (supRes.rows.length === 0) throw new Error('Supplier not found.');
        const supplier = supRes.rows[0];

        // Supplied products
        const prodRes = await db.query(
            `SELECT sp.*, p.product_name, p.product_code, u.abbreviation AS unit
             FROM supplier_products sp
             JOIN products p ON sp.product_id = p.id
             JOIN units u ON p.unit_id = u.id
             WHERE sp.supplier_id = $1`,
            [id]
        );
        supplier.products = prodRes.rows;

        // Purchase history
        const poRes = await db.query(
            `SELECT * FROM purchase_orders WHERE supplier_id = $1 ORDER BY id DESC`,
            [id]
        );
        supplier.purchase_orders = poRes.rows;

        return supplier;
    }

    async createSupplier(data, employeeId = 'EMP-001') {
        const {
            supplier_code, supplier_name, contact_person,
            contact_number, email, address
        } = data;

        const res = await db.query(
            `INSERT INTO suppliers (
                supplier_code, supplier_name, contact_person, contact_number,
                email, address, status, created_at, updated_at
            ) VALUES ($1, $2, $3, $4, $5, $6, 'ACTIVE', NOW(), NOW())
            RETURNING id`,
            [supplier_code, supplier_name, contact_person, contact_number, email, address]
        );

        await logActivity(employeeId, 'SUPPLIER_CREATED', 'SUPPLIERS', `Created supplier ${supplier_name} (${supplier_code})`);
        return { success: true, supplier_id: res.rows[0].id };
    }

    async updateSupplier(id, data, employeeId = 'EMP-001') {
        const {
            supplier_name, contact_person, contact_number, email, address, status
        } = data;

        await db.query(
            `UPDATE suppliers SET
                supplier_name = $1, contact_person = $2, contact_number = $3,
                email = $4, address = $5, status = $6, updated_at = NOW()
             WHERE id = $7`,
            [supplier_name, contact_person, contact_number, email, address, status || 'ACTIVE', id]
        );

        await logActivity(employeeId, 'SUPPLIER_UPDATED', 'SUPPLIERS', `Updated supplier ID ${id}`);
        return { success: true, message: 'Supplier updated successfully.' };
    }
}

module.exports = new SupplierService();
