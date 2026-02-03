-- =====================================================
-- SCRIPT SQL OPTIMIZADO PARA PRODUCCIÓN - LOGISTEIA
-- =====================================================
-- Base de datos: MySQL 8.0
-- Descripción: Estructura completa con índices optimizados
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- =====================================================
-- ELIMINAR TABLAS EXISTENTES
-- =====================================================

DROP TABLE IF EXISTS asignaciones_proyecto;
DROP TABLE IF EXISTS acciones_administrativas;
DROP TABLE IF EXISTS servicios_informatica;
DROP TABLE IF EXISTS servicios;
DROP TABLE IF EXISTS detalle_presupuesto;
DROP TABLE IF EXISTS presupuestos;
DROP TABLE IF EXISTS tareas;
DROP TABLE IF EXISTS proyectos;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS miembros_equipo;
DROP TABLE IF EXISTS equipos;
DROP TABLE IF EXISTS usuarios;

-- =====================================================
-- TABLA: usuarios
-- =====================================================

CREATE TABLE IF NOT EXISTS `usuarios` (
  `dni` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `contrase` varchar(255) NOT NULL,
  `rol` enum('jefe_equipo','trabajador','moderador') NOT NULL DEFAULT 'trabajador',
  `estado` enum('activo','suspendido','eliminado') NOT NULL DEFAULT 'activo',
  `telefono` varchar(20) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dni`),
  UNIQUE KEY `usuarios_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: equipos
-- =====================================================

CREATE TABLE IF NOT EXISTS `equipos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `jefe_dni` varchar(255) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `equipos_jefe_dni_index` (`jefe_dni`),
  CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: miembros_equipo
-- =====================================================

CREATE TABLE IF NOT EXISTS `miembros_equipo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `equipo_id` int NOT NULL,
  `trabajador_dni` varchar(255) NOT NULL,
  `rol_proyecto` varchar(255) NOT NULL,
  `fecha_ingreso` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_invitacion` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `token_invitacion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unico_trabajador_equipo` (`equipo_id`,`trabajador_dni`),
  KEY `miembros_equipo_trabajador_dni_index` (`trabajador_dni`),
  KEY `miembros_equipo_equipo_id_index` (`equipo_id`),
  KEY `idx_miembros_equipo_token` (`token_invitacion`),
  CONSTRAINT `miembros_equipo_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `miembros_equipo_ibfk_2` FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: clientes
-- =====================================================

CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `jefe_dni` varchar(255) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `empresa` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text,
  `cif_nif` varchar(20) DEFAULT NULL,
  `notas` text,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `clientes_jefe_dni_index` (`jefe_dni`),
  KEY `clientes_email_index` (`email`),
  CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: proyectos
-- =====================================================

CREATE TABLE IF NOT EXISTS `proyectos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `jefe_dni` varchar(255) NOT NULL,
  `cliente_id` int DEFAULT NULL,
  `equipo_id` int DEFAULT NULL,
  `presupuesto_numero` varchar(255) DEFAULT NULL COMMENT 'Número del presupuesto asociado',
  `estado` enum('creado','en_proceso','finalizado','pausado','cancelado') NOT NULL DEFAULT 'creado',
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  `fecha_fin_real` date DEFAULT NULL COMMENT 'Fecha real de finalización',
  `horas_estimadas` decimal(10,2) DEFAULT '0.00',
  `precio_hora` decimal(10,2) DEFAULT '0.00',
  `precio_total` decimal(10,2) DEFAULT '0.00',
  `tecnologias` text COMMENT 'JSON con tecnologías',
  `repositorio_github` varchar(255) DEFAULT NULL,
  `notas` text,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `equipo_id` (`equipo_id`),
  KEY `proyectos_jefe_dni_index` (`jefe_dni`),
  KEY `proyectos_cliente_id_index` (`cliente_id`),
  KEY `proyectos_estado_index` (`estado`),
  KEY `proyectos_codigo_index` (`codigo`),
  KEY `proyectos_presupuesto_numero_index` (`presupuesto_numero`),
  CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `proyectos_ibfk_2` FOREIGN KEY (`jefe_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `proyectos_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: tareas
-- =====================================================

CREATE TABLE IF NOT EXISTS `tareas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text,
  `trabajador_dni` varchar(255) DEFAULT NULL,
  `rol_requerido` enum('Frontend Developer','Backend Developer','Database Administrator','UI/UX Designer','QA Tester','DevOps Engineer') DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completada','bloqueada') NOT NULL DEFAULT 'pendiente',
  `prioridad` enum('baja','media','alta','critica') NOT NULL DEFAULT 'media',
  `horas_estimadas` decimal(10,2) DEFAULT '0.00',
  `horas_trabajadas` decimal(10,2) DEFAULT '0.00',
  `fecha_inicio` timestamp NULL DEFAULT NULL,
  `fecha_fin` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `tareas_proyecto_id_index` (`proyecto_id`),
  KEY `tareas_trabajador_dni_index` (`trabajador_dni`),
  KEY `tareas_estado_index` (`estado`),
  CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tareas_ibfk_2` FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios` (`dni`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: presupuestos
-- =====================================================

CREATE TABLE IF NOT EXISTS `presupuestos` (
  `id_presupuesto` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `usuario_dni` varchar(255) NOT NULL,
  `numero_presupuesto` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('borrador','enviado','aprobado','rechazado') NOT NULL DEFAULT 'borrador',
  `validez_dias` int NOT NULL DEFAULT '30',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notas` text,
  PRIMARY KEY (`id_presupuesto`),
  UNIQUE KEY `presupuestos_numero_presupuesto_unique` (`numero_presupuesto`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `presupuestos_usuario_dni_index` (`usuario_dni`),
  CONSTRAINT `presupuestos_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `presupuestos_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `presupuestos_ibfk_3` FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: detalle_presupuesto
-- =====================================================

CREATE TABLE IF NOT EXISTS `detalle_presupuesto` (
  `id_linea` int NOT NULL AUTO_INCREMENT,
  `numero_presupuesto` varchar(255) NOT NULL,
  `presupuesto_id` int DEFAULT NULL,
  `servicio_nombre` varchar(255) NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `preci` decimal(10,2) NOT NULL,
  `comentario` text,
  PRIMARY KEY (`id_linea`),
  KEY `detalle_presupuesto_numero_presupuesto_index` (`numero_presupuesto`),
  KEY `detalle_presupuesto_presupuesto_id_index` (`presupuesto_id`),
  CONSTRAINT `detalle_presupuesto_ibfk_1` FOREIGN KEY (`presupuesto_id`) REFERENCES `presupuestos` (`id_presupuesto`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: servicios
-- =====================================================

CREATE TABLE IF NOT EXISTS `servicios` (
  `nombre` varchar(255) NOT NULL,
  `precio_base` decimal(10,2) NOT NULL,
  `descripcion` text,
  `categoria_nombre` varchar(100) DEFAULT NULL,
  `esta_activo` tinyint(1) NOT NULL DEFAULT '1',
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: servicios_informatica
-- =====================================================

CREATE TABLE IF NOT EXISTS `servicios_informatica` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `categoria` enum('Desarrollo Web','Desarrollo Móvil','Base de Datos','UI/UX Design','Testing','DevOps','Infraestructura','Consultoría','Mantenimiento','Otros') NOT NULL,
  `descripcion` text,
  `precio_base` decimal(10,2) DEFAULT '0.00',
  `unidad` enum('hora','proyecto','mes','otro') NOT NULL DEFAULT 'hora',
  `tecnologias` text COMMENT 'JSON con tecnologías',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `fecha_creacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `servicios_informatica_categoria_index` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: acciones_administrativas
-- =====================================================

CREATE TABLE IF NOT EXISTS `acciones_administrativas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `administrador_dni` varchar(255) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `usuario_dni` varchar(255) DEFAULT NULL,
  `proyecto_id` int DEFAULT NULL,
  `equipo_id` int DEFAULT NULL,
  `motivo` text,
  `ip_origen` varchar(45) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `proyecto_id` (`proyecto_id`),
  KEY `equipo_id` (`equipo_id`),
  KEY `acciones_administrativas_administrador_dni_index` (`administrador_dni`),
  KEY `acciones_administrativas_usuario_dni_index` (`usuario_dni`),
  CONSTRAINT `acciones_administrativas_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `acciones_administrativas_ibfk_2` FOREIGN KEY (`equipo_id`) REFERENCES `equipos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `acciones_administrativas_ibfk_3` FOREIGN KEY (`administrador_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `acciones_administrativas_ibfk_4` FOREIGN KEY (`usuario_dni`) REFERENCES `usuarios` (`dni`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: asignaciones_proyecto
-- =====================================================

CREATE TABLE IF NOT EXISTS `asignaciones_proyecto` (
  `id` int NOT NULL AUTO_INCREMENT,
  `proyecto_id` int NOT NULL,
  `trabajador_dni` varchar(255) NOT NULL,
  `rol_asignado` varchar(255) DEFAULT NULL,
  `fecha_asignacion` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_proyecto_trabajador` (`proyecto_id`,`trabajador_dni`),
  KEY `asignaciones_proyecto_proyecto_id_index` (`proyecto_id`),
  KEY `asignaciones_proyecto_trabajador_dni_index` (`trabajador_dni`),
  CONSTRAINT `asignaciones_proyecto_ibfk_1` FOREIGN KEY (`proyecto_id`) REFERENCES `proyectos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `asignaciones_proyecto_ibfk_2` FOREIGN KEY (`trabajador_dni`) REFERENCES `usuarios` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES: USUARIOS
-- =====================================================

-- Admin con contraseña hasheada (bcrypt de 'admin123')
INSERT INTO usuarios (dni, email, nombre, contrase, rol, estado) VALUES
('00000000A', 'admin@logisteia.com', 'Administrador Sistema', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderador', 'activo');

-- Moderador de ejemplo
INSERT INTO usuarios (dni, email, nombre, contrase, rol, estado) VALUES
('11111111B', 'moderador@logisteia.com', 'Moderador Principal', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderador', 'activo');

-- =====================================================
-- DATOS INICIALES: SERVICIOS INFORMÁTICA
-- =====================================================

INSERT INTO servicios_informatica (nombre, categoria, descripcion, precio_base, unidad, activo) VALUES
('Desarrollo Frontend React', 'Desarrollo Web', 'Desarrollo de interfaces con React.js', 45.00, 'hora', 1),
('Desarrollo Backend Node.js', 'Desarrollo Web', 'API REST con Node.js y Express', 50.00, 'hora', 1),
('Diseño UI/UX', 'UI/UX Design', 'Diseño de interfaces y experiencia de usuario', 40.00, 'hora', 1),
('Desarrollo Móvil Flutter', 'Desarrollo Móvil', 'Apps multiplataforma con Flutter', 55.00, 'hora', 1),
('Administración Base de Datos', 'Base de Datos', 'MySQL, PostgreSQL, MongoDB', 48.00, 'hora', 1),
('Testing QA', 'Testing', 'Pruebas funcionales y automatizadas', 35.00, 'hora', 1),
('Configuración CI/CD', 'DevOps', 'Pipelines de integración continua', 60.00, 'proyecto', 1),
('Hosting y Dominio', 'Infraestructura', 'Gestión de servidores y dominios', 25.00, 'mes', 1),
('Consultoría Técnica', 'Consultoría', 'Asesoramiento técnico especializado', 70.00, 'hora', 1),
('Mantenimiento Web', 'Mantenimiento', 'Actualizaciones y soporte mensual', 150.00, 'mes', 1);

-- =====================================================
-- DATOS INICIALES: SERVICIOS (LEGACY)
-- =====================================================

INSERT INTO servicios (nombre, precio_base, descripcion, categoria_nombre, esta_activo) VALUES
('Desarrollo Web Completo', 3500.00, 'Sitio web corporativo completo', 'Desarrollo Web', 1),
('Aplicación Móvil', 8000.00, 'App iOS y Android nativa', 'Desarrollo Móvil', 1),
('E-commerce', 5500.00, 'Tienda online con carrito de compras', 'Desarrollo Web', 1),
('Diseño Gráfico', 500.00, 'Identidad corporativa y branding', 'Diseño', 1),
('SEO y Marketing', 800.00, 'Optimización y estrategia digital', 'Marketing', 1);

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
