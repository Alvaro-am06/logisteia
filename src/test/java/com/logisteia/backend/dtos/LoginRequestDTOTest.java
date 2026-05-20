package com.logisteia.backend.dtos;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.*;

@DisplayName("LoginRequestDTO Tests")
class LoginRequestDTOTest {

    private LoginRequestDTO dto;

    @BeforeEach
    void setUp() {
        dto = new LoginRequestDTO("usuario@example.com", "MiSenha123!");
    }

    @Test
    @DisplayName("Crear LoginRequest con datos válidos")
    void testCrearLoginRequestValido() {
        assertNotNull(dto);
        assertEquals("usuario@example.com", dto.email());
        assertEquals("MiSenha123!", dto.senha());
    }

    @Test
    @DisplayName("Email no puede ser nulo")
    void testEmailNoNulo() {
        assertNotNull(dto.email());
    }

    @Test
    @DisplayName("Senha no puede ser nula")
    void testSenhaNoNula() {
        assertNotNull(dto.senha());
    }

    @Test
    @DisplayName("Email debe contener @")
    void testEmailContieneSimbolo() {
        assertTrue(dto.email().contains("@"), "Email debe contener @");
    }

    @Test
    @DisplayName("Senha debe tener longitud mínima")
    void testSenhaLongitudMinima() {
        assertTrue(dto.senha().length() >= 6, "Senha debe tener mínimo 6 caracteres");
    }

    @Test
    @DisplayName("Equals funciona con mismo email y senha")
    void testEquals() {
        LoginRequestDTO dto2 = new LoginRequestDTO("usuario@example.com", "MiSenha123!");
        assertEquals(dto, dto2);
    }

    @Test
    @DisplayName("HashCode consistente para objetos iguales")
    void testHashCode() {
        LoginRequestDTO dto2 = new LoginRequestDTO("usuario@example.com", "MiSenha123!");
        assertEquals(dto.hashCode(), dto2.hashCode());
    }

    @Test
    @DisplayName("Diferentes emails generan diferentes objetos")
    void testEmailDiferente() {
        LoginRequestDTO dtoOtro = new LoginRequestDTO("otro@example.com", "MiSenha123!");
        assertNotEquals(dto, dtoOtro);
    }

    @Test
    @DisplayName("Diferentes senhas generan diferentes objetos")
    void testSenhaDiferente() {
        LoginRequestDTO dtoOtro = new LoginRequestDTO("usuario@example.com", "OtraSenha456!");
        assertNotEquals(dto, dtoOtro);
    }

    @Test
    @DisplayName("ToString contiene información")
    void testToString() {
        String str = dto.toString();
        assertNotNull(str);
        assertFalse(str.isEmpty());
    }

    @Test
    @DisplayName("Email con formato válido de correo")
    void testEmailFormatoValido() {
        assertTrue(dto.email().matches("[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}"));
    }
}
