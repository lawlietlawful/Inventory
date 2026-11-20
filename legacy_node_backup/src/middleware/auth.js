/**
 * Authentication and RBAC Middleware
 */
const db = require('../../database/pg-client');

async function authenticate(req, res, next) {
    const authHeader = req.headers.authorization;
    let token = null;

    if (authHeader && authHeader.startsWith('Bearer ')) {
        token = authHeader.substring(7);
    } else if (req.query.token) {
        token = req.query.token;
    }

    // Default development fallback: look for X-User-Id or authorization
    const userIdHeader = req.headers['x-user-id'] || '1'; // defaults to Admin if not provided for testing
    const employeeIdHeader = req.headers['x-employee-id'];

    try {
        let userRes;
        if (employeeIdHeader) {
            userRes = await db.query(
                `SELECT u.*, r.role_name 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.employee_id = $1`,
                [employeeIdHeader]
            );
        } else {
            userRes = await db.query(
                `SELECT u.*, r.role_name 
                 FROM users u 
                 JOIN roles r ON u.role_id = r.id 
                 WHERE u.id = $1`,
                [userIdHeader]
            );
        }

        if (userRes.rows.length === 0) {
            return res.status(401).json({ success: false, error: 'User account not found.' });
        }

        const user = userRes.rows[0];

        // Enforcement: inactive employees cannot access system
        if (user.account_status !== 'ACTIVE') {
            return res.status(403).json({
                success: false,
                error: `Account is ${user.account_status}. Access denied. Please contact HR or System Administrator.`
            });
        }

        // Fetch permissions
        const permRes = await db.query(
            `SELECT p.permission_name 
             FROM role_permissions rp 
             JOIN permissions p ON rp.permission_id = p.id 
             WHERE rp.role_id = $1`,
            [user.role_id]
        );

        user.permissions = permRes.rows.map(p => p.permission_name);
        req.user = user;
        next();
    } catch (err) {
        console.error('[Auth Middleware Error]', err.message);
        res.status(500).json({ success: false, error: 'Authentication verification failure.' });
    }
}

function requirePermission(permissionName) {
    return (req, res, next) => {
        if (!req.user) {
            return res.status(401).json({ success: false, error: 'Unauthorized.' });
        }
        // Administrator role bypasses specific permission checks
        if (req.user.role_name === 'Administrator' || req.user.role_id === 1) {
            return next();
        }
        if (req.user.permissions && req.user.permissions.includes(permissionName)) {
            return next();
        }
        return res.status(403).json({
            success: false,
            error: `Forbidden: Missing required permission '${permissionName}'.`
        });
    };
}

module.exports = {
    authenticate,
    requirePermission
};
