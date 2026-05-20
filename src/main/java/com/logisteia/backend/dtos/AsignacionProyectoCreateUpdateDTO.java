package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotNull;

public record AsignacionProyectoCreateUpdateDTO(
    String rolAsignado,

    @NotNull(message = "El ID del proyecto no puede ser nulo")
    Integer proyectoId,

    @NotNull(message = "El DNI del trabajador no puede ser nulo")
    String trabajadorDni
) {}
