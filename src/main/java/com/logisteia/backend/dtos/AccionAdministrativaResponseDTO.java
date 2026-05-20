package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

public record AccionAdministrativaResponseDTO(
    @NotNull Integer id,
    @NotBlank String accion,
    String motivo,
    String ipOrigen,
    LocalDateTime creadoEn,
    @NotBlank String administradorDni,
    String usuarioAfectadoDni,
    Integer proyectoId,
    Integer equipoId
) {}
