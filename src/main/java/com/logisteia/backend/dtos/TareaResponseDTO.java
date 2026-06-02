package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.LocalDateTime;

public record TareaResponseDTO(
    @NotNull Integer id,
    @NotBlank String nombre,
    String descripcion,
    @NotBlank String estado,
    @NotBlank String prioridad,
    String rolRequerido,
    BigDecimal horasEstimadas,
    BigDecimal horasTrabajadas,
    LocalDateTime fechaInicio,
    LocalDateTime fechaFin,
    LocalDateTime fechaCreacion,
    @NotNull Integer proyectoId,
    String trabajadorDni
) {}
