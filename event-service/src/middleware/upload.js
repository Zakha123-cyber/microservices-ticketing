const upload = require('../config/multer');

module.exports = upload.single('image');
