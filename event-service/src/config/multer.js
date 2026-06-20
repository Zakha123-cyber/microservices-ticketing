const path = require('path');
const multer = require('multer');

const uploadPath = process.env.UPLOAD_PATH || './storage/uploads/events';
const allowed = (process.env.ALLOWED_FILE_TYPES || 'image/jpeg,image/jpg,image/png').split(',');

const storage = multer.diskStorage({
  destination: uploadPath,
  filename: (req, file, callback) => {
    const ext = path.extname(file.originalname).toLowerCase();
    callback(null, `${Date.now()}-${Math.round(Math.random() * 1e9)}${ext}`);
  },
});

const upload = multer({
  storage,
  limits: { fileSize: Number(process.env.MAX_FILE_SIZE || 5242880) },
  fileFilter: (req, file, callback) => {
    if (!allowed.includes(file.mimetype)) return callback(new Error('Invalid file type'));
    callback(null, true);
  },
});

module.exports = upload;
