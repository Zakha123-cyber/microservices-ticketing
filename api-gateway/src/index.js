require('dotenv').config();

const express = require('express');
const cors = require('cors');
const rateLimit = require('express-rate-limit');
const routes = require('./routes');
const jwtValidation = require('./middleware/jwtValidation');
const logger = require('./middleware/logger');
const errorHandler = require('./middleware/errorHandler');

const app = express();
const port = process.env.PORT || 8000;

app.use(cors({ origin: process.env.CORS_ORIGIN || '*' }));
app.use(express.json({ type: ['application/json', 'application/*+json'] }));
app.use(rateLimit({ windowMs: 60 * 1000, limit: 120 }));
app.use(logger);
app.use(jwtValidation);
app.use('/api', routes);
app.use(errorHandler);

app.listen(port, () => {
  console.log(`API Gateway running on port ${port}`);
});
