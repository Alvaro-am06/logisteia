package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

public record EquipoCreateUpdateDTO(
    @NotBlank(message = "El nombre del equipo no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    String descripcion,

    @NotNull(message = "El DNI del jefe no puede ser nulo")
    String jefeDni,

    @NotNull(message = "Debe indicar si el equipo está activo")
    Boolean activo
) {}
