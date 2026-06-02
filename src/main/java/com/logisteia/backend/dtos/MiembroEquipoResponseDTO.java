package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

public record MiembroEquipoResponseDTO(
    @NotNull Integer id,
    @NotBlank String rolProyecto,
    @NotBlank String estadoInvitacion,
    @NotBlank String tokenInvitacion,
    @NotNull Boolean activo,
    LocalDateTime fechaIngreso,
    @NotBlank String equipoId,
    @NotBlank String trabajadorDni
) {}
