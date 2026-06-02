const PROXY_CONFIG = {
  "/api/*": {
    target: "http://localhost:8080",
    secure: false,
    changeOrigin: true,
    logLevel: "debug",
    pathRewrite: {
      "^/api": "/api"  // Mantener la ruta /api
    },
    onProxyReq: (proxyReq, req, res) => {
      // Asegurarse de que los headers de autenticación se reenvían
      if (req.headers['authorization']) {
        proxyReq.setHeader('Authorization', req.headers['authorization']);
      }
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
    },
    onProxyRes: (proxyRes, req, res) => {
      // Log de respuestas del proxy
      console.log(`[${new Date().toLocaleTimeString()}] ${req.method} ${req.url} -> ${proxyRes.statusCode}`);
    }
  }
};

module.exports = PROXY_CONFIG;
