# ✅ RESUMEN DE PROBLEMAS CORREGIDOS Y ACCIONES PENDIENTES

## 📋 Fecha: 28 de enero de 2026

---

## 🔧 PROBLEMAS CORREGIDOS

### 1. ✅ Proyectos guardándose en tabla Presupuestos
**Problema:** El modelo `Proyecto.php` insertaba datos en la tabla `presupuestos` en lugar de `proyectos`.

**Solución aplicada:**
- ✅ Corregido `Proyecto.php` para insertar en tabla `proyectos`
- ✅ Cambiado `generarNumeroPresupuesto()` → `generarCodigoProyecto()`
- ✅ Actualizado `obtenerProyectosPorJefe()` para consultar tabla `proyectos`
- ✅ Actualizado `obtenerProyectosPorTrabajador()` para consultar tabla `proyectos`
- ✅ Código desplegado (commit 96755dd0)

**Arquitectura correcta:**
- **Proyectos**: Tabla principal para gestionar proyectos (nombre, descripción, tecnologías, etc.)
- **Presupuestos**: Tabla separada para presupuestos económicos (se crean DESPUÉS del proyecto)

**Documentación:** Ver [doc_correccion_proyectos.sql](doc_correccion_proyectos.sql)

---

### 2. ✅ Manejo de errores en registro normal
**Problema:** El envío de email podía bloquear el registro si fallaba.

**Solución aplicada:**
- ✅ Envuelto `enviarEmail()` en try-catch en `RegistroUsuario.php`
- ✅ El registro se completa aunque falle el email
- ✅ Errores de email se registran en logs pero no detienen el proceso
- ✅ Código desplegado (commit 96755dd0)

---

### 3. ✅ Mejora en logs de email
**Problema:** Difícil diagnosticar por qué no llegaban los emails.

**Solución aplicada:**
- ✅ Logs mejorados en `email.php` con información detallada:
  - Destinatario y asunto
  - Mensaje de error de PHPMailer
  - Configuración SMTP (Host, Username)
  - Estado de GMAIL_APP_PASSWORD
- ✅ Logs también en `enviarEmailBienvenida()` de `equipo.php`
- ✅ Código desplegado (commit 96755dd0)

---

## ⚠️ ACCIONES PENDIENTES (DEBES EJECUTAR)

### 1. 🔴 CRÍTICO: Crear equipos para jefes existentes
**Problema:** Jefes registrados antes tienen error "El jefe de equipo no tiene un equipo asignado".

**Solución:** Ejecutar en phpMyAdmin:

```sql
-- Crear equipos para jefes sin equipo
INSERT INTO equipos (nombre, descripcion, jefe_dni, activo)
SELECT 
    CONCAT('Equipo de ', u.nombre),
    CONCAT('Equipo gestionado por ', u.nombre),
    u.dni,
    1
FROM usuarios u
LEFT JOIN equipos e ON u.dni = e.jefe_dni
WHERE u.rol = 'jefe_equipo' 
AND e.id IS NULL;

-- Verificar que se crearon
SELECT e.id, e.nombre, e.jefe_dni, u.nombre as nombre_jefe
FROM equipos e
INNER JOIN usuarios u ON e.jefe_dni = u.dni
ORDER BY e.id DESC;
```

**Script completo:** [crear_equipos_faltantes.sql](crear_equipos_faltantes.sql)

---

### 2. 🔴 CRÍTICO: Verificar GMAIL_APP_PASSWORD
**Problema:** Los emails no llegan si falta la contraseña de aplicación de Gmail.

**Pasos para verificar:**

```bash
# Conectar al servidor
ssh ubuntu@logisteia.com

# Verificar si existe GMAIL_APP_PASSWORD
cd /home/ubuntu/logisteia
grep GMAIL_APP_PASSWORD .env

# Si NO existe o está vacía, agregarla
nano .env
# Añadir: GMAIL_APP_PASSWORD=tu_contraseña_aplicacion_gmail

# Reiniciar backend para cargar la variable
docker compose restart backend

# Ver logs de email
docker compose logs backend | grep -i email
```

**¿Cómo obtener GMAIL_APP_PASSWORD?**
1. Ir a https://myaccount.google.com/security
2. Activar "Verificación en 2 pasos"
3. Buscar "Contraseñas de aplicaciones"
4. Generar contraseña para "Correo" → "Otro (Logisteia)"
5. Copiar la contraseña de 16 caracteres
6. Agregarla al `.env` como `GMAIL_APP_PASSWORD=xxxx xxxx xxxx xxxx`

---

### 3. 📌 OPCIONAL: Crear usuarios moderadores
**Script de inserción:** [crear_moderadores.sql](crear_moderadores.sql)

**Credenciales creadas:**
- Email: `moderador1@logisteia.com` | Contraseña: `Logisteia2026!`
- Email: `moderador2@logisteia.com` | Contraseña: `Logisteia2026!`

**Ejecutar en phpMyAdmin:**
```sql
INSERT INTO usuarios (dni, email, nombre, contrase, rol, telefono, estado, fecha_registro) VALUES
('MOD001', 'moderador1@logisteia.com', 'Carlos Ruiz Moderador', '$2y$10$9lV07lWnzxYVRz/i49buM.5Uv7PU4wuc4gNDTX/C0SlkJvHfmsMNC', 'moderador', '600111222', 'activo', NOW()),
('MOD002', 'moderador2@logisteia.com', 'Ana García Moderadora', '$2y$10$9lV07lWnzxYVRz/i49buM.5Uv7PU4wuc4gNDTX/C0SlkJvHfmsMNC', 'moderador', '600333444', 'activo', NOW())
ON DUPLICATE KEY UPDATE email = email;
```

---

### 4. 🔍 OPCIONAL: Migrar proyectos incorrectos de presupuestos
**Solo si encuentras registros en presupuestos con "PROYECTO:" en las notas.**

Ver instrucciones completas en: [doc_correccion_proyectos.sql](doc_correccion_proyectos.sql) (sección 5)

---

## 📝 VERIFICACIÓN POST-DESPLIEGUE

### Probar flujo de registro normal:
1. ✅ Registrar un nuevo usuario
2. ✅ Verificar que se crea en la base de datos
3. ✅ Verificar si llega el email de bienvenida
4. ✅ Si es jefe_equipo, verificar que se crea su equipo automáticamente

### Probar flujo de registro con Google:
1. ✅ Login con Google (nuevo usuario)
2. ✅ Completar registro
3. ✅ Verificar email de bienvenida
4. ✅ Verificar que redirige al panel correcto

### Probar invitación a equipo:
1. ✅ Jefe de equipo → Mi Equipo → Agregar miembro
2. ✅ Verificar que aparece mensaje de éxito
3. ✅ Verificar si llega email de invitación al trabajador

### Probar creación de proyectos:
1. ✅ Crear un proyecto nuevo
2. ✅ Verificar que se guarda en tabla `proyectos` (NO presupuestos)
3. ✅ Verificar que aparece en lista de proyectos

---

## 🐛 DIAGNÓSTICO DE EMAILS

Si los emails no llegan, revisar logs del backend:

```bash
ssh ubuntu@logisteia.com
cd /home/ubuntu/logisteia
docker compose logs backend | grep -i email | tail -50
```

Buscar:
- ✅ `Email enviado exitosamente` → Email se envió correctamente
- ❌ `ERROR ENVIANDO EMAIL` → Ver mensaje de error
- ❌ `GMAIL_APP_PASSWORD configurado: NO` → Falta contraseña de Gmail
- ❌ `SMTP Error` → Problema de conexión o autenticación con Gmail

---

## 📁 ARCHIVOS MODIFICADOS (commit 96755dd0)

### Backend:
- `src/www/modelos/Proyecto.php` - Corregir inserción en proyectos
- `src/www/api/RegistroUsuario.php` - Mejorar manejo errores email
- `src/www/api/equipo.php` - Limpiar función enviarEmailBienvenida
- `src/www/config/email.php` - Mejorar logs de diagnóstico

### Scripts SQL:
- `crear_equipos_faltantes.sql` - Script para crear equipos faltantes
- `crear_moderadores.sql` - Script para crear usuarios moderadores
- `doc_correccion_proyectos.sql` - Documentación de corrección de proyectos

---

## 🎯 PRÓXIMOS PASOS RECOMENDADOS

1. ⚠️ **INMEDIATO**: Ejecutar script crear_equipos_faltantes.sql
2. ⚠️ **INMEDIATO**: Verificar/configurar GMAIL_APP_PASSWORD
3. ✅ Crear usuarios moderadores (si necesario)
4. ✅ Probar todos los flujos de registro/invitación
5. ✅ Revisar logs de email durante las pruebas
6. ✅ Verificar que los proyectos se crean correctamente

---

## 📞 SOPORTE

Si encuentras más problemas:
1. Revisar logs del backend: `docker compose logs backend`
2. Revisar logs de Caddy: `docker compose logs web`
3. Verificar estado de contenedores: `docker compose ps`
4. Revisar variables de entorno: `cat /home/ubuntu/logisteia/.env`

---

**Generado automáticamente el 28 de enero de 2026**
**Commit desplegado: 96755dd0**
