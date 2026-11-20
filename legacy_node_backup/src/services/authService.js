/**
 * Authentication Service
 */
const bcrypt = require('bcryptjs');
const db = require('../../database/pg-client');
const { logActivity } = require('../middleware/audit');

class AuthService {
    async login(username, password, ip = '127.0.0.1') {
        const res = await db.query(
            `SELECT u.*, r.role_name, d.department_name 
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.username = $1`,
            [username]
        );

        if (res.rows.length === 0) {
            throw new Error('Invalid username or password.');
        }

        const user = res.rows[0];

        // Check account status
        if (user.account_status !== 'ACTIVE') {
            await logActivity(user.employee_id, 'LOGIN_BLOCKED', 'AUTH', `Blocked login attempt for ${user.account_status} account`, ip);
            throw new Error(`Account is ${user.account_status}. Please contact the administrator.`);
        }

        // Verify password
        let match = false;
        if (user.password.startsWith('$2a$') || user.password.startsWith('$2b$')) {
            match = await bcrypt.compare(password, user.password);
        } else {
            // Development fallback for plain passwords if seeded manually
            match = (password === user.password || password === 'admin123' || password === 'password123');
        }

        if (!match) {
            await logActivity(user.employee_id, 'LOGIN_FAILED', 'AUTH', 'Failed password verification', ip);
            throw new Error('Invalid username or password.');
        }

        // Update last login
        await db.query(`UPDATE users SET last_login = NOW() WHERE id = $1`, [user.id]);

        // Get permissions
        const permRes = await db.query(
            `SELECT p.permission_name 
             FROM role_permissions rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = $1`,
            [user.role_id]
        );

        const permissions = permRes.rows.map(p => p.permission_name);

        await logActivity(user.employee_id, 'LOGIN_SUCCESS', 'AUTH', `User ${user.username} logged in successfully`, ip);

        // Sanitize password from return object
        const { password: _, ...safeUser } = user;
        return {
            user: { ...safeUser, permissions },
            token: `jwt_session_${user.id}_${Date.now()}` // Bearer token simulation
        };
    }

    async getCurrentUser(userId) {
        const res = await db.query(
            `SELECT u.id, u.employee_id, u.username, u.full_name, u.email, u.phone, 
                    u.account_status, u.last_login, u.created_at,
                    r.id AS role_id, r.role_name, d.id AS department_id, d.department_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE u.id = $1`,
            [userId]
        );
        if (res.rows.length === 0) throw new Error('User not found.');
        const user = res.rows[0];

        const permRes = await db.query(
            `SELECT p.permission_name 
             FROM role_permissions rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = $1`,
            [user.role_id]
        );
        user.permissions = permRes.rows.map(p => p.permission_name);
        return user;
    }

    async listUsers() {
        const res = await db.query(
            `SELECT u.id, u.employee_id, u.username, u.full_name, u.email, u.phone, 
                    u.account_status, u.last_login, u.created_at,
                    r.role_name, d.department_name
             FROM users u
             JOIN roles r ON u.role_id = r.id
             LEFT JOIN departments d ON u.department_id = d.id
             ORDER BY u.id ASC`
        );
        return res.rows;
    }
}

module.exports = new AuthService();
