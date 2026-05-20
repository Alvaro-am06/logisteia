package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.AsignacionProyectoResponseDTO;
import com.logisteia.backend.dtos.AsignacionProyectoCreateUpdateDTO;
import com.logisteia.backend.entities.AsignacionProyecto;
import com.logisteia.backend.repositories.ProyectoRepository;
import com.logisteia.backend.repositories.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class AsignacionProyectoMapper {

    private final ProyectoRepository proyectoRepository;
    private final UsuarioRepository usuarioRepository;

    public AsignacionProyectoResponseDTO toResponseDTO(AsignacionProyecto asignacionProyecto) {
        if (asignacionProyecto == null) return null;
        
        return new AsignacionProyectoResponseDTO(
            asignacionProyecto.getId(),
            asignacionProyecto.getRolAsignado(),
            asignacionProyecto.getFechaAsignacion(),
            asignacionProyecto.getProyecto() != null ? asignacionProyecto.getProyecto().getId() : null,
            asignacionProyecto.getTrabajador() != null ? asignacionProyecto.getTrabajador().getDni() : null
        );
    }

    public AsignacionProyecto toEntity(AsignacionProyectoCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return AsignacionProyecto.builder()
            .rolAsignado(dto.rolAsignado())
            .proyecto(proyectoRepository.findById(dto.proyectoId()).orElse(null))
            .trabajador(usuarioRepository.findById(dto.trabajadorDni()).orElse(null))
            .build();
    }

    public void updateEntityFromDTO(AsignacionProyectoCreateUpdateDTO dto, AsignacionProyecto asignacionProyecto) {
        if (dto == null || asignacionProyecto == null) return;
        
        asignacionProyecto.setRolAsignado(dto.rolAsignado());
        asignacionProyecto.setProyecto(proyectoRepository.findById(dto.proyectoId()).orElse(null));
        asignacionProyecto.setTrabajador(usuarioRepository.findById(dto.trabajadorDni()).orElse(null));
    }
}
