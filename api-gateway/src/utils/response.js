function errorResponse(res, status, message, details = null) {
  return res.status(status).json({ success: false, message, ...(details && { details }) });
}

module.exports = { errorResponse };
