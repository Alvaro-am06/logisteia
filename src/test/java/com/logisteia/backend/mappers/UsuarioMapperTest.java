package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.UsuarioResponseDTO;
import com.logisteia.backend.dtos.UsuarioCreateUpdateDTO;
import com.logisteia.backend.entities.Usuario;
import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import java.time.LocalDateTime;

import static org.junit.jupiter.api.Assertions.*;

@DisplayName("UsuarioMapper Tests")
class UsuarioMapperTest {

    private UsuarioMapper mapper;
    private Usuario usuario;

    @BeforeEach
    void setUp() {
        mapper = new UsuarioMapper();
        usuario = Usuario.builder()
            .dni("12345678A")
            .email("juan@example.com")
            .nombre("Juan Pérez")
            .contrase("hashedPassword")
            .rol(UserRole.JEFE_EQUIPO)
            .estado(UserStatus.ACTIVO)
            .telefono("+34612345678")
            .fechaRegistro(LocalDateTime.now())
            .build();
    }

    @Test
    @DisplayName("toResponseDTO - Convierte Usuario a DTO")
    void testToResponseDTO() {
        UsuarioResponseDTO dto = mapper.toResponseDTO(usuario);

        assertNotNull(dto);
        assertEquals("12345678A", dto.dni());
        assertEquals("juan@example.com", dto.email());
        assertEquals("Juan Pérez", dto.nombre());
        assertEquals(UserRole.JEFE_EQUIPO, dto.rol());
        assertEquals(UserStatus.ACTIVO, dto.estado());
        assertEquals("+34612345678", dto.telefono());
        assertNotNull(dto.fechaRegistro());
    }

    @Test
    @DisplayName("toResponseDTO - Maneja null")
    void testToResponseDTONull() {
        UsuarioResponseDTO dto = mapper.toResponseDTO(null);
        assertNull(dto);
    }

    @Test
    @DisplayName("toEntity - Convierte DTO de creación a Usuario")
    void testToEntity() {
        UsuarioCreateUpdateDTO dto = new UsuarioCreateUpdateDTO(
            "12345678A",
            "juan@example.com",
            "Juan Pérez",
            "password123",
            UserRole.TRABAJADOR,
            UserStatus.ACTIVO,
            "+34612345678"
        );

        Usuario usuario = mapper.toEntity(dto);

        assertNotNull(usuario);
        assertEquals("12345678A", usuario.getDni());
        assertEquals("juan@example.com", usuario.getEmail());
        assertEquals("Juan Pérez", usuario.getNombre());
        assertEquals("password123", usuario.getContrase());
        assertEquals(UserRole.TRABAJADOR, usuario.getRol());
        assertEquals(UserStatus.ACTIVO, usuario.getEstado());
    }

    @Test
    @DisplayName("toEntity - Maneja null")
    void testToEntityNull() {
        Usuario usuario = mapper.toEntity(null);
        assertNull(usuario);
    }

    @Test
    @DisplayName("updateEntityFromDTO - Actualiza fields de Usuario")
    void testUpdateEntityFromDTO() {
        UsuarioCreateUpdateDTO dto = new UsuarioCreateUpdateDTO(
            "12345678A",
            "nuevo@example.com",
            "Nombre Nuevo",
            "newPassword",
            UserRole.MODERADOR,
            UserStatus.SUSPENDIDO,
            "+34699999999"
        );

        mapper.updateEntityFromDTO(dto, usuario);

        assertEquals("nuevo@example.com", usuario.getEmail());
        assertEquals("Nombre Nuevo", usuario.getNombre());
        assertEquals("newPassword", usuario.getContrase());
        assertEquals(UserRole.MODERADOR, usuario.getRol());
        assertEquals(UserStatus.SUSPENDIDO, usuario.getEstado());
        assertEquals("+34699999999", usuario.getTelefono());
        // DNI no debe cambiar (no es actualizable)
        assertEquals("12345678A", usuario.getDni());
    }

    @Test
    @DisplayName("updateEntityFromDTO - Maneja null DTO")
    void testUpdateEntityFromDTONullDTO() {
        String emailOriginal = usuario.getEmail();
        mapper.updateEntityFromDTO(null, usuario);
        assertEquals(emailOriginal, usuario.getEmail());
    }

    @Test
    @DisplayName("updateEntityFromDTO - Maneja null Usuario")
    void testUpdateEntityFromDTONullUsuario() {
        UsuarioCreateUpdateDTO dto = new UsuarioCreateUpdateDTO(
            "12345678A",
            "test@example.com",
            "Test",
            "password",
            UserRole.TRABAJADOR,
            UserStatus.ACTIVO,
            null
        );
        assertDoesNotThrow(() -> mapper.updateEntityFromDTO(dto, null));
    }

    @Test
    @DisplayName("Mapper preserva information en ciclo complete")
    void testRoundTrip() {
        // Entity -> DTO
        UsuarioResponseDTO dto = mapper.toResponseDTO(usuario);
        
        // DTO -> Entity (usando CreateUpdateDTO)
        UsuarioCreateUpdateDTO updateDto = new UsuarioCreateUpdateDTO(
            dto.dni(),
            dto.email(),
            dto.nombre(),
            usuario.getContrase(),
            dto.rol(),
            dto.estado(),
            dto.telefono()
        );
        
        Usuario nuevoUsuario = mapper.toEntity(updateDto);
        
        assertEquals(usuario.getDni(), nuevoUsuario.getDni());
        assertEquals(usuario.getEmail(), nuevoUsuario.getEmail());
        assertEquals(usuario.getNombre(), nuevoUsuario.getNombre());
        assertEquals(usuario.getRol(), nuevoUsuario.getRol());
        assertEquals(usuario.getEstado(), nuevoUsuario.getEstado());
    }
}
