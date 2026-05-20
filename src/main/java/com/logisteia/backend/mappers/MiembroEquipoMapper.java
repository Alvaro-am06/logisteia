package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.MiembroEquipoResponseDTO;
import com.logisteia.backend.dtos.MiembroEquipoCreateUpdateDTO;
import com.logisteia.backend.entities.MiembroEquipo;
import com.logisteia.backend.repositories.EquipoRepository;
import com.logisteia.backend.repositories.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class MiembroEquipoMapper {

    private final EquipoRepository equipoRepository;
    private final UsuarioRepository usuarioRepository;

    public MiembroEquipoResponseDTO toResponseDTO(MiembroEquipo miembroEquipo) {
        if (miembroEquipo == null) return null;
        
        return new MiembroEquipoResponseDTO(
            miembroEquipo.getId(),
            miembroEquipo.getRolProyecto(),
            miembroEquipo.getEstadoInvitacion().toString(),
            miembroEquipo.getTokenInvitacion(),
            miembroEquipo.getActivo(),
            miembroEquipo.getFechaIngreso(),
            miembroEquipo.getEquipo() != null ? miembroEquipo.getEquipo().getId().toString() : null,
            miembroEquipo.getTrabajador() != null ? miembroEquipo.getTrabajador().getDni() : null
        );
    }

    public MiembroEquipo toEntity(MiembroEquipoCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return MiembroEquipo.builder()
            .rolProyecto(dto.rolProyecto())
            .estadoInvitacion(org.springframework.util.StringUtils.hasText(dto.estadoInvitacion()) ? 
                com.logisteia.backend.enums.InvitationStatus.valueOf(dto.estadoInvitacion()) : null)
            .tokenInvitacion(dto.tokenInvitacion())
            .activo(dto.activo())
            .equipo(equipoRepository.findById(dto.equipoId()).orElse(null))
            .trabajador(usuarioRepository.findById(dto.trabajadorDni()).orElse(null))
            .build();
    }

    public void updateEntityFromDTO(MiembroEquipoCreateUpdateDTO dto, MiembroEquipo miembroEquipo) {
        if (dto == null || miembroEquipo == null) return;
        
        miembroEquipo.setRolProyecto(dto.rolProyecto());
        miembroEquipo.setEstadoInvitacion(org.springframework.util.StringUtils.hasText(dto.estadoInvitacion()) ? 
            com.logisteia.backend.enums.InvitationStatus.valueOf(dto.estadoInvitacion()) : null);
        miembroEquipo.setTokenInvitacion(dto.tokenInvitacion());
        miembroEquipo.setActivo(dto.activo());
        miembroEquipo.setEquipo(equipoRepository.findById(dto.equipoId()).orElse(null));
        miembroEquipo.setTrabajador(usuarioRepository.findById(dto.trabajadorDni()).orElse(null));
    }
}
