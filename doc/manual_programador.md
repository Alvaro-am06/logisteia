# Manual del Programador de LOGISTEIA

## Introducción

Este manual está dirigido a desarrolladores que deseen entender, mantener o ampliar el proyecto LOGISTEIA. El sistema es una aplicación web full-stack moderna que combina un frontend Angular con un backend PHP, desplegada en contenedores Docker con arquitectura de microservicios.

---

## Stack tecnológico

### Frontend
- **Framework**: Angular 21.0 con TypeScript 5.9.2
- **Estilos**: Tailwind CSS 3.x
- **Gestión de estado**: RxJS 7.8.0 con Observables
- **HTTP Client**: Angular HttpClient con AuthInterceptor
- **Autenticación**: JWT Bearer tokens en localStorage
- **Compilación**: Angular CLI con Vite/esbuild

### Backend
- **Lenguaje**: Java 25 LTS
- **Framework**: Spring Boot 4.0.6
- **Base de datos**: MySQL 8.0 con mysql-connector-j 8.4.0
- **ORM**: Spring Data JPA con Hibernate 7.2.12
- **Autenticación**: JWT con JJWT 0.12.3
- **Seguridad**: Spring Security 7.0.5
- **Build Tool**: Maven 3.9.6
- **Servidor embebido**: Tomcat 11.0.22
- **Logging**: SLF4J con Logback
- **Pool de conexiones**: HikariCP
- **Mapeo**: MapStruct para DTOs

### Infraestructura
- **Contenedores**: Docker y Docker Compose (opcional)
- **Servidor web**: Nginx o Caddy (proxy reverso y SSL)
- **SSL**: Let's Encrypt automático
- **Despliegue**: Git push con hooks post-receive
- **Hosting**: Oracle Cloud (recomendado)

---

## Arquitectura del sistema

### Estructura de carpetas completa

```
logisteia/
│
├── src/
│   ├── main/
│   │   ├── java/com/logisteia/backend/
│   │   │   ├── LogisteiaBackendApplication.java     # Punto de entrada Spring Boot
│   │   │   │
│   │   │   ├── controllers/                         # Controladores REST
│   │   │   │   ├── AuthController.java              # POST /api/v1/auth/login
│   │   │   │   ├── UsuarioController.java           # CRUD /api/v1/usuarios
│   │   │   │   ├── ClienteController.java           # CRUD /api/v1/clientes
│   │   │   │   ├── EquipoController.java            # CRUD /api/v1/equipos
│   │   │   │   ├── ProyectoController.java          # CRUD /api/v1/projetos
│   │   │   │   ├── TareaController.java             # CRUD /api/v1/tareas
│   │   │   │   └── ... otros controladores
│   │   │   │
│   │   │   ├── services/                            # Lógica de negocio
│   │   │   │   ├── AuthService.java                 # Login y JWT
│   │   │   │   ├── UsuarioService.java              # CRUD Usuarios
│   │   │   │   ├── ClienteService.java              # CRUD Clientes
│   │   │   │   ├── JwtService.java                  # Generación/Validación JWT
│   │   │   │   └── ... otros servicios
│   │   │   │
│   │   │   ├── repositories/                        # Acceso a datos (Spring Data JPA)
│   │   │   │   ├── UsuarioRepository.java
│   │   │   │   ├── ClienteRepository.java
│   │   │   │   ├── EquipoRepository.java
│   │   │   │   └── ... otros repositorios
│   │   │   │
│   │   │   ├── entities/                            # Entidades JPA
│   │   │   │   ├── Usuario.java
│   │   │   │   ├── Cliente.java
│   │   │   │   ├── Equipo.java
│   │   │   │   ├── Proyecto.java
│   │   │   │   ├── Tarea.java
│   │   │   │   └── ... otras entidades
│   │   │   │
│   │   │   ├── dtos/                                # Data Transfer Objects
│   │   │   │   ├── LoginRequestDTO.java
│   │   │   │   ├── LoginResponseDTO.java
│   │   │   │   ├── UsuarioResponseDTO.java
│   │   │   │   ├── ClienteDTO.java
│   │   │   │   └── ... otros DTOs
│   │   │   │
│   │   │   ├── config/                              # Configuración de Spring
│   │   │   │   ├── SecurityConfig.java              # Spring Security + JWT
│   │   │   │   ├── WebConfig.java                   # CORS y beans globales
│   │   │   │   ├── JwtAuthenticationFilter.java     # Filtro JWT
│   │   │   │   └── GlobalExceptionHandler.java      # Manejo de excepciones
│   │   │   │
│   │   │   ├── enums/                               # Enumeraciones
│   │   │   │   ├── UserRole.java
│   │   │   │   ├── ProjectStatus.java
│   │   │   │   └── TaskStatus.java
│   │   │   │
│   │   │   ├── mappers/                             # Conversión Entity ↔ DTO
│   │   │   │   ├── UsuarioMapper.java
│   │   │   │   ├── ClienteMapper.java
│   │   │   │   └── ... otros mappers
│   │   │   │
│   │   │   └── exceptions/                          # Excepciones personalizadas
│   │   │       ├── ResourceNotFoundException.java
│   │   │       ├── AuthenticationException.java
│   │   │       └── ... otras excepciones
│   │   │
│   │   └── resources/
│   │       ├── application.yml                      # Configuración desarrollo
│   │       ├── application-oracle.yml               # Configuración Oracle Cloud
│   │       └── data.sql                             # Datos iniciales
│   │
│   ├── test/
│   │   └── java/com/logisteia/backend/
│   │       ├── dtos/
│   │       │   ├── LoginRequestDTOTest.java
│   │       │   └── UsuarioResponseDTOTest.java
│   │       ├── entities/
│   │       │   └── UsuarioTest.java
│   │       ├── enums/
│   │       │   ├── UserRoleTest.java
│   │       │   └── ProjectStatusTest.java
│   │       └── mappers/
│   │           └── UsuarioMapperTest.java
│   │
│   └── frontend/                                    # Aplicación Angular
│       ├── src/
│       │   ├── app/
│       │   │   ├── components/                      # Componentes reutilizables
│       │   │   ├── guards/                          # Auth guards
│       │   │   ├── services/                        # Servicios HTTP
│       │   │   │   ├── auth.service.ts
│       │   │   │   ├── usuario.service.ts
│       │   │   │   ├── cliente.service.ts
│       │   │   │   └── ... otros servicios
│       │   │   ├── interceptors/                    # HTTP Interceptors
│       │   │   │   └── auth.interceptor.ts          # Inyecta JWT
│       │   │   └── ... módulos y componentes
│       │   ├── environments/
│       │   │   ├── environment.ts                   # Producción
│       │   │   └── environment.development.ts       # Desarrollo
│       │   ├── proxy.conf.js                        # Proxy de desarrollo
│       │   └── main.ts
│       └── package.json
│
├── sql/                                             # Scripts SQL
│   ├── bbdd.sql                                     # Esquema base de datos
│   ├── datos_iniciales.sql                          # Datos de prueba
│   └── datos_pruebas.sql                            # Datos para testing
│
├── pom.xml                                          # Dependencias Maven
├── compose.yml                                      # Docker Compose
└── README.md
```

---

## Flujo de datos

### Arquitectura de tres capas

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND                         │
│            Angular 21 + TypeScript                  │
│                                                     │
│  Componentes → Services → AuthInterceptor          │
└─────────────┬───────────────────────────────────────┘
              │ HTTP/HTTPS (JSON)
              │ Authorization: Bearer <JWT>
┌─────────────▼───────────────────────────────────────┐
│                NGINX/CADDY (Proxy)                  │
│      /api/v1/* → localhost:8080                    │
│      /* → Angular static files                      │
└─────────────┬───────────────────────────────────────┘
              │
┌─────────────▼───────────────────────────────────────┐
│              SPRING BOOT 4.0.6 (Java 25)           │
│         Tomcat 11.0.22 embebido                     │
│                                                     │
│  Controllers → Services → Repositories → Entities  │
│                                                     │
│  - SecurityConfig + JWT Filter                     │
│  - CORS + Spring Security                          │
│  - GlobalExceptionHandler                          │
│  - Logging con SLF4J                               │
└─────────────┬───────────────────────────────────────┘
              │ JDBC
┌─────────────▼───────────────────────────────────────┐
│              MySQL 8.0 (HikariCP)                   │
│         Base de datos Logisteia                     │
│                                                     │
│  Entidades JPA con Hibernate ORM                    │
│  11+ tablas con relaciones y índices               │
└─────────────────────────────────────────────────────┘
```

### Flujo de una petición típica (Login)

1. **Usuario ingresa credenciales en Angular**
   ```typescript
   // Componente llama al servicio
   this.authService.login({email, senha})
   ```

2. **AuthService realiza POST a /api/v1/auth/login**
   ```typescript
   // service realiza petición HTTP
   return this.http.post('/api/v1/auth/login', credentials)
   ```

3. **AuthInterceptor intercepta la petición**
   ```typescript
   // Agrega headers si es necesario (en respuestas posteriores)
   ```

4. **Spring Security procesa la petición**
   - AuthController recibe POST /api/v1/auth/login
   - SecurityConfig permite endpoint público (sin JWT requerido)
   - AuthService valida credenciales contra DB
   - JwtService genera JWT token
   - Retorna {token, usuario}

5. **Frontend almacena el JWT**
   ```typescript
   localStorage.setItem('access_token', response.token)
   ```

6. **Peticiones posteriores incluyen JWT**
   - AuthInterceptor obtiene token de localStorage
   - Agrega header: `Authorization: Bearer <token>`
   - Todas las peticiones a /api/v1/* llevan el token

7. **Backend valida JWT en cada petición**
   - JwtAuthenticationFilter valida el token
   - Si es válido, permite acceso al recurso
   - Si no, retorna 401 Unauthorized

6. **Modelo accede a base de datos**
   - Ejecuta consultas preparadas con PDO
   - Devuelve resultados al controlador

7. **Respuesta JSON al frontend**
   - Formato estandarizado: `{success: boolean, data: any, error?: string}`
   - Headers CORS configurados
   - Código HTTP apropiado

8. **Angular procesa respuesta**
   - Observable emite datos
   - Componente actualiza vista
   - Manejo de errores si aplica

---

## Sistema de autenticación

### Autenticación tradicional (Email/Password)

**Endpoint**: `POST /api/login.php`

```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

**Respuesta**:
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "usuario": {
    "dni": "12345678A",
    "nombre": "Juan Pérez",
    "email": "usuario@ejemplo.com",
    "rol": "jefe_equipo"
  }
}
```

**Proceso**:
1. Rate limiting por IP y email
2. Validación de formato de datos
3. Consulta a base de datos con hash de contraseña
4. Verificación de estado del usuario (no baneado/eliminado)
5. Generación de JWT con payload de usuario
6. Registro de acción en logs

### Autenticación con Google OAuth

**Endpoint**: `POST /api/login-google.php`

```json
{
  "credential": "google_id_token"
}
```

**Proceso**:
1. Validación del token de Google con Google API
2. Extracción de email y datos del usuario
3. Búsqueda de usuario existente por email
4. Si no existe, creación automática con rol `trabajador`
5. Generación de JWT
6. Respuesta con token y datos

### Uso de JWT en el frontend

**Almacenamiento**:
```typescript
localStorage.setItem('token', response.token);
localStorage.setItem('usuario', JSON.stringify(response.usuario));
```

**HTTP Interceptor**:
```typescript
intercept(req: HttpRequest<any>, next: HttpHandler) {
  const token = localStorage.getItem('token');
  if (token) {
    req = req.clone({
      setHeaders: {
        Authorization: `Bearer ${token}`
      }
    });
  }
  return next.handle(req);
}
```

**Validación en backend** (`config/jwt.php`):
```php
function validarTokenJWT($token) {
    try {
        $decoded = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
        return (array)$decoded->data;
    } catch (Exception $e) {
        return null;
    }
}
```

---

## API REST - Endpoints disponibles

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/login.php` | Login con email/password |
| POST | `/api/login-google.php` | Login con Google OAuth |
| POST | `/api/RegistroUsuario.php` | Registro de nuevo usuario |
| POST | `/api/completar-registro-google.php` | Completar perfil OAuth |

### Usuarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/usuarios.php` | Listar todos los usuarios |
| GET | `/api/usuarios.php?dni={dni}` | Obtener usuario específico |
| POST | `/api/usuarios.php` | Cambiar estado de usuario |

### Equipos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/equipo.php` | Obtener equipo del jefe actual |
| GET | `/api/equipo.php?jefe={dni}` | Equipos de un jefe específico |
| POST | `/api/equipo.php` | Crear o actualizar equipo |
| POST | `/api/confirmar-invitacion.php` | Aceptar invitación a equipo |

### Proyectos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/proyectos.php` | Obtener proyectos del usuario |
| POST | `/api/proyectos.php` | Crear nuevo proyecto |
| GET | `/api/proyectos.php/{id}/trabajadores` | Trabajadores asignados |
| POST | `/api/proyectos.php/{id}/trabajadores` | Asignar trabajadores |
| DELETE | `/api/proyectos.php/{id}/trabajadores/{dni}` | Remover asignación |

### Clientes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/clientes.php` | Listar clientes del jefe |
| POST | `/api/clientes.php` | Crear nuevo cliente |
| PUT | `/api/clientes.php` | Actualizar cliente |
| DELETE | `/api/clientes.php?id={id}` | Eliminar cliente |

### Presupuestos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/mis-presupuestos-wizard.php` | Listar presupuestos |
| POST | `/api/guardar-presupuesto-wizard.php` | Crear presupuesto |
| GET | `/api/exportar-presupuesto-pdf.php` | Exportar a PDF |
| POST | `/api/enviar-presupuesto-email.php` | Enviar por email |

### Servicios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/servicios.php` | Servicios generales |
| GET | `/api/servicios-it.php` | Servicios informáticos |

### Moderador

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/moderador/estadisticas.php` | Estadísticas del sistema |
| POST | `/api/moderador/desbanear.php` | Desbanear usuario |
| GET | `/api/moderador/historial-baneos.php` | Historial de baneos |
| GET | `/api/moderador/proyectos.php` | Proyectos de todos los jefes |

### Historial

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/historial.php` | Acciones administrativas |

---

## Modelos de datos

### Modelo de Usuario

**Tabla**: `usuarios`

**Campos principales**:
- `dni` (PK): Identificador único
- `email`: Email único
- `nombre`: Nombre completo
- `contrase`: Hash de contraseña (bcrypt)
- `rol`: ENUM('jefe_equipo', 'trabajador', 'moderador')
- `estado`: ENUM('activo', 'baneado', 'eliminado')
- `telefono`: Teléfono opcional
- `fecha_registro`: Timestamp

**Clase PHP** (`modelos/Usuarios.php`):
```php
class Usuarios {
    private $db;
    
    public function obtenerTodos() { }
    public function obtenerPorDni($dni) { }
    public function obtenerPorEmail($email) { }
    public function crear($datos) { }
    public function actualizar($dni, $datos) { }
    public function cambiarEstado($dni, $estado, $motivo) { }
}
```

### Modelo de Proyecto

**Tabla**: `proyectos`

**Relaciones**:
- `jefe_dni` → `usuarios.dni`
- `cliente_id` → `clientes.id`
- `equipo_id` → `equipos.id`

**Estados**:
- creado
- en_proceso
- finalizado
- pausado
- cancelado

**Clase PHP** (`modelos/Proyecto.php`):
```php
class Proyecto {
    public function obtenerPorJefe($jefe_dni) { }
    public function obtenerPorTrabajador($trabajador_dni) { }
    public function crear($datos) { }
    public function actualizar($id, $datos) { }
    public function cambiarEstado($id, $estado) { }
    public function asignarTrabajadores($proyecto_id, $trabajadores) { }
}
```

### Modelo de Presupuesto

**Tablas relacionadas**:
- `presupuestos` (maestro)
- `detalle_presupuesto` (líneas)
- `servicios` y `servicios_informatica` (catálogos)

**Clase PHP** (`modelos/PresupuestoWizard.php`):
```php
class PresupuestoWizard {
    public function crearPresupuesto($datos) { }
    public function obtenerPresupuestos($jefe_dni) { }
    public function generarPDF($presupuesto_id) { }
    public function enviarPorEmail($presupuesto_id, $email) { }
}
```

---

## Configuración y variables de entorno

### Archivo .env

Todas las configuraciones sensibles se gestionan mediante variables de entorno:

```env
# Base de datos
DB_HOST=db                      # Nombre del contenedor Docker
DB_NAME=Logisteia
DB_USER=root
DB_PASS=contraseña_segura

# JWT
JWT_SECRET=64_caracteres_hex    # Generado aleatoriamente
JWT_EXPIRATION=3600             # 1 hora en segundos

# Google OAuth
GOOGLE_CLIENT_ID=xxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=xxx

# Email (PHPMailer)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password
MAIL_FROM_ADDRESS=noreply@logisteia.com
MAIL_FROM_NAME=LOGISTEIA

# Aplicación
APP_ENV=production              # development | production
APP_DEBUG=false                 # true | false
APP_URL=https://logisteia.com

# Rate Limiting
MAX_LOGIN_ATTEMPTS=5
LOGIN_TIMEOUT_MINUTES=15
```

### Carga de configuración

**PHP** (`config/config.php`):
```php
require_once '/app/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('/app');
$dotenv->load();

define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('JWT_SECRET', $_ENV['JWT_SECRET']);
```

**Angular** (`environments/environment.ts`):
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://api.logisteia.com'
};
```

---

## Seguridad

### Protección contra ataques

**1. Inyección SQL**
- PDO con prepared statements en todos los modelos
- Binding de parámetros: `$stmt->execute([$param1, $param2])`

**2. XSS (Cross-Site Scripting)**
- Sanitización de salida con `htmlspecialchars()`
- Headers `Content-Type: application/json`
- Angular escapa automáticamente en templates

**3. CSRF (Cross-Site Request Forgery)**
- API REST stateless sin cookies de sesión
- JWT en header Authorization
- Verificación de origen en CORS

**4. Rate Limiting**
```php
function verificarRateLimitLogin($identifier) {
    $attempts = $_SESSION['login_attempts_' . md5($identifier)] ?? 0;
    
    if ($attempts >= MAX_LOGIN_ATTEMPTS) {
        return false; // Bloqueado
    }
    
    return true;
}
```

**5. Validación de JWT**
```php
$token = obtenerTokenDeHeader();
$usuario = validarTokenJWT($token);

if (!$usuario) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit();
}
```

**6. Hashing de contraseñas**
```php
$hash = password_hash($password, PASSWORD_BCRYPT);
$valido = password_verify($password, $hash);
```

### Headers de seguridad

**Backend PHP** (`config/config.php`):
```php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
```

**Caddy** (automático):
- SSL/TLS con Let's Encrypt
- HSTS (HTTP Strict Transport Security)
- Redirección HTTP → HTTPS

---

## Buenas prácticas implementadas

### Backend PHP

1. **Arquitectura MVC**
   - Separación clara de responsabilidades
   - Modelos: acceso a datos
   - Controladores: lógica de negocio
   - Vistas: presentación (API devuelve JSON)

2. **Patrón Singleton**
   - Conexión única a base de datos
   - Ahorro de recursos

3. **Dependency Injection**
   - Controladores reciben instancias de modelos
   - Facilita testing y mantenimiento

4. **Logging centralizado**
```php
function logError($mensaje, $contexto = []) {
    error_log(json_encode([
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $mensaje,
        'context' => $contexto
    ]));
}
```

5. **Documentación PHPDoc**
```php
/**
 * Crea un nuevo usuario en el sistema
 * 
 * @param array $datos Datos del usuario (dni, email, nombre, etc.)
 * @return array Resultado con success y mensaje
 * @throws PDOException Si falla la inserción
 */
public function crear($datos) { }
```

### Frontend Angular

1. **Componentes reutilizables**
   - Componentes pequeños y enfocados
   - Props tipadas con TypeScript

2. **Services para lógica compartida**
```typescript
@Injectable({providedIn: 'root'})
export class UsuarioService {
  private apiUrl = `${environment.apiUrl}/api/usuarios.php`;
  
  obtenerUsuarios(): Observable<Usuario[]> {
    return this.http.get<ApiResponse<Usuario[]>>(this.apiUrl)
      .pipe(map(response => response.data));
  }
}
```

3. **Guards para protección de rutas**
```typescript
@Injectable({providedIn: 'root'})
export class AuthGuard implements CanActivate {
  canActivate(): boolean {
    const token = localStorage.getItem('token');
    return !!token;
  }
}
```

4. **Tipado fuerte**
```typescript
interface Usuario {
  dni: string;
  nombre: string;
  email: string;
  rol: 'jefe_equipo' | 'trabajador' | 'moderador';
}
```

5. **Manejo de errores centralizado**
```typescript
catchError(error => {
  console.error('Error:', error);
  return throwError(() => error);
})
```

---

## Testing y debugging

### Backend PHP

**Logs de aplicación**:
```bash
# Ver logs en tiempo real
docker compose logs -f backend

# Logs de errores PHP
docker compose exec backend tail -f /var/www/html/logs/php_errors.log
```

**Debugging con var_dump**:
```php
if (APP_DEBUG) {
    var_dump($variable);
    die();
}
```

**Testing de endpoints**:
```bash
# Con curl
curl -X POST https://api.logisteia.com/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@logisteia.com","password":"1234"}'

# Con Postman o Thunder Client (VS Code)
```

### Frontend Angular

**Angular DevTools**:
- Inspección de componentes
- Estado de servicios
- Profiling de rendimiento

**Console logging**:
```typescript
console.log('Datos recibidos:', data);
console.error('Error:', error);
```

**Network debugging**:
- Chrome DevTools → Network tab
- Inspeccionar peticiones HTTP
- Ver headers y payloads

---

## Despliegue

### Desarrollo local

```bash
# Levantar contenedores
docker compose up -d

# Ver logs
docker compose logs -f

# Rebuild después de cambios
docker compose up -d --build

# Acceder a contenedor
docker compose exec backend bash
```

### Producción

```bash
# Hacer commit de cambios
git add .
git commit -m "Descripción de cambios"

# Desplegar a AWS
git push production main

# El hook post-receive automáticamente:
# 1. Actualiza código en /home/ubuntu/logisteia
# 2. Ejecuta docker compose down
# 3. Ejecuta docker compose up -d --build
# 4. Los contenedores se reconstruyen con los cambios
```

### Rollback en caso de error

```bash
# Conectarse al servidor
ssh -i proyecto.pem ubuntu@logisteia.com

# Ver commits recientes
cd ~/logisteia
git log --oneline

# Volver a un commit anterior
git reset --hard <commit_hash>

# Reconstruir contenedores
docker compose down
docker compose up -d --build
```

---

## Estructura de base de datos

### Tablas principales

1. **usuarios** - Gestión de usuarios del sistema
2. **equipos** - Equipos de trabajo liderados por jefes
3. **miembros_equipo** - Relación N:N entre usuarios y equipos
4. **clientes** - Base de clientes por jefe de equipo
5. **proyectos** - Proyectos asignados a equipos
6. **tareas** - Tareas dentro de proyectos
7. **asignaciones_proyecto** - Trabajadores asignados a proyectos
8. **registro_horas** - Cronómetro de horas trabajadas
9. **presupuestos** - Presupuestos generados
10. **detalle_presupuesto** - Líneas de cada presupuesto
11. **servicios** - Catálogo de servicios generales
12. **servicios_informatica** - Catálogo de servicios IT
13. **facturas** - Facturación
14. **pagos** - Registro de pagos
15. **acciones_administrativas** - Auditoría de acciones
16. **historial_baneos** - Control de baneos
17. **invitaciones** - Sistema de invitaciones por email

### Diagrama de relaciones clave

```
usuarios
  ├─→ equipos (jefe_dni)
  ├─→ miembros_equipo (trabajador_dni)
  ├─→ clientes (jefe_dni)
  └─→ proyectos (jefe_dni)

equipos
  ├─→ miembros_equipo (equipo_id)
  └─→ proyectos (equipo_id)

proyectos
  ├─→ tareas (proyecto_id)
  ├─→ asignaciones_proyecto (proyecto_id)
  ├─→ presupuestos (proyecto_id)
  └─→ registro_horas (proyecto_id)

presupuestos
  └─→ detalle_presupuesto (presupuesto_id)
```

---

## Recursos adicionales

### Documentación oficial

- **Angular**: https://angular.dev
- **PHP**: https://www.php.net/docs.php
- **MySQL**: https://dev.mysql.com/doc/
- **Docker**: https://docs.docker.com
- **Caddy**: https://caddyserver.com/docs/

### Librerías utilizadas

- **PHPMailer**: https://github.com/PHPMailer/PHPMailer
- **Firebase PHP-JWT**: https://github.com/firebase/php-jwt
- **Google API Client**: https://github.com/googleapis/google-api-php-client
- **Tailwind CSS**: https://tailwindcss.com/docs

### Convenciones de código

**PHP**:
- PSR-12 para estilo de código
- CamelCase para clases: `UsuarioControlador`
- snake_case para funciones: `obtener_usuario()`
- Constantes en MAYÚSCULAS: `DB_HOST`

**TypeScript/Angular**:
- PascalCase para clases e interfaces: `Usuario`, `ProyectoService`
- camelCase para variables y funciones: `obtenerUsuarios()`
- kebab-case para nombres de archivos: `usuario.service.ts`

---

## Solución de problemas comunes

### Error: JWT token inválido

**Causa**: JWT_SECRET no coincide entre generación y validación

**Solución**:
```bash
# En el servidor, verificar y actualizar JWT_SECRET
bash update-env-production.sh
docker compose restart backend
```

### Error: CORS bloqueado

**Causa**: Headers CORS mal configurados

**Solución**: Verificar en `config/config.php`:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
```

### Error: Conexión a base de datos rechazada

**Causa**: Contenedor MySQL no está listo

**Solución**:
```bash
docker compose ps  # Verificar estado
docker compose logs db  # Ver logs de MySQL
docker compose restart db  # Reiniciar si es necesario
```

### Error: Módulo PHP no encontrado

**Causa**: Extensión PHP faltante en Dockerfile

**Solución**: Agregar en `docker/backend/Dockerfile`:
```dockerfile
RUN docker-php-ext-install pdo pdo_mysql zip
```

---

## Roadmap de mejoras

### Funcionalidades planeadas

- Sistema de chat en tiempo real (WebSockets)
- Notificaciones push
- Aplicación móvil (React Native)
- Exportación de informes avanzados
- Integración con calendarios (Google Calendar)
- Sistema de roles más granular
- Dashboard con gráficos en tiempo real

### Mejoras técnicas

- Tests unitarios (PHPUnit + Jest)
- Tests de integración
- CI/CD con GitHub Actions
- Monitoreo con Prometheus + Grafana
- Cache con Redis
- CDN para assets estáticos
- Optimización de queries SQL

---

**Autores:**
- Álvaro Andrades Márquez
- Fernando José Leva Rosa
