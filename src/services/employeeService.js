/**
 * Employee Management System Integration Service
 * Manages Employee Synchronization, Account Status Lifecycle,
 * and Employee Transaction Accountability Audit.
 */
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');

class EmployeeService {
    /**
     * Retrieve all active and registered employees
     */
    async getEmployees() {
        const res = await db.query(
            `SELECT u.id, u.employee_id, u.username, u.full_name, u.email, u.phone,
                    u.account_status, u.last_login, u.created_at,
                    r.id AS role_id, r.role_name,
                    d.id AS department_id, d.department_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN departments d ON u.department_id = d.id
             ORDER BY u.id ASC`
        );
        return res.rows;
    }

    /**
     * Retrieve specific employee details with linked activities
     */
    async getEmployeeById(employeeId) {
        const res = await db.query(
            `SELECT u.id, u.employee_id, u.username, u.full_name, u.email, u.phone,
                    u.account_status, u.last_login, u.created_at,
                    r.role_name, d.department_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.employee_id = $1 OR u.id::text = $1`,
            [employeeId]
        );

        if (res.rows.length === 0) {
            throw new Error(`Employee '${employeeId}' not found in system.`);
        }

        const employee = res.rows[0];

        // Retrieve employee's inventory activity footprint
        const txns = await db.query(
            `SELECT t.*, p.product_name, p.product_code 
             FROM inventory_transactions t
             JOIN products p ON t.product_id = p.id
             WHERE t.employee_id = $1
             ORDER BY t.created_at DESC
             LIMIT 50`,
            [employee.employee_id]
        );

        const pos = await db.query(
            `SELECT po.*, s.supplier_name 
             FROM purchase_orders po
             JOIN suppliers s ON po.supplier_id = s.id
             WHERE po.requested_by = $1 OR po.approved_by = $1
             ORDER BY po.created_at DESC`,
            [employee.employee_id]
        );

        const adjs = await db.query(
            `SELECT a.*, p.product_name 
             FROM stock_adjustments a
             JOIN products p ON a.product_id = p.id
             WHERE a.requested_by = $1 OR a.approved_by = $1
             ORDER BY a.created_at DESC`,
            [employee.employee_id]
        );

        return {
            employee,
            transactions: txns.rows,
            purchase_orders: pos.rows,
            adjustments: adjs.rows
        };
    }

    /**
     * Synchronize Employee records from external Employee Management System
     */
    async syncEmployee(payload, adminEmployeeId = 'SYSTEM') {
        const {
            employee_id,
            full_name,
            email,
            phone,
            department_name,
            role_name,
            account_status // 'ACTIVE', 'INACTIVE', 'SUSPENDED'
        } = payload;

        if (!employee_id) throw new Error('Missing employee_id in sync payload.');

        // Find or create department
        let departmentId = null;
        if (department_name) {
            const deptRes = await db.query(
                `SELECT id FROM departments WHERE department_name = $1`,
                [department_name]
            );
            if (deptRes.rows.length > 0) {
                departmentId = deptRes.rows[0].id;
            } else {
                const newDept = await db.query(
                    `INSERT INTO departments (department_name) VALUES ($1) RETURNING id`,
                    [department_name]
                );
                departmentId = newDept.rows[0].id;
            }
        }

        // Find role
        let roleId = 5; // Default to 'Employee'
        if (role_name) {
            const roleRes = await db.query(
                `SELECT id FROM roles WHERE role_name ILIKE $1`,
                [role_name]
            );
            if (roleRes.rows.length > 0) roleId = roleRes.rows[0].id;
        }

        // Check if user already exists
        const existing = await db.query(
            `SELECT * FROM users WHERE employee_id = $1`,
            [employee_id]
        );

        const status = account_status ? account_status.toUpperCase() : 'ACTIVE';

        if (existing.rows.length > 0) {
            // Update existing employee
            await db.query(
                `UPDATE users 
                 SET full_name = COALESCE($1, full_name),
                     email = COALESCE($2, email),
                     phone = COALESCE($3, phone),
                     department_id = COALESCE($4, department_id),
                     role_id = COALESCE($5, role_id),
                     account_status = $6,
                     updated_at = NOW()
                 WHERE employee_id = $7`,
                [full_name, email, phone, departmentId, roleId, status, employee_id]
            );

            await logActivity(
                adminEmployeeId,
                'EMPLOYEE_SYNC_UPDATED',
                'EMPLOYEE_INTEGRATION',
                `Synchronized employee ${employee_id} (${full_name || 'N/A'}). Status: ${status}`
            );

            return {
                action: 'UPDATED',
                employee_id,
                status,
                message: `Employee ${employee_id} synchronized successfully.`
            };
        } else {
            // Create new employee account linked to EMS
            const username = (email ? email.split('@')[0] : `emp_${employee_id.toLowerCase().replace(/[^a-z0-9]/g, '_')}`);
            const defaultPasswordHash = '$2a$10$0rLzSjB3rU7qGv6lUqgX5O2sUo/y1a2zK0rU7qGv6lUqgX5O2sUo.'; // default password 'admin123'

            await db.query(
                `INSERT INTO users (
                    employee_id, username, password, role_id, department_id,
                    full_name, email, phone, account_status, created_at, updated_at
                ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, NOW(), NOW())`,
                [
                    employee_id, username, defaultPasswordHash, roleId, departmentId,
                    full_name || username, email || null, phone || null, status
                ]
            );

            await logActivity(
                adminEmployeeId,
                'EMPLOYEE_SYNC_CREATED',
                'EMPLOYEE_INTEGRATION',
                `Created new employee account for ${employee_id} via EMS synchronization.`
            );

            return {
                action: 'CREATED',
                employee_id,
                username,
                status,
                message: `New employee ${employee_id} registered into IMS.`
            };
        }
    }

    /**
     * Retrieve employee activity timeline
     */
    async getEmployeeActivities(employeeId) {
        const res = await db.query(
            `SELECT * FROM activity_logs 
             WHERE employee_id = $1 
             ORDER BY created_at DESC 
             LIMIT 100`,
            [employeeId]
        );
        return res.rows;
    }
}

module.exports = new EmployeeService();
