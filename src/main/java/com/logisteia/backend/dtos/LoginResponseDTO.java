package com.logisteia.backend.dtos;

/**
 * DTO para respuesta de login/registro.
 */
public record LoginResponseDTO(
    String token,
    String email,
    String nome,
    String role,
    Long expiresIn
) {}
