# Manual del Programador de LOGISTEIA

## Introducción
Este manual está dirigido a desarrolladores que deseen entender, mantener o ampliar el backend PHP del proyecto LOGISTEIA. Aquí se explica la arquitectura, el flujo de datos, la estructura de carpetas y las buenas prácticas utilizadas.

---

## Estructura del proyecto

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

- **index.php**: Punto de entrada principal. Gestiona el login y la redirección al panel de administración.
- **controladores/**: Lógica de negocio y gestión de peticiones del usuario.
- **modelos/**: Acceso y manipulación de datos en la base de datos.
- **vistas/**: Presentación de datos al usuario.

---

## Flujo de datos

1. El usuario interactúa con el formulario HTML en `index.php`.
2. Si envía el login, `index.php` procesa la petición y utiliza `ControladorDeAutenticacion.php` para validar credenciales.
3. Los controladores gestionan la lógica y llaman a los modelos para acceder a la base de datos.
4. Los modelos usan PDO y consultas preparadas para interactuar con la base de datos `Logisteia`.
5. Los resultados se muestran en las vistas correspondientes.

---

## Arquitectura y buenas prácticas
- **MVC**: Separación clara entre modelo, vista y controlador.
- **PDO y consultas preparadas**: Seguridad frente a inyecciones SQL.
- **Sanitización**: Uso de filtros modernos para datos de entrada y salida.
- **Rutas relativas**: Portabilidad y facilidad de despliegue.
- **Documentación PHPDoc**: Métodos y clases documentados para facilitar el mantenimiento.

---

## Base de datos
- El script `src/sql/bbdd.sql` crea la base de datos y todas las tablas necesarias.
- Las relaciones y claves foráneas están definidas para garantizar la integridad referencial.

---

## Autores
- Álvaro Andrades Márquez
- Fernando José Leva Rosa