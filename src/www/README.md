
# LOGISTEIA
# Flujo de datos del backend PHP (`src/www`)

---
**Autores:**
 - Álvaro Andrades Márquez
 - Fernando José Leva Rosa

## Estructura principal

```
src/www/
│
├── index.php
│
├── controladores/
│   ├── ControladorCliente.php
│   ├── ControladorDeAutenticacion.php
│   └── UsuarioControlador.php
│
├── modelos/
│   ├── AccionesAdministrativas.php
│   ├── Administrador.php
│   ├── Cliente.php
│   ├── ConexionBBDD.php
│   └── Usuarios.php
│
└── vistas/
    ├── panel_admin.php
    ├── plantilla.php
    └── usuarios.php
```

---

## Flujo general de los datos

1. **Entrada del usuario (Frontend o formulario HTML)**
   - El usuario accede a la aplicación web y realiza acciones como login, registro, gestión de usuarios/clientes, etc.
   - Los formularios envían datos a través de peticiones HTTP (GET/POST) a `index.php`.

2. **Procesamiento inicial**
   - `index.php`: Punto de entrada principal. Muestra el formulario de login y procesa el envío del mismo.
   - Si el usuario está autenticado, redirige al panel de administración.
   - Si se envía el formulario, incluye y utiliza el controlador de autenticación para validar credenciales.

3. **Controladores**
   - Los controladores reciben los datos del usuario y gestionan la lógica de negocio.
   - Ejemplo:
     - `ControladorDeAutenticacion.php`: Valida credenciales, inicia/cierra sesión.
     - `ControladorCliente.php`: CRUD de clientes, búsqueda y gestión.
     - `UsuarioControlador.php`: Gestión de usuarios y acciones administrativas.

4. **Modelos**
   - Los controladores interactúan con los modelos para acceder o modificar la base de datos.
   - Ejemplo:
     - `Administrador.php`, `Cliente.php`, `Usuarios.php`: Representan entidades y métodos para consultar/actualizar datos.
     - `AccionesAdministrativas.php`: Registra acciones administrativas.
     - `ConexionBBDD.php`: Gestiona la conexión PDO a la base de datos.

5. **Base de datos**
   - Los modelos ejecutan consultas SQL usando PDO y devuelven los resultados a los controladores.

6. **Vistas**
   - Los controladores seleccionan y cargan las vistas adecuadas (`panel_admin.php`, `usuarios.php`, `plantilla.php`).
   - Las vistas muestran los datos procesados al usuario, usando variables PHP y estructuras HTML.

7. **Salida al usuario**
   - El usuario recibe la respuesta en el navegador, ya sea una página HTML renderizada o un mensaje de estado.

---

## Ejemplo de flujo: Login de administrador

1. El usuario envía el formulario de login a `index.php`.
2. `index.php` llama a `ControladorDeAutenticacion.php` si se envió el formulario.
3. El controlador usa el modelo `Administrador.php` para validar credenciales.
4. Si el login es exitoso, se inicia la sesión y se redirige a la vista `panel_admin.php`.
5. La vista muestra los datos del administrador y opciones de gestión.

---

## Diagrama simplificado

```
[Usuario] → [index.php]
    ↓
[Controladores] → [Modelos] → [Base de datos]
    ↓
[Vistas]
    ↓
[Usuario]
```

---
