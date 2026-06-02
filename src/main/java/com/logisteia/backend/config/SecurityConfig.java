package com.logisteia.backend.config;

import lombok.RequiredArgsConstructor;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.security.authentication.AuthenticationManager;
import org.springframework.security.config.annotation.authentication.configuration.AuthenticationConfiguration;
import org.springframework.security.config.annotation.web.builders.HttpSecurity;
import org.springframework.security.config.annotation.web.configuration.EnableWebSecurity;
import org.springframework.security.config.http.SessionCreationPolicy;
import org.springframework.security.crypto.bcrypt.BCryptPasswordEncoder;
import org.springframework.security.crypto.password.PasswordEncoder;
import org.springframework.security.web.SecurityFilterChain;
import org.springframework.security.web.authentication.UsernamePasswordAuthenticationFilter;
import org.springframework.web.cors.CorsConfigurationSource;
import com.logisteia.backend.security.JwtAuthenticationFilter;

/**
 * Configuración de Spring Security 6 con JWT.
 * Sin usar WebSecurityConfigurerAdapter (obsoleto en Spring Security 6).
 */
@Configuration
@EnableWebSecurity
@RequiredArgsConstructor
public class SecurityConfig {

    private final JwtAuthenticationFilter jwtAuthenticationFilter;
    private final CorsConfigurationSource corsConfigurationSource;

    /**
     * Configura la cadena de filtros de seguridad.
     */
    @Bean
    public SecurityFilterChain filterChain(HttpSecurity http) throws Exception {
        http
            // Habilitar CORS
            .cors(cors -> cors.configurationSource(corsConfigurationSource))
            
            // Deshabilitar CSRF (no necesario con JWT stateless)
            .csrf(csrf -> csrf.disable())
            
            // Configurar sesiones como STATELESS (sin sesión servidor)
            .sessionManagement(session -> session
                .sessionCreationPolicy(SessionCreationPolicy.STATELESS)
            )
            
            // Configurar autorización de endpoints
            .authorizeHttpRequests(authz -> authz
                // Permitir acceso público a autenticación
                .requestMatchers("/api/v1/auth/**").permitAll()
                
                // Permitir OPTIONS para CORS preflight
                .requestMatchers("/**").permitAll()
                
                // Requerir autenticación para todo lo demás
                .requestMatchers("/api/v1/**").authenticated()
                
                // Permitir acceso a actuator/health sin autenticación (opcional)
                .requestMatchers("/actuator/health").permitAll()
                
                // Negar todo lo demás
                .anyRequest().denyAll()
            )
            
            // Agregar el filtro JWT antes del filtro de autenticación estándar
            .addFilterBefore(jwtAuthenticationFilter, UsernamePasswordAuthenticationFilter.class);

        return http.build();
    }

    /**
     * Bean para el gestor de autenticación.
     */
    @Bean
    public AuthenticationManager authenticationManager(AuthenticationConfiguration config) throws Exception {
        return config.getAuthenticationManager();
    }

    /**
     * Bean para el codificador de contraseñas (BCrypt).
     */
    @Bean
    public PasswordEncoder passwordEncoder() {
        return new BCryptPasswordEncoder();
    }
}
