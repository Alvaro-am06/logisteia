package com.logisteia.backend.security;

import lombok.RequiredArgsConstructor;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.userdetails.User;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.core.userdetails.UserDetailsService;
import org.springframework.security.core.userdetails.UsernameNotFoundException;
import org.springframework.stereotype.Service;
import com.logisteia.backend.entities.Usuario;
import com.logisteia.backend.enums.UserStatus;
import com.logisteia.backend.repositories.UsuarioRepository;
import java.util.Collections;

/**
 * Servicio personalizado para cargar detalles del usuario desde la base de datos.
 */
@Service
@RequiredArgsConstructor
public class CustomUserDetailsService implements UserDetailsService {

    private final UsuarioRepository usuarioRepository;

    /**
     * Carga los detalles del usuario por email.
     */
    @Override
    public UserDetails loadUserByUsername(String email) throws UsernameNotFoundException {
        Usuario usuario = usuarioRepository.findByEmail(email)
                .orElseThrow(() -> new UsernameNotFoundException("Usuario no encontrado: " + email));

        // Validar que el usuario está activo
        if (usuario.getEstado() != UserStatus.ACTIVO) {
            throw new UsernameNotFoundException("Usuario no está activo: " + email);
        }

        // Mapear el rol del usuario a GrantedAuthority
        SimpleGrantedAuthority authority = new SimpleGrantedAuthority(
                "ROLE_" + usuario.getRol().name()
        );

        return User.builder()
                .username(usuario.getEmail())  // Usar email como username
                .password(usuario.getContrase())
                .authorities(Collections.singletonList(authority))
                .disabled(false)
                .build();
    }
}
