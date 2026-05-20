# 📚 Referencia Rápida - Uso de Entidades y Repositories

## 1. Inyección de Repositories

```java
import org.springframework.beans.factory.annotation.Autowired;
import com.logisteia.backend.repositories.*;

@Service
public class MiServicio {
    
    @Autowired
    private UsuarioRepository usuarioRepository;
    
    @Autowired
    private ProyectoRepository proyectoRepository;
    
    @Autowired
    private PresupuestoRepository presupuestoRepository;
    // ... más repositories
}
```

## 2. CRUD Básico con Repositories

### Crear (CREATE)
```java
// Crear un usuario
Usuario usuario = Usuario.builder()
    .dni("12345678A")
    .email("usuario@logisteia.com")
    .nombre("Juan Pérez")
    .contrase("$2a$10$...") // Bcrypt hasheado
    .rol(UserRole.JEFE_EQUIPO)
    .estado(UserStatus.ACTIVO)
    .telefono("666123456")
    .build();

usuarioRepository.save(usuario);

// Crear con relaciones
Cliente cliente = Cliente.builder()
    .nombre("Tech Solutions SL")
    .empresa("Tech Solutions")
    .email("contacto@techsolutions.com")
    .telefono("912345678")
    .jefe(usuario) // FK a usuario existente
    .build();

clienteRepository.save(cliente);
```

### Leer (READ)
```java
// Por ID
Usuario usuario = usuarioRepository.findById("12345678A").orElse(null);

// Por Email (derivado)
Usuario usuario = usuarioRepository.findByEmail("usuario@logisteia.com").orElse(null);

// Por Rol
List<Usuario> jefeEquipos = usuarioRepository.findByRol(UserRole.JEFE_EQUIPO);

// Por Rol y Estado
List<Usuario> jefeActivos = usuarioRepository.findByRolAndEstado(
    UserRole.JEFE_EQUIPO, 
    UserStatus.ACTIVO
);

// Todos (con paginación en producción)
List<Usuario> todos = usuarioRepository.findAll();
```

### Actualizar (UPDATE)
```java
Usuario usuario = usuarioRepository.findById("12345678A").orElse(null);
if (usuario != null) {
    usuario.setTelefono("666654321");
    usuario.setEstado(UserStatus.SUSPENDIDO);
    usuarioRepository.save(usuario); // save funciona para UPDATE también
}
```

### Eliminar (DELETE)
```java
usuarioRepository.deleteById("12345678A");

// O si tienes la entidad
usuarioRepository.delete(usuario);

// Eliminar todos (cuidado en producción)
usuarioRepository.deleteAll();
```

## 3. Relaciones One-to-Many

```java
// Obtener un equipo con sus miembros
Equipo equipo = equipoRepository.findById(1).orElse(null);
List<MiembroEquipo> miembros = equipo.getMiembros(); // Lazy loaded

// Agregar miembro a equipo
MiembroEquipo miembro = MiembroEquipo.builder()
    .equipo(equipo)
    .trabajador(usuarioRepository.findById("87654321B").get())
    .rolProyecto("Backend Developer")
    .estadoInvitacion(InvitationStatus.PENDIENTE)
    .build();

miembroEquipoRepository.save(miembro);

// Ahora el equipo tiene el nuevo miembro
equipo.getMiembros().add(miembro);
```

## 4. Relaciones Many-to-One

```java
// Buscar proyectos de un cliente específico
Integer clienteId = 5;
List<Proyecto> proyectos = proyectoRepository.findByClienteId(clienteId);

// Acceder a las relaciones
for (Proyecto proyecto : proyectos) {
    System.out.println("Proyecto: " + proyecto.getNombre());
    System.out.println("Jefe: " + proyecto.getJefe().getNombre());
    System.out.println("Cliente: " + proyecto.getCliente().getNombre());
    System.out.println("Equipo: " + proyecto.getEquipo().getNombre());
}
```

## 5. Búsquedas Complejas por Status

```java
// Proyectos en proceso
List<Proyecto> proyectosActivos = proyectoRepository.findByEstado(
    ProjectStatus.EN_PROCESO
);

// Tareas pendientes de un proyecto
List<Tarea> tareasPendientes = tareaRepository.findByProyectoIdAndEstado(
    proyectoId,
    TaskStatus.PENDIENTE
);

// Presupuestos aprobados de un usuario
List<Presupuesto> presupuestosAprobados = presupuestoRepository
    .findByUsuarioDniAndEstado(
        "12345678A",
        BudgetStatus.APROBADO
    );
```

## 6. Trabajar con Presupuestos

```java
// Crear presupuesto completo
Presupuesto presupuesto = Presupuesto.builder()
    .numeroPresupuesto("PRE-2024-001")
    .usuario(usuario)
    .cliente(cliente)
    .proyecto(proyecto)
    .estado(BudgetStatus.BORRADOR)
    .validezDias(30)
    .total(BigDecimal.ZERO) // Se calculará
    .notas("Presupuesto para proyecto frontend")
    .build();

presupuestoRepository.save(presupuesto);

// Agregar detalles
DetallePresupuesto detalle = DetallePresupuesto.builder()
    .numeroPresupuesto("PRE-2024-001")
    .presupuesto(presupuesto)
    .servicioNombre("Desarrollo Frontend React")
    .cantidad(160)
    .precio(new BigDecimal("45.00")) // precio/hora
    .comentario("160 horas a 45€/hora")
    .build();

detallePresupuestoRepository.save(detalle);

// Actualizar presupuesto
BigDecimal total = detalle.getCantidad().multiply(detalle.getPrecio());
presupuesto.setTotal(presupuesto.getTotal().add(total));
presupuesto.setEstado(BudgetStatus.ENVIADO);
presupuestoRepository.save(presupuesto);
```

## 7. Transacciones

```java
import org.springframework.transaction.annotation.Transactional;

@Transactional  // Anotación importante para múltiples operaciones
public void crearProyectoCompleto(Proyecto proyecto, List<Usuario> trabajadores) {
    // Todas estas operaciones se ejecutan en una única transacción
    Proyecto saved = proyectoRepository.save(proyecto);
    
    for (Usuario trabajador : trabajadores) {
        AsignacionProyecto asignacion = AsignacionProyecto.builder()
            .proyecto(saved)
            .trabajador(trabajador)
            .rolAsignado("Developer")
            .build();
        asignacionProyectoRepository.save(asignacion);
    }
    // Si falla algo, se hace rollback de todo
}
```

## 8. Búsquedas por Invitaciones Pendientes

```java
// Invitaciones pendientes de aceptar
List<MiembroEquipo> invitacionesPendientes = miembroEquipoRepository
    .findByEstadoInvitacion(InvitationStatus.PENDIENTE);

// Buscar por token (para aceptar invitación)
Optional<MiembroEquipo> miembro = miembroEquipoRepository
    .findByTokenInvitacion("abc123def456");

if (miembro.isPresent()) {
    miembro.get().setEstadoInvitacion(InvitationStatus.ACEPTADA);
    miembroEquipoRepository.save(miembro.get());
}
```

## 9. Auditoría y Acciones Administrativas

```java
// Registrar una acción administrativa
AccionAdministrativa accion = AccionAdministrativa.builder()
    .administrador(adminUser)
    .accion("SUSPEND_USER")
    .usuarioAfectado(usuarioASuspender)
    .motivo("Violación de términos de servicio")
    .ipOrigen("192.168.1.100")
    .build();

accionAdministrativaRepository.save(accion);

// Auditar acciones por usuario
List<AccionAdministrativa> acciones = accionAdministrativaRepository
    .findByUsuarioAfectadoDni("12345678A");
```

## 10. Servicios de IT

```java
// Obtener todos los servicios IT disponibles
List<ServicioInformatica> servicios = servicioInformaticaRepository
    .findByActivo(true);

// Por categoría
List<ServicioInformatica> frontendServicios = servicioInformaticaRepository
    .findByCategoriaAndActivo(ServiceCategory.DESARROLLO_WEB, true);

// Agregar a presupuesto
for (ServicioInformatica servicio : frontendServicios) {
    DetallePresupuesto detalle = DetallePresupuesto.builder()
        .presupuesto(presupuesto)
        .numeroPresupuesto(presupuesto.getNumeroPresupuesto())
        .servicioNombre(servicio.getNombre())
        .cantidad(1)
        .precio(servicio.getPrecioBase())
        .build();
    detallePresupuestoRepository.save(detalle);
}
```

## 11. Queries Derivadas (Query Methods)

Algunos ejemplos de queries que puedes agregar a los repositories:

```java
// En UsuarioRepository
List<Usuario> findByNombreContainingIgnoreCase(String nombre);
List<Usuario> findByEmailContaining(String emailPart);
Long countByRol(UserRole rol);

// En ProyectoRepository
List<Proyecto> findByJefeDniAndEstadoOrderByFechaCreacionDesc(String dni, ProjectStatus estado);
boolean existsByCodigo(String codigo);

// En TareaRepository
List<Tarea> findByProyectoIdAndEstadoOrderByPrioridadDesc(Integer proyectoId, TaskStatus estado);
Long countByTrabajadorDniAndEstado(String dni, TaskStatus estado);

// En PresupuestoRepository
Page<Presupuesto> findByUsuarioDni(String dni, Pageable pageable);
List<Presupuesto> findByEstadoOrderByFechaCreacionDesc(BudgetStatus estado);
```

## 12. Excepciones Comunes

```java
try {
    Usuario usuario = usuarioRepository.findById("12345678A")
        .orElseThrow(() -> new ResourceNotFoundException("Usuario no encontrado"));
    
    // También puedes usar:
    // .orElseThrow(() -> new IllegalArgumentException("Usuario no existe"));
    
} catch (EntityNotFoundException e) {
    // Entidad no existe
} catch (DataIntegrityViolationException e) {
    // Violación de constraints (email duplicado, etc)
} catch (Exception e) {
    // Otra excepción
}
```

---

## 📌 Tips Importantes

1. **FetchType.LAZY:** Las relaciones están configuradas como LAZY por rendimiento. Accede a `entidad.getRelacion()` dentro de transacciones.

2. **BigDecimal:** Siempre usa BigDecimal para dinero, nunca float/double.

3. **LocalDateTime/LocalDate:** Usa Java Time API, nunca Date/Timestamp antiguo.

4. **Enums:** Spring JPA mapea automáticamente enums a VARCHAR (STRING).

5. **@Transactional:** Usa en servicios para múltiples operaciones DB.

6. **Builder Pattern:** Lombok genera `.builder()` automáticamente.

7. **Null Safety:** Siempre comprueba Optional con `.isPresent()` o `.orElse()`.

---

Este documento será tu guía rápida para los próximos pasos en controllers y servicios.
