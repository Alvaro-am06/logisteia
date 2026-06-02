package com.logisteia.backend.dtos;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;

/**
 * DTO para solicitud de login.
 */
public record LoginRequestDTO(
    @Email(message = "Email deve ser válido")
    @NotBlank(message = "Email é obrigatório")
    String email,
    
    @NotBlank(message = "Senha é obrigatória")
    String senha
) {}
