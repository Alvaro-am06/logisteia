package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import java.time.LocalDateTime;
import java.util.List;

@Entity
@Table(name = "usuarios", uniqueConstraints = {@UniqueConstraint(columnNames = "email")})
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Usuario {

    @Id
    @Column(length = 255)
    private String dni;

    @Column(nullable = false, length = 255)
    private String email;

    @Column(nullable = false, length = 255)
    private String nombre;

    @Column(nullable = false, length = 255)
    private String contrase;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private UserRole rol;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private UserStatus estado;

    @Column(length = 20)
    private String telefono;

    @Column(nullable = false, updatable = false)
    private LocalDateTime fechaRegistro;

    // Relaciones
    @OneToMany(mappedBy = "jefe", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Equipo> equiposGestionados;

    @OneToMany(mappedBy = "trabajador", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<MiembroEquipo> miembroEquipos;

    @OneToMany(mappedBy = "jefe", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Cliente> clientesGestionados;

    @OneToMany(mappedBy = "jefe", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Proyecto> proyectos;

    @OneToMany(mappedBy = "trabajador", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Tarea> tareas;

    @OneToMany(mappedBy = "usuario", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Presupuesto> presupuestos;

    @OneToMany(mappedBy = "administrador", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<AccionAdministrativa> accionesComoAdmin;

    @OneToMany(mappedBy = "usuarioAfectado", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<AccionAdministrativa> accionesComoAfectado;

    @OneToMany(mappedBy = "trabajador", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<AsignacionProyecto> asignacionesProyectos;

    @PrePersist
    protected void onCreate() {
        if (fechaRegistro == null) {
            fechaRegistro = LocalDateTime.now();
        }
    }
}
