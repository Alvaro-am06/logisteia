package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import java.time.LocalDateTime;

@Entity
@Table(name = "asignaciones_proyecto", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"proyecto_id", "trabajador_dni"})
})
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class AsignacionProyecto {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;

    @Column(length = 255)
    private String rolAsignado;

    @Column(nullable = false, updatable = false)
    private LocalDateTime fechaAsignacion;

    // Relaciones
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "proyecto_id", nullable = false)
    private Proyecto proyecto;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "trabajador_dni", nullable = false)
    private Usuario trabajador;

    @PrePersist
    protected void onCreate() {
        if (fechaAsignacion == null) {
            fechaAsignacion = LocalDateTime.now();
        }
    }
}
