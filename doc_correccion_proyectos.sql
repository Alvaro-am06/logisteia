-- =====================================================================
-- DOCUMENTACIÓN: Solución a problemas de Proyectos y Presupuestos
-- =====================================================================
-- Fecha: 28 de enero de 2026
-- Problema: El modelo Proyecto.php insertaba en "presupuestos" en lugar de "proyectos"
-- Solución: Modelo corregido para usar tabla proyectos correctamente
-- =====================================================================

-- 1. ESTRUCTURA CORRECTA DE LAS TABLAS
-- =====================================================================

-- TABLA PROYECTOS (tabla principal para proyectos)
-- - Se usa para crear y gestionar proyectos
-- - Tiene campos: nombre, descripcion, tecnologias, repositorio_github, etc.
-- - Inserta desde: Proyecto.php, PresupuestoWizard.php

-- TABLA PRESUPUESTOS (tabla separada para presupuestos)
-- - Se usa para crear presupuestos económicos para clientes
-- - Tiene campos: numero_presupuesto, validez_dias, total, estado
-- - Puede estar vinculado a un proyecto (campo proyecto_id)
-- - Los presupuestos se crean DESPUÉS del proyecto, en una pantalla separada

-- 2. FLUJO CORRECTO
-- =====================================================================
-- PASO 1: Jefe crea un PROYECTO → INSERT INTO proyectos
-- PASO 2: (Opcional) Jefe crea un PRESUPUESTO para el proyecto → INSERT INTO presupuestos (con proyecto_id)

-- 3. CAMBIOS REALIZADOS EN EL CÓDIGO
-- =====================================================================
-- ✅ Proyecto.php → crearProyecto() ahora inserta en tabla "proyectos"
-- ✅ Proyecto.php → generarCodigoProyecto() genera código PRY-YYYYMMDD-XXXX
-- ✅ Proyecto.php → obtenerProyectosPorJefe() consulta tabla "proyectos"
-- ✅ Proyecto.php → obtenerProyectosPorTrabajador() consulta tabla "proyectos"

-- 4. VERIFICAR DATOS EXISTENTES
-- =====================================================================

-- Ver proyectos actuales en la tabla correcta
SELECT id, codigo, nombre, jefe_dni, estado, fecha_creacion
FROM proyectos
ORDER BY fecha_creacion DESC;

-- Ver "proyectos" que se guardaron incorrectamente en presupuestos
SELECT id_presupuesto, numero_presupuesto, usuario_dni, total, notas
FROM presupuestos
WHERE notas LIKE '%PROYECTO:%'
ORDER BY fecha_creacion DESC;

-- 5. MIGRAR DATOS (SI HAY "PROYECTOS" EN PRESUPUESTOS)
-- =====================================================================
-- Solo ejecutar si encuentras datos con "PROYECTO:" en presupuestos

/*
INSERT INTO proyectos (codigo, nombre, descripcion, jefe_dni, cliente_id, estado, precio_total, notas, fecha_inicio)
SELECT 
    numero_presupuesto as codigo,
    SUBSTRING_INDEX(SUBSTRING_INDEX(notas, 'PROYECTO: ', -1), '\n', 1) as nombre,
    SUBSTRING_INDEX(SUBSTRING_INDEX(notas, 'Descripción: ', -1), '\n', 1) as descripcion,
    usuario_dni as jefe_dni,
    cliente_id,
    CASE estado
        WHEN 'borrador' THEN 'creado'
        WHEN 'enviado' THEN 'en_proceso'
        WHEN 'aprobado' THEN 'en_proceso'
        ELSE 'creado'
    END as estado,
    total as precio_total,
    notas,
    CURDATE() as fecha_inicio
FROM presupuestos
WHERE notas LIKE '%PROYECTO:%';

-- Actualizar referencias en presupuestos después de migrar
UPDATE presupuestos p
INNER JOIN proyectos pr ON p.numero_presupuesto = pr.codigo
SET p.proyecto_id = pr.id
WHERE p.notas LIKE '%PROYECTO:%';
*/

-- 6. LIMPIAR NOTAS INCORRECTAS (OPCIONAL)
-- =====================================================================
-- Si migraste los datos y quieres limpiar las notas de presupuestos:
/*
UPDATE presupuestos
SET notas = NULL
WHERE notas LIKE '%PROYECTO:%';
*/

-- =====================================================================
-- FIN DE LA DOCUMENTACIÓN
-- =====================================================================
