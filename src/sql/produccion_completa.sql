-- =====================================================================
-- LOGISTEIA - SCRIPT COMPLETO PARA PRODUCCIÓN
-- Base de datos para gestión de proyectos en equipo
-- Fecha: 28 de enero de 2026
-- =====================================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS `Logisteia`
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

USE `Logisteia`;

-- =====================================================================
-- 1. TABLA USUARIOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
  `dni` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `contrase` VARCHAR(255) NOT NULL,
  `rol` ENUM('jefe_equipo', 'trabajador', 'moderador') NOT NULL DEFAULT 'trabajador',
  `estado` ENUM('activo', 'baneado', 'eliminado') NOT NULL DEFAULT 'activo',
  `telefono` VARCHAR(20) NULL,
  `avatar` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_baneo` TIMESTAMP NULL,
  `motivo_baneo` TEXT NULL,
  PRIMARY KEY(`dni`),
  UNIQUE KEY `usuarios_email_unique`(`email`),
  INDEX `usuarios_rol_index`(`rol`),
  INDEX `usuarios_estado_index`(`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 2. TABLA EQUIPOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `equipos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  INDEX `equipos_jefe_dni_index`(`jefe_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 3. TABLA MIEMBROS_EQUIPO
-- =====================================================================
CREATE TABLE IF NOT EXISTS `miembros_equipo` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `equipo_id` INT NOT NULL,
  `trabajador_dni` VARCHAR(255) NOT NULL,
  `rol_proyecto` VARCHAR(255) NOT NULL,
  `fecha_ingreso` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `estado_invitacion` ENUM('pendiente', 'aceptada', 'rechazada') NOT NULL DEFAULT 'pendiente',
  `token_invitacion` VARCHAR(255) NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  UNIQUE KEY `unico_trabajador_equipo` (`equipo_id`, `trabajador_dni`),
  INDEX `miembros_equipo_trabajador_dni_index`(`trabajador_dni`),
  INDEX `miembros_equipo_equipo_id_index`(`equipo_id`),
  INDEX `idx_miembros_equipo_token`(`token_invitacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 4. TABLA CLIENTES
-- =====================================================================
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `empresa` VARCHAR(255) NULL,
  `email` VARCHAR(255) NOT NULL,
  `telefono` VARCHAR(20) NULL,
  `direccion` TEXT NULL,
  `cif_nif` VARCHAR(20) NULL,
  `notas` TEXT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  INDEX `clientes_jefe_dni_index`(`jefe_dni`),
  INDEX `clientes_email_index`(`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 5. TABLA PROYECTOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `codigo` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `cliente_id` INT NULL,
  `equipo_id` INT NULL,
  `estado` ENUM('creado', 'en_proceso', 'finalizado', 'pausado', 'cancelado') NOT NULL DEFAULT 'creado',
  `fecha_inicio` DATE NULL,
  `fecha_fin_estimada` DATE NULL,
  `fecha_fin_real` DATE NULL,
  `horas_estimadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `horas_trabajadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `precio_hora` DECIMAL(10, 2) NULL DEFAULT 0,
  `precio_total` DECIMAL(10, 2) NULL DEFAULT 0,
  `tecnologias` TEXT NULL COMMENT 'JSON con tecnologías',
  `repositorio_github` VARCHAR(255) NULL,
  `notas` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_actualizacion` TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE SET NULL,
  INDEX `proyectos_jefe_dni_index`(`jefe_dni`),
  INDEX `proyectos_cliente_id_index`(`cliente_id`),
  INDEX `proyectos_estado_index`(`estado`),
  INDEX `proyectos_codigo_index`(`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 6. TABLA TAREAS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `tareas` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `proyecto_id` INT NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `descripcion` TEXT NULL,
  `trabajador_dni` VARCHAR(255) NULL,
  `rol_requerido` ENUM(
    'Frontend Developer',
    'Backend Developer',
    'Database Administrator',
    'UI/UX Designer',
    'QA Tester',
    'DevOps Engineer'
  ) NULL,
  `estado` ENUM('pendiente', 'en_progreso', 'completada', 'bloqueada') NOT NULL DEFAULT 'pendiente',
  `prioridad` ENUM('baja', 'media', 'alta', 'critica') NOT NULL DEFAULT 'media',
  `horas_estimadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `horas_trabajadas` DECIMAL(10, 2) NULL DEFAULT 0,
  `fecha_inicio` TIMESTAMP NULL,
  `fecha_fin` TIMESTAMP NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE SET NULL,
  INDEX `tareas_proyecto_id_index`(`proyecto_id`),
  INDEX `tareas_trabajador_dni_index`(`trabajador_dni`),
  INDEX `tareas_estado_index`(`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 7. TABLA REGISTRO_HORAS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `registro_horas` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tarea_id` INT NOT NULL,
  `trabajador_dni` VARCHAR(255) NOT NULL,
  `proyecto_id` INT NOT NULL,
  `hora_inicio` TIMESTAMP NOT NULL,
  `hora_fin` TIMESTAMP NULL,
  `duracion_minutos` INT NULL COMMENT 'Calculado automáticamente',
  `descripcion` TEXT NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`tarea_id`) REFERENCES `tareas`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE CASCADE,
  INDEX `registro_horas_tarea_id_index`(`tarea_id`),
  INDEX `registro_horas_trabajador_dni_index`(`trabajador_dni`),
  INDEX `registro_horas_proyecto_id_index`(`proyecto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 8. TABLA PRESUPUESTOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id_presupuesto` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `proyecto_id` INT NULL,
  `cliente_id` INT NULL,
  `usuario_dni` VARCHAR(255) NOT NULL,
  `numero_presupuesto` VARCHAR(255) NOT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `estado` ENUM('borrador', 'enviado', 'aprobado', 'rechazado') NOT NULL DEFAULT 'borrador',
  `validez_dias` INT NOT NULL DEFAULT 30,
  `horas_totales` DECIMAL(10, 2) NULL DEFAULT 0,
  `precio_por_hora` DECIMAL(10, 2) NULL DEFAULT 0,
  `total` DECIMAL(10, 2) NOT NULL DEFAULT 0,
  `notas` TEXT NULL,
  FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios`(`dni`),
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL,
  UNIQUE KEY `presupuestos_numero_presupuesto_unique`(`numero_presupuesto`),
  INDEX `presupuestos_usuario_dni_index`(`usuario_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 9. TABLA DETALLE_PRESUPUESTO
-- =====================================================================
CREATE TABLE IF NOT EXISTS `detalle_presupuesto` (
  `id_linea` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `numero_presupuesto` VARCHAR(255) NOT NULL,
  `presupuesto_id` INT NULL,
  `servicio_nombre` VARCHAR(255) NOT NULL,
  `cantidad` INT NOT NULL DEFAULT 1,
  `preci` DECIMAL(10, 2) NOT NULL,
  `comentario` TEXT NULL,
  INDEX `detalle_presupuesto_numero_presupuesto_index`(`numero_presupuesto`),
  INDEX `detalle_presupuesto_presupuesto_id_index`(`presupuesto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 10. TABLA SERVICIOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `servicios` (
  `nombre` VARCHAR(255) NOT NULL,
  `precio_base` DECIMAL(10, 2) NOT NULL,
  `descripcion` TEXT NULL,
  `categoria_nombre` VARCHAR(100) NULL,
  `esta_activo` TINYINT(1) NOT NULL DEFAULT 1,
  `actualizado_en` TIMESTAMP NULL,
  PRIMARY KEY(`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 11. TABLA SERVICIOS_INFORMATICA
-- =====================================================================
CREATE TABLE IF NOT EXISTS `servicios_informatica` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `categoria` ENUM(
    'Desarrollo Web',
    'Desarrollo Móvil',
    'Base de Datos',
    'UI/UX Design',
    'Testing',
    'DevOps',
    'Infraestructura',
    'Consultoría',
    'Mantenimiento',
    'Otros'
  ) NOT NULL,
  `descripcion` TEXT NULL,
  `precio_base` DECIMAL(10, 2) NULL DEFAULT 0,
  `unidad` ENUM('hora', 'proyecto', 'mes', 'otro') NOT NULL DEFAULT 'hora',
  `tecnologias` TEXT NULL COMMENT 'JSON con tecnologías',
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  INDEX `servicios_informatica_categoria_index`(`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 12. TABLA FACTURAS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `facturas` (
  `factura_numero` VARCHAR(50) NOT NULL,
  `usuario_dni` VARCHAR(255) NOT NULL,
  `presupuesto_numero` VARCHAR(255) NULL,
  `fecha_emision` DATE NOT NULL,
  `fecha_vencimiento` DATE NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT 0,
  `iva` DECIMAL(10, 2) NOT NULL DEFAULT 0,
  `total_factura` DECIMAL(10, 2) NOT NULL DEFAULT 0,
  `estado` ENUM('pendiente', 'pagada', 'vencida', 'anulada') NOT NULL DEFAULT 'pendiente',
  `nombre_servicios` VARCHAR(255) NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY(`factura_numero`),
  FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios`(`dni`),
  INDEX `facturas_usuario_dni_index`(`usuario_dni`),
  INDEX `facturas_presupuesto_numero_index`(`presupuesto_numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 13. TABLA PAGOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `pagos` (
  `numero_pago` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `factura_numero` VARCHAR(50) NOT NULL,
  `fecha_pago` DATETIME NOT NULL,
  `importe` DECIMAL(10, 2) NOT NULL,
  `metodo_pago` ENUM('transferencia', 'tarjeta', 'otro') NOT NULL,
  `referencia` VARCHAR(255) NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`factura_numero`) REFERENCES `facturas`(`factura_numero`),
  INDEX `pagos_factura_numero_index`(`factura_numero`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 14. TABLA ACCIONES_ADMINISTRATIVAS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `acciones_administrativas` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `administrador_dni` VARCHAR(255) NOT NULL,
  `accion` VARCHAR(100) NOT NULL,
  `usuario_dni` VARCHAR(255) NULL,
  `proyecto_id` INT NULL,
  `equipo_id` INT NULL,
  `motivo` TEXT NULL,
  `ip_origen` VARCHAR(45) NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`administrador_dni`) REFERENCES `usuarios`(`dni`),
  FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios`(`dni`),
  FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE SET NULL,
  INDEX `acciones_administrativas_administrador_dni_index`(`administrador_dni`),
  INDEX `acciones_administrativas_usuario_dni_index`(`usuario_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 15. TABLA HISTORIAL_BANEOS
-- =====================================================================
CREATE TABLE IF NOT EXISTS `historial_baneos` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `usuario_dni` VARCHAR(255) NOT NULL,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `motivo` TEXT NOT NULL,
  `fecha_baneo` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_desbaneo` TIMESTAMP NULL,
  `activo` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=baneado, 0=desbaneado',
  FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  INDEX `historial_baneos_usuario_dni_index`(`usuario_dni`),
  INDEX `historial_baneos_jefe_dni_index`(`jefe_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 16. TABLA INVITACIONES
-- =====================================================================
CREATE TABLE IF NOT EXISTS `invitaciones` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `jefe_dni` VARCHAR(255) NOT NULL,
  `trabajador_email` VARCHAR(255) NOT NULL,
  `equipo_id` INT NOT NULL,
  `rol_proyecto` ENUM(
    'Frontend Developer',
    'Backend Developer',
    'Database Administrator',
    'UI/UX Designer',
    'QA Tester',
    'DevOps Engineer'
  ) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `estado` ENUM('pendiente', 'aceptada', 'rechazada', 'expirada') NOT NULL DEFAULT 'pendiente',
  `mensaje` TEXT NULL,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `fecha_expiracion` TIMESTAMP NULL,
  `fecha_respuesta` TIMESTAMP NULL,
  FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  FOREIGN KEY (`equipo_id`) REFERENCES `equipos`(`id`) ON DELETE CASCADE,
  INDEX `invitaciones_trabajador_email_index`(`trabajador_email`),
  INDEX `invitaciones_token_index`(`token`),
  INDEX `invitaciones_estado_index`(`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- 17. TABLA ASIGNACIONES_PROYECTO (para compatibilidad con Proyecto.php)
-- =====================================================================
CREATE TABLE IF NOT EXISTS `asignaciones_proyecto` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `proyecto_id` INT NOT NULL,
  `trabajador_dni` VARCHAR(255) NOT NULL,
  `rol_asignado` VARCHAR(255) NULL,
  `fecha_asignacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  FOREIGN KEY (`proyecto_id`) REFERENCES `presupuestos`(`id_presupuesto`) ON DELETE CASCADE,
  FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios`(`dni`) ON DELETE CASCADE,
  UNIQUE KEY `unique_proyecto_trabajador` (`proyecto_id`, `trabajador_dni`),
  INDEX `asignaciones_proyecto_proyecto_id_index`(`proyecto_id`),
  INDEX `asignaciones_proyecto_trabajador_dni_index`(`trabajador_dni`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- DATOS INICIALES
-- =====================================================================

-- Usuarios de ejemplo
-- Contraseñas (todas son '1234'):
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO usuarios (dni, email, nombre, contrase, rol, estado) VALUES
('11111111A', 'admin@logisteia.com', 'Administrador Sistema', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderador', 'activo'),
('22222222B', 'jefe@logisteia.com', 'Juan Pérez - Jefe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jefe_equipo', 'activo'),
('33333333C', 'trabajador@logisteia.com', 'María López - Trabajadora', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trabajador', 'activo')
ON DUPLICATE KEY UPDATE email = email;

-- Equipo de ejemplo
INSERT INTO equipos (nombre, descripcion, jefe_dni) VALUES
('Equipo Alpha', 'Equipo de desarrollo principal', '22222222B')
ON DUPLICATE KEY UPDATE nombre = nombre;

-- Servicios de informática
INSERT INTO servicios_informatica (nombre, categoria, descripcion, precio_base, unidad, activo) VALUES
('Desarrollo Web Frontend', 'Desarrollo Web', 'Desarrollo de interfaces con React, Angular o Vue', 45.00, 'hora', 1),
('Desarrollo Web Backend', 'Desarrollo Web', 'Desarrollo de APIs REST con PHP, Node.js o Python', 50.00, 'hora', 1),
('Diseño UI/UX', 'UI/UX Design', 'Diseño de interfaces y experiencia de usuario', 40.00, 'hora', 1),
('Administración Base de Datos', 'Base de Datos', 'Diseño, optimización y mantenimiento de BD', 55.00, 'hora', 1),
('Testing y QA', 'Testing', 'Pruebas funcionales y automatizadas', 35.00, 'hora', 1),
('DevOps y CI/CD', 'DevOps', 'Configuración de pipelines y despliegue continuo', 60.00, 'hora', 1),
('Desarrollo Móvil', 'Desarrollo Móvil', 'Apps nativas o híbridas para iOS/Android', 50.00, 'hora', 1),
('Consultoría Técnica', 'Consultoría', 'Asesoramiento en arquitectura y tecnologías', 70.00, 'hora', 1),
('Mantenimiento Web', 'Mantenimiento', 'Mantenimiento mensual de aplicaciones web', 300.00, 'mes', 1),
('Infraestructura Cloud', 'Infraestructura', 'Configuración en AWS, Azure o Google Cloud', 65.00, 'hora', 1)
ON DUPLICATE KEY UPDATE nombre = nombre;

-- Servicios generales (legacy para compatibilidad)
INSERT INTO servicios (nombre, precio_base, descripcion, categoria_nombre, esta_activo) VALUES
('Desarrollo Web Completo', 2500.00, 'Desarrollo de sitio web completo', 'Desarrollo', 1),
('Diseño Gráfico', 500.00, 'Diseño de marca e identidad visual', 'Diseño', 1),
('SEO y Marketing Digital', 800.00, 'Optimización y estrategia de marketing', 'Marketing', 1),
('Soporte Técnico', 150.00, 'Soporte técnico mensual', 'Soporte', 1),
('Hosting Premium', 100.00, 'Hosting con SSL y backup diario', 'Infraestructura', 1)
ON DUPLICATE KEY UPDATE nombre = nombre;

-- =====================================================================
-- FIN DEL SCRIPT
-- =====================================================================
-- Para ejecutar: mysql -u usuario -p < produccion_completa.sql
-- O importar desde phpMyAdmin/Adminer
