const PROXY_CONFIG = {
  "/api/*": {
    target: "http://localhost:8000",
    secure: false,
    changeOrigin: true,
    logLevel: "debug",
    onProxyReq: (proxyReq, req, res) => {
      // Asegurarse de que los headers de autenticación se reenvían
      if (req.headers['x-user-dni']) {
        proxyReq.setHeader('X-User-DNI', req.headers['x-user-dni']);
      }
      if (req.headers['x-user-rol']) {
        proxyReq.setHeader('X-User-Rol', req.headers['x-user-rol']);
      }
      if (req.headers['x-user-nombre']) {
        proxyReq.setHeader('X-User-Nombre', req.headers['x-user-nombre']);
      }
      if (req.headers['x-user-email']) {
        proxyReq.setHeader('X-User-Email', req.headers['x-user-email']);
      }
    }
  }
};

module.exports = PROXY_CONFIG;
