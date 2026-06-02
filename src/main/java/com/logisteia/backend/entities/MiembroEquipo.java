package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import com.logisteia.backend.enums.InvitationStatus;
import java.time.LocalDateTime;

@Entity
@Table(name = "miembros_equipo", uniqueConstraints = {
    @UniqueConstraint(columnNames = {"equipo_id", "trabajador_dni"})
})
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class MiembroEquipo {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;

    @Column(nullable = false, length = 255)
    private String rolProyecto;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private InvitationStatus estadoInvitacion;

    @Column(length = 255)
    private String tokenInvitacion;

    @Column(nullable = false)
    private Boolean activo;

    @Column(nullable = false, updatable = false)
    private LocalDateTime fechaIngreso;

    // Relaciones
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "equipo_id", nullable = false)
    private Equipo equipo;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "trabajador_dni", nullable = false)
    private Usuario trabajador;

    @PrePersist
    protected void onCreate() {
        if (fechaIngreso == null) {
            fechaIngreso = LocalDateTime.now();
        }
        if (activo == null) {
            activo = true;
        }
        if (estadoInvitacion == null) {
            estadoInvitacion = InvitationStatus.PENDIENTE;
        }
    }
}
