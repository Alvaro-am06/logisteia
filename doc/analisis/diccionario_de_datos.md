# Diccionario de datos — LOGISTEIA

Este documento describe la estructura de tablas, columnas, tipos, restricciones e índices del modelo de datos de LOGISTEIA.

---

## usuarios
**Descripción:** Usuarios del sistema (clientes y administradores).

| Columna         | Tipo                              | Nulo | Valor por defecto      | PK / UNQ / FK                | Comentarios                                 |
|-----------------|-----------------------------------|------|-----------------------|-------------------------------|---------------------------------------------|
| dni             | VARCHAR(255)                      | NO   | —                     | PK                            | Identificador principal del usuario         |
| email           | VARCHAR(255)                      | NO   | —                     | UNIQUE (usuarios_email_unique)| Correo electrónico; único                  |
| nombre          | VARCHAR(255)                      | NO   | —                     | —                             | Nombre completo o razón social             |
| contrase        | VARCHAR(255)                      | NO   | —                     | —                             | Hash de contraseña (almacenar cifrado)     |
| rol             | ENUM('administrador','registrado')| NO   | 'registrado'          | —                             | Control de permisos básico                 |
| telefono        | VARCHAR(20)                       | SÍ   | NULL                  | —                             | Teléfono de contacto                       |
| fecha_registro  | TIMESTAMP                         | NO   | CURRENT_TIMESTAMP()   | —                             | Fecha de alta del usuario                  |

---

## servicios
**Descripción:** Catálogo de servicios ofrecidos.

| Columna         | Tipo              | Nulo | Valor por defecto | PK / UNQ / FK | Comentarios                       |
|-----------------|-------------------|------|------------------|---------------|-----------------------------------|
| nombre          | VARCHAR(255)      | NO   | —                | PK            | Identificador del servicio        |
| precio_base     | DECIMAL(10,2)     | NO   | —                | —             | Precio base del servicio          |
| descripcion     | TEXT              | SÍ   | NULL             | —             | Descripción extendida             |
| categoria_nombre| VARCHAR(100)      | SÍ   | NULL             | —             | Nombre de categoría (opcional)    |
| esta_activo     | TINYINT(1)        | NO   | 1                | —             | Indica si el servicio está activo |
| actualizado_en  | TIMESTAMP         | SÍ   | NULL             | —             | Fecha última actualización        |

---

## presupuestos
**Descripción:** Cabeceras de presupuestos generados por usuarios.

| Columna            | Tipo                                      | Nulo | Valor por defecto      | PK / UNQ / FK                        | Comentarios                          |
|--------------------|-------------------------------------------|------|-----------------------|---------------------------------------|--------------------------------------|
| id_presupuesto     | INT                                       | NO   | AUTO_INCREMENT        | PK                                    | Identificador numérico               |
| usuario_dni        | VARCHAR(255)                              | NO   | —                     | FK → usuarios(dni); INDEX             | Usuario que creó el presupuesto      |
| numero_presupuesto | VARCHAR(255)                              | NO   | —                     | UNIQUE (presupuestos_numero_presupuesto_unique) | Número/UUID del presupuesto |
| fecha_creacion     | DATETIME                                  | NO   | CURRENT_TIMESTAMP()   | —                                     | Fecha de creación                    |
| estado             | ENUM('borrador','enviado','aprobado','rechazado') | NO   | 'borrador'            | —                                     | Estado del presupuesto               |
| validez_dias       | INT                                       | NO   | 30                    | —                                     | Días de validez                      |
| total              | DECIMAL(10,2)                             | NO   | 0                     | —                                     | Importe total calculado              |
| notas              | TEXT                                      | SÍ   | NULL                  | —                                     | Observaciones libres                  |

---

## detalle_presupuesto
**Descripción:** Líneas del presupuesto, una por servicio incluido.

| Columna            | Tipo              | Nulo | Valor por defecto | PK / UNQ / FK | Comentarios                                 |
|--------------------|-------------------|------|------------------|---------------|---------------------------------------------|
| id_linea           | INT               | NO   | AUTO_INCREMENT    | PK            | Identificador de línea                      |
| numero_presupuesto | VARCHAR(255)      | NO   | —                | FK → presupuestos(numero_presupuesto); INDEX| Referencia al presupuesto (clave natural)   |
| presupuesto_id     | INT               | SÍ   | NULL             | FK → presupuestos(id_presupuesto); INDEX    | Referencia al id autoincremental (opcional)|
| servicio_nombre    | VARCHAR(255)      | NO   | —                | FK → servicios(nombre)                      | Servicio asociado a la línea                |
| cantidad           | INT               | NO   | 1                | —            | Cantidad solicitada                         |
| preci              | DECIMAL(10,2)     | NO   | —                | —            | Precio por unidad aplicado en la línea      |
| comentario         | TEXT              | SÍ   | NULL             | —            | Notas específicas de la línea               |

---

## facturas
**Descripción:** Facturas emitidas; pueden relacionarse con presupuestos y usuarios.

| Columna            | Tipo              | Nulo | Valor por defecto | PK / UNQ / FK | Comentarios                                 |
|--------------------|-------------------|------|------------------|---------------|---------------------------------------------|
| factura_numero     | VARCHAR(50)       | NO   | —                | PK            | Clave de factura (string)                   |
| usuario_dni        | VARCHAR(255)      | NO   | —                | FK → usuarios(dni); INDEX                   | Cliente al que se emite la factura         |
| presupuesto_numero | VARCHAR(255)      | SÍ   | NULL             | FK → presupuestos(numero_presupuesto); INDEX| Si la factura proviene de un presupuesto   |
| fecha_emision      | DATE              | NO   | —                | —            | Fecha de emisión                            |
| fecha_vencimiento  | DATE              | SÍ   | NULL             | —            | Fecha límite de pago                        |
| subtotal           | DECIMAL(10,2)     | NO   | 0                | —            | Importe antes de impuestos                  |
| iva                | DECIMAL(10,2)     | NO   | 0                | —            | Importe de IVA                              |
| total_factura      | DECIMAL(10,2)     | NO   | 0                | —            | Importe total facturado                     |
| estado             | ENUM('pendiente','pagada','vencida','anulada') | NO   | 'pendiente' | — | Estado de la factura                       |
| nombre_servicios   | VARCHAR(255)      | SÍ   | NULL             | FK → servicios(nombre)                      | Campo libre que referencia servicios       |
| creado_en          | TIMESTAMP         | NO   | CURRENT_TIMESTAMP() | —         | Fecha de creación del registro              |

---

## pagos
**Descripción:** Registro de pagos asociados a facturas.

| Columna        | Tipo              | Nulo | Valor por defecto | PK / UNQ / FK | Comentarios                                 |
|---------------|-------------------|------|------------------|---------------|---------------------------------------------|
| numero_pago   | INT               | NO   | AUTO_INCREMENT    | PK            | Identificador del pago                      |
| factura_numero| VARCHAR(50)       | NO   | —                | FK → facturas(factura_numero); INDEX        | Factura a la que se aplica el pago         |
| fecha_pago    | DATETIME          | NO   | —                | —            | Marca temporal del pago                     |
| importe       | DECIMAL(10,2)     | NO   | —                | —            | Importe del pago                            |
| metodo_pago   | ENUM('transferencia','tarjeta','otro') | NO | — | — | Método usado                                 |
| referencia    | VARCHAR(255)      | SÍ   | NULL             | —            | Referencia externa (transacción)            |
| creado_en     | TIMESTAMP         | NO   | CURRENT_TIMESTAMP() | —         | Fecha registro                              |

---

## acciones_administrativas
**Descripción:** Registro de acciones realizadas por administradores sobre usuarios.

| Columna            | Tipo              | Nulo | Valor por defecto | PK / UNQ / FK | Comentarios                                 |
|--------------------|-------------------|------|------------------|---------------|---------------------------------------------|
| id                 | INT               | NO   | AUTO_INCREMENT    | PK            | Identificador de la acción                   |
| administrador_dni  | VARCHAR(255)      | NO   | —                | FK → usuarios(dni); INDEX                   | Administrador que ejecutó la acción         |
| accion             | VARCHAR(100)      | NO   | —                | —            | Tipo de acción                               |
| usuario_dni        | VARCHAR(255)      | SÍ   | NULL             | FK → usuarios(dni); INDEX                   | Usuario objeto de la acción (opcional)      |
| motivo             | TEXT              | SÍ   | NULL             | —            | Razón o detalle                              |
| ip_origen          | VARCHAR(45)       | SÍ   | NULL             | —            | IP desde la que se realizó la acción         |
| creado_en          | TIMESTAMP         | NO   | CURRENT_TIMESTAMP() | —         | Fecha de registro                            |

---

## Resumen de claves foráneas
- acciones_administrativas.usuario_dni → usuarios.dni (FK acciones_administrativas_usuario_dni_foreign)
- acciones_administrativas.administrador_dni → usuarios.dni (FK acciones_administrativas_administrador_dni_foreign)
- presupuestos.usuario_dni → usuarios.dni (FK presupuestos_usuario_dni_foreign)
- detalle_presupuesto.servicio_nombre → servicios.nombre (FK detalle_presupuesto_servicio_nombre_foreign)
- pagos.factura_numero → facturas.factura_numero (FK pagos_factura_numero_foreign)
- detalle_presupuesto.numero_presupuesto → presupuestos.numero_presupuesto (FK detalle_presupuesto_numero_presupuesto_foreign)
- detalle_presupuesto.presupuesto_id → presupuestos.id_presupuesto (FK detalle_presupuesto_presupuesto_id_foreign)
- facturas.nombre_servicios → servicios.nombre (FK facturas_nombre_servicios_foreign)
- facturas.usuario_dni → usuarios.dni (FK facturas_usuario_dni_foreign)
- facturas.presupuesto_numero → presupuestos.numero_presupuesto (FK facturas_presupuesto_numero_foreign)
