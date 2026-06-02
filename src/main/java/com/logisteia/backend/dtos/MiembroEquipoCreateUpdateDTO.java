package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

public record MiembroEquipoCreateUpdateDTO(
    @NotBlank(message = "El rol no puede estar vacío")
    @Size(min = 3, max = 255, message = "El rol debe tener entre 3 y 255 caracteres")
    String rolProyecto,

    @NotNull(message = "El estado de invitación no puede ser nulo")
    String estadoInvitacion,

    String tokenInvitacion,

    @NotNull(message = "Debe indicar si está activo")
    Boolean activo,

    @NotNull(message = "El ID del equipo no puede ser nulo")
    Integer equipoId,

    @NotNull(message = "El DNI del trabajador no puede ser nulo")
    String trabajadorDni
) {}
