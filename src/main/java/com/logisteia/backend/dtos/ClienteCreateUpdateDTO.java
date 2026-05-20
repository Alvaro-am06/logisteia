package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.Size;

public record ClienteCreateUpdateDTO(
    @NotBlank(message = "El nombre no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    String empresa,

    @NotBlank(message = "El email no puede estar vacío")
    @Email(message = "El email debe ser válido")
    String email,

    String telefono,

    String direccion,

    String cifNif,

    String notas,

    @NotNull(message = "Debe indicar si está activo")
    Boolean activo,

    @NotNull(message = "El DNI del jefe no puede ser nulo")
    String jefeDni
) {}
