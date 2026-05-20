# 🔧 PRÓXIMOS PASOS - DESPUÉS DE FASE 3

## ⚠️ ACCIÓN REQUERIDA ANTES DE COMPILAR

Ahora que Fase 3 está completada, necesitas hacer 2 cambios en la configuración:

---

## ✅ PASO 1: Actualizar `pom.xml` (Dependencias)

### Agregar estas dependencias en la sección `<dependencies>`:

```xml
<!-- Spring Security (incluye autenticación) -->
<dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-security</artifactId>
</dependency>

<!-- JJWT para JWT (3 módulos necesarios) -->
<dependency>
    <groupId>io.jsonwebtoken</groupId>
    <artifactId>jjwt-api</artifactId>
    <version>0.12.3</version>
</dependency>
<dependency>
    <groupId>io.jsonwebtoken</groupId>
    <artifactId>jjwt-impl</artifactId>
    <version>0.12.3</version>
    <scope>runtime</scope>
</dependency>
<dependency>
    <groupId>io.jsonwebtoken</groupId>
    <artifactId>jjwt-jackson</artifactId>
    <version>0.12.3</version>
    <scope>runtime</scope>
</dependency>
```

### Ubicación exacta en pom.xml:
```xml
<project>
    ...
    <dependencies>
        <!-- EXISTENTES -->
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-web</artifactId>
        </dependency>
        
        <!-- AGREGAR ABAJO -->
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-security</artifactId>
        </dependency>
        
        <dependency>
            <groupId>io.jsonwebtoken</groupId>
            <artifactId>jjwt-api</artifactId>
            <version>0.12.3</version>
        </dependency>
        <dependency>
            <groupId>io.jsonwebtoken</groupId>
            <artifactId>jjwt-impl</artifactId>
            <version>0.12.3</version>
            <scope>runtime</scope>
        </dependency>
        <dependency>
            <groupId>io.jsonwebtoken</groupId>
            <artifactId>jjwt-jackson</artifactId>
            <version>0.12.3</version>
            <scope>runtime</scope>
        </dependency>
        
        <!-- MÁS DEPENDENCIAS EXISTENTES... -->
    </dependencies>
</project>
```

---

## ✅ PASO 2: Actualizar `application.yml` (Configuración)

### Agregar sección JWT:

```yaml
# JWT Configuration
jwt:
  secret: mySecretKeyThatShouldBeVeryLongAndSecureInProductionEnvironment12345
  expiration: 86400000  # 24 horas en milisegundos

# Spring Security (mantener lo existente)
spring:
  datasource:
    url: jdbc:mysql://localhost:3306/Logisteia
    username: root
    password: 
    driver-class-name: com.mysql.cj.jdbc.Driver
  
  jpa:
    hibernate:
      ddl-auto: update
    properties:
      hibernate:
        dialect: org.hibernate.dialect.MySQL8Dialect
  
  jackson:
    default-property-inclusion: non_null
  
  # Agregar esto si no existe:
  web:
    cors:
      allowed-origins: http://localhost:4200
      allowed-methods: GET,POST,PUT,DELETE,OPTIONS
      allowed-headers: "*"
      allow-credentials: true
      max-age: 3600

# Agregar puerto
server:
  port: 8080
```

---

## 🔑 CONFIGURACIÓN SEGURA PARA PRODUCCIÓN

### ⚠️ IMPORTANTE: NO USAR VALORES HARDCODEADOS EN PRODUCCIÓN

En lugar de poner la clave secreta en el código:

### Opción 1: Variables de Entorno (RECOMENDADO)

**En Linux/Mac:**
```bash
export JWT_SECRET="your-very-long-random-secret-key-that-is-minimum-256-bits-long"
export JWT_EXPIRATION="86400000"
```

**En Windows (PowerShell):**
```powershell
$env:JWT_SECRET="your-very-long-random-secret-key"
$env:JWT_EXPIRATION="86400000"
```

**En application.yml:**
```yaml
jwt:
  secret: ${JWT_SECRET}
  expiration: ${JWT_EXPIRATION}
```

### Opción 2: Archivo .env (para desarrollo)

**Crear archivo `.env` en la raíz del proyecto:**
```
JWT_SECRET=mySecretKeyThatShouldBeVeryLongAndSecureInProductionEnvironment12345
JWT_EXPIRATION=86400000
DB_PASSWORD=yourDatabasePassword
```

**En application.yml:**
```yaml
jwt:
  secret: ${JWT_SECRET}
  expiration: ${JWT_EXPIRATION}
```

**Agregar a pom.xml:**
```xml
<dependency>
    <groupId>io.github.cdimascio</groupId>
    <artifactId>dotenv-java</artifactId>
    <version>3.0.0</version>
</dependency>
```

### Opción 3: Archivo de propiedades externo

**En `src/main/resources/application.yml`:**
```yaml
spring:
  config:
    import: optional:file:.env[.properties]

jwt:
  secret: ${JWT_SECRET:mySecretKeyForDevelopmentOnly}
  expiration: ${JWT_EXPIRATION:86400000}
```

---

## 🏗️ GENERADOR DE CLAVE SECRETA FUERTE

Para producción, necesitas una clave de mínimo 256 bits. Usa esto:

```bash
# En Linux/Mac:
openssl rand -hex 32

# En PowerShell:
[Convert]::ToBase64String((1..32|ForEach-Object{Get-Random -Maximum 256}))

# O online: https://www.allkeysgenerator.com/Random/Security-Encryption-Key-Generator.html
```

Resultado de ejemplo:
```
e7d3f4a1c2b8e9f0d1a2c3b4e5f6a7b8c9d0e1f2a3b4c5d6e7f8a9b0c1d2e3f
```

---

## 🧪 PASO 3: Compilar y Probar

### Compilar:
```bash
mvn clean compile
```

### Si hay errores, verificar:
- [ ] Todas las 9 clases de Fase 3 están creadas
- [ ] pom.xml tiene las 3 dependencias JJWT
- [ ] application.yml tiene la sección jwt
- [ ] No hay typos en nombres de clases

### Si compila correctamente:
```bash
mvn spring-boot:run
```

La aplicación debe iniciar sin errores.

---

## 🔐 PASO 4: Testing de Seguridad

### Test 1: Registro
```bash
curl -X POST "http://localhost:8080/api/v1/auth/register" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "testuser@example.com",
    "nome": "Test User",
    "dni": "12345678A",
    "senha": "TestPassword123",
    "rol": "TRABAJADOR"
  }'
```

**Esperado:** 201 CREATED con token

### Test 2: Login
```bash
curl -X POST "http://localhost:8080/api/v1/auth/login" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "testuser@example.com",
    "senha": "TestPassword123"
  }'
```

**Esperado:** 200 OK con token

### Test 3: Con token (funciona)
```bash
TOKEN="tu-token-aqui"

curl -X GET "http://localhost:8080/api/v1/equipos" \
  -H "Authorization: Bearer $TOKEN"
```

**Esperado:** 200 OK con lista de equipos

### Test 4: Sin token (falla)
```bash
curl -X GET "http://localhost:8080/api/v1/equipos"
```

**Esperado:** 401 UNAUTHORIZED

---

## 📊 CHECKLIST ANTES DE COMPILAR

- [ ] **pom.xml actualizado** con:
  - [ ] spring-boot-starter-security
  - [ ] jjwt-api
  - [ ] jjwt-impl
  - [ ] jjwt-jackson

- [ ] **application.yml actualizado** con:
  - [ ] jwt.secret
  - [ ] jwt.expiration

- [ ] **9 archivos Fase 3 creados:**
  - [ ] JwtService.java
  - [ ] CustomUserDetailsService.java
  - [ ] JwtAuthenticationFilter.java
  - [ ] SecurityConfig.java
  - [ ] AuthService.java
  - [ ] AuthController.java
  - [ ] LoginRequestDTO.java
  - [ ] RegisterRequestDTO.java
  - [ ] LoginResponseDTO.java

---

## ⚡ ORDEN DE EJECUCIÓN RECOMENDADO

```
1. Editar pom.xml
   ↓
2. Editar application.yml
   ↓
3. Ejecutar: mvn clean compile
   ↓
4. Si compila: mvn spring-boot:run
   ↓
5. Testing con cURL (4 tests)
   ↓
6. Integración con Angular
```

---

## 🚀 DESPUÉS DE VERIFICAR QUE FUNCIONA

Una vez que confirms que la seguridad funciona (los 4 tests pasan):

1. **Integración Frontend (Angular)**
   - Conectar login
   - Guardar token en localStorage
   - Usar token en peticiones

2. **Testing exhaustivo**
   - Todos los 76 endpoints con token
   - Error handling
   - Token expiration

3. **Deployment**
   - Docker
   - Kubernetes
   - Cloud (AWS, GCP, Azure)

---

## 📝 NOTAS IMPORTANTES

- **JWT_SECRET debe cambiar en producción** - No usar el valor de ejemplo
- **HTTPS es obligatorio en producción** - Tokens se envían en texto plano en HTTP
- **Token dura 24 horas** - Después el usuario debe hacer login nuevamente
- **BCrypt es lento** - Es intencional para prevenir ataques de fuerza bruta

---

## ❓ TROUBLESHOOTING

### Error: "Cannot find symbol: JwtService"
**Causa:** Archivo no creado  
**Solución:** Verifica que los 9 archivos estén en el lugar correcto

### Error: "No qualifying bean of type JwtService"
**Causa:** La clase no está anotada con @Service  
**Solución:** Verifica que tenga @Service en la clase JwtService

### Error: "jwt.secret not found"
**Causa:** application.yml no tiene la configuración  
**Solución:** Agrega la sección jwt en application.yml

### Error: "Cannot resolve symbol 'Jwts'"
**Causa:** JJWT no está en pom.xml  
**Solución:** Agrega las 3 dependencias JJWT en pom.xml

### Error: "401 UNAUTHORIZED en todas las rutas"
**Causa:** JwtAuthenticationFilter tiene un problema  
**Solución:** Verifica que extraiga correctamente el token del header

---

## 📚 REFERENCIAS

- Spring Security 6: https://spring.io/projects/spring-security
- JJWT: https://github.com/jwtk/jjwt
- BCrypt: https://github.com/patrickfav/bcrypt
- JWT.io: https://jwt.io/

---

## ✅ SUMA

Ya hiciste:
- ✅ Fase 1: Infrastructure
- ✅ Fase 2A: Exception Handling
- ✅ Fase 2B: Complete REST API (76 endpoints)
- ✅ Fase 3: Security + JWT (9 archivos)

Falta:
- ⏳ Actualizar pom.xml
- ⏳ Actualizar application.yml
- ⏳ Compilar y verificar

Después:
- ⏳ Testing
- ⏳ Angular integration
- ⏳ Deployment

**Tu backend está casi listo. Solo faltan estos 2 cambios de configuración.**

---

**¡Adelante!** Después de estos cambios, tendrás una API REST completamente securizada y lista para producción.
