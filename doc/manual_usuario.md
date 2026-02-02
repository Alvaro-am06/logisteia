# Manual de Usuario de LOGISTEIA

## ¿Qué es LOGISTEIA?

LOGISTEIA es una plataforma web moderna y completa para la gestión integral de proyectos empresariales. El sistema permite gestionar equipos de trabajo, proyectos, clientes, presupuestos, facturación y control de horas trabajadas. Diseñado con una interfaz intuitiva y responsive, funciona perfectamente en ordenadores, tablets y móviles.

### Características principales

- Gestión de equipos de trabajo con roles diferenciados
- Sistema de proyectos con asignación de trabajadores
- Cronómetro de registro de horas trabajadas
- Generador de presupuestos con wizard guiado
- Gestión de clientes y facturas
- Panel de moderador con estadísticas globales
- Autenticación segura con JWT
- Login con Google OAuth
- Sistema de invitaciones por email
- Diseño responsive (adaptable a cualquier dispositivo)

---

## Acceso al sistema

### Primera vez - Registro

1. Abre tu navegador y accede a la URL de LOGISTEIA:
   - Producción: https://logisteia.com
   - Desarrollo: http://localhost

2. En la pantalla de login, haz clic en "Regístrate aquí"

3. Completa el formulario de registro:
   - DNI (con letra)
   - Nombre completo
   - Email
   - Contraseña
   - Teléfono (opcional)

4. Haz clic en "Registrarse"

5. Una vez registrado, podrás iniciar sesión con tu email y contraseña

### Login con Email y Contraseña

1. Introduce tu email y contraseña
2. Haz clic en "Iniciar Sesión"
3. Si los datos son correctos, accederás a tu panel según tu rol

### Login con Google

1. En la pantalla de login, haz clic en el botón "Iniciar sesión con Google"
2. Selecciona tu cuenta de Google
3. Autoriza el acceso a LOGISTEIA
4. Si es tu primera vez, se creará automáticamente tu cuenta
5. Serás redirigido a tu panel de usuario

### Recuperación de contraseña

Si olvidaste tu contraseña:
1. Contacta con tu jefe de equipo o moderador del sistema
2. Ellos podrán ayudarte a restablecer tu acceso
3. Alternativamente, puedes usar "Iniciar sesión con Google" si tu cuenta está asociada

---

## Roles de usuario

LOGISTEIA cuenta con tres roles principales, cada uno con funcionalidades específicas:

### Trabajador

Rol básico asignado por defecto al registrarse. Los trabajadores:

- Pertenecen a un equipo liderado por un jefe
- Pueden ver y gestionar proyectos asignados
- Registran horas trabajadas en proyectos
- Actualizan su perfil personal
- No pueden crear proyectos ni gestionar equipos

### Jefe de Equipo

Rol con permisos de gestión. Los jefes de equipo pueden:

- Crear y gestionar su equipo de trabajadores
- Invitar trabajadores al equipo mediante email
- Crear y gestionar proyectos
- Asignar trabajadores a proyectos
- Gestionar clientes
- Crear presupuestos con el wizard guiado
- Exportar presupuestos a PDF
- Enviar presupuestos por email
- Ver estadísticas de su equipo
- Gestionar facturas y pagos

### Moderador

Rol administrativo con acceso global. Los moderadores pueden:

- Ver estadísticas de todo el sistema
- Gestionar todos los usuarios
- Banear/desbanear usuarios
- Ver historial de baneos
- Supervisar todos los proyectos
- Acceder al historial de acciones administrativas
- No crear contenido, solo supervisar

---

## Panel de Trabajador

### Vista principal

Al iniciar sesión como trabajador verás:

**Información de bienvenida**
- Tu nombre
- Equipo al que perteneces
- Jefe de equipo asignado

**Tarjetas de resumen**
- Proyectos creados: Número total de proyectos
- Proyectos completados: Proyectos finalizados
- Información del jefe de equipo

**Información del equipo**
- Nombre del equipo
- Tu rol en el proyecto

### Menú de navegación (Sidebar)

- **Inicio**: Dashboard principal
- **Mis Proyectos**: Ver proyectos asignados
- **Mi Equipo**: Ver información del equipo y compañeros
- **Perfil**: Actualizar datos personales

### Gestión de proyectos

1. Haz clic en "Mis Proyectos" en el menú lateral
2. Verás una lista de proyectos en los que participas
3. Puedes filtrar por estado: Todos, Creados, En proceso, Finalizados
4. Cada proyecto muestra:
   - Código y nombre
   - Cliente asociado
   - Estado actual
   - Fechas
   - Horas registradas

### Registro de horas (Cronómetro)

Para registrar horas trabajadas en un proyecto:

1. Accede a "Mis Proyectos"
2. Selecciona el proyecto activo
3. Usa el cronómetro para iniciar/pausar/detener
4. Las horas se registran automáticamente en el proyecto

### Actualizar perfil

1. Haz clic en "Perfil" en el menú
2. Puedes actualizar:
   - Nombre
   - Teléfono
   - Email (requiere validación)
   - Contraseña
3. Guarda los cambios

---

## Panel de Jefe de Equipo

### Vista principal (Dashboard)

El dashboard del jefe muestra:

**Tarjetas de estadísticas**
- Trabajadores: Total de miembros en tu equipo
- Clientes: Total de clientes gestionados
- Proyectos: Total de proyectos creados
- Facturado: Total facturado en euros

**Acciones rápidas**
- Ver todos los proyectos
- Gestionar equipo
- Crear nuevo presupuesto

### Menú de navegación

- **Inicio**: Dashboard con estadísticas
- **Mi Equipo**: Gestión de trabajadores
- **Mis Proyectos**: Gestión de proyectos
- **Clientes**: Gestión de clientes
- **Presupuestos**: Crear y gestionar presupuestos
- **Perfil**: Actualizar datos personales

### Gestión de equipo

#### Crear equipo

1. Ve a "Mi Equipo"
2. Si no tienes equipo, haz clic en "Crear Equipo"
3. Introduce:
   - Nombre del equipo
   - Descripción
4. Guarda el equipo

#### Invitar trabajadores

1. En "Mi Equipo", haz clic en "Invitar Trabajador"
2. Introduce:
   - Email del trabajador
   - Rol en el proyecto (ej: Desarrollador, Diseñador, etc.)
3. El trabajador recibirá un email con enlace de invitación
4. Cuando acepte, aparecerá en tu lista de miembros

#### Ver miembros del equipo

En "Mi Equipo" verás:
- DNI de cada miembro
- Nombre completo
- Email
- Rol en el proyecto
- Estado de la invitación (Pendiente/Aceptada)
- Opciones para eliminar del equipo

### Gestión de proyectos

#### Crear proyecto

1. Ve a "Mis Proyectos"
2. Haz clic en "Nuevo Proyecto"
3. Completa el formulario:
   - Código único (ej: PROJ-001)
   - Nombre del proyecto
   - Descripción
   - Cliente (seleccionar de la lista)
   - Equipo asignado
   - Estado inicial
   - Fecha de inicio
   - Fecha estimada de finalización
4. Guarda el proyecto

#### Asignar trabajadores a proyecto

1. En la lista de proyectos, haz clic en "Asignar trabajadores"
2. Verás la lista de miembros de tu equipo
3. Selecciona los trabajadores que participarán
4. Confirma la asignación
5. Los trabajadores verán el proyecto en su panel

#### Estados de proyecto

Los proyectos pueden tener los siguientes estados:
- **Creado**: Proyecto registrado pero sin iniciar
- **En proceso**: Proyecto activo en desarrollo
- **Finalizado**: Proyecto completado exitosamente
- **Pausado**: Proyecto temporalmente detenido
- **Cancelado**: Proyecto cancelado

#### Editar/Eliminar proyecto

1. En la lista de proyectos, usa los botones de acción
2. Puedes editar información del proyecto
3. Cambiar estado
4. Eliminar proyecto (precaución: acción permanente)

### Gestión de clientes

#### Agregar cliente

1. Ve a "Clientes"
2. Haz clic en "Nuevo Cliente"
3. Completa el formulario:
   - Nombre del cliente
   - Empresa (opcional)
   - Email
   - Teléfono
   - Dirección
   - CIF/NIF
   - Notas adicionales
4. Guarda el cliente

#### Ver y editar clientes

1. En "Clientes" verás la lista completa
2. Usa el buscador para filtrar
3. Haz clic en un cliente para ver detalles
4. Edita la información según necesites
5. Puedes desactivar clientes sin eliminarlos

### Sistema de presupuestos (Wizard)

El wizard de presupuestos te guía paso a paso para crear presupuestos profesionales.

#### Paso 1: Información del Proyecto

- Nombre del proyecto
- Descripción detallada
- Selecciona el cliente de tu lista

#### Paso 2: Tipo de Presupuesto

Selecciona el tipo de proyecto:
- Aplicación Web
- Aplicación Móvil
- Tienda Online
- Sistema de Gestión
- Consultoría IT
- Mantenimiento
- Otro (personalizado)

#### Paso 3: Servicios

Selecciona servicios del catálogo:
- Servicios generales (diseño, desarrollo, hosting, etc.)
- Servicios informáticos (servidores, seguridad, backups, etc.)

Para cada servicio:
- Selecciona con checkbox
- Ajusta cantidad de unidades
- El precio se calcula automáticamente

#### Paso 4: Detalles Personalizados

Agrega servicios personalizados:
- Nombre del servicio
- Descripción
- Cantidad
- Precio unitario
- El total se calcula automáticamente

#### Paso 5: Metodología de Trabajo

Describe:
- Metodología a utilizar (Ágil, Scrum, Waterfall, etc.)
- Fases del proyecto
- Entregables
- Garantías y soporte

#### Paso 6: Resumen y Finalización

Revisa:
- Todos los servicios seleccionados
- Detalles del cliente
- Subtotales y totales
- IVA calculado (21% por defecto)
- Total final

**Acciones finales:**
- Guardar presupuesto
- Exportar a PDF
- Enviar por email al cliente
- Imprimir

#### Gestionar presupuestos guardados

1. Ve a "Mis Presupuestos"
2. Verás lista de todos los presupuestos
3. Filtros disponibles:
   - Por cliente
   - Por fecha
   - Por estado
4. Acciones:
   - Ver detalles
   - Exportar PDF
   - Enviar email
   - Duplicar
   - Editar
   - Eliminar

---

## Panel de Moderador

### Vista principal (Dashboard)

El dashboard del moderador muestra estadísticas globales del sistema:

**Tarjetas de estadísticas**
- Total de usuarios registrados
- Usuarios activos
- Usuarios baneados
- Usuarios eliminados
- Total de proyectos
- Proyectos activos
- Total de equipos
- Total de clientes

**Gráficos y análisis**
- Distribución de usuarios por rol
- Evolución de proyectos en el tiempo
- Actividad reciente del sistema

### Pestañas de navegación

El panel del moderador tiene 5 pestañas principales:

#### 1. Dashboard
- Estadísticas globales
- Métricas del sistema
- Actividad reciente

#### 2. Baneos

**Ver historial de baneos**
- Lista de todos los usuarios baneados
- Fecha de baneo
- Motivo del baneo
- Usuario que aplicó el baneo

**Desbanear usuario**
1. Busca el usuario en el historial
2. Haz clic en "Desbanear"
3. Confirma la acción
4. El usuario recupera acceso al sistema

#### 3. Proyectos

**Supervisión de todos los proyectos**
- Ver proyectos de todos los jefes de equipo
- Filtrar por estado
- Ver detalles de cada proyecto
- Estadísticas de proyectos

#### 4. Usuarios

**Gestión global de usuarios**

Ver lista completa con:
- DNI
- Nombre
- Email
- Rol (Jefe, Trabajador, Moderador)
- Estado (Activo, Baneado, Eliminado)
- Fecha de registro

**Acciones sobre usuarios:**
- Ver perfil detallado
- Cambiar estado (Activar/Banear/Eliminar)
- Ver historial de acciones
- Ver proyectos asociados

**Banear usuario:**
1. Selecciona el usuario
2. Haz clic en "Banear"
3. Introduce el motivo del baneo
4. Confirma la acción
5. El usuario perderá acceso inmediatamente

**Activar usuario:**
1. Selecciona usuario inactivo o baneado
2. Haz clic en "Activar"
3. El usuario recupera acceso

#### 5. Perfil

- Ver y editar datos personales del moderador
- Actualizar información de contacto
- Cambiar contraseña

### Historial de acciones administrativas

El moderador puede ver un registro completo de todas las acciones administrativas:

- Usuario que realizó la acción
- Tipo de acción (Creación, Modificación, Eliminación, Baneo, etc.)
- Usuario afectado
- Fecha y hora
- Descripción detallada

---

## Funcionalidades comunes

### Navegación

**Barra superior (Navbar)**
- Logo de LOGISTEIA (vuelve al inicio)
- Nombre del usuario actual
- Rol del usuario
- Botón "Cerrar Sesión"

**Menú lateral (Sidebar)**
- Visible en ordenadores
- Se oculta automáticamente en móviles
- Botón de menú hamburguesa en móvil
- Iconos y texto descriptivo
- Resalta la sección actual

### Notificaciones

El sistema muestra notificaciones para:
- Acciones exitosas (verde)
- Errores o problemas (rojo)
- Advertencias (amarillo)
- Información general (azul)

Las notificaciones aparecen en la parte superior y desaparecen automáticamente.

### Búsquedas y filtros

En todas las listas (usuarios, proyectos, clientes, presupuestos):
- Buscador en tiempo real
- Filtros por estado
- Ordenamiento por columnas
- Paginación automática

### Exportación de datos

Muchas secciones permiten exportar datos:
- Presupuestos a PDF
- Listas a Excel (planificado)
- Reportes personalizados (planificado)

### Diseño responsive

El sistema se adapta a cualquier dispositivo:

**Ordenador de escritorio**
- Vista completa con sidebar
- Tablas con todas las columnas
- Gráficos expandidos

**Tablet**
- Sidebar colapsable
- Tablas adaptadas
- Touch-friendly

**Móvil**
- Menú hamburguesa
- Tarjetas en lugar de tablas
- Botones grandes para touch
- Contenido priorizado

---

## Preguntas frecuentes (FAQ)

### Acceso y cuenta

**¿Puedo cambiar mi email?**
Sí, desde tu perfil puedes actualizar tu email. Requiere verificación.

**¿Puedo cambiar mi rol?**
No directamente. Los roles son asignados por:
- Al registrarte: rol "trabajador" por defecto
- Un jefe puede invitarte a su equipo
- Un moderador puede cambiar tu rol

**¿Qué hago si olvido mi contraseña?**
Contacta con tu jefe de equipo o moderador para que te ayuden a restablecer el acceso. También puedes usar "Iniciar sesión con Google".

**¿Puedo tener múltiples cuentas?**
No es recomendable. Usa una única cuenta con tu email principal o cuenta de Google.

### Equipos y proyectos

**¿Puedo pertenecer a varios equipos?**
Actualmente cada trabajador pertenece a un solo equipo. Esto puede cambiar en futuras versiones.

**¿Cómo acepto una invitación a un equipo?**
Recibirás un email con un enlace único. Haz clic en el enlace e inicia sesión para confirmar.

**¿Puedo ver proyectos de otros equipos?**
No, solo ves los proyectos de tu equipo o en los que estás asignado, a menos que seas moderador.

**¿Qué pasa si elimino un proyecto?**
La eliminación es permanente. Se borran todos los datos asociados: tareas, horas registradas, etc.

### Presupuestos

**¿Puedo editar un presupuesto después de guardarlo?**
Actualmente no se pueden editar presupuestos guardados. Puedes duplicarlo y modificar la copia.

**¿Cómo personalizo los precios de los servicios?**
En el Paso 4 del wizard puedes agregar servicios personalizados con precios propios.

**¿El cliente puede ver el presupuesto en línea?**
Actualmente solo por PDF enviado por email. Portal de cliente está en desarrollo.

**¿Puedo cambiar el IVA aplicado?**
Por defecto es 21%. Para personalizarlo, contacta con el administrador del sistema.

### Clientes

**¿Puedo compartir clientes con otros jefes de equipo?**
No, cada jefe gestiona sus propios clientes. Si necesitas compartir, crea el cliente duplicado en cada cuenta.

**¿Se puede importar clientes desde un archivo?**
Esta funcionalidad está planificada para futuras versiones.

### Problemas técnicos

**La página no carga o va muy lenta**
1. Verifica tu conexión a internet
2. Limpia la caché del navegador
3. Intenta con otro navegador
4. Contacta con soporte si persiste

**No recibo emails del sistema**
1. Revisa tu carpeta de spam
2. Verifica que tu email esté correcto en tu perfil
3. Agrega noreply@logisteia.com a tus contactos

**El cronómetro no registra mis horas**
1. Asegúrate de estar asignado al proyecto
2. Verifica que el proyecto esté en estado "En proceso"
3. Comprueba tu conexión a internet

**No puedo subir archivos**
Verifica que:
- El archivo no supere el tamaño máximo (5MB típicamente)
- El formato sea compatible
- Tu navegador tenga JavaScript habilitado

---

## Consejos y mejores prácticas

### Para todos los usuarios

1. **Mantén tu perfil actualizado**: Email y teléfono correctos facilitan la comunicación

2. **Usa contraseñas seguras**: Combina mayúsculas, minúsculas, números y símbolos

3. **Cierra sesión en ordenadores compartidos**: Protege tu cuenta y datos

4. **Revisa notificaciones regularmente**: No te pierdas actualizaciones importantes

5. **Reporta problemas**: Si encuentras errores, informa al moderador

### Para jefes de equipo

1. **Organiza tu equipo desde el inicio**: Invita a todos los miembros necesarios

2. **Mantén clientes actualizados**: Información correcta evita errores en presupuestos

3. **Códigos de proyecto consistentes**: Usa un sistema de numeración lógico (PROJ-001, PROJ-002...)

4. **Revisa presupuestos antes de enviar**: Verifica todos los datos en el resumen final

5. **Asigna trabajadores correctamente**: Solo asigna personas con disponibilidad real

6. **Actualiza estados de proyectos**: Refleja el estado real para mejor seguimiento

### Para moderadores

1. **Documenta baneos**: Siempre incluye motivo claro y detallado

2. **Revisa estadísticas periódicamente**: Detecta problemas o tendencias

3. **Mantén comunicación**: Informa a jefes sobre cambios importantes

4. **Backups regulares**: Coordina con IT backups de la base de datos

---

## Seguridad y privacidad

### Protección de datos

- Todos los datos están cifrados en tránsito (HTTPS)
- Contraseñas hasheadas con bcrypt
- Cumplimiento con normativas de protección de datos
- Solo personal autorizado accede a datos sensibles

### Buenas prácticas de seguridad

1. **No compartas tu contraseña**: Ni con compañeros ni con superiores
2. **Usa autenticación de dos factores**: Si está disponible en tu cuenta Google
3. **Verifica URLs**: Asegúrate de estar en logisteia.com
4. **Reporta actividad sospechosa**: Si ves algo extraño, informa inmediatamente
5. **Actualiza contraseña periódicamente**: Cada 3-6 meses es recomendable

---

## Glosario de términos

**Cronómetro**: Herramienta para registrar tiempo trabajado en proyectos

**Dashboard**: Panel principal con resumen de información

**DNI**: Documento Nacional de Identidad, identificador único del usuario

**JWT**: JSON Web Token, sistema de autenticación segura

**OAuth**: Protocolo de autenticación externa (ej: Google)

**PDF**: Formato de documento portátil para presupuestos

**Presupuesto Wizard**: Asistente paso a paso para crear presupuestos

**Responsive**: Diseño que se adapta a diferentes tamaños de pantalla

**Rol**: Nivel de permisos del usuario (Trabajador, Jefe, Moderador)

**Sidebar**: Menú lateral de navegación

**Token**: Código único para invitaciones o autenticación

---

## Actualizaciones y novedades

El sistema LOGISTEIA está en constante evolución. Las actualizaciones se realizan de forma transparente y automática. Próximamente:

### Planificadas

- Sistema de notificaciones push
- Chat en tiempo real entre miembros del equipo
- Aplicación móvil nativa (iOS y Android)
- Portal para clientes
- Integración con Google Calendar
- Reportes avanzados con gráficos
- Exportación a Excel
- Importación masiva de datos
- Sistema de tareas con Kanban
- Facturación electrónica automática

---

## Soporte y contacto

### Soporte técnico

Si experimentas problemas técnicos o tienes dudas sobre el uso del sistema:

**Por email:**
- Soporte general: aandradesm01@educarex.es
- Soporte técnico: fjlevar01@educarex.es

**Información a incluir en tu consulta:**
1. Descripción detallada del problema
2. Capturas de pantalla si es posible
3. Navegador y dispositivo que usas
4. Pasos para reproducir el error
5. Tu rol en el sistema

**Tiempo de respuesta:**
- Consultas generales: 24-48 horas
- Problemas críticos: 4-8 horas
- Emergencias: Contactar al moderador de tu sistema

### Recursos adicionales

- Manual de instalación: Para administradores del sistema
- Manual de programador: Para desarrolladores
- Documentación técnica: En el repositorio del proyecto
- Video tutoriales: Próximamente en canal de YouTube

---

## Sobre LOGISTEIA

### Autores

LOGISTEIA ha sido desarrollado por:
- **Álvaro Andrades Márquez** - aandradesm01@educarex.es
- **Fernando José Leva Rosa** - fjlevar01@educarex.es

### Tecnologías

- Frontend: Angular 21 + TypeScript + Tailwind CSS
- Backend: PHP 8.2 + MySQL 8.0
- Infraestructura: Docker + Caddy + AWS
- Autenticación: JWT + Google OAuth

### Licencia

Sistema propietario desarrollado para gestión empresarial.

### Agradecimientos

Gracias a todos los usuarios beta que han ayudado a mejorar el sistema con sus sugerencias y reportes de errores.
