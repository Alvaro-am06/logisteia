package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import jakarta.validation.constraints.DecimalMin;
import java.math.BigDecimal;

public record ServicioInformaticaCreateUpdateDTO(
    @NotBlank(message = "El nombre no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    @NotNull(message = "La categoría no puede ser nula")
    String categoria,

    String descripcion,

    @DecimalMin(value = "0.00", inclusive = false, message = "El precio debe ser mayor a 0")
    BigDecimal precioBase,

    @NotNull(message = "La unidad no puede ser nula")
    String unidad,

    String tecnologias,

    @NotNull(message = "Debe indicar si está activo")
    Boolean activo
) {}
