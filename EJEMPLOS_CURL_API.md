# 📡 Ejemplos de Uso - API REST Logisteia Fase 2

## 1. Variables de Entorno (para cURL)

```bash
# URL base de la API
BASE_URL="http://localhost:8080/api/v1"

# Usuarios de prueba
ADMIN_DNI="00000000A"
USER_DNI="12345678B"
```

## 2. USUARIOS - Ejemplos cURL

### 2.1 Crear Usuario

```bash
curl -X POST "$BASE_URL/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "juan.perez@logisteia.com",
    "nombre": "Juan Pérez García",
    "contrase": "SecurePass123!",
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO",
    "telefono": "666123456"
  }'
```

**Respuesta exitosa (201 Created):**
```json
{
  "dni": "12345678A",
  "email": "juan.perez@logisteia.com",
  "nombre": "Juan Pérez García",
  "rol": "JEFE_EQUIPO",
  "estado": "ACTIVO",
  "telefono": "666123456",
  "fechaRegistro": "2026-05-12T10:30:45.123456"
}
```

### 2.2 Obtener Usuario por DNI

```bash
curl -X GET "$BASE_URL/usuarios/12345678A"
```

**Respuesta (200 OK):**
```json
{
  "dni": "12345678A",
  "email": "juan.perez@logisteia.com",
  "nombre": "Juan Pérez García",
  "rol": "JEFE_EQUIPO",
  "estado": "ACTIVO",
  "telefono": "666123456",
  "fechaRegistro": "2026-05-12T10:30:45.123456"
}
```

### 2.3 Obtener Usuario por Email

```bash
curl -X GET "$BASE_URL/usuarios/email/juan.perez@logisteia.com"
```

### 2.4 Listar Todos los Usuarios (Paginado)

```bash
# Primera página, 10 resultados por página
curl -X GET "$BASE_URL/usuarios?page=0&size=10"

# Segunda página, 20 resultados por página
curl -X GET "$BASE_URL/usuarios?page=1&size=20"
```

**Respuesta:**
```json
{
  "content": [
    {
      "dni": "12345678A",
      "email": "juan.perez@logisteia.com",
      "nombre": "Juan Pérez García",
      "rol": "JEFE_EQUIPO",
      "estado": "ACTIVO",
      "telefono": "666123456",
      "fechaRegistro": "2026-05-12T10:30:45.123456"
    }
  ],
  "pageable": {
    "pageNumber": 0,
    "pageSize": 10,
    "sort": {
      "sorted": false,
      "empty": true,
      "unsorted": true
    },
    "offset": 0,
    "paged": true,
    "unpaged": false
  },
  "totalPages": 1,
  "totalElements": 1,
  "last": true,
  "size": 10,
  "number": 0,
  "sort": {
    "sorted": false,
    "empty": true,
    "unsorted": true
  },
  "first": true,
  "numberOfElements": 1,
  "empty": false
}
```

### 2.5 Actualizar Usuario

```bash
curl -X PUT "$BASE_URL/usuarios/12345678A" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "juan.perez.updated@logisteia.com",
    "nombre": "Juan Pérez García",
    "contrase": "NewPassword456!",
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO",
    "telefono": "666654321"
  }'
```

**Respuesta (200 OK):** Mismo formato que GET

### 2.6 Eliminar Usuario

```bash
curl -X DELETE "$BASE_URL/usuarios/12345678A"
```

**Respuesta (204 No Content):** Sin body

---

## 3. PRESUPUESTOS - Ejemplos cURL

### 3.1 Crear Presupuesto

```bash
curl -X POST "$BASE_URL/presupuestos" \
  -H "Content-Type: application/json" \
  -d '{
    "numeroPresupuesto": "PRE-2026-001",
    "usuarioDni": "12345678A",
    "proyectoId": 1,
    "clienteId": 5,
    "estado": "BORRADOR",
    "validezDias": 30,
    "total": "5000.00",
    "notas": "Presupuesto para desarrollo web completo"
  }'
```

**Respuesta exitosa (201 Created):**
```json
{
  "idPresupuesto": 1,
  "numeroPresupuesto": "PRE-2026-001",
  "estado": "BORRADOR",
  "validezDias": 30,
  "total": "5000.00",
  "notas": "Presupuesto para desarrollo web completo",
  "fechaCreacion": "2026-05-12T10:45:32.654321",
  "usuarioDni": "12345678A",
  "proyectoId": 1,
  "clienteId": 5
}
```

### 3.2 Obtener Presupuesto por ID

```bash
curl -X GET "$BASE_URL/presupuestos/1"
```

### 3.3 Obtener Presupuesto por Número

```bash
curl -X GET "$BASE_URL/presupuestos/numero/PRE-2026-001"
```

### 3.4 Listar Todos los Presupuestos

```bash
curl -X GET "$BASE_URL/presupuestos?page=0&size=20"
```

### 3.5 Listar Presupuestos de un Usuario

```bash
curl -X GET "$BASE_URL/presupuestos/usuario/12345678A"
```

**Respuesta:**
```json
[
  {
    "idPresupuesto": 1,
    "numeroPresupuesto": "PRE-2026-001",
    "estado": "BORRADOR",
    "validezDias": 30,
    "total": "5000.00",
    "notas": "Presupuesto para desarrollo web completo",
    "fechaCreacion": "2026-05-12T10:45:32.654321",
    "usuarioDni": "12345678A",
    "proyectoId": 1,
    "clienteId": 5
  }
]
```

### 3.6 Listar Presupuestos por Estado

```bash
# Estados válidos: BORRADOR, ENVIADO, APROBADO, RECHAZADO
curl -X GET "$BASE_URL/presupuestos/estado/APROBADO"
```

### 3.7 Actualizar Presupuesto

```bash
curl -X PUT "$BASE_URL/presupuestos/1" \
  -H "Content-Type: application/json" \
  -d '{
    "numeroPresupuesto": "PRE-2026-001",
    "usuarioDni": "12345678A",
    "proyectoId": 1,
    "clienteId": 5,
    "estado": "ENVIADO",
    "validezDias": 45,
    "total": "5200.00",
    "notas": "Presupuesto revisado y actualizado"
  }'
```

### 3.8 Eliminar Presupuesto

```bash
curl -X DELETE "$BASE_URL/presupuestos/1"
```

---

## 4. MANEJO DE ERRORES

### 4.1 Error 404 - Recurso No Encontrado

**Request:**
```bash
curl -X GET "$BASE_URL/usuarios/NOTEXISTS"
```

**Response (404 Not Found):**
```json
{
  "status": 404,
  "message": "Usuario con DNI 'NOTEXISTS' no encontrado",
  "error": "NOT_FOUND",
  "timestamp": "2026-05-12T10:50:15.234567",
  "path": "/api/v1/usuarios/NOTEXISTS"
}
```

### 4.2 Error 409 - Conflicto (Email Duplicado)

**Request:**
```bash
curl -X POST "$BASE_URL/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "87654321B",
    "email": "juan.perez@logisteia.com",  # Email ya existe
    "nombre": "Otro Usuario",
    "contrase": "Pass123!",
    "rol": "TRABAJADOR",
    "estado": "ACTIVO",
    "telefono": "666987654"
  }'
```

**Response (409 Conflict):**
```json
{
  "status": 409,
  "message": "Ya existe un registro con email 'juan.perez@logisteia.com'",
  "error": "CONFLICT",
  "timestamp": "2026-05-12T10:55:22.123456",
  "path": "/api/v1/usuarios"
}
```

### 4.3 Error 400 - Validación Fallida

**Request:**
```bash
curl -X POST "$BASE_URL/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "",  # Requerido
    "email": "invalid-email",  # Email inválido
    "nombre": "ab",  # Muy corto (min 3)
    "contrase": "123",  # Muy corta (min 6)
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO",
    "telefono": "666123456"
  }'
```

**Response (400 Bad Request):**
```json
{
  "status": 400,
  "message": "Errores de validación",
  "error": "VALIDATION_ERROR",
  "timestamp": "2026-05-12T11:00:10.456789",
  "path": "/api/v1/usuarios",
  "fieldErrors": [
    {
      "field": "dni",
      "message": "El DNI no puede estar vacío"
    },
    {
      "field": "email",
      "message": "El email debe ser válido"
    },
    {
      "field": "nombre",
      "message": "El nombre debe tener entre 3 y 255 caracteres"
    },
    {
      "field": "contrase",
      "message": "La contraseña debe tener al menos 6 caracteres"
    }
  ]
}
```

### 4.4 Error 500 - Error Interno

**Response (500 Internal Server Error):**
```json
{
  "status": 500,
  "message": "Error interno del servidor",
  "error": "INTERNAL_SERVER_ERROR",
  "timestamp": "2026-05-12T11:05:30.789012",
  "path": "/api/v1/usuarios"
}
```

---

## 5. Scripts de Prueba Completos

### 5.1 Test Suite Usuarios (Bash)

```bash
#!/bin/bash

BASE_URL="http://localhost:8080/api/v1"

echo "=== PRUEBAS DE USUARIOS ==="

# Crear usuario
echo "1. Crear usuario..."
RESPONSE=$(curl -s -X POST "$BASE_URL/usuarios" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "test@logisteia.com",
    "nombre": "Test User",
    "contrase": "TestPass123!",
    "rol": "TRABAJADOR",
    "estado": "ACTIVO",
    "telefono": "666123456"
  }')
echo "$RESPONSE" | jq .

# Obtener usuario
echo -e "\n2. Obtener usuario..."
curl -s -X GET "$BASE_URL/usuarios/12345678A" | jq .

# Listar usuarios
echo -e "\n3. Listar usuarios..."
curl -s -X GET "$BASE_URL/usuarios?page=0&size=10" | jq .

# Actualizar usuario
echo -e "\n4. Actualizar usuario..."
curl -s -X PUT "$BASE_URL/usuarios/12345678A" \
  -H "Content-Type: application/json" \
  -d '{
    "dni": "12345678A",
    "email": "test@logisteia.com",
    "nombre": "Test User Updated",
    "contrase": "TestPass123!",
    "rol": "JEFE_EQUIPO",
    "estado": "ACTIVO",
    "telefono": "666654321"
  }' | jq .

# Eliminar usuario
echo -e "\n5. Eliminar usuario..."
curl -s -X DELETE "$BASE_URL/usuarios/12345678A" -w "\nStatus: %{http_code}\n"
```

---

## 6. Uso con Postman/Insomnia

### 6.1 Variables de Entorno en Postman

Crear una environment con:
```
{
  "base_url": "http://localhost:8080/api/v1",
  "admin_dni": "00000000A",
  "user_dni": "12345678A"
}
```

### 6.2 Request de ejemplo en Postman

```
GET {{base_url}}/usuarios/{{user_dni}}
```

---

## 7. Headers y Autenticación (Fase 3)

En la Fase 3 (Seguridad), todos los endpoints necesitarán:

```bash
curl -X GET "$BASE_URL/usuarios/12345678A" \
  -H "Authorization: Bearer <JWT_TOKEN>" \
  -H "Content-Type: application/json"
```

---

## 8. Resumen de Códigos HTTP

| Código | Significado | Cuándo ocurre |
|--------|------------|--------------|
| 200 | OK | GET, PUT exitosos |
| 201 | Created | POST exitoso |
| 204 | No Content | DELETE exitoso |
| 400 | Bad Request | Validación fallida |
| 404 | Not Found | Recurso no existe |
| 409 | Conflict | Violación de unique constraint |
| 500 | Internal Error | Error no manejado |

---

## 9. Testing con cURL + jq

Instalar jq:
```bash
# macOS
brew install jq

# Ubuntu/Debian
sudo apt-get install jq

# Windows (con Chocolatey)
choco install jq
```

Luego usar:
```bash
curl -s "$BASE_URL/usuarios" | jq '.content[] | {dni, email, nombre}'
```

---

**Próximos pasos:** En la Fase 3 agregaremos autenticación JWT y autorización.
