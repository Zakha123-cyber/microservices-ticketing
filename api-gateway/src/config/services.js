require('dotenv').config();

module.exports = {
  auth: process.env.AUTH_SERVICE_URL || 'http://localhost:8001',
  event: process.env.EVENT_SERVICE_URL || 'http://localhost:8002',
  booking: process.env.BOOKING_SERVICE_URL || 'http://localhost:8003',
};
