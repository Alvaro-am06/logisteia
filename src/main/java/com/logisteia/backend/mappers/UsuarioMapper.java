package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.UsuarioResponseDTO;
import com.logisteia.backend.dtos.UsuarioCreateUpdateDTO;
import com.logisteia.backend.entities.Usuario;
import org.springframework.stereotype.Component;

/**
 * Mapper para convertir entre entidades Usuario y DTOs.
 */
@Component
public class UsuarioMapper {

    /**
     * Convierte una entidad Usuario a DTO de respuesta.
     */
    public UsuarioResponseDTO toResponseDTO(Usuario usuario) {
        if (usuario == null) {
            return null;
        }
        
        return new UsuarioResponseDTO(
            usuario.getDni(),
            usuario.getEmail(),
            usuario.getNombre(),
            usuario.getRol(),
            usuario.getEstado(),
            usuario.getTelefono(),
            usuario.getFechaRegistro()
        );
    }

    /**
     * Convierte un DTO de creación/actualización a entidad Usuario.
     */
    public Usuario toEntity(UsuarioCreateUpdateDTO dto) {
        if (dto == null) {
            return null;
        }
        
        return Usuario.builder()
            .dni(dto.dni())
            .email(dto.email())
            .nombre(dto.nombre())
            .contrase(dto.contrase())
            .rol(dto.rol())
            .estado(dto.estado())
            .telefono(dto.telefono())
            .build();
    }

    /**
     * Actualiza una entidad Usuario existente con datos del DTO.
     */
    public void updateEntityFromDTO(UsuarioCreateUpdateDTO dto, Usuario usuario) {
        if (dto == null || usuario == null) {
            return;
        }
        
        usuario.setEmail(dto.email());
        usuario.setNombre(dto.nombre());
        usuario.setContrase(dto.contrase());
        usuario.setRol(dto.rol());
        usuario.setEstado(dto.estado());
        usuario.setTelefono(dto.telefono());
    }
}
