# 🔴 ANÁLISIS DEL FRONTEND - PROBLEMAS ENCONTRADOS

**Fecha**: 20 de mayo de 2026  
**Status**: ⚠️ **CRÍTICO - INCOMPATIBLE CON NUEVO BACKEND**  

---

## 📊 RESUMEN

El frontend está usando una arquitectura **TOTALMENTE INCOMPATIBLE** con el nuevo backend REST/JWT:

| Componente | Antiguo (PHP) | Nuevo (Spring Boot) | Status |
|-----------|----------------|-------------------|--------|
| **Endpoints** | `/api/usuarios.php` | `/api/v1/usuarios` | ❌ INCOMPATIBLE |
| **Auth** | Headers `X-User-*` | JWT Token | ❌ INCOMPATIBLE |
| **Login** | `/api/login.php` | `/api/v1/auth/login` | ❌ INCOMPATIBLE |
| **Response Format** | `{success, data, error}` | Spring Response | ⚠️ REQUIERE MAPEO |
| **Framework** | Angular 21 | Angular 21 | ✅ OK |
| **TypeScript** | TS 5.9 | TS 5.9 | ✅ OK |
| **Dependencies** | npm 10.9.3 | npm 10.9.3 | ✅ OK |

---

## 🔴 PROBLEMAS CRÍTICOS ENCONTRADOS

### 1️⃣ **Auth Service: Endpoint incorrecto**

**Archivo**: `src/frontend/src/app/services/auth.service.ts`

**Problema**:
```typescript
// ❌ INCORRECTO - Endpoint PHP antiguo
private apiUrl = `${environment.apiUrl}/api`;
login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/login.php`, credentials);
}
```

**Esperado** (Spring Boot):
```typescript
// ✅ CORRECTO - Endpoint REST nuevo
return this.http.post<LoginResponse>(`${this.apiUrl}/v1/auth/login`, credentials);
```

**Impacto**: 🔴 CRÍTICO - Frontend no puede autenticarse

---

### 2️⃣ **Auth Service: Campo incorrecto en LoginRequest**

**Problema**:
```typescript
export interface LoginRequest {
  email: string;
  password: string;  // ❌ INCORRECTO
}

// Backend espera:
// email: string
// senha: string (no password)
```

**Impacto**: 🔴 CRÍTICO - Login falla (campo 'password' no reconocido)

---

### 3️⃣ **Auth Service: Mecanismo de autenticación incompatible**

**Problema**:
```typescript
// Frontend: Usa localStorage con headers personalizados
private getAuthHeaders(): { [key: string]: string } {
    return {
        'X-User-DNI': userData.dni || '',
        'X-User-Rol': userData.rol || '',
        // ...
    };
}

// Backend: Usa JWT Bearer Token
// Authorization: Bearer <jwt-token>
```

**Impacto**: 🔴 CRÍTICO - Autenticación completamente rota

---

### 4️⃣ **Usuario Service: Endpoints con extensión .php**

**Archivo**: `src/frontend/src/app/services/usuario.service.ts`

**Problema**:
```typescript
// ❌ INCORRECTO - Endpoints PHP
private apiUrl = `${environment.apiUrl}/api/usuarios/usuarios.php`;
private historialUrl = `${environment.apiUrl}/api/usuarios/historial.php`;

// Backend espera:
// /api/v1/usuarios
// /api/v1/acciones-administrativas (historial)
```

**Impacto**: 🔴 CRÍTICO - No puede obtener listado de usuarios

---

### 5️⃣ **Todos los servicios: URLs con .php**

**Servicios afectados**:
- ❌ `auth.service.ts` - `/api/login.php`
- ❌ `usuario.service.ts` - `/api/usuarios/usuarios.php`
- ❌ `cliente.service.ts` - `/api/clientes.php`
- ❌ `proyecto.service.ts` - `/api/proyectos.php`
- ❌ `equipo.service.ts` - `/api/equipos.php`
- ❌ `historial.service.ts` - `/api/usuarios/historial.php`
- ❌ `equipo-trabajador.service.ts` - `/api/trabajadores.php`

**Impacto**: 🔴 CRÍTICO - Todos los servicios rotos

---

### 6️⃣ **Environment: URL de producción incorrecta para desarrollo**

**Archivo**: `src/frontend/src/environments/environment.ts`

**Problema**:
```typescript
export const environment = {
  production: true,
  apiUrl: 'https://api.logisteia.es',  // ❌ Dominio real (no existe)
  // ...
};
```

**Para desarrollo local** debería ser:
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8080',  // O dejar vacío si usa proxy
  // ...
};
```

**Impacto**: 🟠 MEDIO - Localhost fallará al intentar conectar

---

### 7️⃣ **Interfaz LoginResponse no coincide con backend**

**Frontend**:
```typescript
export interface LoginResponse {
  success: boolean;
  data?: { id: string; nombre: string; email: string };
  error?: string;
}
```

**Backend** (LoginResponseDTO):
```java
public record LoginResponseDTO(
    boolean success,
    String message,
    String token,
    UsuarioResponseDTO usuario
) {}
```

**Campos diferentes**:
- Frontend: `data` → Backend: `usuario` (de tipo UsuarioResponseDTO)
- Backend tiene: `token` (JWT), `message`

**Impacto**: 🔴 CRÍTICO - Mapeo incorrecto de respuesta

---

## 🎯 LISTA DE CAMBIOS NECESARIOS

| # | Archivo | Cambio | Prioridad |
|---|---------|--------|-----------|
| 1 | `auth.service.ts` | Cambiar endpoint a `/api/v1/auth/login` | 🔴 CRÍTICA |
| 2 | `auth.service.ts` | Cambiar `password` a `senha` | 🔴 CRÍTICA |
| 3 | `auth.service.ts` | Usar JWT token en lugar de headers X-User-* | 🔴 CRÍTICA |
| 4 | `auth.service.ts` | Mapear LoginResponse a LoginResponseDTO | 🔴 CRÍTICA |
| 5 | `usuario.service.ts` | Cambiar endpoints de .php a /api/v1/ | 🔴 CRÍTICA |
| 6 | `cliente.service.ts` | Cambiar endpoints de .php a /api/v1/ | 🔴 CRÍTICA |
| 7 | `proyecto.service.ts` | Cambiar endpoints de .php a /api/v1/ | 🔴 CRÍTICA |
| 8 | `equipo.service.ts` | Cambiar endpoints de .php a /api/v1/ | 🔴 CRÍTICA |
| 9 | `historial.service.ts` | Cambiar endpoints de .php a /api/v1/ | 🔴 CRÍTICA |
| 10 | `equipo-trabajador.service.ts` | Cambiar endpoints de .php a /api/v1/ | 🔴 CRÍTICA |
| 11 | `environment.ts` | Actualizar apiUrl para desarrollo | 🟠 MEDIA |
| 12 | `environment.*.ts` | Crear environment para desarrollo | 🟠 MEDIA |
| 13 | Todos los componentes | Actualizar mapeo de datos | 🟠 MEDIA |

---

## 📋 ENDPOINTS CORRECTOS DEL BACKEND

**Autenticación**:
- POST `/api/v1/auth/login` - Login
- POST `/api/v1/auth/register` - Registro

**Usuarios**:
- GET `/api/v1/usuarios` - Listar
- GET `/api/v1/usuarios/{id}` - Detalle
- POST `/api/v1/usuarios` - Crear
- PUT `/api/v1/usuarios/{id}` - Actualizar
- DELETE `/api/v1/usuarios/{id}` - Eliminar

**Clientes**:
- GET `/api/v1/clientes` - Listar
- GET `/api/v1/clientes/{id}` - Detalle
- POST `/api/v1/clientes` - Crear
- PUT `/api/v1/clientes/{id}` - Actualizar

**Proyectos**:
- GET `/api/v1/proyectos` - Listar
- GET `/api/v1/proyectos/{id}` - Detalle
- POST `/api/v1/proyectos` - Crear

**Equipos**:
- GET `/api/v1/equipos` - Listar
- POST `/api/v1/equipos` - Crear

---

## 🚨 CONSECUENCIAS ACTUALES

### Si intentas ejecutar el frontend ahora:

```
❌ npm install → OK (dependencias instalan)
❌ ng serve → OK (compila)
❌ http://localhost:4200 → OK (carga)
❌ Intenta login → FALLA (endpoint no existe)
❌ El resto de la app → NO FUNCIONA (endpoints .php no existen)
```

### Errores que verás en consola:

```
404 Not Found: POST http://localhost:8080/api/login.php
404 Not Found: GET http://localhost:8080/api/usuarios/usuarios.php
CORS Error (si no tienes proxy correcto)
```

---

## ✅ PLAN DE REPARACIÓN

### Fase 1: Core Services (1-2 horas)

1. **Auth Service** - Implementar JWT
   - Cambiar endpoint a `/api/v1/auth/login`
   - Usar `senha` en lugar de `password`
   - Guardar JWT token en localStorage
   - Implementar AuthInterceptor para inyectar JWT

2. **All Services** - Actualizar endpoints
   - Cambiar `.php` a `/api/v1/`
   - Actualizar respuestas

3. **Environment** - Configuración correcta

### Fase 2: Components (30 min)

1. Actualizar mapeo de datos en componentes
2. Actualizar forms para usar los DTOs correctos

### Fase 3: Testing (30 min)

1. Verificar login funciona
2. Verificar endpoints responden
3. Verificar JWT se envía en headers

---

## 📊 ESTADO ACTUAL

```
Frontend Framework: ✅ Actualizado (Angular 21)
Frontend Dependencies: ✅ Actualizados
Frontend Build: ✅ Compila sin errores
Frontend Runtime: ❌ COMPLETAMENTE ROTO
├─ Auth: ❌ No funciona
├─ API Calls: ❌ URLs .php no existen
├─ JWT: ❌ No implementado
└─ CORS: ✅ Reparado (pero no sirve sin auth)
```

---

## 🎯 RECOMENDACIÓN

**EL FRONTEND REQUIERE ACTUALIZACIÓN INMEDIATA**

No puedes usarlo hasta que no hagas estos cambios. La arquitectura anterior era PHP + Headers personalizados, la nueva es Spring Boot + JWT.

Esto es más que cambiar versiones - es cambiar el patrón de autenticación completamente.

**Tiempo estimado**: 2-3 horas si hace todos los cambios
