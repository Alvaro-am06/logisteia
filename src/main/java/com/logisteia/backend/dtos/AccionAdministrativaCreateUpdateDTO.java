package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

public record AccionAdministrativaCreateUpdateDTO(
    @NotBlank(message = "La acción no puede estar vacía")
    @Size(min = 3, max = 100, message = "La acción debe tener entre 3 y 100 caracteres")
    String accion,

    String motivo,

    String ipOrigen,

    @NotNull(message = "El DNI del administrador no puede ser nulo")
    String administradorDni,

    String usuarioAfectadoDni,

    Integer proyectoId,

    Integer equipoId
) {}
