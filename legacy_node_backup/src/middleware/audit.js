/**
 * Audit Logging Middleware & Service Helper
 */
const db = require('../../database/pg-client');

async function logActivity(employeeId, action, moduleName, description, ip = '127.0.0.1') {
    try {
        await db.query(
            `INSERT INTO activity_logs (employee_id, action, module, description, ip_address, created_at)
             VALUES ($1, $2, $3, $4, $5, NOW())`,
            [employeeId || 'SYSTEM', action, moduleName, description, ip]
        );
    } catch (err) {
        console.error('[Audit Logger Error]', err.message);
    }
}

function auditMiddleware(action, moduleName) {
    return (req, res, next) => {
        const originalSend = res.send;
        res.send = function (body) {
            if (res.statusCode >= 200 && res.statusCode < 300) {
                const empId = req.user ? req.user.employee_id : 'ANONYMOUS';
                const desc = `${req.method} ${req.originalUrl} executed`;
                logActivity(empId, action, moduleName, desc, req.ip);
            }
            return originalSend.apply(this, arguments);
        };
        next();
    };
}

module.exports = {
    logActivity,
    auditMiddleware
};
