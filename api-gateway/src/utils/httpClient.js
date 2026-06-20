const { createProxyMiddleware } = require('http-proxy-middleware');

function fixRequestBody(proxyReq, req) {
  if (!req.body || !Object.keys(req.body).length || req.headers['content-type']?.includes('multipart/form-data')) {
    return;
  }

  const bodyData = JSON.stringify(req.body);
  proxyReq.setHeader('Content-Type', 'application/json');
  proxyReq.setHeader('Content-Length', Buffer.byteLength(bodyData));
  proxyReq.write(bodyData);
}

function forwardRequest(target, servicePrefix) {
  return createProxyMiddleware({
    target,
    changeOrigin: true,
    pathRewrite: (path, req) => `${servicePrefix}${req.url}`,
    timeout: 15000,
    proxyTimeout: 15000,
    on: { proxyReq: fixRequestBody },
    onError(error, req, res) {
      res.status(502).json({
        success: false,
        message: `Failed to proxy request to ${target}`,
        error: error.message,
      });
    },
  });
}

module.exports = { forwardRequest };
