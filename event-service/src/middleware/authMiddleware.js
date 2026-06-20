const { errorResponse } = require('../utils/response');

function attachGatewayUser(req, res, next) {
  req.user = {
    id: Number(req.headers['x-user-id'] || 0),
    role: req.headers['x-user-role'] || 'guest',
  };
  next();
}

function requireAdmin(req, res, next) {
  if (req.user?.role !== 'admin') return errorResponse(res, 403, 'Admin role is required');
  next();
}

module.exports = { attachGatewayUser, requireAdmin };
