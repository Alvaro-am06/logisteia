package com.logisteia.backend.dtos;

import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import java.time.LocalDateTime;

import static org.junit.jupiter.api.Assertions.*;

@DisplayName("UsuarioResponseDTO Tests")
class UsuarioResponseDTOTest {

    private UsuarioResponseDTO dto;

    @BeforeEach
    void setUp() {
        dto = new UsuarioResponseDTO(
                "12345678A",
                "juan@example.com",
                "Juan Pérez",
                UserRole.JEFE_EQUIPO,
                UserStatus.ACTIVO,
                "+34612345678",
                LocalDateTime.now()
        );
    }

    @Test
    @DisplayName("Crear DTO con datos válidos")
    void testCrearUsuarioResponseDTOValido() {
        assertNotNull(dto);
        assertEquals("12345678A", dto.dni());
        assertEquals("Juan Pérez", dto.nombre());
        assertEquals("juan@example.com", dto.email());
        assertEquals(UserRole.JEFE_EQUIPO, dto.rol());
        assertEquals(UserStatus.ACTIVO, dto.estado());
    }

    @Test
    @DisplayName("DNI no puede ser nulo")
    void testDniNoNulo() {
        assertNotNull(dto.dni());
    }

    @Test
    @DisplayName("Email tiene formato válido")
    void testEmailFormatoValido() {
        assertTrue(dto.email().contains("@"));
    }

    @Test
    @DisplayName("Rol es uno de los roles válidos")
    void testRolesValidos() {
        assertTrue(
            dto.rol() == UserRole.JEFE_EQUIPO || 
            dto.rol() == UserRole.TRABAJADOR || 
            dto.rol() == UserRole.MODERADOR
        );
    }

    @Test
    @DisplayName("Estado es uno de los estados válidos")
    void testEstadosValidos() {
        assertTrue(
            dto.estado() == UserStatus.ACTIVO || 
            dto.estado() == UserStatus.SUSPENDIDO ||
            dto.estado() == UserStatus.ELIMINADO
        );
    }

    @Test
    @DisplayName("Nombre no está vacío")
    void testNombreNoVacio() {
        assertNotNull(dto.nombre());
        assertFalse(dto.nombre().isBlank());
    }

    @Test
    @DisplayName("Equals funciona para records con mismos valores")
    void testEqualsYHashCode() {
        UsuarioResponseDTO dto2 = new UsuarioResponseDTO(
                "12345678A",
                "juan@example.com",
                "Juan Pérez",
                UserRole.JEFE_EQUIPO,
                UserStatus.ACTIVO,
                "+34612345678",
                dto.fechaRegistro()
        );
        assertEquals(dto, dto2);
        assertEquals(dto.hashCode(), dto2.hashCode());
    }

    @Test
    @DisplayName("ToString contiene información del record")
    void testToString() {
        String str = dto.toString();
        assertNotNull(str);
        assertTrue(str.contains("UsuarioResponseDTO") || str.contains("juan@example.com"));
    }

    @Test
    @DisplayName("Records diferentes con distintos DNI son desiguales")
    void testDifferentRecords() {
        UsuarioResponseDTO dtoOtro = new UsuarioResponseDTO(
                "87654321B",
                "juan@example.com",
                "Juan Pérez",
                UserRole.JEFE_EQUIPO,
                UserStatus.ACTIVO,
                "+34612345678",
                dto.fechaRegistro()
        );
        assertNotEquals(dto, dtoOtro);
    }

    @Test
    @DisplayName("Fecha de registro no es nula")
    void testFechaRegistroNoNula() {
        assertNotNull(dto.fechaRegistro());
    }

    @Test
    @DisplayName("Teléfono puede ser nulo")
    void testTelefonoOpcional() {
        UsuarioResponseDTO dtoSinTelefono = new UsuarioResponseDTO(
                "12345678A",
                "juan@example.com",
                "Juan Pérez",
                UserRole.TRABAJADOR,
                UserStatus.ACTIVO,
                null,
                LocalDateTime.now()
        );
        assertNull(dtoSinTelefono.telefono());
    }

    @Test
    @DisplayName("Email con formato válido")
    void testEmailValido() {
        assertTrue(dto.email().matches("[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}"));
    }
}
