const { validationResult } = require('express-validator');
const { errorResponse } = require('../utils/response');

function handleValidation(req, res, next) {
  const errors = validationResult(req);
  if (!errors.isEmpty()) {
    const fieldErrors = errors.array().reduce((acc, error) => {
      const field = error.path || error.param || 'request';
      acc[field] = acc[field] || [];
      acc[field].push(error.msg);
      return acc;
    }, {});

    return errorResponse(res, 400, 'Validation error', fieldErrors);
  }
  next();
}

module.exports = { handleValidation };
