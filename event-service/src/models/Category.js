const pool = require('../config/database');

async function findAll() {
  const [rows] = await pool.execute('SELECT * FROM categories ORDER BY name ASC');
  return rows;
}

module.exports = { findAll };
