const express = require('express');
const { body } = require('express-validator');
const eventController = require('../controllers/eventController');
const { attachGatewayUser, requireAdmin } = require('../middleware/authMiddleware');
const upload = require('../middleware/upload');
const { handleValidation } = require('../middleware/validation');

const router = express.Router();

router.use(attachGatewayUser);
router.get('/health', (req, res) => res.json({ success: true, service: 'event-service' }));
router.get('/categories', eventController.categories);
router.get('/', eventController.index);
router.post('/', requireAdmin, upload, [body('title').notEmpty(), body('category_id').notEmpty(), body('date').notEmpty(), body('location').notEmpty(), body('price').isNumeric(), body('quota').isInt({ min: 1 }), handleValidation], eventController.store);
router.post('/check-availability', eventController.checkAvailability);
router.post('/reduce-quota', eventController.reduceQuota);
router.post('/internal/check-availability', eventController.checkAvailability);
router.post('/internal/reduce-quota', eventController.reduceQuota);
router.get('/:id', eventController.show);
router.put('/:id', requireAdmin, upload, eventController.update);
router.delete('/:id', requireAdmin, eventController.destroy);

module.exports = router;
