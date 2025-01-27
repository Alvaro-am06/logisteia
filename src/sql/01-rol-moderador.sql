-- Agregar rol 'moderador' a la tabla usuarios
-- Ejecutar desde: migración manual o script PHP

USE `Logisteia`;

-- Modificar el ENUM de rol para incluir moderador
ALTER TABLE `usuarios` 
  MODIFY `rol` ENUM('jefe_equipo', 'trabajador', 'moderador') NOT NULL DEFAULT 'trabajador';

-- Nota: Los moderadores no necesitan equipo, son supervisores globales
