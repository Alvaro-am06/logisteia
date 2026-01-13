# LOGISTEIA - Sprint Backlog 3
**Planifica con precisión. Ejecuta con control.**

## Tareas del Sprint

### 📋 HU-05: Gestión de Presupuestos

| ID | Tarea | Responsable | Horas Est. | Horas Real | Estado | Fecha Completado |
|----|-------|-------------|------------|------------|--------|------------------|
| T-5.1 | Crear presupuestos con servicios | Álvaro | 4h | 4h | ✅ DONE | Anterior |
| T-5.2 | Listar presupuestos del usuario | Fernando | 3h | 3h | ✅ DONE | Anterior |
| T-5.3 | Ver detalle de presupuesto | Álvaro | 2h | 2h | ✅ DONE | Anterior |
| T-5.4 | API para actualizar presupuesto | Álvaro | 3h | 3h | ✅ DONE | 12/01/2026 |
| T-5.5 | API para eliminar presupuesto | Álvaro | 2h | 2h | ✅ DONE | 12/01/2026 |
| T-5.6 | API para exportar presupuesto a PDF | Álvaro | 4h | 4h | ✅ DONE | 12/01/2026 |
| T-5.7 | Método actualizar() en modelo Presupuesto | Álvaro | 2h | 2h | ✅ DONE | 12/01/2026 |
| T-5.8 | Método eliminar() en modelo Presupuesto | Álvaro | 1h | 1h | ✅ DONE | 12/01/2026 |
| T-5.9 | Frontend: cambiar estado de presupuesto | Fernando | 2h | 2h | ✅ DONE | 12/01/2026 |
| T-5.10 | Frontend: eliminar presupuesto con confirmación | Fernando | 2h | 2h | ✅ DONE | 12/01/2026 |
| T-5.11 | Frontend: exportar PDF (botones y llamadas) | Fernando | 2h | 2h | ✅ DONE | 12/01/2026 |
| T-5.12 | UI: botones de acción en tabla de presupuestos | Fernando | 2h | 2h | ✅ DONE | 12/01/2026 |
| T-5.13 | UI: opciones en modal de detalle | Fernando | 1h | 1h | ✅ DONE | 12/01/2026 |
| T-5.14 | Testing de flujo completo de presupuestos | Ambos | 3h | - | ⏳ PENDIENTE | - |
| T-5.15 | Documentación de HU-05 completada | Álvaro | 1h | 1h | ✅ DONE | 12/01/2026 |

**Total Horas Estimadas**: 34h  
**Total Horas Reales**: 30h (sin testing)  
**Progreso**: 93% completado

---

## Entregables del Sprint

### 📦 Componentes Frontend (Angular)

| Componente | Archivo | Estado | Descripción |
|------------|---------|--------|-------------|
| Crear Presupuesto | `presupuesto.ts/html` | ✅ DONE | Formulario de creación con servicios |
| Mis Proyectos | `mis-proyectos.ts/html` | ✅ DONE | Listado, detalle, acciones CRUD |

### 🔌 APIs Backend (PHP)

| Endpoint | Archivo | Método | Estado | Descripción |
|----------|---------|--------|--------|-------------|
| Crear Presupuesto | `presupuestos.php` | POST | ✅ DONE | Crear nuevo presupuesto |
| Listar Presupuestos | `mis-presupuestos.php` | GET | ✅ DONE | Obtener presupuestos del usuario |
| Detalle Presupuesto | `detalle-presupuesto.php` | GET | ✅ DONE | Obtener detalles de un presupuesto |
| Actualizar Presupuesto | `actualizar-presupuesto.php` | POST/PUT | ✅ DONE | Actualizar presupuesto existente |
| Eliminar Presupuesto | `eliminar-presupuesto.php` | POST/DELETE | ✅ DONE | Eliminación lógica de presupuesto |
| Exportar PDF | `exportar-presupuesto-pdf.php` | GET | ✅ DONE | Generar PDF del presupuesto |

### 🗃️ Modelo de Datos (PHP)

| Modelo | Archivo | Métodos Nuevos | Estado |
|--------|---------|----------------|--------|
| Presupuesto | `Presupuesto.php` | `actualizar()`, `eliminar()` | ✅ DONE |

### 📄 Documentación

| Documento | Estado | Descripción |
|-----------|--------|-------------|
| HU-05-Gestion-Presupuestos-COMPLETADA.md | ✅ DONE | Documentación completa de funcionalidades |
| Sprint Backlog 3.md | ✅ DONE | Este documento |

---

## Funcionalidades Implementadas

### ✅ Gestión Completa de Presupuestos

1. **Crear Presupuestos**
   - Selección de servicios del catálogo
   - Cantidades y comentarios personalizados
   - Cálculo automático de totales
   - Generación de número único (PRES-YYYYMMDD-XXXX)

2. **Listar y Visualizar**
   - Tabla responsive con todos los presupuestos
   - Filtrado por estado (borrador, enviado, aprobado, rechazado)
   - Modal de detalle con información completa
   - Resumen estadístico

3. **Actualizar Estado** ⭐ NUEVO
   - Workflow: borrador → enviado → aprobado/rechazado
   - Confirmaciones antes de cambiar estado
   - Validación de permisos según estado actual

4. **Eliminar Presupuestos** ⭐ NUEVO
   - Eliminación lógica (preserva histórico)
   - Confirmación obligatoria
   - Cambio de estado a "eliminado"

5. **Exportar a PDF** ⭐ NUEVO
   - Generación de documento HTML/PDF
   - Formato profesional con logo LOGISTEIA
   - Incluye todos los detalles y cálculos
   - Apertura en nueva ventana para imprimir

---

## Métricas del Sprint

### Velocity
- **Puntos Comprometidos**: 13
- **Puntos Completados**: 13
- **Velocity**: 13 puntos/sprint

### Horas
- **Horas Estimadas**: 34h
- **Horas Ejecutadas**: 30h
- **Varianza**: -4h (11.7% menos)

### Calidad
- **Bugs Encontrados**: 0
- **Bugs Resueltos**: 0
- **Deuda Técnica**: Implementar librería TCPDF para PDFs más robustos

---

## Retrospectiva del Sprint

###  Qué Salió Bien
- Implementación completa de CRUD de presupuestos
- APIs RESTful bien estructuradas y documentadas
- Interfaz de usuario intuitiva y responsive
- Trabajo en equipo eficiente
- Documentación detallada de funcionalidades

###  Qué Mejorar
- Falta testing automatizado
- PDF básico (sin librería especializada)
- No se implementó edición completa de presupuestos en estado borrador

###  Acciones para el Próximo Sprint
- Implementar tests unitarios y de integración
- Investigar e integrar TCPDF o similar para PDFs profesionales
- Añadir edición completa de presupuestos
- Implementar notificaciones por email

---

## Definition of Done (DoD)

Para considerar una historia completada, debe cumplir:

- [x] Código implementado en frontend (Angular)
- [x] APIs REST implementadas en backend (PHP)
- [x] Modelo de datos actualizado
- [x] Validaciones en frontend y backend
- [x] Manejo de errores implementado
- [x] Interfaz de usuario responsive
- [x] Documentación técnica completada
- [ ] Tests unitarios (pendiente)
- [ ] Tests de integración (pendiente)
- [x] Code review realizado
- [x] Funcionalidad probada manualmente

**Estado DoD**: 8/10 criterios completados (80%)

---

## Impedimentos y Riesgos

| ID | Impedimento/Riesgo | Severidad | Estado | Resolución |
|----|-------------------|-----------|--------|------------|
| I-3.1 | Falta librería PDF profesional | Media |  Abierto | Se implementó solución HTML básica |
| I-3.2 | Testing manual toma tiempo | Media |  Abierto | Pendiente automatización |

---

## Backlog para Próximos Sprints

### Sprint 4 - Propuesto

**Épica: Mejoras y Optimización**

- [ ] HU-07: Reportes avanzados y exportación
- [ ] HU-08: Notificaciones automáticas
- [ ] Implementar testing automatizado
- [ ] Mejorar exportación PDF con TCPDF
- [ ] Edición completa de presupuestos
- [ ] Duplicación de presupuestos

---

## Notas Técnicas

### Estructura de Archivos Creados/Modificados

```
src/
├── www/
│   ├── api/
│   │   ├── actualizar-presupuesto.php  NUEVO
│   │   ├── eliminar-presupuesto.php  NUEVO
│   │   ├── exportar-presupuesto-pdf.php  NUEVO
│   │   ├── presupuestos.php (existente)
│   │   ├── mis-presupuestos.php (existente)
│   │   └── detalle-presupuesto.php (existente)
│   └── modelos/
│       └── Presupuesto.php (actualizado con nuevos métodos)
└── frontend/
    └── src/
        └── app/
            ├── presupuesto/ (existente)
            └── mis-proyectos/ (actualizado)
                ├── mis-proyectos.ts  ACTUALIZADO
                └── mis-proyectos.html  ACTUALIZADO
```

## Finalización HU Parciales

En este sprint se priorizó la **finalización de HU parcialmente implementadas** en lugar de comenzar nuevas funcionalidades, siguiendo las mejores prácticas de desarrollo ágil. Se completaron 2 historias de usuario que estaban iniciadas pero no finalizadas:

- **HU-03**: Gestionar Usuarios Registrados (de 60% → 100%)
- **HU-04**: Login para Registrar Clientes (de 40% → 100%)

### HU-03: Gestionar Usuarios Registrados ✅
- Listado completo con badges de estado
- Activar usuarios como administradores
- Suspender usuarios activos
- Eliminar usuarios (eliminación lógica)
- Historial de acciones administrativas
- Validaciones frontend y backend
- UI moderna con TailwindCSS
- Documentación completa

### HU-04: Login para Registrar Clientes ✅
- Componente RegistrarClienteComponent completo
- API REST de clientes (/api/clientes.php) con CRUD completo
- Componente ClientesComponent con listado
- Validaciones DNI, email, duplicados
- Contraseñas hasheadas con seguridad
- Registro en historial de acciones
- Integración en panel de administración
- Documentación completa

### Tareas Técnicas Realizadas
- Revisar estado HU parcialmente implementadas
- Documentar HU-03 (Gestionar Usuarios)
- Crear API REST de clientes (clientes.php)
- Crear componente RegistrarClienteComponent
- Actualizar ClienteService con CRUD completo
- Actualizar ClientesComponent con eliminación
- Integrar rutas en app.routes.ts
- Actualizar menú en panel-admin.html
- Documentar HU-04 completamente
- Testing manual de flujos completos
- Crear Sprint Backlog 4

