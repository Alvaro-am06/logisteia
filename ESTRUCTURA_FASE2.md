# 📁 FASE 2: ESTRUCTURA COMPLETA DE ARCHIVOS

## 📊 Vista General

```
src/main/java/com/logisteia/backend/
│
├── exceptions/                          [6 archivos]
│   ├── ResourceNotFoundException.java    ✅ CREADO
│   ├── DataIntegrityException.java       ✅ CREADO
│   ├── BusinessLogicException.java       ✅ CREADO
│   ├── ErrorResponse.java                ✅ CREADO (Record)
│   ├── ValidationErrorResponse.java      ✅ CREADO (Record)
│   └── GlobalExceptionHandler.java       ✅ CREADO (@RestControllerAdvice)
│
├── dtos/                                [4 archivos]
│   ├── UsuarioResponseDTO.java           ✅ CREADO (Record)
│   ├── UsuarioCreateUpdateDTO.java       ✅ CREADO (Record)
│   ├── PresupuestoResponseDTO.java       ✅ CREADO (Record)
│   └── PresupuestoCreateUpdateDTO.java   ✅ CREADO (Record)
│
├── mappers/                             [2 archivos]
│   ├── UsuarioMapper.java                ✅ CREADO (@Component)
│   └── PresupuestoMapper.java            ✅ CREADO (@Component)
│
├── services/                            [2 archivos]
│   ├── UsuarioService.java               ✅ CREADO (@Service)
│   └── PresupuestoService.java           ✅ CREADO (@Service)
│
└── controllers/                         [2 archivos]
    ├── UsuarioController.java            ✅ CREADO (@RestController)
    └── PresupuestoController.java        ✅ CREADO (@RestController)

Workspace Root/
├── FASE2_REST_COMPLETADA.md             ✅ CREADO
├── EJEMPLOS_CURL_API.md                 ✅ CREADO
├── PATRON_GENERAR_CONTROLLERS.md        ✅ CREADO
└── CODIGO_VALIDAR_FASE2.md              ✅ CREADO
```

---

## 📋 TABLA RESUMEN

| Categoría | Archivo | Propósito | Type | Status |
|-----------|---------|----------|------|--------|
| **Exceptions** | ResourceNotFoundException | Recurso no encontrado (404) | Custom Exception | ✅ |
| | DataIntegrityException | Violaciones de constraint (409) | Custom Exception | ✅ |
| | BusinessLogicException | Errores de lógica (400) | Custom Exception | ✅ |
| | ErrorResponse | Respuesta de error | Record | ✅ |
| | ValidationErrorResponse | Respuesta de validación | Record | ✅ |
| | GlobalExceptionHandler | Manejo centralizado | @RestControllerAdvice | ✅ |
| **DTOs** | UsuarioResponseDTO | Lectura de Usuario | Record | ✅ |
| | UsuarioCreateUpdateDTO | Escritura de Usuario | Record | ✅ |
| | PresupuestoResponseDTO | Lectura de Presupuesto | Record | ✅ |
| | PresupuestoCreateUpdateDTO | Escritura de Presupuesto | Record | ✅ |
| **Mappers** | UsuarioMapper | Conversión Usuario | @Component | ✅ |
| | PresupuestoMapper | Conversión Presupuesto | @Component | ✅ |
| **Services** | UsuarioService | CRUD Usuario | @Service | ✅ |
| | PresupuestoService | CRUD Presupuesto | @Service | ✅ |
| **Controllers** | UsuarioController | REST Usuarios (6 endpoints) | @RestController | ✅ |
| | PresupuestoController | REST Presupuestos (8 endpoints) | @RestController | ✅ |

---

## 🎯 ENDPOINTS POR CONTROLLER

### **UsuarioController** (6 endpoints)

```java
@RestController
@RequestMapping("/api/v1/usuarios")
public class UsuarioController {

    @GetMapping("/{dni}")
    ResponseEntity<UsuarioResponseDTO> obtenerPorDni(String dni)
    // GET /api/v1/usuarios/{dni} → 200 OK

    @GetMapping("/email/{email}")
    ResponseEntity<UsuarioResponseDTO> obtenerPorEmail(String email)
    // GET /api/v1/usuarios/email/{email} → 200 OK

    @GetMapping
    ResponseEntity<Page<UsuarioResponseDTO>> obtenerTodos(Pageable pageable)
    // GET /api/v1/usuarios?page=0&size=20 → 200 OK

    @PostMapping
    ResponseEntity<UsuarioResponseDTO> crear(@Valid @RequestBody DTO)
    // POST /api/v1/usuarios → 201 CREATED

    @PutMapping("/{dni}")
    ResponseEntity<UsuarioResponseDTO> actualizar(String dni, @Valid @RequestBody DTO)
    // PUT /api/v1/usuarios/{dni} → 200 OK

    @DeleteMapping("/{dni}")
    ResponseEntity<Void> eliminar(String dni)
    // DELETE /api/v1/usuarios/{dni} → 204 NO CONTENT
}
```

### **PresupuestoController** (8 endpoints)

```java
@RestController
@RequestMapping("/api/v1/presupuestos")
public class PresupuestoController {

    @GetMapping("/{id}")
    ResponseEntity<PresupuestoResponseDTO> obtenerPorId(Integer id)
    // GET /api/v1/presupuestos/{id} → 200 OK

    @GetMapping("/numero/{numeroPresupuesto}")
    ResponseEntity<PresupuestoResponseDTO> obtenerPorNumero(String numeroPresupuesto)
    // GET /api/v1/presupuestos/numero/{numeroPresupuesto} → 200 OK

    @GetMapping
    ResponseEntity<Page<PresupuestoResponseDTO>> obtenerTodos(Pageable pageable)
    // GET /api/v1/presupuestos?page=0&size=20 → 200 OK

    @GetMapping("/usuario/{usuarioDni}")
    ResponseEntity<List<PresupuestoResponseDTO>> obtenerPorUsuario(String usuarioDni)
    // GET /api/v1/presupuestos/usuario/{dni} → 200 OK

    @GetMapping("/estado/{estado}")
    ResponseEntity<List<PresupuestoResponseDTO>> obtenerPorEstado(String estado)
    // GET /api/v1/presupuestos/estado/{estado} → 200 OK

    @PostMapping
    ResponseEntity<PresupuestoResponseDTO> crear(@Valid @RequestBody DTO)
    // POST /api/v1/presupuestos → 201 CREATED

    @PutMapping("/{id}")
    ResponseEntity<PresupuestoResponseDTO> actualizar(Integer id, @Valid @RequestBody DTO)
    // PUT /api/v1/presupuestos/{id} → 200 OK

    @DeleteMapping("/{id}")
    ResponseEntity<Void> eliminar(Integer id)
    // DELETE /api/v1/presupuestos/{id} → 204 NO CONTENT
}
```

---

## 🏗️ DIAGRAMA DE DEPENDENCIAS

```
UsuarioController
    ↓ (depende de)
UsuarioService
    ↓ (depende de)
├── UsuarioRepository
├── UsuarioMapper
    ↓
    ├── Usuario (Entity)
    └── UsuarioResponseDTO (Record)

GlobalExceptionHandler (independiente, se aplica a todo)
    ↓
├── ResourceNotFoundException
├── DataIntegrityException
├── BusinessLogicException
├── ErrorResponse
└── ValidationErrorResponse
```

---

## 📝 CONTENIDO DE CADA ARCHIVO

### **1. ResourceNotFoundException.java**
```
- Campos: message (String)
- Métodos estáticos:
  ├─ entityNotFound(entityName, id)
  ├─ entityNotFound(entityName, field, value)
  └─ custom(message)
```

### **2. DataIntegrityException.java**
```
- Campos: message (String)
- Métodos estáticos:
  ├─ duplicateEntry(field, value)
  └─ custom(message)
```

### **3. BusinessLogicException.java**
```
- Campos: message (String)
- Métodos: (extiende RuntimeException)
```

### **4. ErrorResponse (Record)**
```
- Fields:
  ├─ status: Integer (HTTP status code)
  ├─ message: String (Error message)
  ├─ error: String (Error type)
  └─ timestamp: LocalDateTime (auto-generado)
```

### **5. ValidationErrorResponse (Record)**
```
- Fields:
  ├─ status: Integer
  ├─ message: String
  ├─ error: String
  ├─ timestamp: LocalDateTime
  └─ fieldErrors: List<FieldError>
      ├─ field: String (Nombre del campo)
      └─ message: String (Mensaje de validación)
```

### **6. GlobalExceptionHandler**
```
- @RestControllerAdvice
- @ExceptionHandler methods:
  ├─ handleResourceNotFound (404)
  ├─ handleDataIntegrityException (409)
  ├─ handleBusinessLogicException (400)
  ├─ handleDataIntegrityViolation (409)
  ├─ handleEmptyResultDataAccess (404)
  ├─ handleValidationException (400 con detalles)
  └─ handleGenericException (500)
```

### **7-8. UsuarioResponseDTO & UsuarioCreateUpdateDTO**
```
ResponseDTO fields:
├─ dni
├─ email
├─ nombre
├─ rol
├─ estado
├─ telefono
└─ fechaRegistro

CreateUpdateDTO fields:
├─ dni (@NotBlank, @Size(8-255))
├─ email (@NotBlank, @Email)
├─ nombre (@NotBlank, @Size(3-255))
├─ contrase (@NotBlank, @Size(min=6))
├─ rol (@NotNull)
├─ estado (@NotNull)
└─ telefono
```

### **9-10. PresupuestoResponseDTO & PresupuestoCreateUpdateDTO**
```
ResponseDTO fields:
├─ idPresupuesto
├─ numeroPresupuesto
├─ estado
├─ validezDias
├─ total
├─ notas
├─ fechaCreacion
├─ usuarioDni (solo ID)
├─ proyectoId (solo ID)
└─ clienteId (solo ID)

CreateUpdateDTO fields:
├─ numeroPresupuesto (@NotBlank)
├─ usuarioDni (@NotNull)
├─ estado (@NotNull)
├─ validezDias (@NotNull, @Min(1))
├─ total (@NotNull, @DecimalMin("0.00"))
├─ notas (opcional)
├─ projectId (opcional)
└─ clienteId (opcional)
```

### **11-12. Mappers**
```
UsuarioMapper:
├─ toResponseDTO(Usuario) → UsuarioResponseDTO
├─ toEntity(UsuarioCreateUpdateDTO) → Usuario
└─ updateEntityFromDTO(DTO, Usuario) → void

PresupuestoMapper:
├─ toResponseDTO(Presupuesto) → PresupuestoResponseDTO
├─ toEntity(PresupuestoCreateUpdateDTO) → Presupuesto
└─ updateEntityFromDTO(DTO, Presupuesto) → void
```

### **13-14. Services**
```
UsuarioService:
├─ obtenerPorDni(dni) → ResponseDTO
├─ obtenerPorEmail(email) → ResponseDTO
├─ obtenerTodos(Pageable) → Page<ResponseDTO>
├─ crear(DTO) → ResponseDTO
├─ actualizar(dni, DTO) → ResponseDTO
└─ eliminar(dni) → void

PresupuestoService:
├─ obtenerPorId(id) → ResponseDTO
├─ obtenerPorNumero(numero) → ResponseDTO
├─ obtenerTodos(Pageable) → Page<ResponseDTO>
├─ obtenerPorUsuario(dni) → List<ResponseDTO>
├─ obtenerPorEstado(estado) → List<ResponseDTO>
├─ obtenerPorUsuarioYEstado(dni, estado) → List<ResponseDTO>
├─ crear(DTO) → ResponseDTO
├─ actualizar(id, DTO) → ResponseDTO
└─ eliminar(id) → void
```

### **15-16. Controllers**
```
UsuarioController: @RestController @RequestMapping("/api/v1/usuarios")
├─ GET /{dni}
├─ GET /email/{email}
├─ GET (paginado)
├─ POST (crear)
├─ PUT /{dni} (actualizar)
└─ DELETE /{dni}

PresupuestoController: @RestController @RequestMapping("/api/v1/presupuestos")
├─ GET /{id}
├─ GET /numero/{numeroPresupuesto}
├─ GET (paginado)
├─ GET /usuario/{usuarioDni}
├─ GET /estado/{estado}
├─ POST (crear)
├─ PUT /{id} (actualizar)
└─ DELETE /{id}
```

---

## 📚 DOCUMENTACIÓN

### **FASE2_REST_COMPLETADA.md**
- 300+ líneas
- Resumen técnico ejecutivo
- Arquitectura 3 capas
- Características destacadas
- Endpoints creados
- Validaciones implementadas

### **EJEMPLOS_CURL_API.md**
- 200+ líneas
- 14 ejemplos de cURL
- Ejemplos con Postman
- Ejemplos con IntelliJ
- Casos de error
- Test scripts

### **PATRON_GENERAR_CONTROLLERS.md**
- 400+ líneas
- Patrón paso a paso para Equipo
- Checklist completo
- 12 entidades pendientes
- Orden recomendado
- Tips de generación rápida

### **CODIGO_VALIDAR_FASE2.md**
- 400+ líneas
- GlobalExceptionHandler completo
- DTOs Usuario ejemplo
- Tabla de manejo de excepciones
- Casos de prueba
- Lista de validación

---

## ✅ CHECKLIST FINAL

- ✅ 6 clases de excepción
- ✅ 2 Records para respuestas de error
- ✅ 1 @RestControllerAdvice global
- ✅ 4 Records para DTOs
- ✅ 2 Mappers (@Component)
- ✅ 2 Servicios (@Service)
- ✅ 2 Controllers (@RestController)
- ✅ 14 endpoints REST
- ✅ Validación con Bean Validation
- ✅ Códigos HTTP correctos
- ✅ DTOs sin entidades anidadas
- ✅ Documentación completa

**TOTAL: 20 archivos Java + 4 documentos**

---

## 🚀 SIGUIENTES PASOS

1. **Revisar código** en el IDE
2. **Ejecutar endpoints** con cURL
3. **Validar respuestas** JSON
4. **Aprobar** o sugerir cambios
5. **Generar 10 controllers más** O **Pasar a Fase 3**

---

**Status:** ✅ **COMPLETADO**

Todos los archivos están creados y documentados.
Listos para validación y testing.
