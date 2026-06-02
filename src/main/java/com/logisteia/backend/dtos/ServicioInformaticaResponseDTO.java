package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.LocalDateTime;

public record ServicioInformaticaResponseDTO(
    @NotNull Integer id,
    @NotBlank String nombre,
    @NotBlank String categoria,
    String descripcion,
    BigDecimal precioBase,
    @NotBlank String unidad,
    String tecnologias,
    @NotNull Boolean activo,
    LocalDateTime fechaCreacion
) {}
