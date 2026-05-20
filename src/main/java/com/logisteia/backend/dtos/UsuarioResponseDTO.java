package com.logisteia.backend.dtos;

import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import java.time.LocalDateTime;

/**
 * DTO para lectura de Usuario (GET).
 * No incluye relaciones para evitar serialización circular.
 */
public record UsuarioResponseDTO(
    String dni,
    String email,
    String nombre,
    UserRole rol,
    UserStatus estado,
    String telefono,
    LocalDateTime fechaRegistro
) {}
