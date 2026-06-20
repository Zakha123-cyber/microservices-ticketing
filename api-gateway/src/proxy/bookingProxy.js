const services = require('../config/services');
const { forwardRequest } = require('../utils/httpClient');

module.exports = forwardRequest(services.booking);
