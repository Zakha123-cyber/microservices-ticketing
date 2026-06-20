function errorHandler(error, req, res, next) {
  console.error(error);

  if (error.code === 'LIMIT_FILE_SIZE') {
    return res.status(400).json({ success: false, message: 'Validation error', errors: { image: ['Image must not be greater than 5MB'] } });
  }

  if (error.message === 'Invalid file type') {
    return res.status(400).json({ success: false, message: 'Validation error', errors: { image: ['Image must be jpg, jpeg, or png'] } });
  }

  res.status(500).json({ success: false, message: error.message || 'Internal server error' });
}

module.exports = errorHandler;
