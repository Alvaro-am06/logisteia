# Manual de instalación de LOGISTEIA

## Requisitos previos
- XAMPP instalado y configurado en tu equipo (incluye Apache y MySQL).
- Script de base de datos: `src/sql/bbdd.sql`.
- Proyecto backend ubicado en: `src/www`.

---

## Pasos de instalación

### 1. Iniciar XAMPP
- Abre el panel de control de XAMPP.
- Inicia los servicios de **Apache** y **MySQL**.

### 2. Crear la base de datos
- Accede a **phpMyAdmin** desde el panel de XAMPP (normalmente en [http://localhost/phpmyadmin](http://localhost/phpmyadmin)).
- Haz clic en la pestaña **Importar**.
- Selecciona el archivo `src/sql/bbdd.sql` desde tu proyecto.
- Haz clic en **Continuar** para ejecutar el script y crear la base de datos `Logisteia` y todas sus tablas.

### 3. Ubicar el proyecto en la carpeta de XAMPP
- Copia la carpeta `src/www` de tu proyecto a la carpeta `htdocs` de XAMPP:
  - Ejemplo: `C:/xampp/htdocs/Proyecto/src/www`

### 4. Acceder a la aplicación
- Abre tu navegador y accede a la siguiente URL:
  - `http://localhost/Proyecto/src/www/index.php`
- Deberías ver el formulario de inicio de sesión de LOGISTEIA.

---

## Notas adicionales
- Si el puerto de Apache es diferente (por ejemplo, 8080), la URL será: `http://localhost:8080/Proyecto/src/www/index.php`
- Si tienes problemas de permisos, asegúrate de que los archivos tengan permisos de lectura y escritura.
- El script `bbdd.sql` crea la base de datos y todas las tablas necesarias para el funcionamiento del sistema.

---

**Autores:**
- Álvaro Andrades Márquez
- Fernando José Leva Rosa
