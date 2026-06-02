package com.logisteia.backend.security;

import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import lombok.RequiredArgsConstructor;
import lombok.extern.slf4j.Slf4j;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.security.core.userdetails.UserDetails;
import org.springframework.security.web.authentication.WebAuthenticationDetailsSource;
import org.springframework.stereotype.Component;
import org.springframework.util.StringUtils;
import org.springframework.web.filter.OncePerRequestFilter;
import java.io.IOException;

/**
 * Filtro JWT que intercepta cada petición para validar el token Bearer.
 * Extiende OncePerRequestFilter para asegurar que se ejecute una sola vez por request.
 */
@Component
@RequiredArgsConstructor
@Slf4j
public class JwtAuthenticationFilter extends OncePerRequestFilter {

    private final JwtService jwtService;
    private final CustomUserDetailsService userDetailsService;

    @Override
    protected void doFilterInternal(HttpServletRequest request, 
                                    HttpServletResponse response, 
                                    FilterChain filterChain) throws ServletException, IOException {
        try {
            String jwt = extractTokenFromRequest(request);

            if (StringUtils.hasText(jwt) && jwtService.isTokenValid(jwt)) {
                // Token válido, extraer email (username)
                String email = jwtService.extractEmail(jwt);
                
                // Cargar detalles del usuario
                UserDetails userDetails = userDetailsService.loadUserByUsername(email);
                
                // Crear autenticación y establecer en el contexto de Spring Security
                UsernamePasswordAuthenticationToken authentication = 
                        new UsernamePasswordAuthenticationToken(
                                userDetails, 
                                null, 
                                userDetails.getAuthorities()
                        );
                authentication.setDetails(new WebAuthenticationDetailsSource().buildDetails(request));
                
                SecurityContextHolder.getContext().setAuthentication(authentication);
                log.debug("JWT validado para usuario: {}", email);
            }
        } catch (Exception e) {
            log.error("Error validando JWT: {}", e.getMessage());
        }

        // Continuar con la cadena de filtros
        filterChain.doFilter(request, response);
    }

    /**
     * Extrae el token JWT del header Authorization.
     * Espera formato: "Bearer <token>"
     */
    private String extractTokenFromRequest(HttpServletRequest request) {
        String bearerToken = request.getHeader("Authorization");
        
        if (StringUtils.hasText(bearerToken) && bearerToken.startsWith("Bearer ")) {
            return bearerToken.substring(7);  // Remover "Bearer "
        }
        
        return null;
    }
}
