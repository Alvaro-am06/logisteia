# 📚 REFERENCIA RÁPIDA DE ENDPOINTS - FASE 2B

## Tabla de Contenidos
- [Usuarios](#usuarios) (6 endpoints - preexistentes)
- [Presupuestos](#presupuestos) (8 endpoints - preexistentes)
- [Equipos](#equipos) (7 endpoints - NUEVO)
- [Miembros de Equipo](#miembros-de-equipo) (7 endpoints - NUEVO)
- [Clientes](#clientes) (7 endpoints - NUEVO)
- [Proyectos](#proyectos) (8 endpoints - NUEVO)
- [Tareas](#tareas) (8 endpoints - NUEVO)
- [Detalles de Presupuesto](#detalles-de-presupuesto) (7 endpoints - NUEVO)
- [Servicios](#servicios) (6 endpoints - NUEVO)
- [Servicios Informáticos](#servicios-informáticos) (7 endpoints - NUEVO)
- [Acciones Administrativas](#acciones-administrativas) (7 endpoints - NUEVO)
- [Asignaciones de Proyecto](#asignaciones-de-proyecto) (7 endpoints - NUEVO)

---

## Usuarios
```
GET    /api/v1/usuarios/{dni}
GET    /api/v1/usuarios/email/{email}
GET    /api/v1/usuarios?page=0&size=20
POST   /api/v1/usuarios
PUT    /api/v1/usuarios/{dni}
DELETE /api/v1/usuarios/{dni}
```

## Presupuestos
```
GET    /api/v1/presupuestos/{id}
GET    /api/v1/presupuestos/numero/{numeroPresupuesto}
GET    /api/v1/presupuestos?page=0&size=20
GET    /api/v1/presupuestos/usuario/{usuarioDni}
GET    /api/v1/presupuestos/estado/{estado}
POST   /api/v1/presupuestos
PUT    /api/v1/presupuestos/{id}
DELETE /api/v1/presupuestos/{id}
```

---

## Equipos
**Path:** `/api/v1/equipos`
```
GET    /{id}                          → Obtener equipo por ID
GET    /                              → Listar todos (paginado)
GET    /jefe/{jefeDni}                → Equipos del jefe
GET    /activos/lista                 → Listar activos
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Miembros de Equipo
**Path:** `/api/v1/miembros-equipo`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /equipo/{equipoId}             → Miembros del equipo
GET    /trabajador/{trabajadorDni}    → Equipos del trabajador
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Clientes
**Path:** `/api/v1/clientes`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /email/{email}                 → Buscar por email
GET    /jefe/{jefeDni}                → Clientes del jefe
GET    /activos/lista                 → Listar activos
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Proyectos
**Path:** `/api/v1/proyectos`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /codigo/{codigo}               → Buscar por código
GET    /estado/{estado}               → Por estado (ACTIVE, CLOSED, etc)
GET    /jefe/{jefeDni}                → Proyectos del jefe
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Tareas
**Path:** `/api/v1/tareas`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /proyecto/{proyectoId}         → Tareas del proyecto
GET    /trabajador/{trabajadorDni}    → Tareas asignadas
GET    /estado/{estado}               → Por estado (TODO, DOING, DONE, etc)
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Detalles de Presupuesto
**Path:** `/api/v1/detalles-presupuesto`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /presupuesto/{presupuestoId}   → Detalles del presupuesto
GET    /numero/{numeroPresupuesto}    → Por número de presupuesto
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Servicios
**Path:** `/api/v1/servicios`
```
GET    /{nombre}                      → Obtener por nombre
GET    /                              → Listar todos (paginado)
GET    /activos/lista                 → Listar activos
POST   /                              → Crear
PUT    /{nombre}                      → Actualizar
DELETE /{nombre}                      → Eliminar
```

## Servicios Informáticos
**Path:** `/api/v1/servicios-informatica`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /categoria/{categoria}         → Por categoría
GET    /activos/lista                 → Listar activos
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Acciones Administrativas
**Path:** `/api/v1/acciones-administrativas`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /administrador/{administradorDni}  → Acciones del admin
GET    /usuario/{usuarioAfectadoDni}      → Acciones sobre usuario
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

## Asignaciones de Proyecto
**Path:** `/api/v1/asignaciones-proyecto`
```
GET    /{id}                          → Obtener por ID
GET    /                              → Listar todos (paginado)
GET    /proyecto/{proyectoId}         → Asignaciones del proyecto
GET    /trabajador/{trabajadorDni}    → Asignaciones del trabajador
POST   /                              → Crear
PUT    /{id}                          → Actualizar
DELETE /{id}                          → Eliminar
```

---

## Códigos HTTP Utilizados

| Código | Significado | Usado en |
|--------|-------------|----------|
| **200** | OK | GET, PUT |
| **201** | Created | POST |
| **204** | No Content | DELETE |
| **400** | Bad Request | Validación fallida |
| **404** | Not Found | Recurso no existe |
| **409** | Conflict | Duplicado/Integridad |
| **500** | Internal Server Error | Error del servidor |

---

## Parámetros Comunes

### Paginación
```
?page=0&size=20
```
- `page`: Número de página (0-indexed)
- `size`: Elementos por página (default: 20)

### Enum States
**ProjectStatus:**
- ACTIVE
- CLOSED
- SUSPENDED

**TaskStatus:**
- TODO
- DOING
- DONE
- BLOCKED

**BudgetStatus:**
- DRAFT
- SENT
- ACCEPTED
- REJECTED

**ServiceCategory:**
- DEVELOPMENT
- MAINTENANCE
- SUPPORT

---

## Ejemplos cURL

### Crear Equipo
```bash
curl -X POST "http://localhost:8080/api/v1/equipos" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Backend Team",
    "descripcion": "Team for backend services",
    "jefeDni": "12345678A",
    "activo": true
  }'
```

### Obtener Equipos Activos
```bash
curl "http://localhost:8080/api/v1/equipos/activos/lista"
```

### Crear Proyecto
```bash
curl -X POST "http://localhost:8080/api/v1/proyectos" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Logisteia",
    "codigo": "LOG001",
    "descripcion": "Sistema de logística",
    "jefeDni": "12345678A",
    "clienteId": 1,
    "equipoId": 1,
    "estado": "ACTIVE"
  }'
```

### Crear Tarea
```bash
curl -X POST "http://localhost:8080/api/v1/tareas" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Implementar API",
    "descripcion": "Crear endpoints REST",
    "proyectoId": 1,
    "trabajadorDni": "12345678A",
    "estado": "TODO",
    "prioridad": "HIGH",
    "rol": "DEVELOPER"
  }'
```

### Listar Tareas del Proyecto
```bash
curl "http://localhost:8080/api/v1/tareas/proyecto/1"
```

### Actualizar Tarea
```bash
curl -X PUT "http://localhost:8080/api/v1/tareas/1" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Implementar API v2",
    "descripcion": "Crear endpoints REST mejorados",
    "proyectoId": 1,
    "trabajadorDni": "12345678A",
    "estado": "DOING",
    "prioridad": "HIGH",
    "rol": "DEVELOPER"
  }'
```

---

## Validaciones en DTOs

### Campos Obligatorios (@NotBlank/@NotNull)
- Todos los nombres, descripciones, emails
- IDs de relaciones
- Estados y enums

### Email (@Email)
- Cliente email
- Usuario email

### Números Decimales (@DecimalMin)
- Precios y montos: mínimo 0.00

### Rangos (@Size, @Min, @Max)
- Strings: longitudes definidas por entidad
- Números: validaciones positivas

---

## Estado de Implementación

✅ **COMPLETADO**: 12 entidades con CRUD completo (76 endpoints)
⏳ **PRÓXIMO**: Spring Security 6 + JWT (Fase 3)

---

**Última actualización:** Fase 2B Completada
**Archivos generados:** 50 nuevos
**Endpoints nuevos:** 62
**Endpoints totales:** 76
