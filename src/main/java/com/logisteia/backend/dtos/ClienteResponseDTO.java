package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

public record ClienteResponseDTO(
    @NotNull Integer id,
    @NotBlank String nombre,
    String empresa,
    @NotBlank String email,
    String telefono,
    String direccion,
    String cifNif,
    String notas,
    @NotNull Boolean activo,
    LocalDateTime fechaRegistro,
    @NotBlank String jefeDni
) {}
