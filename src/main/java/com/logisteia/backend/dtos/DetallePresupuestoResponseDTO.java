package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;

public record DetallePresupuestoResponseDTO(
    @NotNull Integer idLinea,
    @NotBlank String numeroPresupuesto,
    @NotBlank String servicioNombre,
    @NotNull Integer cantidad,
    @NotNull BigDecimal precio,
    String comentario,
    @NotNull Integer presupuestoId
) {}
