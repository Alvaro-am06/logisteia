package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;

public record ProyectoResponseDTO(
    @NotNull Integer id,
    @NotBlank String codigo,
    @NotBlank String nombre,
    String descripcion,
    @NotBlank String estado,
    LocalDate fechaInicio,
    LocalDate fechaFinEstimada,
    LocalDate fechaFinReal,
    BigDecimal horasEstimadas,
    BigDecimal precioHora,
    BigDecimal precioTotal,
    String tecnologias,
    String repositorioGithub,
    String notas,
    String numeroPresupuesto,
    LocalDateTime fechaCreacion,
    LocalDateTime fechaActualizacion,
    @NotBlank String jefeDni,
    Integer clienteId,
    Integer equipoId
) {}
