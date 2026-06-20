function imageUrl(imagePath) {
  if (!imagePath) return null;
  return `${process.env.BASE_URL || 'http://localhost:8002'}/uploads/events/${imagePath}`;
}

module.exports = { imageUrl };
