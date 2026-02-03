# Instrucciones de Deploy a Producción

## Configuración de CORS

### Variables de Entorno Requeridas

En el servidor de producción, asegúrate de que el archivo `.env` en `/app/.env` contenga:

```bash
# Ambiente de producción
APP_ENV=production
APP_DEBUG=false

# Orígenes permitidos para CORS (separados por comas)
ALLOWED_ORIGINS=https://logisteia.es,https://www.logisteia.es,https://api.logisteia.es

# Base de datos
DB_HOST=localhost
DB_NAME=logisteia
DB_USER=tu_usuario
DB_PASS=tu_password
DB_CHARSET=utf8mb4

# JWT
JWT_SECRET=TU_SECRET_KEY_SEGURA_AQUI
JWT_EXPIRATION=3600
```

### Verificar Configuración CORS

Después de hacer push a producción:

1. SSH al servidor:
```bash
ssh usuario@logisteia.es
```

2. Verificar que el `.env` existe en el contenedor de backend:
```bash
docker exec backend cat /app/.env | grep ALLOWED_ORIGINS
```

Debe mostrar:
```
ALLOWED_ORIGINS=https://logisteia.es,https://www.logisteia.es,https://api.logisteia.es
```

3. Si el `.env` no existe, crearlo:
```bash
docker exec -it backend sh
cd /app
cat > .env << 'EOF'
APP_ENV=production
APP_DEBUG=false
ALLOWED_ORIGINS=https://logisteia.es,https://www.logisteia.es,https://api.logisteia.es
DB_HOST=localhost
DB_NAME=logisteia
DB_USER=tu_usuario
DB_PASS=tu_password
DB_CHARSET=utf8mb4
JWT_SECRET=TU_SECRET_KEY_SEGURA_AQUI
JWT_EXPIRATION=3600
EOF
exit
```

4. Reiniciar el contenedor de backend para que cargue las nuevas variables:
```bash
docker restart backend
```

### Verificar que CORS funciona

Desde la consola del navegador en https://logisteia.es, ejecuta:

```javascript
fetch('https://api.logisteia.es/api/enviar-presupuesto-email.php', {
  method: 'OPTIONS',
  headers: {
    'Origin': 'https://logisteia.es',
    'Access-Control-Request-Method': 'POST',
    'Access-Control-Request-Headers': 'Content-Type'
  }
})
.then(r => console.log('Status:', r.status, 'Headers:', [...r.headers.entries()]))
.catch(e => console.error('Error:', e));
```

Debe devolver status 204 con headers:
- `access-control-allow-origin: https://logisteia.es`
- `access-control-allow-credentials: true`
- `access-control-allow-methods: GET, POST, PUT, DELETE, OPTIONS, PATCH`

## Correcciones Aplicadas

### 1. Función `setupCors()` en config.php

**Problema anterior**: En producción, si el origen no estaba en la lista permitida, no se enviaba `Access-Control-Allow-Credentials`, causando que el navegador bloqueara la respuesta.

**Solución**: Ahora SIEMPRE se envía `Access-Control-Allow-Credentials: true` en producción, y se garantiza que se envíe un `Access-Control-Allow-Origin` aunque el origen no esté en la lista.

### 2. Función `handlePreflight()` mejorada

**Problema anterior**: Llamaba a `exit()` directamente, lo que causaba problemas con output buffering.

**Solución**: Ahora retorna `true/false` para que el código llamador pueda manejar el exit limpiamente con `ob_end_clean()`.

## Comandos de Deploy

```bash
# Desde tu máquina local
git add .
git commit -m "Fix: Corregir headers CORS para producción"
git push production main

# El webhook automáticamente hará pull en el servidor
# Si necesitas reiniciar manualmente:
ssh usuario@logisteia.es
cd /ruta/a/logisteia
docker-compose restart backend
```
