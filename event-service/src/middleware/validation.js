const { validationResult } = require('express-validator');
const { errorResponse } = require('../utils/response');

function handleValidation(req, res, next) {
  const errors = validationResult(req);
  if (!errors.isEmpty()) return errorResponse(res, 400, 'Validation error', errors.array());
  next();
}

module.exports = { handleValidation };
