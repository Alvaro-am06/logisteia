package com.logisteia.backend.dtos;

import com.logisteia.backend.enums.BudgetStatus;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.DecimalMin;
import jakarta.validation.constraints.Min;
import java.math.BigDecimal;

/**
 * DTO para creación/actualización de Presupuesto (POST/PUT).
 * Las relaciones se establecen por IDs, no por entidades.
 */
public record PresupuestoCreateUpdateDTO(
    @NotBlank(message = "El número de presupuesto no puede estar vacío")
    String numeroPresupuesto,

    @NotNull(message = "El usuario DNI no puede estar vacío")
    String usuarioDni,

    Integer proyectoId,  // Optional

    Integer clienteId,   // Optional

    @NotNull(message = "El estado no puede ser nulo")
    BudgetStatus estado,

    @NotNull(message = "La validez en días no puede estar vacía")
    @Min(value = 1, message = "La validez debe ser al menos 1 día")
    Integer validezDias,

    @NotNull(message = "El total no puede estar vacío")
    @DecimalMin(value = "0.00", inclusive = false, message = "El total debe ser mayor a 0")
    BigDecimal total,

    String notas
) {}
