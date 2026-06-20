require('dotenv').config();

const pool = require('../config/database');
const { hashPassword } = require('../utils/bcrypt');

async function seed() {
  await pool.execute(`CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`);

  const users = [
    { name: 'Admin User', email: 'admin@ticketing.com', password: 'admin123', role: 'admin' },
    ...Array.from({ length: 5 }, (_, index) => ({ name: `User ${index + 1}`, email: `user${index + 1}@example.com`, password: 'password123', role: 'user' })),
  ];

  for (const user of users) {
    await pool.execute(
      'INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)',
      [user.name, user.email, await hashPassword(user.password), user.role],
    );
  }

  console.log('Auth users seeded');
  await pool.end();
}

seed().catch((error) => {
  console.error(error);
  process.exit(1);
});
