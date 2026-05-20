package com.logisteia.backend.dtos;

import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;
import com.logisteia.backend.enums.UserRole;

/**
 * DTO para solicitud de registro.
 */
public record RegisterRequestDTO(
    @NotBlank(message = "Email é obrigatório")
    @Email(message = "Email deve ser válido")
    String email,
    
    @NotBlank(message = "Nome é obrigatório")
    @Size(min = 3, max = 255, message = "Nome deve ter entre 3 e 255 caracteres")
    String nome,
    
    @NotBlank(message = "DNI é obrigatório")
    @Size(min = 8, max = 255, message = "DNI deve ser válido")
    String dni,
    
    @NotBlank(message = "Senha é obrigatória")
    @Size(min = 6, message = "Senha deve ter no mínimo 6 caracteres")
    String senha,
    
    UserRole rol
) {}
