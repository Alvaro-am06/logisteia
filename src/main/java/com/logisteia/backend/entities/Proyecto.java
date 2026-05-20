package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import com.logisteia.backend.enums.ProjectStatus;
import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;

@Entity
@Table(name = "proyectos")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Proyecto {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;

    @Column(nullable = false, length = 50, unique = true)
    private String codigo;

    @Column(nullable = false, length = 255)
    private String nombre;

    @Column(columnDefinition = "TEXT")
    private String descripcion;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private ProjectStatus estado;

    @Column
    private LocalDate fechaInicio;

    @Column
    private LocalDate fechaFinEstimada;

    @Column
    private LocalDate fechaFinReal;

    @Column(precision = 10, scale = 2)
    private BigDecimal horasEstimadas;

    @Column(precision = 10, scale = 2)
    private BigDecimal precioHora;

    @Column(precision = 10, scale = 2)
    private BigDecimal precioTotal;

    @Column(columnDefinition = "TEXT")
    private String tecnologias;

    @Column(length = 255)
    private String repositorioGithub;

    @Column(columnDefinition = "TEXT")
    private String notas;

    @Column(length = 255)
    private String numeroPresupuesto;

    @Column(nullable = false, updatable = false)
    private LocalDateTime fechaCreacion;

    @Column
    private LocalDateTime fechaActualizacion;

    // Relaciones
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "jefe_dni", nullable = false)
    private Usuario jefe;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "cliente_id")
    private Cliente cliente;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "equipo_id")
    private Equipo equipo;

    @OneToMany(mappedBy = "proyecto", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<Tarea> tareas;

    @OneToMany(mappedBy = "proyecto", cascade = CascadeType.ALL, orphanRemoval = false)
    private List<Presupuesto> presupuestos;

    @OneToMany(mappedBy = "proyecto", cascade = CascadeType.ALL, orphanRemoval = false)
    private List<AccionAdministrativa> acciones;

    @OneToMany(mappedBy = "proyecto", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<AsignacionProyecto> asignaciones;

    @PrePersist
    protected void onCreate() {
        if (fechaCreacion == null) {
            fechaCreacion = LocalDateTime.now();
        }
        if (estado == null) {
            estado = ProjectStatus.CREADO;
        }
        if (horasEstimadas == null) {
            horasEstimadas = BigDecimal.ZERO;
        }
        if (precioHora == null) {
            precioHora = BigDecimal.ZERO;
        }
        if (precioTotal == null) {
            precioTotal = BigDecimal.ZERO;
        }
    }

    @PreUpdate
    protected void onUpdate() {
        fechaActualizacion = LocalDateTime.now();
    }
}
