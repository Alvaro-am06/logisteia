-- Script para agregar la columna token_invitacion a la tabla miembros_equipo
-- Ejecutar este script si la tabla ya existe y no tiene la columna token_invitacion

ALTER TABLE miembros_equipo ADD COLUMN token_invitacion VARCHAR(255) NULL AFTER estado_invitacion;

-- Crear índice para mejorar el rendimiento de las búsquedas por token
CREATE INDEX idx_miembros_equipo_token ON miembros_equipo(token_invitacion);