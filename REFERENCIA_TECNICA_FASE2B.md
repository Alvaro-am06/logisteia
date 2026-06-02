# 🔧 REFERENCIA TÉCNICA DETALLADA - FASE 2B

## 1. ESTRUCTURA DE DTOs

### Patrón General
```java
public record [Entidad]ResponseDTO(
    // ID principal
    Integer id,  // o String si es clave compuesta
    
    // Campos de la entidad
    String campo1,
    String campo2,
    
    // Relaciones (solo IDs)
    Integer relacionadoId,
    String usuarioDni
) {}

public record [Entidad]CreateUpdateDTO(
    @NotBlank(message = "Campo obligatorio")
    String campo1,
    
    @Email(message = "Email inválido")
    String email,
    
    @DecimalMin("0.00", message = "Debe ser positivo")
    BigDecimal precio,
    
    // Relaciones
    Integer relacionadoId,
    String usuarioDni
) {}
```

### Validaciones Estándar

| Anotación | Entidades Usadas | Mensaje |
|-----------|-----------------|---------|
| @NotBlank | Casi todas | Campo no puede estar vacío |
| @NotNull | IDs de relaciones | Campo requerido |
| @Email | Cliente, Usuario | Email inválido |
| @Size | Strings | Tamaño incorrecto |
| @DecimalMin | Precios, montos | Debe ser >= 0.00 |
| @Min | Números enteros | Debe ser >= 1 |

---

## 2. ESTRUCTURA DE MAPPERS

### Patrón General
```java
@Component
@RequiredArgsConstructor
public class [Entidad]Mapper {
    
    private final [Repositorio]Repository relacionadoRepository;
    
    // Convertir entity → DTO
    public [Entidad]ResponseDTO toResponseDTO([Entidad] entity) {
        if (entity == null) return null;
        return new [Entidad]ResponseDTO(
            entity.getId(),
            entity.getNombre(),
            entity.getRelacionado() != null ? entity.getRelacionado().getId() : null
        );
    }
    
    // Convertir DTO → entity
    public [Entidad] toEntity([Entidad]CreateUpdateDTO dto) {
        if (dto == null) return null;
        
        [Entidad] entity = new [Entidad]();
        entity.setNombre(dto.nombre());
        
        // Resolver relaciones por ID
        if (dto.relacionadoId() != null) {
            Relacionado relacionado = relacionadoRepository.findById(dto.relacionadoId())
                .orElseThrow(() -> ResourceNotFoundException.entityNotFound(...));
            entity.setRelacionado(relacionado);
        }
        
        return entity;
    }
    
    // Actualizar entity desde DTO
    public void updateEntityFromDTO([Entidad]CreateUpdateDTO dto, [Entidad] entity) {
        if (dto == null) return;
        entity.setNombre(dto.nombre());
        // Resolver relaciones si es necesario
    }
}
```

### Dependencias por Entidad

| Mapper | Repositorios Inyectados |
|--------|------------------------|
| EquipoMapper | UsuarioRepository |
| MiembroEquipoMapper | EquipoRepository, UsuarioRepository |
| ClienteMapper | UsuarioRepository |
| ProyectoMapper | UsuarioRepository, ClienteRepository, EquipoRepository |
| TareaMapper | ProyectoRepository, UsuarioRepository |
| DetallePresupuestoMapper | PresupuestoRepository |
| ServicioMapper | (ninguno - String nombre es PK) |
| ServicioInformaticaMapper | (ninguno) |
| AccionAdministrativaMapper | UsuarioRepository, ProyectoRepository, EquipoRepository |
| AsignacionProyectoMapper | ProyectoRepository, UsuarioRepository |

---

## 3. ESTRUCTURA DE SERVICIOS

### Patrón General
```java
@Service
@RequiredArgsConstructor
@Transactional
public class [Entidad]Service {
    
    private final [Entidad]Repository repository;
    private final [Entidad]Mapper mapper;
    
    // Lectura (readOnly = true)
    @Transactional(readOnly = true)
    public [Entidad]ResponseDTO obtenerPorId(Integer id) {
        [Entidad] entity = repository.findById(id)
            .orElseThrow(() -> 
                ResourceNotFoundException.entityNotFound("[Entidad]", "ID", id.toString())
            );
        return mapper.toResponseDTO(entity);
    }
    
    @Transactional(readOnly = true)
    public Page<[Entidad]ResponseDTO> obtenerTodos(Pageable pageable) {
        return repository.findAll(pageable)
            .map(mapper::toResponseDTO);
    }
    
    @Transactional(readOnly = true)
    public List<[Entidad]ResponseDTO> obtenerPor[Filtro](String criterio) {
        return repository.findBy[Filtro](criterio)
            .stream()
            .map(mapper::toResponseDTO)
            .toList();
    }
    
    // Escritura (transactional)
    public [Entidad]ResponseDTO crear([Entidad]CreateUpdateDTO dto) {
        // Validación de negocio adicional si es necesario
        [Entidad] entity = mapper.toEntity(dto);
        [Entidad] guardado = repository.save(entity);
        return mapper.toResponseDTO(guardado);
    }
    
    public [Entidad]ResponseDTO actualizar(Integer id, [Entidad]CreateUpdateDTO dto) {
        [Entidad] entity = repository.findById(id)
            .orElseThrow(() -> 
                ResourceNotFoundException.entityNotFound("[Entidad]", "ID", id.toString())
            );
        mapper.updateEntityFromDTO(dto, entity);
        [Entidad] actualizado = repository.save(entity);
        return mapper.toResponseDTO(actualizado);
    }
    
    public void eliminar(Integer id) {
        [Entidad] entity = repository.findById(id)
            .orElseThrow(() -> 
                ResourceNotFoundException.entityNotFound("[Entidad]", "ID", id.toString())
            );
        repository.delete(entity);
    }
}
```

### Métodos por Entidad

| Servicio | Métodos Especializados |
|----------|----------------------|
| EquipoService | obtenerPorJefe(jefeDni), obtenerActivos() |
| MiembroEquipoService | obtenerPorEquipo(equipoId), obtenerPorTrabajador(dni) |
| ClienteService | obtenerPorEmail(email), obtenerPorJefe(dni), obtenerActivos() |
| ProyectoService | obtenerPorCodigo(codigo), obtenerPorEstado(status), obtenerPorJefe(dni) |
| TareaService | obtenerPorProyecto(id), obtenerPorTrabajador(dni), obtenerPorEstado(status) |
| DetallePresupuestoService | obtenerPorPresupuesto(id), obtenerPorNumeroPresupuesto(numero) |
| ServicioService | obtenerPorNombre(nombre), obtenerActivos() |
| ServicioInformaticaService | obtenerPorCategoria(category), obtenerActivos() |
| AccionAdministrativaService | obtenerPorAdministrador(dni), obtenerPorUsuarioAfectado(dni) |
| AsignacionProyectoService | obtenerPorProyecto(id), obtenerPorTrabajador(dni) |

---

## 4. ESTRUCTURA DE CONTROLLERS

### Patrón General
```java
@RestController
@RequestMapping("/api/v1/[recurso]")
@RequiredArgsConstructor
public class [Entidad]Controller {
    
    private final [Entidad]Service service;
    
    // GET por ID → 200 OK o 404 NOT FOUND
    @GetMapping("/{id}")
    public ResponseEntity<[Entidad]ResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(service.obtenerPorId(id));
    }
    
    // GET paginado → 200 OK
    @GetMapping
    public ResponseEntity<Page<[Entidad]ResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(service.obtenerTodos(pageable));
    }
    
    // GET filtrados → 200 OK
    @GetMapping("/[filtro]/{valor}")
    public ResponseEntity<List<[Entidad]ResponseDTO>> obtenerPor[Filtro](
            @PathVariable String valor) {
        return ResponseEntity.ok(service.obtenerPor[Filtro](valor));
    }
    
    // POST → 201 CREATED o 400/409
    @PostMapping
    public ResponseEntity<[Entidad]ResponseDTO> crear(
            @Valid @RequestBody [Entidad]CreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED)
            .body(service.crear(dto));
    }
    
    // PUT → 200 OK o 400/404/409
    @PutMapping("/{id}")
    public ResponseEntity<[Entidad]ResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody [Entidad]CreateUpdateDTO dto) {
        return ResponseEntity.ok(service.actualizar(id, dto));
    }
    
    // DELETE → 204 NO CONTENT o 404
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        service.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
```

### Endpoints por Controlador

| Controlador | Endpoints | Path Principal |
|------------|-----------|----------------|
| EquipoController | 7 | /api/v1/equipos |
| MiembroEquipoController | 7 | /api/v1/miembros-equipo |
| ClienteController | 7 | /api/v1/clientes |
| ProyectoController | 8 | /api/v1/proyectos |
| TareaController | 8 | /api/v1/tareas |
| DetallePresupuestoController | 7 | /api/v1/detalles-presupuesto |
| ServicioController | 6 | /api/v1/servicios |
| ServicioInformaticaController | 7 | /api/v1/servicios-informatica |
| AccionAdministrativaController | 7 | /api/v1/acciones-administrativas |
| AsignacionProyectoController | 7 | /api/v1/asignaciones-proyecto |

---

## 5. MANEJO DE EXCEPCIONES

### GlobalExceptionHandler (Preexistente)

```java
@RestControllerAdvice
public class GlobalExceptionHandler {
    
    @ExceptionHandler(ResourceNotFoundException.class)
    public ResponseEntity<ErrorResponse> handleResourceNotFound(...) {
        return ResponseEntity.status(HttpStatus.NOT_FOUND)
            .body(new ErrorResponse(...));
    }
    
    @ExceptionHandler(DataIntegrityException.class)
    public ResponseEntity<ErrorResponse> handleDataIntegrity(...) {
        return ResponseEntity.status(HttpStatus.CONFLICT)
            .body(new ErrorResponse(...));
    }
    
    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ValidationErrorResponse> handleValidation(...) {
        // Extrae errores por campo
        return ResponseEntity.status(HttpStatus.BAD_REQUEST)
            .body(new ValidationErrorResponse(...));
    }
}
```

### Excepciones Lanzadas

| Excepción | HTTP | Caso |
|-----------|------|------|
| ResourceNotFoundException | 404 | Recurso no encontrado |
| DataIntegrityException | 409 | Violación de restricción |
| BusinessLogicException | 400 | Validación de negocio |
| MethodArgumentNotValidException | 400 | @Valid falla |
| Exception (catch-all) | 500 | Error inesperado |

---

## 6. ENUMS UTILIZADOS

### Estados y Categorías

```java
// TaskStatus: TODO, DOING, DONE, BLOCKED
// ProjectStatus: ACTIVE, CLOSED, SUSPENDED
// BudgetStatus: DRAFT, SENT, ACCEPTED, REJECTED
// TaskPriority: LOW, MEDIUM, HIGH
// ServiceCategory: DEVELOPMENT, MAINTENANCE, SUPPORT
// Unit: HOUR, DAY, WEEK, PROJECT
// UserRole: ADMIN, MANAGER, EMPLOYEE, CLIENT
// UserStatus: ACTIVE, INACTIVE, SUSPENDED
// InvitationStatus: PENDING, ACCEPTED, REJECTED
// TaskRole: DEVELOPER, MANAGER, TESTER, DESIGNER
```

---

## 7. ANOTACIONES PRINCIPALES

### Clases
```java
@RestController          // Spring MVC REST endpoint
@RequestMapping(...)     // Ruta base
@RequiredArgsConstructor // Lombok - constructor con required args
@Service                 // Componente de servicios
@Transactional          // Transaccional por defecto
@Component              // Bean de Spring
```

### Métodos
```java
@GetMapping(...)        // GET HTTP
@PostMapping(...)       // POST HTTP
@PutMapping(...)        // PUT HTTP
@DeleteMapping(...)     // DELETE HTTP
@Transactional(readOnly = true)  // Lectura
@RequestParam(...)      // Query params
@PathVariable(...)      // Path params
@Valid                  // Validación Bean
@RequestBody            // Body JSON
```

### DTOs
```java
@NotBlank               // No null ni espacios
@NotNull                // No null
@Email                  // Formato email
@Size(min=, max=)       // Rango de caracteres
@DecimalMin("...")      // Número mínimo
@Min(...)               // Entero mínimo
```

---

## 8. FLUJO DE REQUESTS

### POST /api/v1/equipos
```
1. JSON Request → Spring deserializa a @RequestBody EquipoCreateUpdateDTO
2. @Valid → Jakarta Bean Validation valida el DTO
3. Si hay error → MethodArgumentNotValidException → 400 BAD_REQUEST
4. Si OK → EquipoController.crear(dto)
5. → EquipoService.crear(dto)
6. → EquipoMapper.toEntity(dto) → resuelve relaciones
7. → equipoRepository.save(entity)
8. → EquipoMapper.toResponseDTO(guardado)
9. ResponseEntity.status(201).body(...) → JSON Response 201 CREATED
```

### GET /api/v1/equipos/1
```
1. Extrae path variable id=1
2. EquipoController.obtenerPorId(1)
3. → EquipoService.obtenerPorId(1) (@Transactional(readOnly=true))
4. → equipoRepository.findById(1)
5. Si no existe → orElseThrow(ResourceNotFoundException)
6. → GlobalExceptionHandler → 404 NOT_FOUND
7. Si existe → EquipoMapper.toResponseDTO(equipo)
8. ResponseEntity.ok(...) → JSON Response 200 OK
```

### PUT /api/v1/equipos/1
```
1. Path variable id=1, JSON body → EquipoCreateUpdateDTO
2. @Valid → Validación
3. EquipoController.actualizar(1, dto)
4. → EquipoService.actualizar(1, dto)
5. → equipoRepository.findById(1) (si no → 404)
6. → EquipoMapper.updateEntityFromDTO(dto, entity)
7. → equipoRepository.save(entity)
8. → EquipoMapper.toResponseDTO(actualizado)
9. ResponseEntity.ok(...) → JSON Response 200 OK
```

### DELETE /api/v1/equipos/1
```
1. Path variable id=1
2. EquipoController.eliminar(1)
3. → EquipoService.eliminar(1)
4. → equipoRepository.findById(1) (si no → 404)
5. → equipoRepository.delete(entity)
6. ResponseEntity.noContent().build() → 204 NO_CONTENT
```

---

## 9. CONFIGURACIÓN NECESARIA

### application.yml (Preexistente)
```yaml
spring:
  datasource:
    url: jdbc:mysql://localhost:3306/Logisteia
    username: root
    password: your_password
    hikari:
      maximum-pool-size: 10
  jpa:
    hibernate:
      ddl-auto: update
    properties:
      hibernate:
        dialect: org.hibernate.dialect.MySQL8Dialect
  jackson:
    serialization:
      write-dates-as-timestamps: false
  web:
    cors:
      allowed-origins: http://localhost:4200
      allowed-methods: GET,POST,PUT,DELETE
```

---

## 10. TESTING RÁPIDO

### JUnit 5 + MockMvc (Patrón para tus tests)
```java
@WebMvcTest(EquipoController.class)
class EquipoControllerTest {
    
    @MockBean
    private EquipoService equipoService;
    
    @Autowired
    private MockMvc mockMvc;
    
    @Test
    void testObtenerPorId() throws Exception {
        EquipoResponseDTO dto = new EquipoResponseDTO(...);
        when(equipoService.obtenerPorId(1)).thenReturn(dto);
        
        mockMvc.perform(get("/api/v1/equipos/1"))
            .andExpect(status().isOk())
            .andExpect(jsonPath("$.nombre").value("Backend Team"));
    }
}
```

---

## 📝 CHECKLIST DE QUALITY ASSURANCE

- ✅ Todas las excepciones son capturadas por GlobalExceptionHandler
- ✅ @Valid en todos los @PostMapping y @PutMapping
- ✅ @Transactional(readOnly=true) en todos los GET
- ✅ Códigos HTTP correctos (200, 201, 204, 400, 404, 409)
- ✅ DTOs con validaciones en portugués
- ✅ DTOs sin entidades anidadas (solo IDs)
- ✅ Servicios inyectan mapper y repository
- ✅ Controllers inyectan service
- ✅ Paginación en métodos obtenerTodos()
- ✅ Relaciones resueltas en mappers
- ✅ Busquedas especializadas por entidad

---

**Documento de referencia técnica para implementación y mantenimiento de Fase 2B**
