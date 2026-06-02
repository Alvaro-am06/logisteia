# 🔍 AUDITORÍA DE SEGURIDAD - LOGISTEIA BACKEND

**Fecha**: 20 de mayo de 2026  
**Estado**: ⚠️ **REQUIERE ATENCIÓN INMEDIATA**

---

## 📊 RESUMEN EJECUTIVO

| Área | Hallazgos | Severidad | Estado |
|------|-----------|-----------|--------|
| **CVEs** | 1 encontrado | 🔴 HIGH | ⚠️ Requiere parcheo |
| **CORS** | No configurado | 🟠 MEDIUM | ❌ Bloqueador |
| **Proxy Frontend** | Mal configurado | 🟠 MEDIUM | ❌ No funciona |
| **Incompatibilidades** | 2 encontradas | 🟠 MEDIUM | ⚠️ Requiere fix |
| **JWT** | Bien configurado | 🟢 LOW | ✅ OK |
| **Base de Datos** | Bien configurado | 🟢 LOW | ✅ OK |

---

## 🔴 PROBLEMAS CRÍTICOS

### 1. CVE EN MYSQL CONNECTOR

**Vulnerabilidad**: CVE-2023-22102  
**Dependencia**: `com.mysql:mysql-connector-j:8.0.33`  
**Severidad**: 🔴 **HIGH**  
**Descripción**: MySQL Connectors takeover vulnerability  

> Vulnerability in the MySQL Connectors product of Oracle MySQL (component: Connector/J). Supported versions that are affected are 8.1.0 and prior. Difficult to exploit vulnerability allows unauthenticated attacker with network access via multiple protocols to compromise MySQL Connectors.

**Impacto**: Riesgo de takeover de conexiones de BD  
**Solución**: Actualizar a versión 8.4.0 o superior

```xml
<!-- ANTES (vulnerable) -->
<dependency>
    <groupId>com.mysql</groupId>
    <artifactId>mysql-connector-j</artifactId>
    <!-- 8.0.33 - VULNERABLE -->
</dependency>

<!-- DESPUÉS (parchado) -->
<dependency>
    <groupId>com.mysql</groupId>
    <artifactId>mysql-connector-j</artifactId>
    <!-- Spring Boot 4.0.6 tiene 8.0.33, pero está deprecated -->
    <!-- Necesita actualización manual -->
</dependency>
```

---

### 2. ⚠️ CORS NO CONFIGURADO - FRONTEND BLOQUEADO

**Problema**: El frontend (Angular en localhost:4200) NO puede conectarse al backend  

**Evidencia**:
```
✓ CORS configurado EN application.yml
✗ CORS NO aplicado en SecurityConfig
✗ Frontend NO recibirá headers Access-Control-Allow-Origin
```

**Archivo problemático**: `src/main/java/com/logisteia/backend/config/SecurityConfig.java`

**Síntomas que verás**:
```
Error en consola del navegador:
Access to XMLHttpRequest at 'http://localhost:8080/api/v1/auth/login' 
from origin 'http://localhost:4200' has been blocked by CORS policy: 
No 'Access-Control-Allow-Origin' header is present on the requested resource.
```

**Solución necesaria**: Agregar WebConfig con CorsConfigurationSource

---

### 3. ❌ PROXY FRONTEND MAL CONFIGURADO

**Problema 1**: `src/frontend/proxy.conf.json` apunta a ruta incorrecta

```json
// ❌ INCORRECTO
{
  "/api/*": {
    "target": "http://localhost/logisteia/src/www",
    // ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
    // Esto NO es el backend - es un servidor web estático
    "secure": false,
    "changeOrigin": true
  }
}
```

**Problema 2**: `src/frontend/proxy.conf.js` apunta a puerto incorrecto

```js
// ❌ INCORRECTO
const PROXY_CONFIG = {
  "/api/*": {
    target: "http://localhost:8000",
    // Backend está en 8080, no 8000
```

**Solución necesaria**: Ambos deben apuntar a `http://localhost:8080`

---

## 🟠 PROBLEMAS DE COMPATIBILIDAD

### 4. Incompatibilidad: Java 25 vs sun.misc.Unsafe

**Advertencia en Maven**:
```
WARNING: A terminally deprecated method in sun.misc.Unsafe has been called
Called by: com.google.common.util.concurrent.AbstractFuture$UnsafeAtomicHelper
File: C:/maven/lib/guava-32.0.1-jre.jar
```

**Problema**: Guava 32.0.1 usa `sun.misc.Unsafe` que está deprecated en Java 25

**Impacto**: ⚠️ Funcionará ahora pero puede quebrar en Java 26+

**Solución**: Actualizar Guava a versión 33.x (compatible con Java 25)

Spring Boot BOM 4.0.6 incluye Guava 32.0.1. Se necesita actualización manual.

---

### 5. Incompatibilidad: Rutas API Inconsistentes

**Problema**: Diferentes versiones de endpoints en el código

```
SecurityConfig:
- /api/v1/auth/**  ← v1
- /api/v1/**       ← v1

Pero en los controllers puede haber:
- /api/auth/...    ← sin v1
- /api/usuarios/... ← sin v1

Esto causa rutas inconsistentes
```

**Verificar**: Todos los endpoints deberían tener `/api/v1/` como prefijo

---

## ✅ LO QUE ESTÁ BIEN

### JWT - Bien Configurado ✅
- Implementación correcta con JJWT 0.12.3
- Filter bien integrado en cadena de seguridad
- Stateless sessions correctamente configuradas

### Base de Datos - Bien Configurado ✅
- MySQL 8.0 compatible
- HikariCP pool configurado correctamente
- Conexión segura con SSL soportado

### Spring Boot - Actualizado ✅
- Version 4.0.6 (actual)
- Java 25 LTS compatible
- Tomcat 11.0.22 sin CVEs

---

## 🛠️ PLAN DE CORRECCIÓN

### PRIORIDAD 1: CRÍTICA (Hazte ahora)

#### A. Parchear CVE MySQL Connector
```xml
<!-- En pom.xml, actualizar mysql-connector-j -->
<mysql:mysql-connector-j version="8.4.0"/>
```

**Tiempo**: 5 minutos  
**Impacto**: Elimina vulnerabilidad de seguridad

---

#### B. Configurar CORS correctamente
Crear `WebConfig.java`:

```java
package com.logisteia.backend.config;

import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.cors.CorsConfiguration;
import org.springframework.web.cors.CorsConfigurationSource;
import org.springframework.web.cors.UrlBasedCorsConfigurationSource;

@Configuration
public class WebConfig {

    @Bean
    public CorsConfigurationSource corsConfigurationSource() {
        CorsConfiguration configuration = new CorsConfiguration();
        configuration.setAllowedOrigins(List.of(
            "http://localhost:4200",    // Angular dev
            "http://localhost:3000",    // Backup
            "http://localhost",         // Local
            "http://127.0.0.1:4200"
        ));
        configuration.setAllowedMethods(List.of(
            "GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS"
        ));
        configuration.setAllowedHeaders(List.of("*"));
        configuration.setAllowCredentials(true);
        configuration.setMaxAge(3600L);

        UrlBasedCorsConfigurationSource source = 
            new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);
        return source;
    }
}
```

Luego en `SecurityConfig.java` agregar:

```java
@Bean
public SecurityFilterChain filterChain(HttpSecurity http) throws Exception {
    http
        .cors(cors -> cors.configurationSource(corsConfigurationSource()))
        // ... resto de configuración
}
```

**Tiempo**: 15 minutos  
**Impacto**: Frontend podrá conectar al backend

---

#### C. Reparar Proxy del Frontend

**Opción 1**: Reparar `proxy.conf.json`
```json
{
  "/api/*": {
    "target": "http://localhost:8080",
    "secure": false,
    "changeOrigin": true,
    "pathRewrite": {
      "^/api": "/api"
    }
  }
}
```

**Opción 2**: Reparar `proxy.conf.js`
```js
const PROXY_CONFIG = {
  "/api/*": {
    target: "http://localhost:8080",  // ← Cambiar de 8000 a 8080
    secure: false,
    changeOrigin: true,
    logLevel: "debug"
  }
};

module.exports = PROXY_CONFIG;
```

**Tiempo**: 5 minutos  
**Impacto**: Proxy funcionará correctamente

---

### PRIORIDAD 2: ALTA (Hazte esta semana)

#### D. Actualizar Guava
```xml
<dependency>
    <groupId>com.google.guava</groupId>
    <artifactId>guava</artifactId>
    <version>33.0.0-jre</version>
</dependency>
```

**Tiempo**: 10 minutos  
**Impacto**: Elimina warnings de Java 25

---

#### E. Normalizar rutas API
Verificar todos los endpoints usen `/api/v1/` consistentemente

```bash
# Buscar en controllers
grep -r "@RequestMapping\|@PostMapping\|@GetMapping" src/main/java/com/logisteia/backend/controllers/
```

**Tiempo**: 20 minutos  
**Impacto**: API consistente

---

## 🧪 VERIFICACIÓN POST-FIX

### Test 1: CVE Arreglado
```bash
mvn clean test-compile
# Verificar sin warnings de sun.misc.Unsafe
```

### Test 2: CORS Funciona
```bash
# Desde el navegador en http://localhost:4200
fetch('http://localhost:8080/api/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({...})
})
// Debe funcionar sin error CORS
```

### Test 3: Proxy Funciona
```bash
# En carpeta frontend
ng serve
# Hacer login desde http://localhost:4200
# Debe conectar sin errores
```

---

## 📋 CHECKLIST DE CORRECCIÓN

- [ ] **Paso 1**: Actualizar mysql-connector-j a 8.4.0
- [ ] **Paso 2**: Crear WebConfig.java con CORS
- [ ] **Paso 3**: Agregar CORS bean a SecurityConfig
- [ ] **Paso 4**: Reparar proxy.conf.js (port 8080)
- [ ] **Paso 5**: Opcional: Reparar proxy.conf.json (8080)
- [ ] **Paso 6**: Actualizar Guava a 33.0.0-jre
- [ ] **Paso 7**: Normalizar rutas API (/api/v1/)
- [ ] **Paso 8**: `mvn clean test` (verificar 64/64)
- [ ] **Paso 9**: `ng serve` + probar login
- [ ] **Paso 10**: Verificar sin warnings en Maven

---

## 🎯 ORDEN DE EJECUCIÓN RECOMENDADO

```
1. CVE MySQL (5 min)      [INMEDIATO]
2. CORS Config (15 min)   [INMEDIATO]
3. Proxy Fix (5 min)      [INMEDIATO]
4. Guava Update (10 min)  [HOY]
5. API Routes (20 min)    [HOY]
6. Tests & Verify (10 min) [HOY]

Total: ~65 minutos para tener todo arreglado
```

---

## 📞 RESUMEN EJECUTIVO

| Problema | Severidad | Solución | Tiempo |
|----------|-----------|----------|--------|
| CVE MySQL | 🔴 HIGH | Actualizar a 8.4.0 | 5 min |
| CORS Blocked | 🔴 HIGH | Crear WebConfig | 15 min |
| Proxy Incorrecto | 🔴 HIGH | Cambiar puerto a 8080 | 5 min |
| Guava Deprecated | 🟠 MEDIUM | Actualizar a 33.0.0 | 10 min |
| API Routes | 🟠 MEDIUM | Normalizar /api/v1/ | 20 min |

**Total de problemas**: 5  
**Bloqueadores de producción**: 3 (CVE, CORS, Proxy)  
**Tiempo total de fixes**: ~65 minutos  

---

**STATUS**: ⚠️ El proyecto NO está listo para producción hasta que se hagan estos fixes.
