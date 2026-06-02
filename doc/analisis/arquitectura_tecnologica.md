# Arquitectura Tecnológica de LOGISTEIA

## Resumen del sistema
LOGISTEIA es una aplicación web para la gestión de clientes, presupuestos, servicios y facturas. El sistema está desarrollado en pareja siguiendo la metodología Scrum.

---

## Diagrama de arquitectura (texto)
```
[Angular 21 (Frontend)] ←→ [Spring Boot 4.0.6 REST API] ←→ [MySQL 8.0 (Base de datos)]
                              (Java 25 LTS, JWT Auth)
                              (Tomcat 11.0.22)
```
- El usuario interactúa con la interfaz Angular compilada en el navegador.
- Angular realiza peticiones HTTP a la API REST desarrollada en Spring Boot.
- La API Spring Boot gestiona la lógica de negocio, valida JWT y accede a la base de datos MySQL mediante Spring Data JPA.
- Las respuestas se devuelven en formato JSON con código HTTP apropiado.

---

## Tecnologías utilizadas
- **Frontend:** Angular 21.0 con TypeScript 5.9.2 y Tailwind CSS
- **Backend:** Spring Boot 4.0.6 (Java 25 LTS)
- **Autenticación:** Spring Security 7.0.5 con JWT (JJWT 0.12.3)
- **Acceso a datos:** Spring Data JPA con Hibernate 7.2.12
- **Base de datos:** MySQL 8.0 con mysql-connector-j 8.4.0
- **Build tool:** Maven 3.9.6
- **Servidor embebido:** Tomcat 11.0.22
- **Testing:** JUnit 5 (64 unit tests)
- **Logging:** SLF4J con Logback
- **Containerización:** Docker y Docker Compose (opcional)
- **Metodología:** Scrum (trabajo colaborativo)

---

## Estructura de capas
- **Presentación:** Angular 21 (SPA, componentes, servicios HTTP, interceptores)
- **Lógica de negocio:** Spring Boot (controladores REST, servicios, mappers)
- **Acceso a datos:** Spring Data JPA con Hibernate (repositorios, entidades, anotaciones)
- **Persistencia:** MySQL 8.0 (tablas normalizadas, relaciones, índices, constraints)

---

## Flujo de datos
1. El usuario accede a la aplicación Angular (compilada como SPA).
2. El usuario realiza login: POST `/api/v1/auth/login` con {email, senha}
3. Spring Boot autentica y devuelve JWT token + datos de usuario
4. Angular almacena el token en localStorage
5. Para peticiones posteriores, AuthInterceptor agrega header: `Authorization: Bearer <token>`
6. Spring Security valida el JWT con JwtAuthenticationFilter
7. Si es válido, el controlador REST procesa la petición
8. El servicio ejecuta lógica de negocio y llama a repositorio
9. Spring Data JPA traduce a queries SQL y accede a MySQL
10. La respuesta se devuelve como JSON al frontend
11. Angular procesa la respuesta y actualiza la vista

---

## Justificación tecnológica
- **Angular 21:** Frontend moderno, escalable, con arquitectura de componentes y RxJS reactivo
- **Spring Boot 4.0.6:** Backend maduro, con soporte para Java 25, amplia comunidad, miles de librerías
- **Java 25 LTS:** Lenguaje fuertemente tipado, seguro, con excelente rendimiento y soporte a largo plazo
- **Spring Security:** Autenticación y autorización robustas, soporte nativo para JWT
- **Spring Data JPA:** ORM que elimina boilerplate SQL, relaciones automáticas, validaciones
- **MySQL 8.0:** Base de datos relacional estable, performante, con soporte para JSON
- **Maven:** Gestión reproducible de dependencias, plugins para build, test, deploy
- **JWT:** Autenticación stateless, escalable para microservicios y API REST
- **Docker:** Despliegue consistente en desarrollo, pruebas y producción
- **JUnit 5:** Tests modernos, parametrizados, con excelente soporte en IDEs

---

## Stack completo (2025)

### Frontend
```
Angular 21.0
├── TypeScript 5.9.2
├── Tailwind CSS 3.x
├── RxJS 7.8.0
├── Angular HttpClient
├── AuthInterceptor
└── npm 10.9.3
```

### Backend
```
Spring Boot 4.0.6
├── Java 25 LTS
├── Spring Security 7.0.5
├── Spring Data JPA
├── Hibernate 7.2.12
├── JJWT 0.12.3 (JWT)
├── Lombok
├── MapStruct
├── SLF4J/Logback
├── JUnit 5
└── Tomcat 11.0.22 (embedded)
```

### DevOps
```
Maven 3.9.6 (Build)
├── MySQL 8.0 (Database)
├── Docker (Containerization)
├── Docker Compose (Orchestration)
├── Git (Version Control)
└── Oracle Cloud (Production)
```

## Requisitos de infraestructura
- XAMPP instalado y configurado (Apache, PHP, MySQL)
- PHPMyAdmin accesible para la gestión de la base de datos
- Node.js y Angular CLI instalados para el desarrollo del frontend

---

## Escalabilidad y seguridad
- La arquitectura permite separar y escalar el frontend y el backend de forma independiente.
- Uso de API REST para comunicación segura y estructurada.
- Acceso a la base de datos mediante consultas preparadas y sanitización de datos.

---

**Autores:**
- Álvaro Andrades Márquez
- Fernando José Leva Rosa
