const jwt = require('jsonwebtoken');
const { errorResponse } = require('../utils/response');

const publicPrefixes = ['/api/auth/register', '/api/auth/login'];

function jwtValidation(req, res, next) {
  if (publicPrefixes.some((prefix) => req.path.startsWith(prefix))) {
    return next();
  }

  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;

  if (!token) {
    return errorResponse(res, 401, 'Authorization token is required');
  }

  try {
    const payload = jwt.verify(token, process.env.JWT_SECRET);
    req.user = payload;
    req.headers['x-user-id'] = String(payload.id);
    req.headers['x-user-role'] = payload.role;
    return next();
  } catch (error) {
    return errorResponse(res, 401, 'Invalid or expired token');
  }
}

module.exports = jwtValidation;
