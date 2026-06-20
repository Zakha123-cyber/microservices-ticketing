require('dotenv').config();

const pool = require('../config/database');

const events = [
  [1, 'Rock Concert 2026', 'Amazing rock concert.', '2026-07-15 19:00:00', 'Stadium Jakarta', 500000, 1000, 1],
  [1, 'Jazz Night', 'Relaxing jazz performance.', '2026-07-20 20:00:00', 'Blue Note Jazz Club', 350000, 200, 1],
  [2, 'Football Match - Final', 'Final match event.', '2026-08-10 16:00:00', 'Gelora Bung Karno', 150000, 50000, 1],
  [2, 'Basketball Tournament', 'Basketball tournament.', '2026-08-15 18:00:00', 'Basket Hall Jakarta', 100000, 5000, 1],
  [3, 'Tech Conference 2026', 'Technology conference.', '2026-09-05 09:00:00', 'Convention Center', 750000, 500, 1],
  [3, 'Startup Pitch Day', 'Startup pitching and networking event.', '2026-09-12 10:00:00', 'Co-working Space', 250000, 150, 1],
  [4, 'Summer Music Festival', 'Outdoor summer music festival.', '2026-10-01 12:00:00', 'Beach Area Ancol', 600000, 3000, 1],
  [4, 'Food Festival', 'Local and international food festival.', '2026-10-15 10:00:00', 'City Park', 50000, 10000, 1],
  [4, 'Electronic Music Festival', 'Electronic dance music festival.', '2026-11-05 18:00:00', 'Open Field Sentul', 800000, 5000, 1],
  [2, 'Marathon Event', 'City marathon event.', '2026-11-20 05:00:00', 'Start at Monas', 200000, 2000, 1],
];

async function seed() {
  await pool.execute(`CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500),
    date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quota INT NOT NULL,
    available_tickets INT NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  )`);

  for (const event of events) {
    const [categoryId, title, description, date, location, price, quota, createdBy] = event;
    await pool.execute(
      `INSERT INTO events (category_id, title, description, date, location, price, quota, available_tickets, created_by)
       SELECT ?, ?, ?, ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM events WHERE title = ?)`,
      [categoryId, title, description, date, location, price, quota, quota, createdBy, title],
    );
  }

  console.log('Events seeded');
  await pool.end();
}

seed().catch((error) => {
  console.error(error);
  process.exit(1);
});
