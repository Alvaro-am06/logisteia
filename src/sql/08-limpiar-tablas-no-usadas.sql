-- =====================================================
-- LIMPIEZA: Eliminar tablas no utilizadas
-- =====================================================
-- Fecha: 2026-02-03
-- Descripción: Elimina tablas facturas y pagos que no se están usando
--              en la aplicación actual
-- =====================================================

USE logisteia;

-- Nota: Ejecutar solo si confirmaste que estas tablas no se usan
-- en ningún código de la aplicación

-- Verificar si existen datos en las tablas antes de eliminar
SELECT 
    'Tabla facturas:' AS info,
    COUNT(*) AS total_registros
FROM facturas
WHERE EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES 
              WHERE TABLE_SCHEMA = 'logisteia' 
              AND TABLE_NAME = 'facturas');

SELECT 
    'Tabla pagos:' AS info,
    COUNT(*) AS total_registros
FROM pagos
WHERE EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.TABLES 
              WHERE TABLE_SCHEMA = 'logisteia' 
              AND TABLE_NAME = 'pagos');

-- ADVERTENCIA: Descomentar solo si estás seguro de eliminar estas tablas

-- SET FOREIGN_KEY_CHECKS = 0;
-- DROP TABLE IF EXISTS pagos;
-- DROP TABLE IF EXISTS facturas;
-- SET FOREIGN_KEY_CHECKS = 1;

-- SELECT 'Tablas eliminadas correctamente' AS status;

-- =====================================================
-- NOTA IMPORTANTE
-- =====================================================
-- Las tablas facturas y pagos NO se usan en el código actual:
-- - No hay referencias en PHP
-- - No hay referencias en TypeScript/Angular
-- - No hay foreign keys desde otras tablas que se usen
--
-- Sin embargo, están comentadas por seguridad.
-- Revisa los datos antes de descomentar y ejecutar.
-- =====================================================
