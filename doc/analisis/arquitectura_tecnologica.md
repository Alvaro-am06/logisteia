# Arquitectura Tecnológica de LOGISTEIA

## Resumen del sistema
LOGISTEIA es una aplicación web para la gestión de clientes, presupuestos, servicios y facturas. El sistema está desarrollado en pareja siguiendo la metodología Scrum.

---

## Diagrama de arquitectura (texto)
```
[Angular (Frontend)] ←→ [API REST PHP] ←→ [PHPMyAdmin/MySQL (Base de datos)]
```
- El usuario interactúa con la interfaz Angular.
- Angular realiza peticiones HTTP a la API desarrollada en PHP.
- La API PHP gestiona la lógica de negocio y accede a la base de datos MySQL mediante PHPMyAdmin.

---

## Tecnologías utilizadas
- **Frontend:** Angular
- **Backend:** PHP (XAMPP)
- **Base de datos:** MySQL gestionada con PHPMyAdmin
- **Servidor local:** XAMPP (Apache + PHP + MySQL)
- **API:** Endpoints PHP que exponen datos y operaciones para el frontend Angular
- **Metodología:** Scrum (trabajo colaborativo en pareja)

---

## Estructura de capas
- **Presentación:** Angular (SPA, componentes, servicios)
- **Lógica de negocio:** PHP (controladores, modelos, API REST)
- **Persistencia:** MySQL (tablas, relaciones, integridad referencial)

---

## Flujo de datos
1. El usuario accede a la aplicación Angular en el navegador.
2. Angular envía peticiones HTTP (GET, POST, PUT, DELETE) a la API PHP.
3. La API PHP procesa la petición, accede a la base de datos y devuelve la respuesta en formato JSON.
4. Angular muestra los datos al usuario y permite la interacción.

---

## Justificación tecnológica
- **Angular:** Permite crear una interfaz moderna, dinámica y escalable.
- **PHP + XAMPP:** Facilita el desarrollo y despliegue local del backend y la API.
- **MySQL + PHPMyAdmin:** Gestión sencilla y visual de la base de datos.
- **Scrum:** Favorece la organización, la colaboración y la entrega incremental.

---

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
