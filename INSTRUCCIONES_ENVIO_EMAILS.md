# 📧 INSTRUCCIONES: Enviar emails a trabajadores existentes

## 🎯 Propósito
Este script envía emails de bienvenida a todos los trabajadores y jefes de equipo ya registrados en la plataforma.

## 📋 Pre-requisitos
1. ✅ Verificar que `GMAIL_APP_PASSWORD` esté configurado en el servidor
2. ✅ Tener acceso SSH al servidor

---

## 🚀 Ejecución del Script

### Opción 1: Ejecutar desde el servidor (RECOMENDADO)

```bash
# Conectar al servidor
ssh ubuntu@logisteia.com

# Ir al directorio del proyecto
cd /home/ubuntu/logisteia

# Ejecutar el script
docker compose exec backend php /var/www/html/scripts/enviar_emails_trabajadores.php
```

### Opción 2: Ejecutar localmente con Docker

```bash
# Desde tu máquina local, en el directorio del proyecto
cd C:\Users\el_an\Documents\Repositorios\logisteia

# Ejecutar en el contenedor backend
docker compose exec backend php /var/www/html/scripts/enviar_emails_trabajadores.php
```

---

## 📊 Qué hace el script

1. **Busca todos los usuarios** con rol `trabajador` o `jefe_equipo` que estén activos
2. **Genera un email personalizado** para cada usuario con:
   - Nombre del usuario
   - Email
   - DNI
   - Rol traducido (Trabajador / Jefe de Equipo)
   - Fecha de registro
   - Enlace a la plataforma
3. **Envía el email** usando PHPMailer y Gmail SMTP
4. **Espera 2 segundos** entre cada email (para no saturar el servidor SMTP)
5. **Muestra un resumen** al final:
   - ✅ Emails exitosos
   - ❌ Emails fallidos
   - 📊 Total procesado

---

## 📝 Salida esperada

```
========================================
ENVÍO DE EMAILS A TRABAJADORES
========================================

📊 Total de usuarios encontrados: 15

Procesando: Juan Pérez (juan@example.com)... ✅ Email enviado
Procesando: María López (maria@example.com)... ✅ Email enviado
Procesando: Carlos García (carlos@example.com)... ❌ Error al enviar
...

========================================
RESUMEN
========================================
✅ Exitosos: 14
❌ Fallidos: 1
📊 Total: 15
========================================

✅ Script completado.
```

---

## ⚠️ Solución de Problemas

### Problema: "❌ Error al enviar"

**Causa:** Falta `GMAIL_APP_PASSWORD` o está mal configurada.

**Solución:**
```bash
# Verificar en el servidor
ssh ubuntu@logisteia.com
cd /home/ubuntu/logisteia
grep GMAIL_APP_PASSWORD .env

# Si NO existe, añadirla
nano .env
# Agregar: GMAIL_APP_PASSWORD=tu_contraseña_16_caracteres

# Reiniciar backend
docker compose restart backend
```

### Problema: "Connection timed out"

**Causa:** El servidor SMTP de Gmail está bloqueado o la conexión es lenta.

**Solución:**
- Verificar que el servidor tenga acceso a Internet
- Aumentar el timeout en email.php (línea SMTPOptions)
- Verificar que Gmail no esté bloqueando el acceso

### Problema: "Authentication failed"

**Causa:** La contraseña de aplicación de Gmail es incorrecta.

**Solución:**
1. Ir a https://myaccount.google.com/security
2. Generar nueva contraseña de aplicación
3. Actualizar `.env` con la nueva contraseña
4. Reiniciar backend

---

## 📧 Ejemplo de Email Enviado

```
Asunto: Bienvenido a Logisteia

¡Bienvenido a Logisteia, Juan Pérez!

Tu cuenta ha sido creada exitosamente en nuestra plataforma de gestión de proyectos.

Datos de tu cuenta:
• Email: juan@example.com
• DNI: 12345678A
• Rol: Jefe de Equipo
• Fecha de registro: 15/01/2026

Ya puedes iniciar sesión en la plataforma con tus credenciales.

[Iniciar Sesión] → https://logisteia.com

Si tienes alguna pregunta o problema, no dudes en contactarnos.

Saludos,
Equipo Logisteia
```

---

## 🔄 Ejecutar el script periódicamente (OPCIONAL)

Si quieres enviar emails automáticamente a nuevos usuarios:

```bash
# En el servidor, editar crontab
crontab -e

# Añadir línea para ejecutar cada lunes a las 9:00 AM
0 9 * * 1 cd /home/ubuntu/logisteia && docker compose exec -T backend php /var/www/html/scripts/enviar_emails_trabajadores.php >> /var/log/logisteia-emails.log 2>&1
```

---

## ✅ Checklist Post-Ejecución

- [ ] Verificar en Gmail que los emails se enviaron
- [ ] Revisar la carpeta de Spam si no aparecen
- [ ] Comprobar que los trabajadores recibieron el email
- [ ] Revisar logs del backend: `docker compose logs backend | grep email`

---

**Última actualización:** 28 de enero de 2026
**Ubicación del script:** `src/www/scripts/enviar_emails_trabajadores.php`
