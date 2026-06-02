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
- Docker y Docker Compose (opcional)
- Nginx o Caddy como proxy reverso
- Certificados SSL automáticos con Let's Encrypt

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
git clone git@github.com:Alvaro-am06/logisteia.git
cd logisteia
```

#### 3. Configurar variables de entorno

Crear archivo `.env` en la raíz del proyecto:

```env
# Base de datos
DB_HOST=localhost
DB_PORT=3306
DB_NAME=Logisteia
DB_USER=root
DB_PASS=tu_contraseña_segura

# JWT
JWT_SECRET=genera_una_clave_aleatoria_de_64_caracteres
JWT_EXPIRATION=3600000

# Aplicación
SERVER_PORT=8080
SPRING_PROFILES_ACTIVE=development
SPRING_JPA_HIBERNATE_DDL_AUTO=create-drop

# CORS
CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost:3000,http://localhost
```

**Nota:** Para generar un JWT_SECRET seguro, ejecuta:
```bash
# Linux/Mac
openssl rand -hex 32

# Windows PowerShell
[System.BitConverter]::ToString([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32)) -replace '-'
```

#### 4. Compilar el Backend

```bash
# Descargar dependencias y compilar
mvn clean install

# O simplemente compilar sin tests
mvn clean compile -DskipTests
```

#### 5. Instalar dependencias del Frontend

```bash
cd src/frontend
npm install
```

#### 6. Ejecutar la aplicación localmente

**Opción A: Terminal local**

```bash
# Terminal 1: Backend Spring Boot
mvn spring-boot:run

# Terminal 2: Frontend Angular
cd src/frontend
ng serve --proxy-config proxy.conf.js
```

La aplicación estará disponible en:
- Frontend: http://localhost:4200
- Backend: http://localhost:8080
- API Docs: http://localhost:8080/actuator/health

**Opción B: Con Docker Compose**

```bash
# Construir y levantar contenedores
docker compose up -d --build

# Ver logs
docker compose logs -f
```

#### 7. Inicializar la base de datos

La primera vez que arranca, Spring Boot ejecuta automáticamente las migraciones de Liquibase (o Hibernate con `ddl-auto=create-drop`). 

Usuarios de prueba predefinidos:
- Email: `admin@logisteia.es` / Contraseña: `admin123`
- Email: `usuario@logisteia.es` / Contraseña: `user123`

#### 8. Tests

```bash
# Ejecutar tests unitarios (64 tests)
mvn clean test

# Ejecutar con cobertura
mvn clean test jacoco:report
```

La salida esperada: **BUILD SUCCESS - 64/64 tests passed**

#### 9. Build para producción

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

