# ⚡ VALIDACIÓN RÁPIDA DE FASE 2

## 📋 Checklist de 5 Minutos

Revisa estos puntos rápidamente:

### **1. Excepciones (30 segundos)**
- [ ] Abre: `src/main/java/com/logisteia/backend/exceptions/GlobalExceptionHandler.java`
- [ ] Verifica que tenga `@RestControllerAdvice`
- [ ] Cuenta 7 métodos `@ExceptionHandler`
- [ ] ✅ LISTO

### **2. DTOs (30 segundos)**
- [ ] Abre: `src/main/java/com/logisteia/backend/dtos/UsuarioResponseDTO.java`
- [ ] Verifica que sea `public record`
- [ ] Verifica que tenga `@NotNull` en los fields
- [ ] Abre: `src/main/java/com/logisteia/backend/dtos/UsuarioCreateUpdateDTO.java`
- [ ] Verifica que tenga validaciones (`@Email`, `@Size`, etc.)
- [ ] ✅ LISTO

### **3. Mappers (30 segundos)**
- [ ] Abre: `src/main/java/com/logisteia/backend/mappers/UsuarioMapper.java`
- [ ] Verifica que tenga `@Component`
- [ ] Verifica que tenga 3 métodos: `toResponseDTO()`, `toEntity()`, `updateEntityFromDTO()`
- [ ] ✅ LISTO

### **4. Servicios (30 segundos)**
- [ ] Abre: `src/main/java/com/logisteia/backend/services/UsuarioService.java`
- [ ] Verifica que tenga `@Service @RequiredArgsConstructor @Transactional`
- [ ] Verifica que tenga métodos CRUD (obtener, crear, actualizar, eliminar)
- [ ] ✅ LISTO

### **5. Controllers (1 minuto)**
- [ ] Abre: `src/main/java/com/logisteia/backend/controllers/UsuarioController.java`
- [ ] Verifica que tenga `@RestController @RequestMapping("/api/v1/usuarios")`
- [ ] Cuenta 6 métodos (GET, GET by email, GET paginated, POST, PUT, DELETE)
- [ ] Verifica que usen `ResponseEntity` con códigos HTTP
- [ ] ✅ LISTO

### **6. Documentación (1 minuto)**
- [ ] Verifica que existan estos 4 documentos:
  - [ ] `FASE2_REST_COMPLETADA.md` ✅
  - [ ] `EJEMPLOS_CURL_API.md` ✅
  - [ ] `PATRON_GENERAR_CONTROLLERS.md` ✅
  - [ ] `CODIGO_VALIDAR_FASE2.md` ✅

**Total: 5 minutos**

---

## 🧪 TEST RÁPIDO (3 pasos)

### **Paso 1: Iniciar aplicación**
```bash
# En terminal (desde raíz del proyecto)
mvn clean spring-boot:run

# O en IntelliJ:
# Click en play button en main class
```

### **Paso 2: Crear un usuario (cURL)**
```bash
curl -X POST "http://localhost:8080/api/v1/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "test@logisteia.com",
    "nombre": "Test User",
    "contrase": "Pass123!",
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO",
    "telefono": "666123456"
  }'

# Debe responder: 201 CREATED
```

### **Paso 3: Obtener usuario**
```bash
curl "http://localhost:8080/api/v1/usuarios/12345678A"

# Debe responder: 200 OK con los datos del usuario
```

**Si ambos responden correctamente → ✅ VALIDADO**

---

## 🔍 VALIDACIÓN DE ERRORES (2 pasos)

### **Test 1: Email inválido**
```bash
curl -X POST "http://localhost:8080/api/v1/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678B",
    "email": "correo_invalido",
    "nombre": "Test",
    "contrase": "Pass123!",
    "rol": "TRABAJADOR",
    "estado": "ACTIVO"
  }'

# Debe responder: 400 BAD REQUEST con fieldErrors
```

### **Test 2: Usuario no encontrado**
```bash
curl "http://localhost:8080/api/v1/usuarios/NOTEXISTS"

# Debe responder: 404 NOT_FOUND
```

**Si ambas responden correctamente → ✅ VALIDADO**

---

## 🎯 5 COSAS CRÍTICAS A REVISAR

| # | Qué | Dónde | ✅ |
|---|-----|-------|-----|
| 1 | GlobalExceptionHandler tiene `@RestControllerAdvice` | exceptions/ | [ ] |
| 2 | DTOs son `public record` | dtos/ | [ ] |
| 3 | Controllers usan `/api/v1/` | controllers/ | [ ] |
| 4 | Validación con `@Valid` en POST/PUT | controllers/ | [ ] |
| 5 | Códigos HTTP: 201 POST, 204 DELETE | controllers/ | [ ] |

---

## 🚨 PROBLEMAS COMUNES (Si algo falla)

### **Error: "No endpoint found"**
```
Causa: Controllers no están siendo detectados
Solución: 
  - Verifica que @RestController esté presente
  - Verifica que @RequestMapping esté correcto
  - Restart la aplicación
```

### **Error: "Validation failed"**
```
Causa: Los valores no cumplen validaciones
Solución:
  - Lee el error: dice qué field falla
  - Envía datos que cumplan las reglas (@Email, @Size, etc.)
```

### **Error: "404 Not Found"**
```
Causa 1: El usuario no existe
  Solución: Crear usuario primero
  
Causa 2: Ruta mal formada
  Solución: Verifica la URL exacta en el controller
```

### **Error: "409 Conflict"**
```
Causa: Email ya existe (duplicado)
Solución: Usa un email diferente
```

---

## 📊 VISTA RÁPIDA DE ARCHIVOS

**¿Dónde está cada cosa?**

| Necesito revisar | Archivo |
|------------------|---------|
| Manejo de errores | `GlobalExceptionHandler.java` |
| Validación de Email | `UsuarioCreateUpdateDTO.java` |
| Lógica de crear usuario | `UsuarioService.java` |
| Endpoint GET usuario | `UsuarioController.java` |
| Patrón a copiar | `PATRON_GENERAR_CONTROLLERS.md` |
| Ejemplos de cURL | `EJEMPLOS_CURL_API.md` |

---

## ✅ RESULTADO ESPERADO

Si todo está correcto, deberías ver:

```
✅ 20 archivos Java creados
✅ 4 documentos de guía
✅ 14 endpoints funcionando
✅ GlobalExceptionHandler manejando errores
✅ DTOs validando datos
✅ Services con lógica de negocio
✅ Controllers REST completamente funcionales
```

---

## 🎉 SIGUIENTES ACCIONES

Si **TODO está OK**:
```
→ Aprueba la Fase 2
→ Elige:
   Opción A: Generar 10 controllers más (Equipo, Cliente, etc.)
   Opción B: Pasar a Fase 3 (Spring Security + JWT)
```

Si **hay problemas**:
```
→ Comparte el error específico
→ Haremos debugging rápido
→ Ajustaremos el código
```

---

## 📞 RESUMEN FINAL

**Archivos creados:** 20 Java + 4 documentos  
**Endpoints listos:** 14 (Usuario 6 + Presupuesto 8)  
**Tiempo de validación:** 5 minutos  
**Estado:** ✅ LISTO PARA APROBAR  

---

Ahora **revisa y valida rápidamente**, y **cuéntame si todo está bien** para proceder con los siguientes pasos.

Si algo no se ve claro, **comparte una captura** del error y lo arreglamos en 2 minutos.
