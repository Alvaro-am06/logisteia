package com.logisteia.backend.config;

import java.util.Arrays;
import java.util.List;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;
import org.springframework.web.cors.CorsConfiguration;
import org.springframework.web.cors.CorsConfigurationSource;
import org.springframework.web.cors.UrlBasedCorsConfigurationSource;

/**
 * Configuración de CORS para permitir comunicación entre frontend y backend.
 * 
 * Frontend (Angular) ejecutándose en http://localhost:4200
 * Backend (Spring Boot) ejecutándose en http://localhost:8080
 * 
 * Sin esta configuración, el navegador bloqueará las peticiones AJAX/fetch debido a CORS policy.
 */
@Configuration
public class WebConfig {

    /**
     * Configura CORS a nivel de aplicación.
     * Permite que el frontend Angular en localhost:4200 haga peticiones al backend en localhost:8080.
     */
    @Bean
    public CorsConfigurationSource corsConfigurationSource() {
        CorsConfiguration configuration = new CorsConfiguration();
        
        // Orígenes permitidos (frontend)
        List<String> allowedOrigins = Arrays.asList(
            "http://localhost:4200",      // Angular dev server
            "http://localhost:3000",      // Respaldo / Node dev server
            "http://localhost",           // Localhost sin puerto (80)
            "http://127.0.0.1:4200",      // 127.0.0.1 variant
            "http://127.0.0.1",           // 127.0.0.1 sin puerto
            "http://0.0.0.0:4200"         // 0.0.0.0 para contenedores
        );
        configuration.setAllowedOrigins(allowedOrigins);
        
        // Métodos HTTP permitidos
        List<String> allowedMethods = Arrays.asList(
            "GET", "POST", "PUT", "DELETE", "PATCH", "OPTIONS", "HEAD"
        );
        configuration.setAllowedMethods(allowedMethods);
        
        // Headers permitidos
        List<String> allowedHeaders = Arrays.asList(
            "*"  // Permitir todos los headers (alternativa: especificar manualmente)
        );
        configuration.setAllowedHeaders(allowedHeaders);
        
        // Permitir credenciales (cookies, autorización)
        configuration.setAllowCredentials(true);
        
        // Tiempo de caché CORS preflight
        configuration.setMaxAge(3600L);  // 1 hora
        
        // Headers expuestos
        List<String> exposedHeaders = Arrays.asList(
            "Content-Type",
            "Authorization",
            "X-Requested-With",
            "X-CSRF-Token",
            "Access-Control-Allow-Credentials"
        );
        configuration.setExposedHeaders(exposedHeaders);
        
        // Aplicar CORS a todas las rutas
        UrlBasedCorsConfigurationSource source = new UrlBasedCorsConfigurationSource();
        source.registerCorsConfiguration("/**", configuration);
        
        return source;
    }
}
