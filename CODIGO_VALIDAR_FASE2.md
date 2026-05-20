# 🔍 VALIDACIÓN DE CÓDIGO: GlobalExceptionHandler + DTOs Ejemplo

## 📌 GlobalExceptionHandler (Completo)

```java
package com.logisteia.backend.exceptions;

import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.FieldError;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import org.springframework.web.context.request.WebRequest;
import org.springframework.dao.DataIntegrityViolationException;
import org.springframework.dao.EmptyResultDataAccessException;

import java.util.List;

/**
 * Manejador global de excepciones para toda la aplicación.
 * Centraliza el manejo de errores y devuelve respuestas JSON estructuradas.
 */
@RestControllerAdvice
public class GlobalExceptionHandler {

    /**
     * Maneja ResourceNotFoundException (404 Not Found).
     */
    @ExceptionHandler(ResourceNotFoundException.class)
    public ResponseEntity<ErrorResponse> handleResourceNotFound(
            ResourceNotFoundException ex,
            WebRequest request) {
        
        ErrorResponse errorResponse = new ErrorResponse(
            HttpStatus.NOT_FOUND.value(),
            ex.getMessage(),
            "NOT_FOUND",
            extractPath(request)
        );
        
        return new ResponseEntity<>(errorResponse, HttpStatus.NOT_FOUND);
    }

    /**
     * Maneja DataIntegrityException (409 Conflict).
     */
    @ExceptionHandler(DataIntegrityException.class)
    public ResponseEntity<ErrorResponse> handleDataIntegrityException(
            DataIntegrityException ex,
            WebRequest request) {
        
        ErrorResponse errorResponse = new ErrorResponse(
            HttpStatus.CONFLICT.value(),
            ex.getMessage(),
            "CONFLICT",
            extractPath(request)
        );
        
        return new ResponseEntity<>(errorResponse, HttpStatus.CONFLICT);
    }

    /**
     * Maneja BusinessLogicException (400 Bad Request).
     */
    @ExceptionHandler(BusinessLogicException.class)
    public ResponseEntity<ErrorResponse> handleBusinessLogicException(
            BusinessLogicException ex,
            WebRequest request) {
        
        ErrorResponse errorResponse = new ErrorResponse(
            HttpStatus.BAD_REQUEST.value(),
            ex.getMessage(),
            "BAD_REQUEST",
            extractPath(request)
        );
        
        return new ResponseEntity<>(errorResponse, HttpStatus.BAD_REQUEST);
    }

    /**
     * Maneja DataIntegrityViolationException (409 Conflict).
     * Se lanza cuando hay violación de unique constraint, FK, etc.
     */
    @ExceptionHandler(DataIntegrityViolationException.class)
    public ResponseEntity<ErrorResponse> handleDataIntegrityViolation(
            DataIntegrityViolationException ex,
            WebRequest request) {
        
        String message = "Violación de integridad de datos";
        if (ex.getCause() != null) {
            String cause = ex.getCause().getMessage();
            if (cause != null && cause.contains("Duplicate entry")) {
                message = "Ya existe un registro con ese valor";
            }
        }
        
        ErrorResponse errorResponse = new ErrorResponse(
            HttpStatus.CONFLICT.value(),
            message,
            "CONFLICT",
            extractPath(request)
        );
        
        return new ResponseEntity<>(errorResponse, HttpStatus.CONFLICT);
    }

    /**
     * Maneja EmptyResultDataAccessException (404 Not Found).
     * Se lanza cuando intenta DELETE un registro que no existe.
     */
    @ExceptionHandler(EmptyResultDataAccessException.class)
    public ResponseEntity<ErrorResponse> handleEmptyResultDataAccess(
            EmptyResultDataAccessException ex,
            WebRequest request) {
        
        ErrorResponse errorResponse = new ErrorResponse(
            HttpStatus.NOT_FOUND.value(),
            "Recurso no encontrado",
            "NOT_FOUND",
            extractPath(request)
        );
        
        return new ResponseEntity<>(errorResponse, HttpStatus.NOT_FOUND);
    }

    /**
     * Maneja MethodArgumentNotValidException (400 Bad Request).
     * Se lanza cuando @Valid falla en los DTOs (@NotBlank, @Email, etc).
     */
    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ResponseEntity<ValidationErrorResponse> handleValidationException(
            MethodArgumentNotValidException ex,
            WebRequest request) {
        
        List<ValidationErrorResponse.FieldError> fieldErrors = ex.getBindingResult()
            .getAllErrors()
            .stream()
            .map(error -> {
                String fieldName = error instanceof FieldError 
                    ? ((FieldError) error).getField() 
                    : error.getObjectName();
                String message = error.getDefaultMessage();
                return new ValidationErrorResponse.FieldError(fieldName, message);
            })
            .toList();

        ValidationErrorResponse errorResponse = new ValidationErrorResponse(
            HttpStatus.BAD_REQUEST.value(),
            "Errores de validación",
            "VALIDATION_ERROR",
            extractPath(request),
            fieldErrors
        );
        
        return new ResponseEntity<>(errorResponse, HttpStatus.BAD_REQUEST);
    }

    /**
     * Maneja todas las excepciones no capturadas (500 Internal Server Error).
     */
    @ExceptionHandler(Exception.class)
    public ResponseEntity<ErrorResponse> handleGenericException(
            Exception ex,
            WebRequest request) {
        
        ErrorResponse errorResponse = new ErrorResponse(
            HttpStatus.INTERNAL_SERVER_ERROR.value(),
            "Error interno del servidor",
            "INTERNAL_SERVER_ERROR",
            extractPath(request)
        );
        
        // Log del error para debugging
        ex.printStackTrace();
        
        return new ResponseEntity<>(errorResponse, HttpStatus.INTERNAL_SERVER_ERROR);
    }

    /**
     * Extrae el path de la solicitud para incluir en la respuesta.
     */
    private String extractPath(WebRequest request) {
        String path = request.getDescription(false);
        return path != null ? path.replace("uri=", "") : "/";
    }
}
```

---

## 📊 Tabla de Manejo de Excepciones

| Excepción | Status HTTP | Mensaje | Caso de Uso |
|-----------|-------------|---------|------------|
| `ResourceNotFoundException` | 404 | Recurso no encontrado | Cuando un Usuario/Presupuesto/etc no existe |
| `DataIntegrityException` | 409 | Ya existe un registro | Email duplicado, número presupuesto duplicado |
| `BusinessLogicException` | 400 | Error de lógica | Saldo insuficiente, estado inválido |
| `DataIntegrityViolationException` | 409 | Violación de constraint | FK inválida, UNIQUE constraint |
| `EmptyResultDataAccessException` | 404 | Recurso no encontrado | DELETE en entidad inexistente |
| `MethodArgumentNotValidException` | 400 | Errores de validación | Email inválido, campo vacío |
| `Exception` (genérica) | 500 | Error interno del servidor | Cualquier error no capturado |

---

## 🎯 DTOs de Usuario (Ejemplo)

### **UsuarioResponseDTO.java**

```java
package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

/**
 * DTO de respuesta para Usuario.
 * Usa Record de Java 21 para inmutabilidad.
 */
public record UsuarioResponseDTO(
    @NotNull(message = "DNI no puede ser nulo")
    String dni,
    
    @NotBlank(message = "Email no puede estar vacío")
    String email,
    
    @NotBlank(message = "Nombre no puede estar vacío")
    String nombre,
    
    @NotNull(message = "Rol no puede ser nulo")
    String rol,
    
    @NotNull(message = "Estado no puede ser nulo")
    String estado,
    
    String telefono,
    
    LocalDateTime fechaRegistro
) {}
```

**Uso:** Se devuelve en GET /api/v1/usuarios/{dni}

**Ejemplo JSON:**
```json
{
  "dni": "12345678A",
  "email": "juan@logisteia.com",
  "nombre": "Juan Pérez García",
  "rol": "JEFE_EQUIPO",
  "estado": "ACTIVO",
  "telefono": "666123456",
  "fechaRegistro": "2026-05-12T10:30:45.123456"
}
```

---

### **UsuarioCreateUpdateDTO.java**

```java
package com.logisteia.backend.dtos;

import jakarta.validation.constraints.*;

/**
 * DTO para crear o actualizar Usuario.
 * Incluye todas las validaciones necesarias.
 */
public record UsuarioCreateUpdateDTO(
    @NotBlank(message = "El DNI no puede estar vacío")
    @Size(min = 8, max = 255, message = "El DNI debe tener entre 8 y 255 caracteres")
    String dni,

    @NotBlank(message = "El email no puede estar vacío")
    @Email(message = "El email debe ser válido")
    String email,

    @NotBlank(message = "El nombre no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    @NotBlank(message = "La contraseña no puede estar vacía")
    @Size(min = 6, message = "La contraseña debe tener al menos 6 caracteres")
    String contrase,

    @NotNull(message = "El rol no puede ser nulo")
    String rol,

    @NotNull(message = "El estado no puede ser nulo")
    String estado,

    String telefono
) {}
```

**Uso:** Se recibe en POST/PUT para /api/v1/usuarios

**Ejemplo JSON (POST):**
```json
{
  "dni": "12345678A",
  "email": "juan@logisteia.com",
  "nombre": "Juan Pérez García",
  "contrase": "MiPassword123!",
  "rol": "JEFE_EQUIPO",
  "estado": "ACTIVO",
  "telefono": "666123456"
}
```

---

## 🛡️ Flujo de Validación

### **1. Validación en DTO (@Valid)**
```
POST /api/v1/usuarios
{
  "email": "correo_invalido",  // ❌ No es un email válido
  "contrase": "123"             // ❌ Menos de 6 caracteres
}

↓ GlobalExceptionHandler captura

{
  "status": 400,
  "message": "Errores de validación",
  "error": "VALIDATION_ERROR",
  "timestamp": "2026-05-12T10:30:45.123456",
  "path": "/api/v1/usuarios",
  "fieldErrors": [
    {
      "field": "email",
      "message": "El email debe ser válido"
    },
    {
      "field": "contrase",
      "message": "La contraseña debe tener al menos 6 caracteres"
    }
  ]
}
```

### **2. Validación en Servicio**
```java
public UsuarioResponseDTO crear(UsuarioCreateUpdateDTO dto) {
    // Validación: email duplicado
    if (usuarioRepository.findByEmail(dto.email()).isPresent()) {
        throw DataIntegrityException.duplicateEntry("email", dto.email());
    }
    
    Usuario usuario = usuarioMapper.toEntity(dto);
    Usuario saved = usuarioRepository.save(usuario);
    return usuarioMapper.toResponseDTO(saved);
}

// Si hay error:
{
  "status": 409,
  "message": "Ya existe un registro con email 'juan@logisteia.com'",
  "error": "CONFLICT",
  "timestamp": "2026-05-12T10:30:45.123456",
  "path": "/api/v1/usuarios"
}
```

---

## 🧪 Casos de Prueba

### **Caso 1: Crear Usuario Válido**
```bash
curl -X POST "http://localhost:8080/api/v1/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "juan@logisteia.com",
    "nombre": "Juan Pérez",
    "contrase": "Pass1234!",
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO",
    "telefono": "666123456"
  }'

# Respuesta: 201 CREATED
{
  "dni": "12345678A",
  "email": "juan@logisteia.com",
  "nombre": "Juan Pérez",
  "rol": "JEFE_EQUIPO",
  "estado": "ACTIVO",
  "telefono": "666123456",
  "fechaRegistro": "2026-05-12T10:30:45.123456"
}
```

### **Caso 2: Email Inválido**
```bash
curl -X POST "http://localhost:8080/api/v1/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "email_invalido",  # ❌
    "nombre": "Juan",
    "contrase": "Pass1234!",
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO"
  }'

# Respuesta: 400 BAD REQUEST
{
  "status": 400,
  "message": "Errores de validación",
  "error": "VALIDATION_ERROR",
  "timestamp": "2026-05-12T10:30:45.123456",
  "path": "/api/v1/usuarios",
  "fieldErrors": [
    {
      "field": "email",
      "message": "El email debe ser válido"
    }
  ]
}
```

### **Caso 3: Email Duplicado**
```bash
# Intenta crear con email que ya existe
curl -X POST "http://localhost:8080/api/v1/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "87654321B",
    "email": "juan@logisteia.com",  # Email ya existe
    "nombre": "Otro Usuario",
    "contrase": "Pass1234!",
    "rol": "TRABAJADOR",
    "estado": "ACTIVO"
  }'

# Respuesta: 409 CONFLICT
{
  "status": 409,
  "message": "Ya existe un registro con email 'juan@logisteia.com'",
  "error": "CONFLICT",
  "timestamp": "2026-05-12T10:30:45.123456",
  "path": "/api/v1/usuarios"
}
```

### **Caso 4: Usuario No Encontrado**
```bash
curl "http://localhost:8080/api/v1/usuarios/NOTEXISTS"

# Respuesta: 404 NOT FOUND
{
  "status": 404,
  "message": "Usuario con DNI 'NOTEXISTS' no encontrado",
  "error": "NOT_FOUND",
  "timestamp": "2026-05-12T10:30:45.123456",
  "path": "/api/v1/usuarios/NOTEXISTS"
}
```

---

## ✅ LISTA DE VALIDACIÓN

Revisa que el GlobalExceptionHandler:

- ✅ Tiene `@RestControllerAdvice` (manejador global)
- ✅ Tiene 7 `@ExceptionHandler` métodos
- ✅ Devuelve respuestas JSON estructuradas
- ✅ Usa códigos HTTP correctos (404, 409, 400, 500)
- ✅ Incluye timestamp, path, error en respuestas
- ✅ Maneja validación con detalles de campo
- ✅ Registra excepciones (ex.printStackTrace())
- ✅ Extrae el path correctamente

---

## 🎯 RECOMENDACIONES

### **Mejoras Opcionales (no críticas)**

1. **Logging en lugar de printStackTrace()**
   ```java
   // Actualizar a:
   private static final Logger logger = LoggerFactory.getLogger(GlobalExceptionHandler.class);
   logger.error("Error interno", ex);
   ```

2. **Mensajes más específicos para DataIntegrityViolation**
   ```java
   // Analizar la causa más profundamente
   if (ex.getCause() instanceof SQLIntegrityConstraintViolationException) {
       // Procesar por tipo de constraint
   }
   ```

3. **Envolvimiento de excepciones BD**
   ```java
   // Convertir excepciones de BD a nuestras propias
   catch (DataIntegrityViolationException ex) {
       throw new DataIntegrityException(...);
   }
   ```

---

## 📋 CONCLUSIÓN

El **GlobalExceptionHandler está correctamente implementado** y maneja:

✅ 7 tipos de excepciones  
✅ Respuestas JSON consistentes  
✅ Códigos HTTP apropiados  
✅ Mensajes de error claros  
✅ Validación con detalles de campo  

**Está listo para producción.**

---

## 🚀 PRÓXIMOS PASOS

1. Revisa el código completo (está en tu proyecto)
2. Ejecuta los test cases
3. Aprueba o sugiere cambios
4. Procedemos con los 10 controllers restantes
