# HU-04: Login para Registrar Clientes - COMPLETADA

**Estado**: ✅ COMPLETADA  
**Fecha de finalización**: 2024  
**Prioridad**: ALTA  
**Puntos de historia**: 5

---

## Descripción

Como administrador del sistema, necesito poder registrar nuevos clientes desde el panel de administración después de autenticarme, para mantener una base de datos actualizada de clientes que puedan solicitar presupuestos.

---

## Criterios de Aceptación

✅ **CA-01**: El administrador accede al sistema mediante usuario y contraseña válidos  
- Login existente validando credenciales contra la base de datos
- Sesiones seguras con verificación de rol de administrador

✅ **CA-02**: El sistema valida las credenciales y muestra mensajes de error claros  
- Validación de campos requeridos en el formulario
- Mensajes de error específicos (DNI duplicado, email inválido, etc.)
- Validación de formato de DNI (8 números + letra mayúscula)

✅ **CA-03**: Tras el login, el administrador puede registrar nuevos clientes  
- Botón "Registrar Cliente" en el sidebar del panel admin
- Formulario completo con validación frontend y backend
- Campos: DNI, nombre, email, teléfono, contraseña

✅ **CA-04**: Los datos de los clientes se guardan correctamente en la base de datos  
- API REST completo (`/api/clientes.php`) con operaciones CRUD
- Contraseñas hasheadas con `password_hash()`
- Registro de acciones administrativas en historial

✅ **CA-05**: El sistema cumple con requisitos de seguridad básicos  
- Contraseñas cifradas con PASSWORD_DEFAULT
- Validación de formato en frontend y backend
- Headers CORS configurados correctamente
- Protección contra duplicados (DNI y email únicos)

---

## Implementación Técnica

### Backend (PHP)

#### 1. API REST de Clientes (`/src/www/api/clientes.php`)
```php
Endpoints implementados:
- GET     → Listar todos los clientes o uno específico (por DNI)
- POST    → Crear nuevo cliente con validaciones
- PUT     → Actualizar cliente existente
- DELETE  → Eliminar cliente (eliminación física)

Validaciones:
✓ Formato DNI: 8 números + letra mayúscula
✓ Email válido con filter_var()
✓ Contraseña mínima 6 caracteres
✓ Verificación de duplicados (DNI y email)
✓ Sanitización de datos con trim()
```

#### 2. Modelo Cliente (`/src/www/modelos/Cliente.php`)
Métodos utilizados:
- `crear()`: Inserta nuevo cliente con rol 'registrado'
- `obtenerTodos()`: Lista todos los clientes
- `obtenerPorDni($dni)`: Obtiene un cliente específico
- `obtenerPorEmail($email)`: Verifica existencia por email
- `actualizar()`: Actualiza datos del cliente
- `eliminar($dni)`: Elimina cliente de la BD

#### 3. Registro de Acciones Administrativas
```php
// Se registra automáticamente en cada operación:
- Registro de cliente
- Actualización de cliente  
- Eliminación de cliente

// Incluye: usuario admin, tipo acción, descripción, timestamp
```

### Frontend (Angular 18+)

#### 1. Componente RegistrarCliente
**Ubicación**: `/src/frontend/src/app/components/registrar-cliente/`

**Archivos**:
- `registrar-cliente.component.ts`: Lógica de validación y registro
- `registrar-cliente.component.html`: Formulario responsive
- `registrar-cliente.component.scss`: Estilos con TailwindCSS

**Características**:
```typescript
✓ Validación en tiempo real
✓ Mensajes de error específicos
✓ Auto-redirección tras registro exitoso
✓ Loader durante el guardado
✓ Limpieza de formulario tras éxito
```

**Validaciones Frontend**:
- DNI: Regex `/^\d{8}[A-Za-z]$/`
- Email: Regex `/^[^\s@]+@[^\s@]+\.[^\s@]+$/`
- Todos los campos obligatorios

#### 2. Componente Clientes (Listado)
**Ubicación**: `/src/frontend/src/app/components/clientes/`

**Funcionalidades**:
```typescript
✓ Listado completo de clientes
✓ Botón "Actualizar" para recargar datos
✓ Botón "Nuevo Cliente" que redirige al formulario
✓ Botón "Eliminar" por cada cliente
✓ Confirmación antes de eliminar
✓ Mensajes de éxito/error
✓ Estado de carga visual (spinner)
✓ Diseño responsive con TailwindCSS
```

#### 3. Servicio ClienteService
**Ubicación**: `/src/frontend/src/app/services/cliente.service.ts`

**Métodos implementados**:
```typescript
- getClientes(): Observable<ApiResponse<Cliente[]>>
- getCliente(dni): Observable<ApiResponse<Cliente>>
- crearCliente(cliente): Observable<ApiResponse<Cliente>>
- actualizarCliente(dni, datos): Observable<ApiResponse<Cliente>>
- eliminarCliente(dni): Observable<ApiResponse<any>>
```

**Interfaces**:
```typescript
Cliente {
  dni: string;
  nombre: string;
  email: string;
  telefono: string;
  fecha_registro?: string;
}

ClienteRegistro extends Cliente {
  password: string;
}
```

#### 4. Integración en Panel Admin
**Cambios en**: `/src/frontend/src/app/panel-admin/panel-admin.html`

Menú actualizado:
```html
✓ Usuarios → /usuarios
✓ Registrar Cliente → /registrar-cliente (NUEVO)
✓ Clientes → /clientes (ACTUALIZADO)
✓ Dashboard, Pedidos, Reportes
```

#### 5. Rutas
**Archivo**: `/src/frontend/src/app/app.routes.ts`

Rutas añadidas:
```typescript
{ path: 'registrar-cliente', component: RegistrarClienteComponent }
{ path: 'clientes', component: ClientesComponent }
```

---

## Flujo de Uso

### 1. Acceso desde Panel Admin
```
Panel Admin → Sidebar → Click "Registrar Cliente"
↓
Navega a /registrar-cliente
```

### 2. Registro de Cliente
```
Formulario de Registro
↓
Completa: DNI, Nombre, Email, Teléfono, Contraseña
↓
Click "Registrar Cliente"
↓
Validación Frontend (formato, campos requeridos)
↓
Envío a API POST /clientes.php
↓
Validación Backend (duplicados, formato, seguridad)
↓
Hasheo de contraseña + Inserción BD
↓
Registro de acción administrativa
↓
Respuesta exitosa al frontend
↓
Mensaje de éxito + Redirección a /panel-admin
```

### 3. Listado y Gestión
```
Panel Admin → Sidebar → Click "Clientes"
↓
GET /clientes.php → Lista completa
↓
Tabla con todos los clientes
↓
Opciones: Actualizar lista, Eliminar cliente individual
```

---

## Seguridad Implementada

### Frontend
- ✅ Validación de formato de datos
- ✅ Sanitización de inputs
- ✅ Confirmación antes de eliminar

### Backend
- ✅ Contraseñas hasheadas (PASSWORD_DEFAULT)
- ✅ Validación de duplicados (DNI y email únicos)
- ✅ Headers CORS configurados
- ✅ Preparación de statements SQL (PDO)
- ✅ Registro de acciones administrativas
- ✅ Validación de método HTTP
- ✅ Respuestas HTTP apropiadas (201, 400, 404, 409, 500)

---

## Testing Manual Realizado

### ✅ Casos de Prueba Exitosos
1. **Registro con datos válidos**
   - DNI: 12345678A
   - Resultado: Cliente creado correctamente

2. **Validación de DNI duplicado**
   - Intento de registro con DNI existente
   - Resultado: Error 409 "Ya existe un cliente con este DNI"

3. **Validación de email duplicado**
   - Intento de registro con email existente
   - Resultado: Error 409 "Ya existe un cliente con este email"

4. **Validación de formato DNI**
   - DNI: 1234567 (sin letra)
   - Resultado: Error frontend y backend

5. **Validación de email**
   - Email: usuario@invalido
   - Resultado: Error "Formato de email inválido"

6. **Listado de clientes**
   - Acceso a /clientes
   - Resultado: Tabla completa con todos los clientes

7. **Eliminación de cliente**
   - Click en icono eliminar + confirmación
   - Resultado: Cliente eliminado, lista actualizada

---

## Archivos Creados/Modificados

### Nuevos Archivos
```
✓ src/www/api/clientes.php
✓ src/frontend/src/app/components/registrar-cliente/
  - registrar-cliente.component.ts
  - registrar-cliente.component.html
  - registrar-cliente.component.scss
```

### Archivos Modificados
```
✓ src/frontend/src/app/app.routes.ts
✓ src/frontend/src/app/panel-admin/panel-admin.html
✓ src/frontend/src/app/services/cliente.service.ts
✓ src/frontend/src/app/components/clientes/clientes.component.ts
✓ src/frontend/src/app/components/clientes/clientes.component.html
```

### Archivos Existentes Utilizados
```
✓ src/www/modelos/Cliente.php (sin cambios, ya tenía todos los métodos)
✓ src/www/modelos/AccionesAdministrativas.php
✓ src/www/modelos/ConexionBBDD.php
```

---

## Mejoras Futuras (Backlog)

1. **Validación de letra del DNI**
   - Implementar algoritmo de validación de letra DNI español

2. **Edición de clientes**
   - Formulario de edición inline en la tabla
   - Modal de edición completo

3. **Búsqueda y filtros**
   - Barra de búsqueda por nombre, DNI, email
   - Filtros por fecha de registro

4. **Paginación**
   - Implementar paginación para listas largas

5. **Importación masiva**
   - Subida de CSV con múltiples clientes

6. **Exportación**
   - Descargar lista de clientes en Excel/PDF

---

## Conclusión

La **HU-04** ha sido completada exitosamente cumpliendo con todos los criterios de aceptación. El administrador ahora puede:

1. ✅ Acceder de forma segura al sistema
2. ✅ Registrar nuevos clientes con validaciones robustas
3. ✅ Listar todos los clientes registrados
4. ✅ Eliminar clientes cuando sea necesario
5. ✅ Ver un historial completo de acciones administrativas

El sistema garantiza la seguridad mediante:
- Contraseñas hasheadas
- Validaciones frontend y backend
- Prevención de duplicados
- Registro de auditoría

La interfaz es intuitiva, responsive y sigue los principios de diseño del proyecto (TailwindCSS + Bootstrap Icons).

---

**Desarrollado por**: GitHub Copilot  
**Revisado**: ✅  
**Estado final**: PRODUCTION READY
