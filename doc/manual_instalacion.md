# Manual de instalación de LOGISTEIA

## Descripción del sistema

LOGISTEIA es una aplicación web de gestión empresarial que incluye:
- Sistema de gestión de equipos de trabajo
- Gestión de proyectos y tareas
- Cronómetro de registro de horas
- Sistema de presupuestos y facturación
- Gestión de clientes
- Panel de administración (moderador)
- Autenticación con Google OAuth
- Sistema de invitaciones por token

## Arquitectura tecnológica

**Frontend:**
- Angular 21.0
- TypeScript 5.9.2
- Tailwind CSS
- RxJS 7.8.0
- AuthInterceptor para JWT automático

**Backend:**
- Spring Boot 4.0.6
- Java 25 LTS
- Spring Security 7.0.5 con JWT (JJWT 0.12.3)
- Spring Data JPA con Hibernate 7.2.12
- Maven 3.9.6 para gestión de dependencias
- Tomcat 11.0.22 (embedded)
- Logs con SLF4J

**Base de datos:**
- MySQL 8.0 con mysql-connector-j 8.4.0
- HikariCP para connection pooling

**Infraestructura:**
- Docker y Docker Compose
- Nginx (Alpine) como proxy reverso + SPA routing
- Certificados SSL con Let's Encrypt (opcional)

---

## Instalación en entorno de desarrollo local

### Requisitos previos

- **Java 25 LTS** instalado
- Maven 3.9.x instalado
- MySQL 8.0 instalado y ejecutándose
- Git instalado
- Node.js 20+ (para desarrollo frontend)
- Editor de código (recomendado: VS Code)

### Pasos de instalación

#### 1. Clonar el repositorio

```bash
git clone https://github.com/Alvaro-am06/logisteia.git
cd logisteia
git checkout main
```

#### 2. Configurar variables de entorno (Desarrollo)

```bash
# Copiar plantilla
cp .env.template .env

# Editar valores locales
nano .env
```

**Valores mínimos para desarrollo**:
```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=Logisteia
DB_USER=logisteia_user
DB_PASS=desarrollo
JWT_SECRET=tu_secret_desarrollo_minimo_32_caracteres
SPRING_PROFILES_ACTIVE=development
CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost
```

Para generar un JWT_SECRET seguro:
```bash
# Linux/Mac
openssl rand -base64 32

# Windows PowerShell
[System.Convert]::ToBase64String([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32))
```

#### 3. Ejecutar con Docker Compose (Recomendado)

```bash
# Validar configuración
docker-compose config

# Construir imágenes
docker-compose build

# Iniciar servicios
docker-compose up -d

# Ver logs
docker-compose logs -f
```

**Servicios disponibles**:
- Frontend: http://localhost
- Backend API: http://localhost/api
- Health Check: http://localhost/api/actuator/health
- phpMyAdmin: http://localhost:8081 (opcional)

#### 4. Ejecución local nativa (Sin Docker)

```bash
# Terminal 1: Iniciar MySQL
mysql -u logisteia_user -p

# Terminal 2: Backend Spring Boot
mvn spring-boot:run -Dspring-boot.run.arguments="--spring.profiles.active=development"

# Terminal 3: Frontend Angular
cd src/frontend
ng serve --proxy-config proxy.conf.json --port 4200
```

**Servicios disponibles**:
- Frontend: http://localhost:4200
- Backend: http://localhost:8080
- API: http://localhost:8080/api

#### 5. Usuarios de prueba

Los siguientes usuarios están precargados en la base de datos:
- **Admin**: `admin@logisteia.es` / `admin123`
- **Usuario**: `usuario@logisteia.es` / `user123`

#### 6. Detener servicios

```bash
# Parar contenedores (preserva datos)
docker-compose down

# Parar y eliminar volúmenes (limpia todo)
docker-compose down -v

# Parar servicio específico
docker-compose stop backend
```

---

## Tests (Desarrollo)

```bash
# Ejecutar tests unitarios
mvn clean test

# Ejecutar dentro del contenedor
docker-compose exec backend mvn clean test

# Con cobertura
mvn clean test jacoco:report
```

### Requisitos previos

- Servidor Ubuntu 20.04+ (Cloud: AWS, Azure, Oracle Cloud, GCP, etc.)
- Dominio configurado apuntando al servidor
- Docker 20.10+ y Docker Compose 2.0+ instalados
- Acceso SSH con clave privada o contraseña
- Certificado SSL (Let's Encrypt o comercial, opcional)

### Arquitectura de despliegue

El proyecto utiliza Git HTTPS como sistema de despliegue:

```
Repositorio local → git push origin main → GitHub
            ↓
        Servidor (manual pull)
            ↓
docker compose up -d --build
```

### Configuración inicial del servidor

#### 1. Conectarse al servidor

```bash
# SSH con contraseña
ssh ubuntu@tu-dominio.com

# O con clave privada
ssh -i proyecto.pem ubuntu@tu-dominio.com
```

#### 2. Instalar dependencias

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
sudo apt install -y docker.io docker-compose git curl wget

# Agregar usuario al grupo docker (sin necesidad de sudo)
sudo usermod -aG docker ubuntu

# Aplicar cambios de grupo
newgrp docker

# Verificar instalación
docker --version
docker-compose --version
git --version
```

#### 3. Clonar repositorio

```bash
# Crear directorio de aplicación
mkdir -p /opt/logisteia
cd /opt/logisteia

# Clonar repositorio (HTTPS)
git clone https://github.com/Alvaro-am06/logisteia.git .

# Cambiar a rama main
git checkout main
```

#### 4. Crear archivo .env

```bash
# Copiar plantilla
cp .env.template .env

# Editar con valores de producción
nano .env
```

**Variables críticas a configurar**:
```env
# Base de datos
DB_HOST=db
DB_PORT=3306
DB_NAME=Logisteia
DB_USER=logisteia_user
DB_PASS=<CONTRASEÑA_SEGURA_MIN_12_CHARS>

# JWT - Generar con: openssl rand -base64 32
JWT_SECRET=<RESULTADO_OPENSSL>
JWT_EXPIRATION_MS=86400000
JWT_REFRESH_EXPIRATION_MS=604800000

# CORS - USAR DOMINIO REAL, NO LOCALHOST
CORS_ALLOWED_ORIGINS=https://tu-dominio.com,https://www.tu-dominio.com

# Producción
SPRING_PROFILES_ACTIVE=production
LOG_LEVEL=WARN
LOG_FILE=/var/log/logisteia/app.log
```

#### 5. Crear directorios de logs

```bash
sudo mkdir -p /var/log/logisteia
sudo chown $USER:$USER /var/log/logisteia
sudo chmod 755 /var/log/logisteia
```

#### 6. Construir e iniciar servicios

```bash
# Validar configuración
docker-compose config

# Construir imágenes
docker-compose build

# Iniciar servicios en background
docker-compose up -d

# Ver estado
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f
```

#### 7. Verificar salud

```bash
# Health check del backend
curl http://localhost/api/actuator/health

# Respuesta esperada:
# {"status":"UP","components":{"db":{"status":"UP"},...}}
```

#### 8. Configurar SSL/HTTPS (Recomendado)

```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-nginx -y

# Obtener certificado (Let's Encrypt)
sudo certbot certonly --standalone \
  -d tu-dominio.com \
  -d www.tu-dominio.com

# Los certificados estarán en:
# /etc/letsencrypt/live/tu-dominio.com/
```

Luego actualizar `compose.yml` y `docker/nginx/nginx.conf` con rutas de certificados.

#### 9. Configurar actualizaciones automáticas

```bash
# Crear script de actualización
cat > ~/update-logisteia.sh << 'EOF'
#!/bin/bash
cd /opt/logisteia
git pull origin main
docker-compose build
docker-compose up -d
EOF

# Dar permisos
chmod +x ~/update-logisteia.sh

# Programar con cron (ej: diarios a las 2 AM)
crontab -e
# Agregar línea:
# 0 2 * * * /home/ubuntu/update-logisteia.sh
```

### Desplegar cambios desde máquina local

```bash
# Hacer commit de cambios
git add .
git commit -m "Descripción de cambios"

# Hacer push a GitHub
git push origin main

# Conectarse al servidor
ssh ubuntu@tu-dominio.com
```

**En el servidor**:

```bash
cd /opt/logisteia

# Actualizar código
git pull origin main

# Reconstruir imágenes
docker-compose build

# Aplicar cambios
docker-compose up -d

# Ver logs
docker-compose logs -f backend
```

### Rollback (Revertir cambios)

```bash
cd /opt/logisteia

# Ver historial de commits
git log --oneline -10

# Revertir a commit anterior
git reset --hard <COMMIT_HASH>

# Reaplicar cambios
docker-compose build
docker-compose up -d
```

### Configuración de SSL/HTTPS

La configuración SSL ya está lista en `docker/nginx/nginx.conf`. Solo necesitas:

1. Obtener certificados con Let's Encrypt (ver paso 8 de configuración inicial)
2. Montar los certificados en `compose.yml`:
   ```yaml
   volumes:
     - /etc/letsencrypt/live/tu-dominio.com/fullchain.pem:/etc/nginx/certs/cert.pem:ro
     - /etc/letsencrypt/live/tu-dominio.com/privkey.pem:/etc/nginx/certs/key.pem:ro
   ```
3. Descomentar el bloque HTTPS en `docker/nginx/nginx.conf`
4. Reiniciar: `docker-compose restart web`

---

## Monitoreo y Mantenimiento

---

## Gestión de dependencias

### Backend (PHP)

Las dependencias se gestionan con Composer (`composer.json`):

```json
{
    "require": {
        "phpmailer/phpmailer": "^7.0",
        "vlucas/phpdotenv": "^5.6",
        "firebase/php-jwt": "^7.0"
    }
}
```

Instalar/actualizar:
```bash
composer install
composer update
```

### Frontend (Angular)

Las dependencias se gestionan con npm (`src/frontend/package.json`):

- Angular 21.0
- RxJS 7.8
- TypeScript
- Tailwind CSS

Instalar/actualizar:
```bash
cd src/frontend
npm install
npm update
```

---

## Comandos útiles

### Docker

```bash
# Ver logs de todos los contenedores
docker compose logs -f

# Ver logs de un contenedor específico
docker compose logs -f backend

# Reiniciar un contenedor
docker compose restart backend

# Reconstruir imágenes
docker compose up -d --build

# Detener todos los contenedores
docker compose down

# Ejecutar comando en un contenedor
docker compose exec backend bash
```

### Base de datos

```bash
# Acceder a MySQL desde el contenedor
docker compose exec db mysql -u root -p Logisteia

# Importar un script SQL
docker compose exec -T db mysql -u root -p${DB_PASS} Logisteia < src/sql/script.sql

# Backup de base de datos
docker compose exec db mysqldump -u root -p${DB_PASS} Logisteia > backup.sql
```

### Git

```bash
# Ver remotes configurados
git remote -v

# Ver estado de los cambios
git status

# Desplegar a producción
git push production main

# Ver logs del servidor
ssh -i proyecto.pem ubuntu@logisteia.com "docker compose -f ~/logisteia/compose.yml logs -f"
```

---

## Resolución de problemas

### Error de login con Google OAuth

Verificar que JWT_SECRET tenga 64 caracteres hexadecimales. Ejecutar en el servidor:

```bash
bash update-env-production.sh
```

### Error "El jefe de equipo no tiene un equipo asignado"

Verificar que la tabla `usuarios` tenga las columnas: `estado`, `fecha_baneo`, `motivo_baneo`. Si faltan, ejecutar:

```bash
docker compose exec -T db mysql -u root -p${DB_PASS} Logisteia < src/sql/migracion_estado_usuarios.sql
```

### Los cambios no se reflejan en producción

1. Verificar que el push se haya completado correctamente
2. Revisar logs del hook post-receive en el servidor
3. Reconstruir las imágenes manualmente:

```bash
ssh -i proyecto.pem ubuntu@logisteia.com
cd ~/logisteia
docker compose down
docker compose up -d --build
```

### Error de permisos en archivos

Ajustar permisos en el servidor:

```bash
sudo chown -R ubuntu:ubuntu ~/logisteia
chmod -R 755 ~/logisteia
```

### Problemas con certificados SSL

Verificar que el dominio apunte correctamente al servidor. Ver logs de Caddy:

```bash
docker compose logs -f web
```

---

## Seguridad

### Recomendaciones para producción

1. Cambiar todas las contraseñas por defecto
2. Usar JWT_SECRET de 64 caracteres generado aleatoriamente
3. Configurar firewall en el servidor (UFW)
4. Mantener Docker y dependencias actualizadas
5. Hacer backups periódicos de la base de datos
6. Usar contraseñas de aplicación para SMTP
7. Revisar logs regularmente
8. No exponer phpMyAdmin en producción o protegerlo con autenticación adicional

### Archivos sensibles excluidos de Git

El archivo `.gitignore` excluye:
- `.env` (variables de entorno)
- `*.pem` (claves SSH)
- `vendor/` (dependencias PHP)
- `node_modules/` (dependencias Node)
- `logs/` (archivos de log)

---

## Soporte y documentación adicional

- **Manual de usuario:** `doc/manual_usuario.md`
- **Manual de programador:** `doc/manual_programador.md`
- **Arquitectura tecnológica:** `doc/analisis/arquitectura_tecnologica.md`
- **Diccionario de datos:** `doc/analisis/diccionario_de_datos.md`
- **README producción SQL:** `src/sql/README_PRODUCCION.md`

---

**Autores:**
- Álvaro Andrades Márquez
- Fernando José Leva Rosa

