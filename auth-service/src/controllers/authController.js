const User = require('../models/User');
const { hashPassword, comparePassword } = require('../utils/bcrypt');
const { signToken } = require('../utils/jwt');
const { successResponse, errorResponse } = require('../utils/response');

async function register(req, res, next) {
  try {
    const { name, email, password } = req.body;
    const existing = await User.findByEmail(email);
    if (existing) return errorResponse(res, 400, 'Email already exists');

    const user = await User.createUser({ name, email, password: await hashPassword(password) });
    const token = signToken(user);
    return successResponse(res, 201, 'User registered successfully', { user, token });
  } catch (error) {
    return next(error);
  }
}

async function login(req, res, next) {
  try {
    const { email, password } = req.body;
    const user = await User.findByEmail(email);
    if (!user || !(await comparePassword(password, user.password))) {
      return errorResponse(res, 401, 'Invalid credentials');
    }

    const safeUser = { id: user.id, name: user.name, email: user.email, role: user.role };
    const token = signToken(safeUser);
    return successResponse(res, 200, 'Login successful', { user: safeUser, token });
  } catch (error) {
    return next(error);
  }
}

async function profile(req, res, next) {
  try {
    const user = await User.findById(req.user.id);
    if (!user) return errorResponse(res, 404, 'User not found');
    return res.json({ success: true, data: user });
  } catch (error) {
    return next(error);
  }
}

async function updateProfile(req, res, next) {
  try {
    const user = await User.updateUser(req.user.id, req.body);
    return successResponse(res, 200, 'Profile updated successfully', user);
  } catch (error) {
    return next(error);
  }
}

module.exports = { register, login, profile, updateProfile };
