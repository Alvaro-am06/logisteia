# 🔧 Configuración de pom.xml para Spring Boot 3 + Logisteia

Si aún no tienes un proyecto Spring Boot 3 creado, aquí están todas las dependencias y configuraciones necesarias.

## Opción 1: Crear proyecto base (Recomendado)

Usa Spring Initializr: https://start.spring.io/

Configura así:
- **Project:** Maven Project
- **Language:** Java
- **Spring Boot:** 3.3.x (o 3.4.x más reciente)
- **Project Metadata:**
  - Group: com.logisteia
  - Artifact: backend
  - Name: Logisteia Backend
  - Description: API REST para Logisteia
  - Package name: com.logisteia.backend
  - Packaging: Jar
  - Java: 21

**Dependencies a agregar:**
- Spring Web
- Spring Data JPA
- MySQL Driver
- Lombok
- Spring Boot DevTools

## Opción 2: POM.xml Manual

```xml
<?xml version="1.0" encoding="UTF-8"?>
<project xmlns="http://maven.apache.org/POM/4.0.0"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://maven.apache.org/POM/4.0.0
         http://maven.apache.org/xsd/maven-4.0.0.xsd">
    <modelVersion>4.0.0</modelVersion>

    <parent>
        <groupId>org.springframework.boot</groupId>
        <artifactId>spring-boot-starter-parent</artifactId>
        <version>3.3.3</version> <!-- Cambiar a versión más reciente si existe -->
        <relativePath/> <!-- lookup parent from repository -->
    </parent>

    <groupId>com.logisteia</groupId>
    <artifactId>backend</artifactId>
    <version>1.0.0</version>
    <name>Logisteia Backend</name>
    <description>API REST para Logisteia</description>

    <properties>
        <java.version>21</java.version>
        <maven.compiler.source>21</maven.compiler.source>
        <maven.compiler.target>21</maven.compiler.target>
        <project.build.sourceEncoding>UTF-8</project.build.sourceEncoding>
    </properties>

    <dependencies>
        <!-- Spring Boot Starters -->
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-web</artifactId>
        </dependency>

        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-data-jpa</artifactId>
        </dependency>

        <!-- Database -->
        <dependency>
            <groupId>com.mysql</groupId>
            <artifactId>mysql-connector-j</artifactId>
            <version>8.0.33</version>
            <scope>runtime</scope>
        </dependency>

        <!-- Lombok -->
        <dependency>
            <groupId>org.projectlombok</groupId>
            <artifactId>lombok</artifactId>
            <optional>true</optional>
        </dependency>

        <!-- Jakarta Persistence API (ya incluida en JPA starter, pero explícito) -->
        <dependency>
            <groupId>jakarta.persistence</groupId>
            <artifactId>jakarta.persistence-api</artifactId>
        </dependency>

        <!-- Validation -->
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-validation</artifactId>
        </dependency>

        <!-- Development Tools -->
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-devtools</artifactId>
            <scope>runtime</scope>
            <optional>true</optional>
        </dependency>

        <!-- Testing (para Iteración 5) -->
        <dependency>
            <groupId>org.springframework.boot</groupId>
            <artifactId>spring-boot-starter-test</artifactId>
            <scope>test</scope>
        </dependency>

        <!-- Versión compatible de Jackson (JSON) -->
        <dependency>
            <groupId>com.fasterxml.jackson.datatype</groupId>
            <artifactId>jackson-datatype-jsr310</artifactId>
        </dependency>
    </dependencies>

    <build>
        <plugins>
            <plugin>
                <groupId>org.springframework.boot</groupId>
                <artifactId>spring-boot-maven-plugin</artifactId>
                <configuration>
                    <excludes>
                        <exclude>
                            <groupId>org.projectlombok</groupId>
                            <artifactId>lombok</artifactId>
                        </exclude>
                    </excludes>
                </configuration>
            </plugin>

            <!-- Compiler Plugin para Java 21 -->
            <plugin>
                <groupId>org.apache.maven.plugins</groupId>
                <artifactId>maven-compiler-plugin</artifactId>
                <version>3.11.0</version>
                <configuration>
                    <source>21</source>
                    <target>21</target>
                    <release>21</release>
                </configuration>
            </plugin>
        </plugins>
    </build>
</project>
```

## Estructura de Carpetas del Proyecto

Una vez creado con Maven, debería verse así:

```
logisteia-backend/
├── src/
│   ├── main/
│   │   ├── java/
│   │   │   └── com/
│   │   │       └── logisteia/
│   │   │           └── backend/
│   │   │               ├── LogisteiaApplication.java (Main)
│   │   │               ├── enums/
│   │   │               │   ├── UserRole.java
│   │   │               │   ├── UserStatus.java
│   │   │               │   ├── ProjectStatus.java
│   │   │               │   ├── TaskStatus.java
│   │   │               │   ├── TaskPriority.java
│   │   │               │   ├── TaskRole.java
│   │   │               │   ├── InvitationStatus.java
│   │   │               │   ├── BudgetStatus.java
│   │   │               │   ├── ServiceCategory.java
│   │   │               │   └── Unit.java
│   │   │               ├── entities/
│   │   │               │   ├── Usuario.java
│   │   │               │   ├── Equipo.java
│   │   │               │   ├── MiembroEquipo.java
│   │   │               │   ├── Cliente.java
│   │   │               │   ├── Proyecto.java
│   │   │               │   ├── Tarea.java
│   │   │               │   ├── Presupuesto.java
│   │   │               │   ├── DetallePresupuesto.java
│   │   │               │   ├── Servicio.java
│   │   │               │   ├── ServicioInformatica.java
│   │   │               │   ├── AccionAdministrativa.java
│   │   │               │   └── AsignacionProyecto.java
│   │   │               └── repositories/
│   │   │                   ├── UsuarioRepository.java
│   │   │                   ├── EquipoRepository.java
│   │   │                   ├── MiembroEquipoRepository.java
│   │   │                   ├── ClienteRepository.java
│   │   │                   ├── ProyectoRepository.java
│   │   │                   ├── TareaRepository.java
│   │   │                   ├── PresupuestoRepository.java
│   │   │                   ├── DetallePresupuestoRepository.java
│   │   │                   ├── ServicioRepository.java
│   │   │                   ├── ServicioInformaticaRepository.java
│   │   │                   ├── AccionAdministrativaRepository.java
│   │   │                   └── AsignacionProyectoRepository.java
│   │   └── resources/
│   │       └── application.yml (ya creado)
│   └── test/
│       └── java/
│           └── com/logisteia/backend/
│               └── LogisteiaApplicationTests.java
├── pom.xml
├── .mvn/
├── mvnw (wrapper para maven)
├── mvnw.cmd
└── README.md
```

## Archivo Principal de Aplicación (LogisteiaApplication.java)

```java
package com.logisteia.backend;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class LogisteiaApplication {

    public static void main(String[] args) {
        SpringApplication.run(LogisteiaApplication.class, args);
    }
}
```

## Ejecutar la Aplicación

```bash
# Con Maven
mvn spring-boot:run

# O si usas el wrapper
./mvnw spring-boot:run

# Compilar JAR
mvn clean package

# Ejecutar JAR
java -jar target/backend-1.0.0.jar
```

## Variables de Entorno (.env o VM options)

```bash
# Archivo .env (crear en raíz del proyecto)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=Logisteia
DB_USER=root
DB_PASS=tu_contraseña_mysql
SERVER_PORT=8080
CORS_ALLOWED_ORIGINS=http://localhost:4200,http://localhost:3000
```

O configurar en IDE (IntelliJ IDEA → Run → Edit Configurations):

```
VM options: 
-Dspring.datasource.url=jdbc:mysql://localhost:3306/Logisteia
-Dspring.datasource.username=root
-Dspring.datasource.password=tu_contraseña
```

## Verificación de Configuración

1. **Iniciar contenedores:**
   ```bash
   docker-compose up -d
   ```

2. **Verificar conexión a BD:**
   ```bash
   mysql -h localhost -u root -p Logisteia
   ```

3. **Ejecutar aplicación Spring Boot:**
   ```bash
   mvn spring-boot:run
   ```

4. **Verificar logs:**
   - Deberías ver logs como:
   ```
   Tomcat initialized with port(s): 8080 (http)
   Started LogisteiaApplication in X.XXX seconds
   ```

5. **Probar conexión:**
   ```
   GET http://localhost:8080/api/ (sin endpoint, debería dar 404 pero confirma que Spring funciona)
   ```

## Problemas Comunes

### Error: "Unable to determine a suitable JDBC Driver"
- **Solución:** Verifica que `mysql-connector-j` esté en pom.xml

### Error: "Access denied for user 'root'@'localhost'"
- **Solución:** Verifica `DB_PASS` correcta en `application.yml` o compose.yml

### Error: "No qualifying bean of type 'UsuarioRepository'"
- **Solución:** Asegúrate de que:
  - @SpringBootApplication esté en el package raíz
  - Los repositories estén en el package correcto
  - Tengas @EnableJpaRepositories si es necesario

### Puerto 8080 ya en uso
- **Solución:** Cambia `server.port: 9090` en application.yml

### Lombok no funciona
- **Solución:** 
  - IntelliJ: Settings → Plugins → Instalar Lombok
  - O: Settings → Build, Execution, Deployment → Compiler → Annotation Processors → Enable

## Próximos Pasos

Una vez verificado que todo funciona:
1. Copia los archivos de `enums/`, `entities/` y `repositories/` que ya creaste
2. Copia el archivo `application.yml` a `src/main/resources/`
3. Ejecuta `mvn clean install` para descargar dependencias
4. Intenta ejecutar la aplicación
5. Si ves los logs sin errores, ¡estás listo para la Iteración 2 (Controladores)!

---

**Nota:** Si tienes VS Code y el workspace de Logisteia, puedes copiar directamente los archivos que ya hemos generado a la estructura Maven correcta.
