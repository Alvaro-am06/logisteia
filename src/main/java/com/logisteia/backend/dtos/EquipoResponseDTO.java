package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

public record EquipoResponseDTO(
    @NotNull Integer id,
    @NotBlank String nombre,
    String descripcion,
    @NotNull Boolean activo,
    LocalDateTime fechaCreacion,
    @NotBlank String jefeDni
) {}
