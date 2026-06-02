package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

public record AsignacionProyectoResponseDTO(
    @NotNull Integer id,
    String rolAsignado,
    LocalDateTime fechaAsignacion,
    @NotNull Integer proyectoId,
    @NotBlank String trabajadorDni
) {}
