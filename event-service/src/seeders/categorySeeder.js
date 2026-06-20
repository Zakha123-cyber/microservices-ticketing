require('dotenv').config();

const pool = require('../config/database');

async function seed() {
  await pool.execute(`CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`);

  for (const name of ['Music', 'Sport', 'Seminar', 'Festival']) {
    await pool.execute('INSERT IGNORE INTO categories (name) VALUES (?)', [name]);
  }

  console.log('Categories seeded');
  await pool.end();
}

seed().catch((error) => {
  console.error(error);
  process.exit(1);
});
