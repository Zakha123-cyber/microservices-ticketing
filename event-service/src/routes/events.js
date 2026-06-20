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
const eventValidationRules = [
  body('title').trim().notEmpty().withMessage('Title is required'),
  body('category_id').notEmpty().withMessage('Category is required').bail().isInt({ min: 1 }).withMessage('Category must be valid'),
  body('date').notEmpty().withMessage('Date is required').bail().isISO8601().withMessage('Date must be a valid date'),
  body('location').trim().notEmpty().withMessage('Location is required'),
  body('price').notEmpty().withMessage('Price is required').bail().isNumeric().withMessage('Price must be numeric'),
  body('quota').notEmpty().withMessage('Quota is required').bail().isInt({ min: 1 }).withMessage('Quota must be at least 1'),
  handleValidation,
];

router.post('/', requireAdmin, upload, eventValidationRules, eventController.store);
router.post('/check-availability', eventController.checkAvailability);
router.post('/reduce-quota', eventController.reduceQuota);
router.post('/internal/check-availability', eventController.checkAvailability);
router.post('/internal/reduce-quota', eventController.reduceQuota);
router.get('/:id', eventController.show);
router.post('/:id/update', requireAdmin, upload, eventValidationRules, eventController.update);
router.put('/:id', requireAdmin, upload, eventValidationRules, eventController.update);
router.delete('/:id', requireAdmin, eventController.destroy);

module.exports = router;
