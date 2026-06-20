const pool = require('../config/database');

async function findByEmail(email) {
  const [rows] = await pool.execute('SELECT * FROM users WHERE email = ? LIMIT 1', [email]);
  return rows[0] || null;
}

async function findById(id) {
  const [rows] = await pool.execute('SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1', [id]);
  return rows[0] || null;
}

async function createUser({ name, email, password, role = 'user' }) {
  const [result] = await pool.execute('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)', [name, email, password, role]);
  return findById(result.insertId);
}

async function updateUser(id, { name, email }) {
  await pool.execute('UPDATE users SET name = ?, email = ? WHERE id = ?', [name, email, id]);
  return findById(id);
}

module.exports = { findByEmail, findById, createUser, updateUser };
