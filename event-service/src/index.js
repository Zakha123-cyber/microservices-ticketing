require('dotenv').config();

const path = require('path');
const express = require('express');
const cors = require('cors');
const eventRoutes = require('./routes/events');
const errorHandler = require('./middleware/errorHandler');

const app = express();
const port = process.env.PORT || 8002;

app.use(cors({ origin: process.env.CORS_ORIGIN || '*' }));
app.use(express.json());
app.use('/uploads/events', express.static(path.resolve(process.env.UPLOAD_PATH || './storage/uploads/events')));
app.use('/api/events', eventRoutes);
app.use(errorHandler);

app.listen(port, () => {
  console.log(`Event Service running on port ${port}`);
});
