package com.logisteia.backend.controllers;

import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import com.logisteia.backend.dtos.LoginRequestDTO;
import com.logisteia.backend.dtos.LoginResponseDTO;
import com.logisteia.backend.dtos.RegisterRequestDTO;
import com.logisteia.backend.services.AuthService;

/**
 * Controlador REST para autenticación (login y registro).
 * Endpoints públicos sin requerir JWT.
 */
@RestController
@RequestMapping("/api/v1/auth")
@RequiredArgsConstructor
public class AuthController {

    private final AuthService authService;

    /**
     * Login: autentica un usuario y retorna un token JWT.
     * 
     * @param request Credenciales del usuario (email y contraseña)
     * @return Token JWT y datos del usuario
     */
    @PostMapping("/login")
    public ResponseEntity<LoginResponseDTO> login(@Valid @RequestBody LoginRequestDTO request) {
        LoginResponseDTO response = authService.login(request);
        return ResponseEntity.ok(response);
    }

    /**
     * Registro: crea un nuevo usuario y retorna un token JWT.
     * 
     * @param request Datos del nuevo usuario
     * @return Token JWT y datos del usuario registrado
     */
    @PostMapping("/register")
    public ResponseEntity<LoginResponseDTO> register(@Valid @RequestBody RegisterRequestDTO request) {
        LoginResponseDTO response = authService.register(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(response);
    }
}
