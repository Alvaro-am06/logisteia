
CREATE TABLE `usuarios`(
  `dni` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `nombre` VARCHAR(255) NOT NULL,
  `contrase` VARCHAR(255) NOT NULL,
  `rol` ENUM('administrador', 'registrado') NOT NULL DEFAULT 'registrado',
  `telefono` VARCHAR(20) NULL,
  `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(), PRIMARY KEY(`dni`));
ALTER TABLE
  `usuarios` ADD UNIQUE `usuarios_email_unique`(`email`);
CREATE TABLE `servicios`(
  `nombre` VARCHAR(255) NOT NULL,
  `precio_base` DECIMAL(10, 2) NOT NULL,
  `descripcion` TEXT NULL,
  `categoria_nombre` VARCHAR(100) NULL,
  `esta_activo` TINYINT(1) NOT NULL DEFAULT '1',
  `actualizado_en` TIMESTAMP NULL,
  PRIMARY KEY(`nombre`)
);
CREATE TABLE `presupuestos`(
  `id_presupuesto` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `usuario_dni` VARCHAR(255) NOT NULL,
  `numero_presupuesto` VARCHAR(255) NOT NULL,
  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP(), `estado` ENUM(
    'borrador',
    'enviado',
    'aprobado',
    'rechazado'
  ) NOT NULL DEFAULT 'borrador', `validez_dias` INT NOT NULL DEFAULT '30', `total` DECIMAL(10, 2) NOT NULL DEFAULT '0', `notas` TEXT NULL);
ALTER TABLE
  `presupuestos` ADD INDEX `presupuestos_usuario_dni_index`(`usuario_dni`);
ALTER TABLE
  `presupuestos` ADD UNIQUE `presupuestos_numero_presupuesto_unique`(`numero_presupuesto`);
CREATE TABLE `detalle_presupuesto`(
  `id_linea` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `numero_presupuesto` VARCHAR(255) NOT NULL,
  `presupuesto_id` INT NULL DEFAULT 'DEFAULT NULL',
  `servicio_nombre` VARCHAR(255) NOT NULL,
  `cantidad` INT NOT NULL DEFAULT '1',
  `preci` DECIMAL(10, 2) NOT NULL,
  `comentario` TEXT NULL
);
ALTER TABLE
  `detalle_presupuesto` ADD INDEX `detalle_presupuesto_numero_presupuesto_index`(`numero_presupuesto`);
ALTER TABLE
  `detalle_presupuesto` ADD INDEX `detalle_presupuesto_presupuesto_id_index`(`presupuesto_id`);
CREATE TABLE `facturas`(
  `factura_numero` VARCHAR(50) NOT NULL,
  `usuario_dni` VARCHAR(255) NOT NULL,
  `presupuesto_numero` VARCHAR(255) NULL,
  `fecha_emision` DATE NOT NULL,
  `fecha_vencimiento` DATE NULL,
  `subtotal` DECIMAL(10, 2) NOT NULL DEFAULT '0',
  `iva` DECIMAL(10, 2) NOT NULL DEFAULT '0',
  `total_factura` DECIMAL(10, 2) NOT NULL DEFAULT '0',
  `estado` ENUM(
    'pendiente',
    'pagada',
    'vencida',
    'anulada'
  ) NOT NULL DEFAULT 'pendiente',
  `nombre_servicios` VARCHAR(255) NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(), PRIMARY KEY(`factura_numero`));
ALTER TABLE
  `facturas` ADD INDEX `facturas_usuario_dni_index`(`usuario_dni`);
ALTER TABLE
  `facturas` ADD INDEX `facturas_presupuesto_numero_index`(`presupuesto_numero`);
CREATE TABLE `pagos`(
  `numero_pago` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `factura_numero` VARCHAR(50) NOT NULL,
  `fecha_pago` DATETIME NOT NULL,
  `importe` DECIMAL(10, 2) NOT NULL,
  `metodo_pago` ENUM('transferencia', 'tarjeta', 'otro') NOT NULL,
  `referencia` VARCHAR(255) NULL,
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP());
ALTER TABLE
  `pagos` ADD INDEX `pagos_factura_numero_index`(`factura_numero`);
CREATE TABLE `acciones_administrativas`(
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `administrador_dni` VARCHAR(255) NOT NULL,
  `accion` VARCHAR(100) NOT NULL,
  `usuario_dni` VARCHAR(255) NULL DEFAULT 'DEFAULT NULL',
  `motivo` TEXT NULL DEFAULT 'DEFAULT NULL',
  `ip_origen` VARCHAR(45) NULL DEFAULT 'DEFAULT NULL',
  `creado_en` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP());
ALTER TABLE
  `acciones_administrativas` ADD INDEX `acciones_administrativas_administrador_dni_index`(`administrador_dni`);
ALTER TABLE
  `acciones_administrativas` ADD INDEX `acciones_administrativas_usuario_dni_index`(`usuario_dni`);
ALTER TABLE
  `acciones_administrativas` ADD CONSTRAINT `acciones_administrativas_usuario_dni_foreign` FOREIGN KEY(`usuario_dni`) REFERENCES `usuarios`(`dni`);
ALTER TABLE
  `acciones_administrativas` ADD CONSTRAINT `acciones_administrativas_administrador_dni_foreign` FOREIGN KEY(`administrador_dni`) REFERENCES `usuarios`(`dni`);
ALTER TABLE
  `presupuestos` ADD CONSTRAINT `presupuestos_usuario_dni_foreign` FOREIGN KEY(`usuario_dni`) REFERENCES `usuarios`(`dni`);
ALTER TABLE
  `detalle_presupuesto` ADD CONSTRAINT `detalle_presupuesto_servicio_nombre_foreign` FOREIGN KEY(`servicio_nombre`) REFERENCES `servicios`(`nombre`);
ALTER TABLE
  `pagos` ADD CONSTRAINT `pagos_factura_numero_foreign` FOREIGN KEY(`factura_numero`) REFERENCES `facturas`(`factura_numero`);
ALTER TABLE
  `detalle_presupuesto` ADD CONSTRAINT `detalle_presupuesto_numero_presupuesto_foreign` FOREIGN KEY(`numero_presupuesto`) REFERENCES `presupuestos`(`numero_presupuesto`);
ALTER TABLE
  `detalle_presupuesto` ADD CONSTRAINT `detalle_presupuesto_presupuesto_id_foreign` FOREIGN KEY(`presupuesto_id`) REFERENCES `presupuestos`(`id_presupuesto`);
ALTER TABLE
  `facturas` ADD CONSTRAINT `facturas_nombre_servicios_foreign` FOREIGN KEY(`nombre_servicios`) REFERENCES `servicios`(`nombre`);
ALTER TABLE
  `facturas` ADD CONSTRAINT `facturas_usuario_dni_foreign` FOREIGN KEY(`usuario_dni`) REFERENCES `usuarios`(`dni`);
ALTER TABLE
  `facturas` ADD CONSTRAINT `facturas_presupuesto_numero_foreign` FOREIGN KEY(`presupuesto_numero`) REFERENCES `presupuestos`(`numero_presupuesto`);