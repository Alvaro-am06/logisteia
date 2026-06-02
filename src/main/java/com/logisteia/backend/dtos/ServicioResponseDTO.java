package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.LocalDateTime;

public record ServicioResponseDTO(
    @NotBlank String nombre,
    @NotNull BigDecimal precioBase,
    String descripcion,
    String categoriaNombre,
    @NotNull Boolean estaActivo,
    LocalDateTime actualizadoEn
) {}
