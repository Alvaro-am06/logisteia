# Manual del Programador de LOGISTEIA

## Introducción

Este manual está dirigido a desarrolladores que deseen entender, mantener o ampliar el proyecto LOGISTEIA. El sistema es una aplicación web full-stack moderna que combina un frontend Angular con un backend Spring Boot 4.0, desplegada en contenedores Docker con arquitectura de microservicios.

---

## Stack tecnológico

### Frontend
- **Framework**: Angular 21.0 con TypeScript
- **Estilos**: Tailwind CSS + SCSS
- **Gestión de estado**: RxJS con Observables
- **HTTP Client**: Angular HttpClient con interceptores
- **Autenticación**: JWT en localStorage
- **Compilación**: Angular CLI con Vite

### Backend
- **Lenguaje**: Java 25 LTS
- **Framework**: Spring Boot 4.0.6
- **Servidor**: Tomcat (embebido)
- **Base de datos**: MySQL 8.0
- **ORM**: Spring Data JPA + Hibernate
- **Autenticación**: Spring Security 7.0.5 + JJWT
- **Mapeo de objetos**: MapStruct
- **Inyección de dependencias**: Spring DI (Autowired)
- **Logging**: SLF4J + Logback

### Infraestructura
- **Contenedores**: Docker y Docker Compose
- **Servidor web**: Nginx (proxy reverso)
- **SSL**: Let's Encrypt automático
- **Despliegue**: Docker Compose
- **Hosting**: Oracle Cloud

---

## Arquitectura del sistema

### Estructura de carpetas completa

```
logisteia/
│
├── docker/                          # Configuración Docker
│   ├── frontend/
│   │   └── Dockerfile              # Node 20 + Angular
│   └── nginx/
│       └── nginx.conf              # Configuración proxy reverso
│
├── src/
│   ├── frontend/                    # Aplicación Angular
│   │   ├── src/
│   │   │   ├── app/
│   │   │   │   ├── components/     # Componentes reutilizables
│   │   │   │   ├── guards/         # Guardias de autenticación
│   │   │   │   ├── services/       # Servicios HTTP
│   │   │   │   ├── utils/          # Utilidades y helpers
│   │   │   │   ├── login/          # Módulo de login
│   │   │   │   ├── panel-jefe-equipo/
│   │   │   │   ├── panel-moderador/
│   │   │   │   ├── panel-registrado/
│   │   │   │   ├── mi-equipo/
│   │   │   │   ├── mis-proyectos/
│   │   │   │   ├── presupuesto/
│   │   │   │   └── usuarios/
│   │   │   └── environments/       # Configuración por entorno
│   │   ├── angular.json
│   │   ├── package.json
│   │   └── tailwind.config.js
│   │
│   ├── main/
│   │   ├── java/
│   │   │   └── com/logisteia/
│   │   │       ├── controllers/    # REST Controllers
│   │   │       ├── services/       # Lógica de negocio
│   │   │       ├── repositories/   # Data access layer (JPA)
│   │   │       ├── entities/       # Entidades JPA
│   │   │       ├── dto/            # Data Transfer Objects
│   │   │       ├── config/         # Configuración Spring
│   │   │       ├── utils/          # Utilidades
│   │   │       └── LogisteiaBackendApplication.java
│   │   │
│   │   └── resources/
│   │       ├── application.yml      # Configuración principal
│   │       ├── application-dev.yml  # Configuración desarrollo
│   │       ├── application-prod.yml # Configuración producción
│   │       └── logback-spring.xml   # Configuración logging
│   │
│   ├── test/
│   │   ├── java/                   # Tests unitarios e integración
│   │   └── resources/
│   │
│   └── sql/                        # Scripts de base de datos
│       ├── produccion_optimizada.sql  # Script principal
│       └── migraciones/               # Scripts de migración
│
├── pom.xml                          # Dependencias Maven
├── compose.yml                      # Docker Compose
├── .env                            # Variables de entorno (no en Git)
└── .gitignore
```

---

## Flujo de datos

### Arquitectura de tres capas

```
┌─────────────────────────────────────────────────────┐
│                    FRONTEND                         │
│            Angular 21 + TypeScript                  │
│                                                     │
│  Componentes → Services → HTTP Interceptors        │
└─────────────┬───────────────────────────────────────┘
              │ HTTP/HTTPS (JSON)
              │ JWT en Authorization header
┌─────────────▼───────────────────────────────────────┐
│              Nginx (Proxy Reverso)                 │
│   api.logisteia.com → spring-boot:8080            │
│   logisteia.com → Angular Frontend                │
└─────────────┬───────────────────────────────────────┘
              │
┌─────────────▼───────────────────────────────────────┐
│           BACKEND SPRING BOOT 4.0                   │
│                                                     │
│  @RestController → @Service → @Repository         │
│                                                     │
│  - Validación JWT (Spring Security)                │
│  - Exception Handling Global                       │
│  - Transacciones ACID                              │
│  - Logging con SLF4J                               │
└─────────────┬───────────────────────────────────────┘
              │
┌─────────────▼───────────────────────────────────────┐
│              MySQL 8.0                              │
│         Base de datos Logisteia                     │
│                                                     │
│  17 tablas con relaciones y foreign keys           │
└─────────────────────────────────────────────────────┘
```

### Flujo de una petición típica

1. **Usuario realiza acción en Angular**
   - Componente invoca método del servicio
   - Servicio construye petición HTTP

2. **HTTP Interceptor procesa la petición**
   - Agrega JWT token en header `Authorization: Bearer <token>`
   - Agrega headers CORS necesarios

3. **Nginx recibe la petición**
   - Aplica SSL/TLS
   - Enruta a Spring Boot en puerto 8080

4. **DispatcherServlet procesa la petición**
   - Mapea URL a @RestController
   - Aplica filtros de seguridad

5. **@RestController procesa**
   - Valida JWT token con JwtAuthenticationFilter
   - Extrae usuario autenticado
   - Valida anotaciones (@Valid)
   - Llama al servicio correspondiente

6. **@Service ejecuta lógica de negocio**
   - Valida reglas de negocio
   - Llama a repositories necesarios
   - Gestiona transacciones con @Transactional

7. **@Repository accede a base de datos**
   - Spring Data JPA ejecuta queries
   - Hibernate mapea resultados a entidades
   - Devuelve datos al servicio

8. **Respuesta JSON al frontend**
   - GlobalExceptionHandler captura excepciones
   - Formato estandarizado: `{success: boolean, data: any, error?: string}`
   - Código HTTP apropiado según @ResponseStatus

9. **Angular procesa respuesta**
   - Observable emite datos
   - Componente actualiza vista
   - Manejo de errores si aplica

---

## Sistema de autenticación

### Autenticación tradicional (Email/Password)

**Endpoint**: `POST /api/v1/auth/login`

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
1. Validación de entrada con @Valid
2. Consulta a BD con JPA
3. Verificación de contraseña con BCrypt
4. Validación de estado del usuario
5. Generación de JWT con JwtProvider
6. Registro de acción en logs

### Autenticación con Google OAuth

**Endpoint**: `POST /api/v1/auth/google`

```json
{
  "credential": "google_id_token"
}
```

**Proceso**:
1. Validación del token de Google con Google API Client
2. Extracción de email y datos del usuario
3. Búsqueda de usuario existente con JPA
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

**Validación en backend** (`config/JwtAuthenticationFilter.java`):
```java
@Component
public class JwtAuthenticationFilter extends OncePerRequestFilter {
    @Override
    protected void doFilterInternal(HttpServletRequest request,
                                  HttpServletResponse response,
                                  FilterChain chain) throws ServletException, IOException {
        String token = extractTokenFromHeader(request);
        if (token != null && jwtProvider.validateToken(token)) {
            String email = jwtProvider.getEmailFromToken(token);
            UserDetails user = userDetailsService.loadUserByUsername(email);
            // Establecer seguridad
        }
        chain.doFilter(request, response);
    }
}
```

---

## API REST - Endpoints disponibles (v1)

> Ver documentación completa en: [REFERENCIA_ENDPOINTS_FASE2B.md](REFERENCIA_ENDPOINTS_FASE2B.md)

### Endpoints principales

- `POST /api/v1/auth/login` - Login con email/password
- `POST /api/v1/auth/google` - Login con Google OAuth  
- `POST /api/v1/auth/registro` - Registro de nuevo usuario
- `GET /api/v1/usuarios` - Listar usuarios
- `GET /api/v1/equipos` - Obtener equipo del jefe
- `POST /api/v1/equipos` - Crear/actualizar equipo
- `GET /api/v1/proyectos` - Obtener proyectos
- `POST /api/v1/proyectos` - Crear proyecto
- Y más... (ver referencia completa)

---

## Modelos de datos

### Modelo de Usuario

**Entidad JPA**: `com.logisteia.entities.Usuario`

**Campos principales**:
- `dni` (PK): Identificador único
- `email`: Email único (@Column(unique=true))
- `nombre`: Nombre completo
- `password`: Hash de contraseña (BCrypt)
- `rol`: ENUM('JEFE_EQUIPO', 'TRABAJADOR', 'MODERADOR')
- `estado`: ENUM('ACTIVO', 'BANEADO', 'ELIMINADO')
- `telefono`: Teléfono opcional
- `fechaRegistro`: Timestamp

**Clase Java**:
```java
@Entity
@Table(name = "usuarios")
public class Usuario {
    @Id
    private String dni;
    
    @Column(unique = true, nullable = false)
    private String email;
    
    @Enumerated(EnumType.STRING)
    private RolUsuario rol;
    
    @Enumerated(EnumType.STRING)
    private EstadoUsuario estado;
}
```

### Modelo de Proyecto

**Entidad JPA**: `com.logisteia.entities.Proyecto`

**Relaciones**:
- `jefe` → `@ManyToOne` Usuario (JEFE_EQUIPO)
- `cliente` → `@ManyToOne` Cliente
- `equipo` → `@ManyToOne` Equipo

**Estados**:
- CREADO
- EN_PROCESO
- FINALIZADO
- PAUSADO
- CANCELADO

**Clase Java**:
```java
@Entity
@Table(name = "proyectos")
public class Proyecto {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;
    
    @ManyToOne
    @JoinColumn(name = "jefe_dni", nullable = false)
    private Usuario jefe;
    
    @Enumerated(EnumType.STRING)
    private EstadoProyecto estado;
}
```

### Modelo de Presupuesto

**Entidades JPA relacionadas**:
- `Presupuesto` (maestro)
- `DetallePresupuesto` (líneas)
- `Servicio` y `ServicioInformatica` (catálogos)

**Clase Java**:
```java
@Entity
@Table(name = "presupuestos")
@Transactional(readOnly = false)
public class Presupuesto {
    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;
    
    @OneToMany(mappedBy = "presupuesto", cascade = CascadeType.ALL)
    private List<DetallePresupuesto> detalles;
    
    @Enumerated(EnumType.STRING)
    private EstadoPresupuesto estado;
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

# Email (JavaMail)
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

**Spring Boot** (`application.yml`):
```yaml
spring:
  datasource:
    url: jdbc:mysql://${DB_HOST}:3306/${DB_NAME}
    username: ${DB_USER}
    password: ${DB_PASS}
  jpa:
    hibernate:
      ddl-auto: validate

jwt:
  secret: ${JWT_SECRET}
  expiration: ${JWT_EXPIRATION}

logging:
  level:
    root: INFO
    com.logisteia: DEBUG
```

**Angular** (`environments/environment.ts`):
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://api.logisteia.com/api/v1'
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

**4. Rate Limiting con Spring Security**
```java
@Component
public class RateLimitingFilter extends OncePerRequestFilter {
    @Override
    protected void doFilterInternal(HttpServletRequest request,
                                  HttpServletResponse response,
                                  FilterChain chain) throws ServletException, IOException {
        // Rate limiting implementation
    }
}
```

**5. Validación de JWT**
```java
@Component
public class JwtProvider {
    public boolean validateToken(String token) {
        try {
            Jwts.parser().setSigningKey(SECRET_KEY).parseClaimsJws(token);
            return true;
        } catch (JwtException e) {
            return false;
        }
    }
}
```

**6. Hashing de contraseñas**
```java
@Bean
public PasswordEncoder passwordEncoder() {
    return new BCryptPasswordEncoder();
}
```

### Headers de seguridad

**Spring Security** (`config/SecurityConfig.java`):
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY
- X-XSS-Protection: 1; mode=block

**Nginx** (automático):
- SSL/TLS con Let's Encrypt
- HSTS (HTTP Strict Transport Security)
- Redirección HTTP → HTTPS

---

## Buenas prácticas implementadas

### Backend Spring Boot

1. **Arquitectura en capas**
   - Controllers: Manejo de peticiones
   - Services: Lógica de negocio
   - Repositories: Acceso a datos
   - DTOs: Transferencia de datos

2. **Inyección de dependencias**
   - Todos los componentes se inyectan con @Autowired
   - Facilita testing y mantenimiento

3. **Transacciones ACID**
   - @Transactional en servicios
   - Manejo automático de rollback

4. **Validación centralizada**
```java
@RestController
public class UsuarioController {
    @PostMapping
    public ResponseEntity<UsuarioDTO> crear(@Valid @RequestBody CrearUsuarioDTO dto) {
        // Validación automática
        return ResponseEntity.ok(usuarioService.crear(dto));
    }
}
```

5. **Manejo de errores centralizado**
```java
@RestControllerAdvice
public class GlobalExceptionHandler {
    @ExceptionHandler(UsuarioNoEncontradoException.class)
    public ResponseEntity<ErrorResponse> handleUsuarioNoEncontrado() {
        return ResponseEntity.status(HttpStatus.NOT_FOUND).build();
    }
}
```

6. **Logging con SLF4J**
```java
private static final Logger logger = LoggerFactory.getLogger(UsuarioService.class);

logger.info("Usuario creado: {}", usuario.getDni());
logger.error("Error al obtener usuario", exception);
```

7. **Documentación con Javadoc**
```java
/**
 * Crea un nuevo usuario en el sistema
 * 
 * @param dto Datos del usuario a crear
 * @return Usuario creado
 * @throws DniYaExisteException si el DNI ya existe
 */
public UsuarioDTO crear(CrearUsuarioDTO dto) {
    // Implementación
}
```
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

### Backend Spring Boot

**Logs de aplicación**:
```bash
# Ver logs en tiempo real
docker compose logs -f backend

# Logs de Spring Boot en archivo
docker compose exec backend tail -f /var/log/spring-boot.log
```

**Debugging con punto de quiebre**:
```java
@RestController
public class UsuarioController {
    @GetMapping("/{dni}")
    public ResponseEntity<UsuarioDTO> obtenerUsuario(@PathVariable String dni) {
        // Punto de quiebre aquí con debugger
        return ResponseEntity.ok(usuarioService.obtenerPorDni(dni));
    }
}
```

**Testing de endpoints**:
```bash
# Con curl
curl -X POST https://api.logisteia.com/api/v1/auth/login \
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

# Hacer push a GitHub
git push origin main

# En el servidor remoto:
cd /opt/logisteia
git pull origin main
docker-compose build
docker-compose up -d
```

Para detalles completos de despliegue en producción, ver [DEPLOYMENT.md](../DEPLOYMENT.md)

### Rollback en caso de error

```bash
# Conectarse al servidor
ssh ubuntu@tu-dominio.com

# Revertir a commit anterior
cd /opt/logisteia
git log --oneline -5
git reset --hard <COMMIT_HASH>

# Reaplicar cambios
docker-compose build
docker-compose up -d

# Verificar salud
curl http://localhost/api/actuator/health
```

---

## Monitoreo en Producción

### Health Checks

```bash
# Backend
curl http://localhost/api/actuator/health

# Respuesta esperada:
# {"status":"UP","components":{"db":{"status":"UP"},...}}
```

### Ver Logs

```bash
# Logs en tiempo real
docker-compose logs -f backend

# Últimas 100 líneas
docker-compose logs --tail=100 backend

# Específicamente del archivo de logs
docker-compose exec backend tail -f /var/log/logisteia/app.log
```

### Métricas

```bash
# Obtener métricas disponibles
curl http://localhost/api/actuator/metrics

# Métrica específica (JVM memory)
curl http://localhost/api/actuator/metrics/jvm.memory.used
```

### Backups de Base de Datos

```bash
# Backup manual
docker-compose exec db mysqldump -u logisteia_user -p Logisteia > backup-$(date +%Y%m%d).sql

# Restaurar desde backup
docker-compose exec -T db mysql -u logisteia_user -p Logisteia < backup-20260603.sql
```

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
- **Spring Boot**: https://spring.io/projects/spring-boot
- **Spring Security**: https://spring.io/projects/spring-security
- **MySQL**: https://dev.mysql.com/doc/
- **Docker**: https://docs.docker.com
- **Nginx**: https://nginx.org/en/docs/

### Librerías utilizadas

- **Spring Data JPA**: JPA implementation para acceso a datos
- **JJWT**: Librería de JWT para Java
- **MapStruct**: Generador de code para mapeo de objetos
- **Lombok**: Reductor de boilerplate
- **Tailwind CSS**: Framework de CSS utility-first

### Convenciones de código

**Java**:
- Google Java Style Guide
- PascalCase para clases: `UsuarioController`
- camelCase para métodos y variables: `obtenerUsuario()`
- UPPER_SNAKE_CASE para constantes: `DB_HOST`
- @RestController, @Service, @Repository anotaciones

**TypeScript**:
- PascalCase para clases e interfaces: `Usuario`, `ProyectoService`
- camelCase para variables y funciones: `obtenerUsuarios()`
- kebab-case para nombres de archivos: `usuario.service.ts`

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
