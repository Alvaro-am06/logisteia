-- Script para crear equipos para jefes_equipo que no tienen equipo asignado
-- Ejecutar en la base de datos de producción

-- Verificar jefes sin equipo
SELECT 
    u.dni,
    u.nombre,
    u.email,
    u.rol
FROM usuarios u
LEFT JOIN equipos e ON u.dni = e.jefe_dni
WHERE u.rol = 'jefe_equipo' 
AND e.id IS NULL;

-- Crear equipos para jefes sin equipo
INSERT INTO equipos (nombre, descripcion, jefe_dni, activo)
SELECT 
    CONCAT('Equipo de ', u.nombre) as nombre,
    CONCAT('Equipo gestionado por ', u.nombre) as descripcion,
    u.dni as jefe_dni,
    1 as activo
FROM usuarios u
LEFT JOIN equipos e ON u.dni = e.jefe_dni
WHERE u.rol = 'jefe_equipo' 
AND e.id IS NULL;

-- Verificar que se crearon correctamente
SELECT 
    e.id,
    e.nombre,
    e.jefe_dni,
    u.nombre as nombre_jefe,
    e.activo
FROM equipos e
INNER JOIN usuarios u ON e.jefe_dni = u.dni
WHERE u.rol = 'jefe_equipo'
ORDER BY e.id DESC;
