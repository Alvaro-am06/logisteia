package com.logisteia.backend.services;

import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import com.logisteia.backend.dtos.LoginRequestDTO;
import com.logisteia.backend.dtos.LoginResponseDTO;
import com.logisteia.backend.dtos.RegisterRequestDTO;
import com.logisteia.backend.entities.Usuario;
import com.logisteia.backend.enums.UserStatus;
import com.logisteia.backend.exceptions.DataIntegrityException;
import com.logisteia.backend.exceptions.BusinessLogicException;
import com.logisteia.backend.repositories.UsuarioRepository;
import com.logisteia.backend.security.JwtService;
import java.time.LocalDateTime;

/**
 * Servicio de autenticación: login y registro de usuarios.
 */
@Service
@RequiredArgsConstructor
@Transactional
@Slf4j
public class AuthService {

    private final UsuarioRepository usuarioRepository;
    private final PasswordEncoder passwordEncoder;
    private final JwtService jwtService;

    /**
     * Autentica un usuario y retorna un token JWT.
     */
    public LoginResponseDTO login(LoginRequestDTO request) {
        // Buscar usuario por email
        Usuario usuario = usuarioRepository.findByEmail(request.email())
                .orElseThrow(() -> 
                    new BusinessLogicException("Email o contraseña inválidos")
                );

        // Validar contraseña
        if (!passwordEncoder.matches(request.senha(), usuario.getContrase())) {
            log.warn("Intento de login fallido para: {}", request.email());
            throw new BusinessLogicException("Email o contraseña inválidos");
        }

        // Validar estado del usuario
        if (usuario.getEstado() != UserStatus.ACTIVO) {
            throw new BusinessLogicException("Usuario no está activo");
        }

        // Generar token JWT
        String token = jwtService.generateToken(usuario);
        long expiresIn = jwtService.getExpirationTime();

        log.info("Login exitoso para: {}", request.email());

        return new LoginResponseDTO(
            token,
            usuario.getEmail(),
            usuario.getNombre(),
            usuario.getRol().name(),
            expiresIn
        );
    }

    /**
     * Registra un nuevo usuario y retorna un token JWT.
     */
    public LoginResponseDTO register(RegisterRequestDTO request) {
        // Validar que el email no exista
        if (usuarioRepository.findByEmail(request.email()).isPresent()) {
            throw DataIntegrityException.duplicateEntry("email", request.email());
        }

        // Validar que el DNI no exista
        if (usuarioRepository.findById(request.dni()).isPresent()) {
            throw DataIntegrityException.duplicateEntry("dni", request.dni());
        }

        // Crear nuevo usuario
        Usuario usuario = Usuario.builder()
                .dni(request.dni())
                .email(request.email())
                .nombre(request.nome())
                .contrase(passwordEncoder.encode(request.senha()))
                .rol(request.rol() != null ? request.rol() : com.logisteia.backend.enums.UserRole.TRABAJADOR)
                .estado(UserStatus.ACTIVO)
                .fechaRegistro(LocalDateTime.now())
                .build();

        Usuario usuarioGuardado = usuarioRepository.save(usuario);

        // Generar token JWT
        String token = jwtService.generateToken(usuarioGuardado);
        long expiresIn = jwtService.getExpirationTime();

        log.info("Registro exitoso para: {}", request.email());

        return new LoginResponseDTO(
            token,
            usuarioGuardado.getEmail(),
            usuarioGuardado.getNombre(),
            usuarioGuardado.getRol().name(),
            expiresIn
        );
    }
}
