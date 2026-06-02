package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import java.math.BigDecimal;

@Entity
@Table(name = "detalle_presupuesto")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class DetallePresupuesto {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer idLinea;

    @Column(nullable = false, length = 255)
    private String numeroPresupuesto;

    @Column(nullable = false, length = 255)
    private String servicioNombre;

    @Column(nullable = false)
    private Integer cantidad;

    @Column(nullable = false, precision = 10, scale = 2)
    private BigDecimal precio;

    @Column(columnDefinition = "TEXT")
    private String comentario;

    // Relaciones
    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "presupuesto_id")
    private Presupuesto presupuesto;

    @PrePersist
    protected void onCreate() {
        if (cantidad == null) {
            cantidad = 1;
        }
    }
}
