package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;
import java.math.BigDecimal;
import java.time.LocalDateTime;

public record TareaCreateUpdateDTO(
    @NotBlank(message = "El nombre no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    String descripcion,

    @NotNull(message = "El estado no puede ser nulo")
    String estado,

    @NotNull(message = "La prioridad no puede ser nula")
    String prioridad,

    String rolRequerido,

    BigDecimal horasEstimadas,

    BigDecimal horasTrabajadas,

    LocalDateTime fechaInicio,

    LocalDateTime fechaFin,

    @NotNull(message = "El ID del proyecto no puede ser nulo")
    Integer proyectoId,

    String trabajadorDni
) {}
