function successResponse(res, status, message, data = null) {
  return res.status(status).json({ success: true, message, ...(data && { data }) });
}

function errorResponse(res, status, message, errors = null) {
  return res.status(status).json({ success: false, message, ...(errors && { errors }) });
}

module.exports = { successResponse, errorResponse };
