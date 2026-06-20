const express = require('express');
const { body } = require('express-validator');
const authController = require('../controllers/authController');
const authMiddleware = require('../middleware/authMiddleware');
const { handleValidation } = require('../middleware/validation');

const router = express.Router();

router.get('/health', (req, res) => res.json({ success: true, service: 'auth-service' }));
router.post('/register', [
  body('name').notEmpty(),
  body('email').isEmail(),
  body('password').isLength({ min: 6 }),
  body('password_confirmation').custom((value, { req }) => value === req.body.password),
  handleValidation,
], authController.register);
router.post('/login', [body('email').isEmail(), body('password').notEmpty(), handleValidation], authController.login);
router.get('/profile', authMiddleware, authController.profile);
router.put('/profile', authMiddleware, [body('name').notEmpty(), body('email').isEmail(), handleValidation], authController.updateProfile);

module.exports = router;
