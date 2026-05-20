package com.logisteia.backend.dtos;

import com.logisteia.backend.enums.BudgetStatus;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.time.LocalDateTime;

/**
 * DTO para lectura de Presupuesto (GET).
 * Incluye solo datos básicos, sin relaciones para evitar serialización circular.
 */
public record PresupuestoResponseDTO(
    @NotNull Integer idPresupuesto,
    @NotBlank String numeroPresupuesto,
    @NotNull BudgetStatus estado,
    @NotNull Integer validezDias,
    @NotNull BigDecimal total,
    String notas,
    LocalDateTime fechaCreacion,
    String usuarioDni,  // Solo el ID, no la entidad completa
    Integer proyectoId, // Solo el ID
    Integer clienteId   // Solo el ID
) {}
