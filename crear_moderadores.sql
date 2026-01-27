-- Script para crear dos moderadores en la base de datos de producción
-- Contraseña para ambos: Logisteia2026!
-- Hash bcrypt generado con PASSWORD_DEFAULT

-- IMPORTANTE: Ejecutar en phpMyAdmin o MySQL CLI

-- Moderador 1
INSERT INTO usuarios (dni, email, nombre, contrase, rol, telefono, estado, fecha_registro) VALUES
('MOD001', 'moderador1@logisteia.com', 'Carlos Ruiz Moderador', '$2y$10$9lV07lWnzxYVRz/i49buM.5Uv7PU4wuc4gNDTX/C0SlkJvHfmsMNC', 'moderador', '600111222', 'activo', NOW())
ON DUPLICATE KEY UPDATE nombre = nombre;

-- Moderador 2
INSERT INTO usuarios (dni, email, nombre, contrase, rol, telefono, estado, fecha_registro) VALUES
('MOD002', 'moderador2@logisteia.com', 'Ana García Moderadora', '$2y$10$9lV07lWnzxYVRz/i49buM.5Uv7PU4wuc4gNDTX/C0SlkJvHfmsMNC', 'moderador', '600333444', 'activo', NOW())
ON DUPLICATE KEY UPDATE nombre = nombre;

-- Verificar que se crearon correctamente
SELECT dni, email, nombre, rol, estado, fecha_registro
FROM usuarios
WHERE rol = 'moderador'
ORDER BY fecha_registro DESC;

-- =====================================================================
-- CREDENCIALES DE ACCESO:
-- =====================================================================
-- Email: moderador1@logisteia.com
-- Contraseña: Logisteia2026!
-- 
-- Email: moderador2@logisteia.com
-- Contraseña: Logisteia2026!
-- =====================================================================
