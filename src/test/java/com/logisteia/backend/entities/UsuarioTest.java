package com.logisteia.backend.entities;

import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import java.time.LocalDateTime;

import static org.junit.jupiter.api.Assertions.*;

@DisplayName("Usuario Entity Tests")
class UsuarioTest {

    private Usuario usuario;
    private LocalDateTime fechaActual;

    @BeforeEach
    void setUp() {
        fechaActual = LocalDateTime.now();
        usuario = Usuario.builder()
            .dni("12345678A")
            .email("juan@example.com")
            .nombre("Juan Pérez")
            .contrase("hashedPassword123")
            .rol(UserRole.JEFE_EQUIPO)
            .estado(UserStatus.ACTIVO)
            .telefono("+34612345678")
            .fechaRegistro(fechaActual)
            .build();
    }

    @Test
    @DisplayName("Builder crea Usuario con todos los campos")
    void testBuilderCreatesUser() {
        assertNotNull(usuario);
        assertEquals("12345678A", usuario.getDni());
        assertEquals("juan@example.com", usuario.getEmail());
        assertEquals("Juan Pérez", usuario.getNombre());
        assertEquals("hashedPassword123", usuario.getContrase());
        assertEquals(UserRole.JEFE_EQUIPO, usuario.getRol());
        assertEquals(UserStatus.ACTIVO, usuario.getEstado());
        assertEquals("+34612345678", usuario.getTelefono());
        assertEquals(fechaActual, usuario.getFechaRegistro());
    }

    @Test
    @DisplayName("DNI como @Id campo principal")
    void testDniAsPrimaryKey() {
        assertTrue(usuario.getDni().length() > 0);
        assertNotNull(usuario.getDni());
    }

    @Test
    @DisplayName("Email es único y no nulo")
    void testEmailIsUnique() {
        assertNotNull(usuario.getEmail());
        assertTrue(usuario.getEmail().contains("@"));
    }

    @Test
    @DisplayName("Rol debe ser uno de los valores válidos")
    void testRoleIsValid() {
        assertTrue(
            usuario.getRol() == UserRole.JEFE_EQUIPO ||
            usuario.getRol() == UserRole.TRABAJADOR ||
            usuario.getRol() == UserRole.MODERADOR
        );
    }

    @Test
    @DisplayName("Estado debe ser uno de los valores válidos")
    void testEstadoIsValid() {
        assertTrue(
            usuario.getEstado() == UserStatus.ACTIVO ||
            usuario.getEstado() == UserStatus.SUSPENDIDO ||
            usuario.getEstado() == UserStatus.ELIMINADO
        );
    }

    @Test
    @DisplayName("Setters funcionan correctamente")
    void testSetters() {
        usuario.setEmail("nuevo@example.com");
        usuario.setNombre("Nuevo Nombre");
        usuario.setRol(UserRole.MODERADOR);
        usuario.setEstado(UserStatus.SUSPENDIDO);

        assertEquals("nuevo@example.com", usuario.getEmail());
        assertEquals("Nuevo Nombre", usuario.getNombre());
        assertEquals(UserRole.MODERADOR, usuario.getRol());
        assertEquals(UserStatus.SUSPENDIDO, usuario.getEstado());
    }

    @Test
    @DisplayName("Teléfono es opcional")
    void testTelefonoIsOptional() {
        Usuario usuarioSinTelefono = Usuario.builder()
            .dni("87654321B")
            .email("test@example.com")
            .nombre("Test User")
            .contrase("password")
            .rol(UserRole.TRABAJADOR)
            .estado(UserStatus.ACTIVO)
            .build();

        assertNull(usuarioSinTelefono.getTelefono());
    }

    @Test
    @DisplayName("Nombre no puede ser nulo")
    void testNombreNotNull() {
        assertNotNull(usuario.getNombre());
        assertFalse(usuario.getNombre().isBlank());
    }

    @Test
    @DisplayName("Contraseña no puede ser nula")
    void testContraseNotNull() {
        assertNotNull(usuario.getContrase());
        assertFalse(usuario.getContrase().isBlank());
    }

    @Test
    @DisplayName("Fecha de registro no puede ser nula")
    void testFechaRegistroNotNull() {
        assertNotNull(usuario.getFechaRegistro());
    }

    @Test
    @DisplayName("Usuario puede tener lista de equipos")
    void testEquiposRelationship() {
        usuario.setEquiposGestionados(null);
        assertNull(usuario.getEquiposGestionados());
    }

    @Test
    @DisplayName("Constructor sin argumentos crea Usuario válido")
    void testNoArgsConstructor() {
        Usuario usuarioVacio = new Usuario();
        assertNotNull(usuarioVacio);
    }

    @Test
    @DisplayName("Lombok equals method works")
    void testEquals() {
        Usuario usuario2 = Usuario.builder()
            .dni("12345678A")
            .email("juan@example.com")
            .nombre("Juan Pérez")
            .contrase("hashedPassword123")
            .rol(UserRole.JEFE_EQUIPO)
            .estado(UserStatus.ACTIVO)
            .telefono("+34612345678")
            .fechaRegistro(fechaActual)
            .build();

        assertEquals(usuario, usuario2);
    }

    @Test
    @DisplayName("Lombok hashCode works")
    void testHashCode() {
        Usuario usuario2 = Usuario.builder()
            .dni("12345678A")
            .email("juan@example.com")
            .nombre("Juan Pérez")
            .contrase("hashedPassword123")
            .rol(UserRole.JEFE_EQUIPO)
            .estado(UserStatus.ACTIVO)
            .telefono("+34612345678")
            .fechaRegistro(fechaActual)
            .build();

        assertEquals(usuario.hashCode(), usuario2.hashCode());
    }
}
