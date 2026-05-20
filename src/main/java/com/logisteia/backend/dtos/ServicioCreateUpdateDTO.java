package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.DecimalMin;
import java.math.BigDecimal;

public record ServicioCreateUpdateDTO(
    @NotBlank(message = "El nombre no puede estar vacío")
    String nombre,

    @NotNull(message = "El precio base no puede ser nulo")
    @DecimalMin(value = "0.00", inclusive = false, message = "El precio debe ser mayor a 0")
    BigDecimal precioBase,

    String descripcion,

    String categoriaNombre,

    @NotNull(message = "Debe indicar si está activo")
    Boolean estaActivo
) {}
