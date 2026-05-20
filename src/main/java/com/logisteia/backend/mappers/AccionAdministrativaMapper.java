package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.AccionAdministrativaResponseDTO;
import com.logisteia.backend.dtos.AccionAdministrativaCreateUpdateDTO;
import com.logisteia.backend.entities.AccionAdministrativa;
import com.logisteia.backend.repositories.UsuarioRepository;
import com.logisteia.backend.repositories.ProyectoRepository;
import com.logisteia.backend.repositories.EquipoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class AccionAdministrativaMapper {

    private final UsuarioRepository usuarioRepository;
    private final ProyectoRepository proyectoRepository;
    private final EquipoRepository equipoRepository;

    public AccionAdministrativaResponseDTO toResponseDTO(AccionAdministrativa accionAdministrativa) {
        if (accionAdministrativa == null) return null;
        
        return new AccionAdministrativaResponseDTO(
            accionAdministrativa.getId(),
            accionAdministrativa.getAccion(),
            accionAdministrativa.getMotivo(),
            accionAdministrativa.getIpOrigen(),
            accionAdministrativa.getCreadoEn(),
            accionAdministrativa.getAdministrador() != null ? accionAdministrativa.getAdministrador().getDni() : null,
            accionAdministrativa.getUsuarioAfectado() != null ? accionAdministrativa.getUsuarioAfectado().getDni() : null,
            accionAdministrativa.getProyecto() != null ? accionAdministrativa.getProyecto().getId() : null,
            accionAdministrativa.getEquipo() != null ? accionAdministrativa.getEquipo().getId() : null
        );
    }

    public AccionAdministrativa toEntity(AccionAdministrativaCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return AccionAdministrativa.builder()
            .accion(dto.accion())
            .motivo(dto.motivo())
            .ipOrigen(dto.ipOrigen())
            .administrador(usuarioRepository.findById(dto.administradorDni()).orElse(null))
            .usuarioAfectado(dto.usuarioAfectadoDni() != null ? usuarioRepository.findById(dto.usuarioAfectadoDni()).orElse(null) : null)
            .proyecto(dto.proyectoId() != null ? proyectoRepository.findById(dto.proyectoId()).orElse(null) : null)
            .equipo(dto.equipoId() != null ? equipoRepository.findById(dto.equipoId()).orElse(null) : null)
            .build();
    }

    public void updateEntityFromDTO(AccionAdministrativaCreateUpdateDTO dto, AccionAdministrativa accionAdministrativa) {
        if (dto == null || accionAdministrativa == null) return;
        
        accionAdministrativa.setAccion(dto.accion());
        accionAdministrativa.setMotivo(dto.motivo());
        accionAdministrativa.setIpOrigen(dto.ipOrigen());
        accionAdministrativa.setAdministrador(usuarioRepository.findById(dto.administradorDni()).orElse(null));
        accionAdministrativa.setUsuarioAfectado(dto.usuarioAfectadoDni() != null ? usuarioRepository.findById(dto.usuarioAfectadoDni()).orElse(null) : null);
        accionAdministrativa.setProyecto(dto.proyectoId() != null ? proyectoRepository.findById(dto.proyectoId()).orElse(null) : null);
        accionAdministrativa.setEquipo(dto.equipoId() != null ? equipoRepository.findById(dto.equipoId()).orElse(null) : null);
    }
}
