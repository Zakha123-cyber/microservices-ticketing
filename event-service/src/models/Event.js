const pool = require('../config/database');

function normalizeDate(value) {
  return typeof value === 'string' ? value.replace('T', ' ') : value;
}

async function findAll({ limit = 10, offset = 0, search, category_id, date_from, date_to }) {
  const safeLimit = Math.max(1, Math.min(Number.parseInt(limit, 10) || 10, 100));
  const safeOffset = Math.max(0, Number.parseInt(offset, 10) || 0);
  const filters = [];
  const params = [];

  if (search) {
    filters.push('e.title LIKE ?');
    params.push(`%${search}%`);
  }

  if (category_id) {
    filters.push('e.category_id = ?');
    params.push(Number(category_id));
  }

  if (date_from) {
    filters.push('DATE(e.date) >= ?');
    params.push(date_from);
  }

  if (date_to) {
    filters.push('DATE(e.date) <= ?');
    params.push(date_to);
  }

  const where = filters.length ? `WHERE ${filters.join(' AND ')}` : '';
  const [rows] = await pool.execute(
    `SELECT e.*, c.name AS category_name FROM events e JOIN categories c ON c.id = e.category_id ${where} ORDER BY e.date ASC LIMIT ${safeLimit} OFFSET ${safeOffset}`,
    params,
  );

  const [countRows] = await pool.execute(`SELECT COUNT(*) AS total FROM events e ${where}`, params);
  return { rows, total: countRows[0].total };
}

async function findById(id) {
  const [rows] = await pool.execute(
    `SELECT e.*, c.name AS category_name FROM events e JOIN categories c ON c.id = e.category_id WHERE e.id = ? LIMIT 1`,
    [id],
  );
  return rows[0] || null;
}

async function createEvent(data) {
  const [result] = await pool.execute(
    `INSERT INTO events (category_id, title, description, image_path, date, location, price, quota, available_tickets, created_by)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
    [data.category_id, data.title, data.description, data.image_path, normalizeDate(data.date), data.location, data.price, data.quota, data.quota, data.created_by],
  );
  return findById(result.insertId);
}

async function updateEvent(id, data) {
  const current = await findById(id);
  if (!current) return null;

  const nextQuota = data.quota ? Number(data.quota) : current.quota;
  const soldTickets = Number(current.quota) - Number(current.available_tickets);
  const nextAvailable = Math.max(nextQuota - soldTickets, 0);

  await pool.execute(
    `UPDATE events SET category_id = ?, title = ?, description = ?, image_path = ?, date = ?, location = ?, price = ?, quota = ?, available_tickets = ? WHERE id = ?`,
    [
      data.category_id ?? current.category_id,
      data.title ?? current.title,
      data.description ?? current.description,
      data.image_path ?? current.image_path,
      normalizeDate(data.date ?? current.date),
      data.location ?? current.location,
      data.price ?? current.price,
      nextQuota,
      nextAvailable,
      id,
    ],
  );

  return findById(id);
}

async function deleteEvent(id) {
  const current = await findById(id);
  if (!current) return null;
  await pool.execute('DELETE FROM events WHERE id = ?', [id]);
  return current;
}

async function reduceQuota(id, quantity) {
  await pool.execute('UPDATE events SET available_tickets = available_tickets - ? WHERE id = ? AND available_tickets >= ?', [quantity, id, quantity]);
  return findById(id);
}

module.exports = { findAll, findById, createEvent, updateEvent, deleteEvent, reduceQuota };
