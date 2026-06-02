package com.logisteia.backend.security;

import io.jsonwebtoken.Claims;
import io.jsonwebtoken.Jwts;
import io.jsonwebtoken.security.Keys;
import lombok.extern.slf4j.Slf4j;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.stereotype.Service;
import com.logisteia.backend.entities.Usuario;
import java.util.Date;
import java.util.HashMap;
import java.util.Map;
import javax.crypto.SecretKey;

/**
 * Servicio para generación y validación de tokens JWT.
 */
@Service
@Slf4j
public class JwtService {

    @Value("${jwt.secret:mySecretKeyThatShouldBeVeryLongAndSecureInProductionEnvironment12345}")
    private String secretKey;

    @Value("${jwt.expiration:86400000}")  // 24 horas en milisegundos
    private long expiration;

    /**
     * Obtiene la clave secreta para firmar.
     */
    private SecretKey getSigningKey() {
        return Keys.hmacShaKeyFor(secretKey.getBytes());
    }

    /**
     * Genera un token JWT para el usuario.
     */
    public String generateToken(Usuario usuario) {
        Map<String, Object> claims = new HashMap<>();
        claims.put("email", usuario.getEmail());
        claims.put("nombre", usuario.getNombre());
        claims.put("rol", usuario.getRol().name());
        claims.put("estado", usuario.getEstado().name());
        
        return createToken(claims, usuario.getDni());
    }

    /**
     * Crea el token JWT con los claims dados.
     */
    private String createToken(Map<String, Object> claims, String subject) {
        Date now = new Date();
        Date expiryDate = new Date(now.getTime() + expiration);

        return Jwts.builder()
                .claims(claims)
                .subject(subject)
                .issuedAt(now)
                .expiration(expiryDate)
                .signWith(getSigningKey())
                .compact();
    }

    /**
     * Extrae el email (username) del token JWT.
     */
    public String extractEmail(String token) {
        return extractClaim(token, claims -> (String) claims.get("email"));
    }

    /**
     * Extrae el DNI (subject) del token JWT.
     */
    public String extractDni(String token) {
        return extractClaim(token, Claims::getSubject);
    }

    /**
     * Extrae la fecha de expiración del token.
     */
    public Date extractExpiration(String token) {
        return extractClaim(token, Claims::getExpiration);
    }

    /**
     * Extrae un claim específico del token.
     */
    public <T> T extractClaim(String token, java.util.function.Function<Claims, T> claimsResolver) {
        final Claims claims = extractAllClaims(token);
        return claimsResolver.apply(claims);
    }

    /**
     * Extrae todos los claims del token.
     */
    private Claims extractAllClaims(String token) {
        return Jwts.parser()
                .verifyWith(getSigningKey())
                .build()
                .parseSignedClaims(token)
                .getPayload();
    }

    /**
     * Valida si el token es válido.
     */
    public boolean isTokenValid(String token) {
        try {
            Jwts.parser()
                    .verifyWith(getSigningKey())
                    .build()
                    .parseSignedClaims(token);
            return !isTokenExpired(token);
        } catch (Exception e) {
            log.error("Token inválido: {}", e.getMessage());
            return false;
        }
    }

    /**
     * Verifica si el token está expirado.
     */
    private boolean isTokenExpired(String token) {
        return extractExpiration(token).before(new Date());
    }

    /**
     * Retorna el tiempo de expiración en milisegundos.
     */
    public long getExpirationTime() {
        return expiration;
    }
}
