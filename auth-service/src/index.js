require('dotenv').config();

const express = require('express');
const cors = require('cors');
const authRoutes = require('./routes/auth');
const errorHandler = require('./middleware/errorHandler');

const app = express();
const port = process.env.PORT || 8001;

app.use(cors({ origin: process.env.CORS_ORIGIN || '*' }));
app.use(express.json());
app.use('/api/auth', authRoutes);
app.use(errorHandler);

app.listen(port, () => {
  console.log(`Auth Service running on port ${port}`);
});
