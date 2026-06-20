const express = require('express');
const authProxy = require('../proxy/authProxy');
const eventProxy = require('../proxy/eventProxy');
const bookingProxy = require('../proxy/bookingProxy');

const router = express.Router();

router.get('/health', (req, res) => res.json({ success: true, service: 'api-gateway' }));
router.use('/auth', authProxy);
router.use('/events', eventProxy);
router.use('/bookings', bookingProxy);

module.exports = router;
