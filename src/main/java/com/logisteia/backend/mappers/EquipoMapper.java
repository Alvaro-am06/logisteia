package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.EquipoResponseDTO;
import com.logisteia.backend.dtos.EquipoCreateUpdateDTO;
import com.logisteia.backend.entities.Equipo;
import com.logisteia.backend.repositories.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class EquipoMapper {

    private final UsuarioRepository usuarioRepository;

    public EquipoResponseDTO toResponseDTO(Equipo equipo) {
        if (equipo == null) return null;
        
        return new EquipoResponseDTO(
            equipo.getId(),
            equipo.getNombre(),
            equipo.getDescripcion(),
            equipo.getActivo(),
            equipo.getFechaCreacion(),
            equipo.getJefe() != null ? equipo.getJefe().getDni() : null
        );
    }

    public Equipo toEntity(EquipoCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return Equipo.builder()
            .nombre(dto.nombre())
            .descripcion(dto.descripcion())
            .jefe(usuarioRepository.findById(dto.jefeDni()).orElse(null))
            .activo(dto.activo())
            .build();
    }

    public void updateEntityFromDTO(EquipoCreateUpdateDTO dto, Equipo equipo) {
        if (dto == null || equipo == null) return;
        
        equipo.setNombre(dto.nombre());
        equipo.setDescripcion(dto.descripcion());
        equipo.setJefe(usuarioRepository.findById(dto.jefeDni()).orElse(null));
        equipo.setActivo(dto.activo());
    }
}
