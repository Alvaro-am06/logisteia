package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import java.math.BigDecimal;
import java.time.LocalDate;

public record ProyectoCreateUpdateDTO(
    @NotBlank(message = "El código no puede estar vacío")
    @Size(min = 1, max = 50, message = "El código debe tener entre 1 y 50 caracteres")
    String codigo,

    @NotBlank(message = "El nombre no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    String descripcion,

    @NotNull(message = "El estado no puede ser nulo")
    String estado,

    LocalDate fechaInicio,

    LocalDate fechaFinEstimada,

    BigDecimal horasEstimadas,

    BigDecimal precioHora,

    BigDecimal precioTotal,

    String tecnologias,

    String repositorioGithub,

    String notas,

    String numeroPresupuesto,

    @NotNull(message = "El DNI del jefe no puede ser nulo")
    String jefeDni,

    Integer clienteId,

    Integer equipoId
) {}
