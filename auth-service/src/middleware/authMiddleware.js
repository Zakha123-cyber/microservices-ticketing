const jwt = require('jsonwebtoken');
const { errorResponse } = require('../utils/response');

function authMiddleware(req, res, next) {
  const header = req.headers.authorization || '';
  const token = header.startsWith('Bearer ') ? header.slice(7) : null;

  if (!token) {
    return errorResponse(res, 401, 'Authorization token is required');
  }

  try {
    req.user = jwt.verify(token, process.env.JWT_SECRET);
    return next();
  } catch (error) {
    return errorResponse(res, 401, 'Invalid or expired token');
  }
}

module.exports = authMiddleware;
