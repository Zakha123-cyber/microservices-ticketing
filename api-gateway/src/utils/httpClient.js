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

function forwardRequest(target) {
  return createProxyMiddleware({
    target,
    changeOrigin: true,
    on: { proxyReq: fixRequestBody },
  });
}

module.exports = { forwardRequest };
