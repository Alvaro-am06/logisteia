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
- TypeScript
- Tailwind CSS
- RxJS

**Backend:**
- PHP 8.2 con PHP-FPM
- Composer para gestión de dependencias
- JWT para autenticación
- PHPMailer para envío de correos
- Firebase PHP-JWT para tokens
- Google API Client para OAuth

**Base de datos:**
- MySQL 8.0

**Infraestructura:**
- Docker y Docker Compose
- Caddy como servidor web y proxy reverso
- Certificados SSL automáticos con Let's Encrypt

---

## Instalación en entorno de desarrollo local

### Requisitos previos

- Docker Desktop instalado y en ejecución
- Git instalado
- Editor de código (recomendado: VS Code)
- Node.js 20+ (para desarrollo frontend)

### Pasos de instalación

#### 1. Clonar el repositorio

```bash
git clone git@github.com:Alvaro-am06/logisteia.git
cd logisteia
```

#### 2. Configurar variables de entorno

Crear archivo `.env` en la raíz del proyecto:

```env
# Base de datos
DB_HOST=db
DB_NAME=Logisteia
DB_USER=root
DB_PASS=tu_contraseña_segura

# JWT
JWT_SECRET=genera_una_clave_aleatoria_de_64_caracteres
JWT_EXPIRATION=3600

# Google OAuth
GOOGLE_CLIENT_ID=tu_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=tu_google_client_secret

# Email (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_FROM_ADDRESS=tu_email@gmail.com
MAIL_FROM_NAME=LOGISTEIA

# Aplicación
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost
```

**Nota:** Para generar un JWT_SECRET seguro, ejecuta:
```bash
php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
```

#### 3. Instalar dependencias PHP

```bash
composer install
```

#### 4. Inicializar la base de datos

El script `src/sql/produccion_optimizada.sql` se ejecuta automáticamente al iniciar Docker por primera vez. Incluye:

- Todas las tablas necesarias (17 tablas)
- Usuarios de prueba:
  - Moderador: `admin@logisteia.com` / `1234`
  - Jefe de equipo: `jefe@logisteia.com` / `1234`
  - Trabajador: `trabajador@logisteia.com` / `1234`
- Servicios informáticos predefinidos
- Estructura completa de equipos, proyectos, tareas, etc.

#### 5. Levantar los contenedores Docker

```bash
docker compose up -d
```

Esto iniciará 4 contenedores:
- `logisteia_db`: MySQL 8.0 (puerto interno 3306)
- `logisteia_backend`: PHP 8.2-FPM
- `logisteia_web`: Caddy + Angular compilado (puerto 80/443)
- `logisteia_pma`: phpMyAdmin (accesible a través de Caddy)

#### 6. Verificar los servicios

- **Frontend Angular:** http://localhost
- **API Backend:** http://localhost/api/login.php
- **phpMyAdmin:** http://localhost:8080 (si está configurado en compose.yml)

#### 7. Desarrollo del frontend

Para desarrollar en el frontend con hot-reload:

```bash
cd src/frontend
npm install
npm start
```

Esto iniciará el servidor de desarrollo en `http://localhost:4200` con proxy a la API backend.

---

## Instalación en producción (AWS)

### Requisitos previos

- Servidor Ubuntu en AWS EC2
- Dominio configurado apuntando al servidor
- Docker y Docker Compose instalados en el servidor
- Acceso SSH con clave privada (.pem)

### Arquitectura de despliegue

El proyecto utiliza Git como sistema de despliegue:

```
Repositorio local → git push production main → Servidor AWS
```

### Configuración inicial del servidor

#### 1. Conectarse al servidor

```bash
ssh -i proyecto.pem ubuntu@logisteia.com
```

#### 2. Instalar dependencias

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Docker
sudo apt install docker.io docker-compose -y

# Agregar usuario al grupo docker
sudo usermod -aG docker ubuntu
newgrp docker

# Instalar Git
sudo apt install git -y
```

#### 3. Configurar repositorio Git en el servidor

```bash
# Crear directorio para el repositorio bare
mkdir -p ~/logisteia.git
cd ~/logisteia.git
git init --bare

# Crear directorio para el código
mkdir -p ~/logisteia
```

#### 4. Crear hook post-receive

Crear el archivo `~/logisteia.git/hooks/post-receive`:

```bash
#!/bin/bash
GIT_WORK_TREE=/home/ubuntu/logisteia git checkout -f main
cd /home/ubuntu/logisteia
docker compose down
docker compose up -d --build
```

Dar permisos de ejecución:

```bash
chmod +x ~/logisteia.git/hooks/post-receive
```

### Configuración del repositorio local

#### 1. Agregar remote de producción

```bash
git remote add production ssh://ubuntu@logisteia.com/home/ubuntu/logisteia.git
```

#### 2. Configurar archivo .env en el servidor

Conectarse al servidor y crear `/home/ubuntu/logisteia/.env`:

```env
DB_HOST=db
DB_NAME=Logisteia
DB_USER=root
DB_PASS=contraseña_produccion_segura

JWT_SECRET=clave_64_caracteres_hexadecimales_produccion
JWT_EXPIRATION=3600

GOOGLE_CLIENT_ID=client_id_produccion.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=secret_produccion

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=noreply@logisteia.com
MAIL_PASSWORD=app_password_produccion
MAIL_FROM_ADDRESS=noreply@logisteia.com
MAIL_FROM_NAME=LOGISTEIA

APP_ENV=production
APP_DEBUG=false
APP_URL=https://logisteia.com
```

#### 3. Actualizar JWT_SECRET en producción

Si necesitas actualizar el JWT_SECRET después del despliegue:

```bash
# En tu máquina local, hacer push del script
git push production main

# Conectarse al servidor
ssh -i proyecto.pem ubuntu@logisteia.com

# Ejecutar el script de actualización
cd ~/logisteia
bash update-env-production.sh
```

### Desplegar cambios

Desde tu máquina local:

```bash
# Hacer commit de los cambios
git add .
git commit -m "Descripción de cambios"

# Desplegar a producción
git push production main
```

El hook post-receive automáticamente:
1. Actualiza el código en el servidor
2. Reconstruye las imágenes Docker
3. Reinicia los contenedores

### Configuración de Caddy y SSL

El archivo `docker/caddy/Caddyfile` configura automáticamente:

- **logisteia.com / www.logisteia.com:** Frontend Angular + Backend PHP
- **api.logisteia.com:** API Backend
- **pma.logisteia.com:** phpMyAdmin (opcional)
- Certificados SSL automáticos de Let's Encrypt
- Redirección HTTP a HTTPS
- Compresión gzip

Los certificados se guardan en el volumen `caddy_data` y se renuevan automáticamente.

---

## Estructura de la base de datos

El script `src/sql/produccion_optimizada.sql` crea 17 tablas:

1. **usuarios** - Gestión de usuarios (jefe_equipo, trabajador, moderador)
2. **equipos** - Equipos de trabajo
3. **miembros_equipo** - Relación usuarios-equipos con invitaciones
4. **clientes** - Base de clientes por jefe de equipo
5. **proyectos** - Gestión de proyectos
6. **tareas** - Tareas dentro de proyectos
7. **asignaciones_proyecto** - Asignación de trabajadores a proyectos
8. **registro_horas** - Cronómetro de horas trabajadas
9. **presupuestos** - Presupuestos generados
10. **detalle_presupuesto** - Líneas de cada presupuesto
11. **servicios** - Catálogo de servicios generales
12. **servicios_informatica** - Catálogo de servicios IT
13. **facturas** - Facturación
14. **pagos** - Registro de pagos
15. **acciones_administrativas** - Auditoría de acciones
16. **historial_baneos** - Control de baneos de usuarios
17. **invitaciones** - Sistema de invitaciones por email

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

