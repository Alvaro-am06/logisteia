% DEPLOYMENT GUIDE - Guía de Despliegue en Producción

# 🚀 LOGISTEIA - Guía de Despliegue en Producción

## Descripción General

Este documento proporciona instrucciones paso a paso para desplegar la aplicación Logisteia (Spring Boot 4.0.6 + Angular 21) en producción usando Docker Compose.

---

## ✅ Pre-requisitos

### Hardware Recomendado
- **CPU:** Mínimo 2 cores, recomendado 4+
- **RAM:** Mínimo 4GB, recomendado 8GB+
- **Almacenamiento:** 20GB mínimo (para aplicación + datos)

### Software Requerido
- Docker 20.10+
- Docker Compose 2.0+
- Git (para clonar repositorio)
- Certificado SSL (opcional, para HTTPS)

### Verificar Instalación

```bash
docker --version        # Docker version 20.10+
docker-compose version  # Docker Compose version 2.0+
git --version          # Git version 2.0+
```

---

## 📋 Paso 1: Obtener el Código

```bash
# Clonar repositorio
git clone https://github.com/Alvaro-am06/logisteia.git
cd logisteia

# Cambiar a rama main
git checkout main
git pull origin main
```

---

## 🔧 Paso 2: Configurar Variables de Entorno

### 2.1 Crear archivo .env

```bash
# Copiar plantilla
cp .env.template .env

# Editar con valores reales
nano .env  # o tu editor favorito
```

### 2.2 Generar JWT_SECRET Seguro

```bash
# Generar secret aleatorio de 32 bytes en base64
openssl rand -base64 32
```

**Guardar el output en `JWT_SECRET=...`**

### 2.3 Variables Críticas a Configurar

```env
# Base de datos
DB_HOST=db
DB_PORT=3306
DB_NAME=Logisteia
DB_USER=logisteia_user
DB_PASS=<CONTRASEÑA_FUERTE_AQUÍ>  # Mínimo 12 caracteres

# Seguridad JWT
JWT_SECRET=<SALIDA_OPENSSL_AQUÍ>
JWT_EXPIRATION_MS=86400000  # 24 horas
JWT_REFRESH_EXPIRATION_MS=604800000  # 7 días

# CORS (IMPORTANTE: Usar HTTPS en producción)
CORS_ALLOWED_ORIGINS=https://tu-dominio.com,https://www.tu-dominio.com

# Logging
LOG_LEVEL=WARN
LOG_FILE=/var/log/logisteia/app.log

# Spring Profiles
SPRING_PROFILES_ACTIVE=production
```

### 2.4 Validar Configuración

```bash
# Ver qué variables están seteadas
grep -E "^[A-Z_]+=" .env | sort

# Ver valores críticos (sin mostrar contraseñas)
grep "JWT_SECRET\|DB_USER\|CORS_ALLOWED_ORIGINS" .env
```

---

## 🐳 Paso 3: Construir Imágenes Docker

```bash
# Validar configuración YAML
docker-compose config

# Construir todas las imágenes
docker-compose build

# Opcional: Construir con output detallado
docker-compose build --no-cache
```

**Esto puede tardar 5-10 minutos en primera ejecución**

---

## ▶️ Paso 4: Iniciar Servicios

```bash
# Iniciar todos los servicios en background
docker-compose up -d

# Ver logs en tiempo real
docker-compose logs -f
```

### Servicios Disponibles Después del Inicio

| Servicio | URL | Puerto | Descripción |
|----------|-----|--------|-------------|
| Frontend | http://localhost | 80 | Aplicación Angular |
| API Backend | http://localhost/api | - | Spring Boot API |
| Health Check | http://localhost/api/actuator/health | - | Estado del backend |
| phpMyAdmin | http://localhost:8081 | 8081 | Gestor de BD (opcional) |

---

## 🏥 Paso 5: Verificar Salud del Deployment

### 5.1 Ver Estado de Servicios

```bash
# Ver contenedores en ejecución
docker-compose ps

# Salida esperada:
# NAME                COMMAND             STATUS              PORTS
# logisteia_backend   "java -jar..."      Up (healthy)        8080/tcp
# logisteia_web       "nginx -g..."       Up (healthy)        0.0.0.0:80->80/tcp
# logisteia_db        "docker-entry..."   Up (healthy)        3306/tcp
```

### 5.2 Verificar Health Checks

```bash
# Verificar salud del backend
curl http://localhost/api/actuator/health

# Salida esperada:
# {"status":"UP","components":{"db":{"status":"UP"},...}}
```

### 5.3 Ver Logs

```bash
# Logs del backend
docker-compose logs backend | tail -50

# Logs del frontend
docker-compose logs web | tail -50

# Logs de la BD
docker-compose logs db | tail -50

# Monitorear logs en tiempo real
docker-compose logs -f
```

---

## 🧪 Paso 6: Pruebas Básicas de Funcionamiento

### 6.1 Probar API de Autenticación

```bash
# Login (cambiar credenciales según BD)
curl -X POST http://localhost/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"password"}'

# Respuesta esperada:
# {"token":"eyJhbGc...","refreshToken":"eyJhbGc..."}
```

### 6.2 Probar Endpoint Protegido

```bash
# Usar token del paso anterior
curl -H "Authorization: Bearer <TOKEN>" \
  http://localhost/api/usuarios/me

# Respuesta esperada:
# {"id":1,"username":"admin",...}
```

### 6.3 Probar Frontend

```bash
# Acceder a la aplicación Angular
open http://localhost

# O desde línea de comandos
curl -s http://localhost | head -20
```

---

## 🔒 Paso 7: Configuración SSL/HTTPS (Opcional pero Recomendado)

### 7.1 Obtener Certificado SSL

**Opción A: Let's Encrypt (Gratuito)**

```bash
# Instalar Certbot
apt-get install certbot

# Obtener certificado
certbot certonly --standalone \
  -d tu-dominio.com \
  -d www.tu-dominio.com
```

**Opción B: Certificado Comercial**
- Comprar a proveedor (Comodo, DigiCert, etc.)
- Descargar archivos: certificate.crt, private.key

### 7.2 Montar Certificados en Docker

Actualizar `compose.yml`:

```yaml
  web:
    # ... resto de configuración
    volumes:
      - /etc/letsencrypt/live/tu-dominio.com/fullchain.pem:/etc/nginx/certs/cert.pem:ro
      - /etc/letsencrypt/live/tu-dominio.com/privkey.pem:/etc/nginx/certs/key.pem:ro
```

### 7.3 Habilitar HTTPS en nginx.conf

En `docker/nginx/nginx.conf`:

```nginx
# Descomenta y actualiza:
server {
    listen 443 ssl http2;
    server_name tu-dominio.com www.tu-dominio.com;
    
    ssl_certificate /etc/nginx/certs/cert.pem;
    ssl_certificate_key /etc/nginx/certs/key.pem;
    
    # ... resto de configuración
}
```

### 7.4 Reiniciar Servicios

```bash
docker-compose down
docker-compose up -d
```

---

## 📊 Monitoreo en Producción

### Métricas Disponibles

```bash
# Ver métricas del backend
curl http://localhost/api/actuator/metrics

# Métrica específica (ej: JVM memory)
curl http://localhost/api/actuator/metrics/jvm.memory.used
```

### Logs Persistentes

Los logs se guardan en `/var/log/logisteia/app.log` (configurable via `LOG_FILE`).

```bash
# Ver logs dentro del contenedor
docker exec logisteia_backend tail -f /var/log/logisteia/app.log
```

### Backups de BD

```bash
# Backup manual
docker-compose exec db mysqldump -u logisteia_user -p Logisteia > backup.sql

# Restaurar desde backup
docker-compose exec -T db mysql -u logisteia_user -p Logisteia < backup.sql
```

---

## 🛑 Parar/Reiniciar Servicios

```bash
# Parar todos los servicios (preserva datos)
docker-compose down

# Parar servicios específicos
docker-compose stop backend
docker-compose stop web

# Reiniciar servicio
docker-compose restart backend

# Reiniciar desde cero (limpia datos)
docker-compose down -v
```

---

## ⚠️ Troubleshooting

### Error: "Cannot connect to database"

```bash
# Verificar que BD está disponible
docker-compose logs db | grep "ready for connections"

# Reiniciar BD
docker-compose restart db

# Esperar 10 segundos y reintentar backend
sleep 10
docker-compose restart backend
```

### Error: "JWT_SECRET not configured"

```bash
# Verificar que está en .env
grep JWT_SECRET .env

# Si no existe, generarlo:
echo "JWT_SECRET=$(openssl rand -base64 32)" >> .env

# Reiniciar backend
docker-compose restart backend
```

### Error: "Port 80 already in use"

```bash
# Encontrar qué está usando puerto 80
netstat -tlnp | grep :80

# Cambiar puerto en compose.yml:
# ports:
#   - "8000:80"  # En lugar de "80:80"
```

### Error: "CORS error" en frontend

```bash
# Verificar CORS_ALLOWED_ORIGINS en .env
grep CORS_ALLOWED_ORIGINS .env

# Debe contener el dominio del frontend
# Ejemplo: CORS_ALLOWED_ORIGINS=https://tu-dominio.com
```

---

## 📝 Checklista de Deployment

- [ ] Git clone completado
- [ ] .env configurado con valores reales
- [ ] JWT_SECRET generado con openssl
- [ ] DB_PASS configurada (mínimo 12 caracteres)
- [ ] CORS_ALLOWED_ORIGINS con dominio real
- [ ] `docker-compose config` sin errores
- [ ] `docker-compose build` exitoso
- [ ] `docker-compose up -d` iniciado
- [ ] Todos los servicios en estado "healthy"
- [ ] Health check responde OK
- [ ] Login API funciona
- [ ] Frontend accesible en http://localhost
- [ ] Logs monitoreados sin errores críticos
- [ ] SSL/HTTPS configurado (si aplica)

---

## 📞 Soporte

Para problemas o preguntas:

1. Revisar logs: `docker-compose logs -f`
2. Verificar .env: `grep -E "^[A-Z_]+=" .env`
3. Revisar documentación Spring Boot oficial
4. Crear issue en GitHub con logs y configuración (sin exponer secretos)

---

## Versiones de Referencia

- **Java:** 25 LTS
- **Spring Boot:** 4.0.6
- **Angular:** 21
- **MySQL:** 8.0
- **Nginx:** latest (Alpine)
- **Docker Compose:** 2.0+

---

**Última actualización:** 3 de junio de 2026  
**Versión del documento:** 1.0
