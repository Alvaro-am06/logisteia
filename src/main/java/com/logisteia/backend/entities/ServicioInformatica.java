package com.logisteia.backend.entities;

import jakarta.persistence.*;
import lombok.AllArgsConstructor;
import lombok.Builder;
import lombok.Data;
import lombok.NoArgsConstructor;
import com.logisteia.backend.enums.ServiceCategory;
import com.logisteia.backend.enums.Unit;
import java.math.BigDecimal;
import java.time.LocalDateTime;

@Entity
@Table(name = "servicios_informatica")
@Data
@NoArgsConstructor
@AllArgsConstructor
@Builder
public class ServicioInformatica {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Integer id;

    @Column(nullable = false, length = 255)
    private String nombre;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private ServiceCategory categoria;

    @Column(columnDefinition = "TEXT")
    private String descripcion;

    @Column(precision = 10, scale = 2)
    private BigDecimal precioBase;

    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private Unit unidad;

    @Column(columnDefinition = "TEXT")
    private String tecnologias;

    @Column(nullable = false)
    private Boolean activo;

    @Column(nullable = false, updatable = false)
    private LocalDateTime fechaCreacion;

    @PrePersist
    protected void onCreate() {
        if (fechaCreacion == null) {
            fechaCreacion = LocalDateTime.now();
        }
        if (activo == null) {
            activo = true;
        }
        if (unidad == null) {
            unidad = Unit.HORA;
        }
        if (precioBase == null) {
            precioBase = BigDecimal.ZERO;
        }
    }
}
