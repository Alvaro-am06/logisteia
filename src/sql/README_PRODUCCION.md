# Script SQL para Producción - Logisteia

## 📋 Resumen

He analizado todo el código (modelos PHP, APIs, frontend Angular) y creado un script SQL consolidado que incluye **toda la estructura de base de datos** que tu aplicación necesita.

## ✅ ¿Qué incluye el script?

### Tablas Principales:
1. **usuarios** - Con roles: `jefe_equipo`, `trabajador`, `moderador`
2. **equipos** - Equipos gestionados por jefes
3. **miembros_equipo** - Relación trabajadores-equipos
4. **clientes** - Clientes de cada jefe
5. **proyectos** - Gestión de proyectos
6. **tareas** - Tareas dentro de proyectos
7. **registro_horas** - Cronómetro de horas trabajadas
8. **presupuestos** - Sistema de presupuestos
9. **detalle_presupuesto** - Líneas de presupuesto
10. **servicios** - Servicios generales (legacy)
11. **servicios_informatica** - Servicios IT específicos
12. **facturas** - Facturación
13. **pagos** - Control de pagos
14. **acciones_administrativas** - Auditoría
15. **historial_baneos** - Control de baneos
16. **invitaciones** - Sistema de invitaciones
17. **asignaciones_proyecto** - Asignación trabajadores-proyectos

### Datos Iniciales:
- ✅ Usuario moderador: `admin@logisteia.com` (contraseña: `1234`)
- ✅ Usuario jefe: `jefe@logisteia.com` (contraseña: `1234`)
- ✅ Usuario trabajador: `trabajador@logisteia.com` (contraseña: `1234`)
- ✅ Equipo de ejemplo
- ✅ 10 servicios informáticos predefinidos
- ✅ 5 servicios generales

## 🔑 Diferencias con `bbdd.sql` original

### ❌ Problemas del archivo original:
1. **Falta campo `estado`** en usuarios (activo/baneado/eliminado)
2. **Roles incorrectos**: Usa `administrador/registrado` en vez de `jefe_equipo/trabajador/moderador`
3. **Faltan tablas**: equipos, proyectos, tareas, clientes, etc.
4. **Faltan campos**: avatar, bio, fecha_baneo, motivo_baneo

### ✅ Script nuevo incluye:
- Todos los campos que usa el código PHP
- Todas las tablas que consulta la API
- Estructura compatible con Angular frontend
- Foreign keys correctas
- Índices optimizados
- Datos de prueba iniciales

## 🚀 Cómo usar el script

### Opción 1: Desde línea de comandos
```bash
mysql -u tu_usuario -p < produccion_completa.sql
```

### Opción 2: phpMyAdmin
1. Accede a phpMyAdmin
2. Selecciona "Importar"
3. Elige el archivo `produccion_completa.sql`
4. Ejecuta

### Opción 3: Adminer
1. Accede a Adminer
2. Menú "SQL command"
3. Copia y pega el contenido
4. Ejecuta

### Opción 4: Servidor de producción (Railway, Heroku, etc.)
```bash
# Si tienes acceso SSH
cat produccion_completa.sql | mysql -h HOST -u USER -p DATABASE

# O desde el panel web del proveedor
```

## ⚠️ Notas Importantes

1. **El script usa `IF NOT EXISTS`**: Es seguro ejecutarlo múltiples veces
2. **Usa `ON DUPLICATE KEY UPDATE`**: Los datos de ejemplo no se duplicarán
3. **Todas las contraseñas son `1234`**: Cámbialas después en producción
4. **Los hashes son bcrypt**: El código PHP usa `password_verify()`

## 🔍 Verificación Post-Instalación

Después de ejecutar el script, verifica:

```sql
-- Verificar tablas creadas
SHOW TABLES;

-- Verificar usuarios iniciales
SELECT dni, email, rol, estado FROM usuarios;

-- Verificar servicios
SELECT COUNT(*) FROM servicios_informatica;
SELECT COUNT(*) FROM servicios;

-- Verificar equipo
SELECT * FROM equipos;
```

## 📱 Login en la Aplicación

Después de ejecutar el script, puedes hacer login con:

| Rol | Email | Contraseña | Permisos |
|-----|-------|-----------|----------|
| Moderador | admin@logisteia.com | 1234 | Admin global |
| Jefe | jefe@logisteia.com | 1234 | Gestión de equipos |
| Trabajador | trabajador@logisteia.com | 1234 | Miembro de equipo |

## 🔄 Migraciones Incluidas

El script unifica estos archivos:
- ✅ `bbdd.sql` (estructura base)
- ✅ `migracion_estado_usuarios.sql` (campo estado)
- ✅ `00-migracion-arquitectura.sql` (equipos y proyectos)
- ✅ `01-agregar-token-invitacion.sql` (tokens)
- ✅ `datos_iniciales.sql` (datos de prueba)

**Ya no necesitas ejecutar migraciones por separado.**

## 📂 Ubicación del Script

```
logisteia/
└── src/
    └── sql/
        └── produccion_completa.sql  ← ESTE ARCHIVO
```

## 💡 Recomendaciones

1. **Backup primero**: Si ya tienes datos, haz backup antes
2. **Revisa credenciales**: Cambia las contraseñas de ejemplo
3. **Verifica conexión**: Asegúrate que `config/database.php` apunta a la BD correcta
4. **Testing**: Prueba login y operaciones básicas después

## 🐛 Solución de Problemas

### Error: "Table already exists"
✅ Normal, el script usa `IF NOT EXISTS`, simplemente continúa.

### Error: "Foreign key constraint fails"
❌ Ejecuta el script desde el principio en una BD limpia.

### Error: "Access denied"
❌ Verifica los permisos del usuario MySQL.

### No puedo hacer login
✅ Verifica que la tabla usuarios tenga los datos:
```sql
SELECT * FROM usuarios WHERE email = 'admin@logisteia.com';
```

---

**Script generado el**: 28 de enero de 2026  
**Compatible con**: PHP 8.x, MySQL 5.7+, MariaDB 10.3+
