function errorHandler(error, req, res, next) {
  const status = error.response?.status || 500;
  const data = error.response?.data || { success: false, message: error.message || 'Internal server error' };
  res.status(status).json(data);
}

module.exports = errorHandler;
