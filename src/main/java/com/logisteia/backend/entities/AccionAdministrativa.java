package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import java.time.LocalDateTime;

@Entity
@Table(name = "acciones_administrativas")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class AccionAdministrativa {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;

    @Column(nullable = false, length = 100)
    private String accion;

    @Column(columnDefinition = "TEXT")
    private String motivo;

    @Column(length = 45)
    private String ipOrigen;

    @Column(nullable = false, updatable = false)
    private LocalDateTime creadoEn;

    // Relaciones
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "administrador_dni", nullable = false)
    private Usuario administrador;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "usuario_dni")
    private Usuario usuarioAfectado;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "proyecto_id")
    private Proyecto proyecto;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "equipo_id")
    private Equipo equipo;

    @PrePersist
    protected void onCreate() {
        if (creadoEn == null) {
            creadoEn = LocalDateTime.now();
        }
    }
}
