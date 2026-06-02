package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.DecimalMin;
import java.math.BigDecimal;

public record DetallePresupuestoCreateUpdateDTO(
    @NotBlank(message = "El número de presupuesto no puede estar vacío")
    String numeroPresupuesto,

    @NotBlank(message = "El nombre del servicio no puede estar vacío")
    String servicioNombre,

    @NotNull(message = "La cantidad no puede ser nula")
    @Min(value = 1, message = "La cantidad debe ser al menos 1")
    Integer cantidad,

    @NotNull(message = "El precio no puede ser nulo")
    @DecimalMin(value = "0.00", inclusive = false, message = "El precio debe ser mayor a 0")
    BigDecimal precio,

    String comentario,

    @NotNull(message = "El ID del presupuesto no puede ser nulo")
    Integer presupuestoId
) {}
