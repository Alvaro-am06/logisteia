# 🔐 EJEMPLOS CURL - FASE 3 (SPRING SECURITY + JWT)

## 1. REGISTRO DE USUARIO

### Crear un nuevo usuario
```bash
curl -X POST "http://localhost:8080/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "nome": "John Doe",
    "dni": "12345678A",
    "senha": "SecurePassword123!",
    "rol": "TRABAJADOR"
  }'
```

**Respuesta exitosa (201 CREATED):**
```json
{
  "token": "eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJlbWFpbCI6ImpvaG4uZG9lQGV4YW1wbGUuY29tIiwibm9tYnJlIjoiSm9obiBEb2UiLCJyb2wiOiJUUkFCQUpBRE9SIiwiZXN0YWRvIjoiQUNUSVZFIiwic3ViIjoiMTIzNDU2NzhhIiwiaWF0IjoxNjI4MzQ3NzAwLCJleHAiOjE2MjgyNjc3MDB9.abc123...",
  "email": "john.doe@example.com",
  "nome": "John Doe",
  "role": "TRABAJADOR",
  "expiresIn": 86400000
}
```

**Guardando el token:**
```bash
# En bash/shell
TOKEN="eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9..."

# O en PowerShell
$TOKEN="eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9..."
```

---

## 2. LOGIN

### Autenticarse con email y contraseña
```bash
curl -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "senha": "SecurePassword123!"
  }'
```

**Respuesta exitosa (200 OK):**
```json
{
  "token": "eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9.eyJlbWFpbCI6ImpvaG4uZG9lQGV4YW1wbGUuY29tIiwibm9tYnJlIjoiSm9obiBEb2UiLCJyb2wiOiJUUkFCQUpBRE9SIiwiZXN0YWRvIjoiQUNUSVZFIiwic3ViIjoiMTIzNDU2NzhhIiwiaWF0IjoxNjI4MzQ3NzAwLCJleHAiOjE2MjgyNjc3MDB9.abc123...",
  "email": "john.doe@example.com",
  "nome": "John Doe",
  "role": "TRABAJADOR",
  "expiresIn": 86400000
}
```

### Error: Email o contraseña incorrectos (400 BAD_REQUEST)
```bash
curl -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "nonexistent@example.com",
    "senha": "WrongPassword"
  }'
```

**Respuesta:**
```json
{
  "status": 400,
  "message": "Email o contraseña inválidos",
  "error": "BUSINESS_LOGIC_ERROR",
  "timestamp": "2024-05-12T10:30:00Z",
  "path": "/api/v1/auth/login"
}
```

---

## 3. USAR TOKEN EN PETICIONES PROTEGIDAS

### Variable con el token (bash/shell)
```bash
# Después de hacer login, guardar el token
TOKEN=$(curl -s -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john.doe@example.com",
    "senha": "SecurePassword123!"
  }' | jq -r '.token')

echo "Token: $TOKEN"
```

### Petición protegida con token válido
```bash
curl -X GET "http://localhost:8080/api/v1/equipos" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json"
```

**Respuesta (200 OK):**
```json
{
  "content": [
    {
      "id": 1,
      "nombre": "Backend Team",
      "descripcion": "Team for backend development",
      "jefeDni": "12345678A",
      "activo": true
    },
    {
      "id": 2,
      "nombre": "Frontend Team",
      "descripcion": "Team for frontend development",
      "jefeDni": "87654321B",
      "activo": true
    }
  ],
  "pageable": {
    "pageNumber": 0,
    "pageSize": 20,
    "totalElements": 2,
    "totalPages": 1
  }
}
```

---

## 4. ERRORES DE AUTENTICACIÓN

### Error: Sin token
```bash
curl -X GET "http://localhost:8080/api/v1/equipos"
```

**Respuesta (401 UNAUTHORIZED):**
```json
{
  "status": 401,
  "message": "Authentication required",
  "error": "UNAUTHORIZED",
  "timestamp": "2024-05-12T10:30:00Z",
  "path": "/api/v1/equipos"
}
```

### Error: Token mal formado
```bash
curl -X GET "http://localhost:8080/api/v1/equipos" \
  -H "Authorization: Bearer invalid-token-here"
```

**Respuesta (401 UNAUTHORIZED):**
```json
{
  "status": 401,
  "message": "Token inválido",
  "error": "UNAUTHORIZED",
  "timestamp": "2024-05-12T10:30:00Z",
  "path": "/api/v1/equipos"
}
```

### Error: Token expirado
```bash
# Si el token ha expirado (después de 24 horas)
curl -X GET "http://localhost:8080/api/v1/equipos" \
  -H "Authorization: Bearer eyJhbGciOiJIUzUxMiJ9...expiredToken..."
```

**Respuesta (401 UNAUTHORIZED):**
```json
{
  "status": 401,
  "message": "Token expirado",
  "error": "UNAUTHORIZED",
  "timestamp": "2024-05-12T10:30:00Z",
  "path": "/api/v1/equipos"
}
```

### Error: Rol insuficiente (futuro con @PreAuthorize)
```bash
# Si una ruta requiere JEFE_EQUIPO y el usuario es TRABAJADOR
curl -X DELETE "http://localhost:8080/api/v1/usuarios/12345678A" \
  -H "Authorization: Bearer $TOKEN"
```

**Respuesta (403 FORBIDDEN):**
```json
{
  "status": 403,
  "message": "Acceso denegado",
  "error": "FORBIDDEN",
  "timestamp": "2024-05-12T10:30:00Z",
  "path": "/api/v1/usuarios/12345678A"
}
```

---

## 5. OPERACIONES CRUD AUTENTICADAS

### Crear Equipo (autenticado)
```bash
curl -X POST "http://localhost:8080/api/v1/equipos" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "QA Team",
    "descripcion": "Quality Assurance team",
    "jefeDni": "12345678A",
    "activo": true
  }'
```

**Respuesta (201 CREATED):**
```json
{
  "id": 3,
  "nombre": "QA Team",
  "descripcion": "Quality Assurance team",
  "jefeDni": "12345678A",
  "activo": true
}
```

### Obtener Equipo por ID
```bash
curl -X GET "http://localhost:8080/api/v1/equipos/1" \
  -H "Authorization: Bearer $TOKEN"
```

**Respuesta (200 OK):**
```json
{
  "id": 1,
  "nombre": "Backend Team",
  "descripcion": "Team for backend development",
  "jefeDni": "12345678A",
  "activo": true
}
```

### Actualizar Equipo
```bash
curl -X PUT "http://localhost:8080/api/v1/equipos/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Backend Team - Updated",
    "descripcion": "Updated description",
    "jefeDni": "12345678A",
    "activo": true
  }'
```

**Respuesta (200 OK):**
```json
{
  "id": 1,
  "nombre": "Backend Team - Updated",
  "descripcion": "Updated description",
  "jefeDni": "12345678A",
  "activo": true
}
```

### Eliminar Equipo
```bash
curl -X DELETE "http://localhost:8080/api/v1/equipos/3" \
  -H "Authorization: Bearer $TOKEN"
```

**Respuesta (204 NO_CONTENT):**
```
(sin body)
```

---

## 6. FLUJO COMPLETO DE TESTING

### Paso 1: Registrar usuario
```bash
curl -X POST "http://localhost:8080/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "testuser@example.com",
    "nome": "Test User",
    "dni": "99999999Z",
    "senha": "TestPassword123",
    "rol": "TRABAJADOR"
  }'
```

### Paso 2: Hacer login (si el registro falló)
```bash
curl -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "testuser@example.com",
    "senha": "TestPassword123"
  }'
```

### Paso 3: Guardar el token
```bash
TOKEN="eyJhbGciOiJIUzUxMiJ9..."
```

### Paso 4: Crear un proyecto
```bash
curl -X POST "http://localhost:8080/api/v1/proyectos" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Test Project",
    "codigo": "TST001",
    "descripcion": "Testing project",
    "jefeDni": "12345678A",
    "clienteId": 1,
    "equipoId": 1,
    "estado": "ACTIVE"
  }'
```

### Paso 5: Obtener proyectos
```bash
curl -X GET "http://localhost:8080/api/v1/proyectos?page=0&size=20" \
  -H "Authorization: Bearer $TOKEN"
```

### Paso 6: Crear tarea en el proyecto
```bash
curl -X POST "http://localhost:8080/api/v1/tareas" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Implement API security",
    "descripcion": "Add JWT authentication to all endpoints",
    "proyectoId": 1,
    "trabajadorDni": "99999999Z",
    "estado": "TODO",
    "prioridad": "HIGH",
    "rol": "DEVELOPER"
  }'
```

### Paso 7: Actualizar tarea a DOING
```bash
curl -X PUT "http://localhost:8080/api/v1/tareas/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Implement API security",
    "descripcion": "Add JWT authentication to all endpoints",
    "proyectoId": 1,
    "trabajadorDni": "99999999Z",
    "estado": "DOING",
    "prioridad": "HIGH",
    "rol": "DEVELOPER"
  }'
```

### Paso 8: Listar tareas del proyecto
```bash
curl -X GET "http://localhost:8080/api/v1/tareas/proyecto/1" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 7. TESTING CON POSTMAN

### Configuración de Postman

**Variables de entorno:**
```
{
  "baseUrl": "http://localhost:8080",
  "token": ""
}
```

**Pre-request Script (en la colección o request):**
```javascript
// Si quieres renovar el token automáticamente
if (!pm.environment.get("token") || pm.environment.get("tokenExpired")) {
    const loginRequest = {
        url: pm.environment.get("baseUrl") + "/api/v1/auth/login",
        method: "POST",
        header: { "Content-Type": "application/json" },
        body: {
            mode: "raw",
            raw: JSON.stringify({
                email: "testuser@example.com",
                senha: "TestPassword123"
            })
        }
    };
    
    pm.sendRequest(loginRequest, function(err, response) {
        if (!err) {
            var data = response.json();
            pm.environment.set("token", data.token);
        }
    });
}
```

**Header en cada request:**
```
Authorization: Bearer {{token}}
```

---

## 8. TESTING CON INSOMNIA

### Pasos:
1. Crear carpeta "Logisteia API"
2. Crear request POST `/api/v1/auth/login`
3. En la pestaña "Response", copiar el `token`
4. Crear variable de entorno `TOKEN`
5. En cada request, agregar header: `Authorization: Bearer <TOKEN>`

---

## 9. VALIDACIÓN DE RESPUESTAS

### Header Authorization correcto:
```
Authorization: Bearer eyJhbGciOiJIUzUxMiIsInR5cCI6IkpXVCJ9...
```

### Estructura esperada del JWT (decodificado):
```json
// Header
{
  "alg": "HS512",
  "typ": "JWT"
}

// Payload
{
  "email": "john.doe@example.com",
  "nombre": "John Doe",
  "rol": "TRABAJADOR",
  "estado": "ACTIVE",
  "sub": "12345678A",
  "iat": 1628347700,
  "exp": 1628434100
}

// Signature
HMACSHA512(base64UrlEncode(header) + "." + base64UrlEncode(payload), secret)
```

---

## 🎯 CHECKLIST DE TESTING

- [ ] Registro de usuario exitoso (201)
- [ ] Login exitoso (200)
- [ ] Token se obtiene correctamente
- [ ] Petición protegida con token funciona (200)
- [ ] Petición sin token falla (401)
- [ ] Petición con token inválido falla (401)
- [ ] CRUD de Equipos funciona
- [ ] CRUD de Proyectos funciona
- [ ] CRUD de Tareas funciona
- [ ] Token se usa en múltiples peticiones
- [ ] Validación de campos en DTOs funciona
- [ ] Manejo de excepciones funciona

---

## 📝 NOTAS IMPORTANTES

1. **Token expira en 24 horas** - Después debe hacer login nuevamente
2. **Contraseña se codifica con BCrypt** - No se guarda en texto plano
3. **JWT contiene datos del usuario** - No consulta BD en cada petición
4. **HTTPS en producción** - Tokens solo se transmiten por HTTPS
5. **Secret en variables de entorno** - Nunca en código

---

**¡Ahora está listo para probar toda tu API REST con seguridad!**
