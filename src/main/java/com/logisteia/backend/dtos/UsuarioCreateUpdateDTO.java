package com.logisteia.backend.dtos;

import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;

/**
 * DTO para creación/actualización de Usuario (POST/PUT).
 */
public record UsuarioCreateUpdateDTO(
    @NotBlank(message = "El DNI no puede estar vacío")
    @Size(min = 8, max = 255, message = "El DNI debe tener entre 8 y 255 caracteres")
    String dni,

    @NotBlank(message = "El email no puede estar vacío")
    @Email(message = "El email debe ser válido")
    String email,

    @NotBlank(message = "El nombre no puede estar vacío")
    @Size(min = 3, max = 255, message = "El nombre debe tener entre 3 y 255 caracteres")
    String nombre,

    @NotBlank(message = "La contraseña no puede estar vacía")
    @Size(min = 6, message = "La contraseña debe tener al menos 6 caracteres")
    String contrase,

    UserRole rol,

    UserStatus estado,

    String telefono
) {}
