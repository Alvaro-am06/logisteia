package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import com.logisteia.backend.enums.TaskStatus;
import com.logisteia.backend.enums.TaskPriority;
import com.logisteia.backend.enums.TaskRole;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "tareas")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class Tarea {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;

    @Column(nullable = false, length = 255)
    private String nombre;

    @Column(columnDefinition = "TEXT")
    private String descripcion;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private TaskStatus estado;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private TaskPriority prioridad;

    @Enumerated(EnumType.STRING)
    private TaskRole rolRequerido;

    @Column(precision = 10, scale = 2)
    private BigDecimal horasEstimadas;

    @Column(precision = 10, scale = 2)
    private BigDecimal horasTrabajadas;

    @Column
    private LocalDateTime fechaInicio;

    @Column
    private LocalDateTime fechaFin;

    @Column(nullable = false, updatable = false)
    private LocalDateTime fechaCreacion;

    // Relaciones
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "proyecto_id", nullable = false)
    private Proyecto proyecto;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "trabajador_dni")
    private Usuario trabajador;

    @PrePersist
    protected void onCreate() {
        if (fechaCreacion == null) {
            fechaCreacion = LocalDateTime.now();
        }
        if (estado == null) {
            estado = TaskStatus.PENDIENTE;
        }
        if (prioridad == null) {
            prioridad = TaskPriority.MEDIA;
        }
        if (horasEstimadas == null) {
            horasEstimadas = BigDecimal.ZERO;
        }
        if (horasTrabajadas == null) {
            horasTrabajadas = BigDecimal.ZERO;
        }
    }
}
