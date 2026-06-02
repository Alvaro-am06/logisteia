package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.ProyectoResponseDTO;
import com.logisteia.backend.dtos.ProyectoCreateUpdateDTO;
import com.logisteia.backend.entities.Proyecto;
import com.logisteia.backend.repositories.UsuarioRepository;
import com.logisteia.backend.repositories.ClienteRepository;
import com.logisteia.backend.repositories.EquipoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class ProyectoMapper {

    private final UsuarioRepository usuarioRepository;
    private final ClienteRepository clienteRepository;
    private final EquipoRepository equipoRepository;

    public ProyectoResponseDTO toResponseDTO(Proyecto proyecto) {
        if (proyecto == null) return null;
        
        return new ProyectoResponseDTO(
            proyecto.getId(),
            proyecto.getCodigo(),
            proyecto.getNombre(),
            proyecto.getDescripcion(),
            proyecto.getEstado().toString(),
            proyecto.getFechaInicio(),
            proyecto.getFechaFinEstimada(),
            proyecto.getFechaFinReal(),
            proyecto.getHorasEstimadas(),
            proyecto.getPrecioHora(),
            proyecto.getPrecioTotal(),
            proyecto.getTecnologias(),
            proyecto.getRepositorioGithub(),
            proyecto.getNotas(),
            proyecto.getNumeroPresupuesto(),
            proyecto.getFechaCreacion(),
            proyecto.getFechaActualizacion(),
            proyecto.getJefe() != null ? proyecto.getJefe().getDni() : null,
            proyecto.getCliente() != null ? proyecto.getCliente().getId() : null,
            proyecto.getEquipo() != null ? proyecto.getEquipo().getId() : null
        );
    }

    public Proyecto toEntity(ProyectoCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return Proyecto.builder()
            .codigo(dto.codigo())
            .nombre(dto.nombre())
            .descripcion(dto.descripcion())
            .estado(com.logisteia.backend.enums.ProjectStatus.valueOf(dto.estado()))
            .fechaInicio(dto.fechaInicio())
            .fechaFinEstimada(dto.fechaFinEstimada())
            .horasEstimadas(dto.horasEstimadas())
            .precioHora(dto.precioHora())
            .precioTotal(dto.precioTotal())
            .tecnologias(dto.tecnologias())
            .repositorioGithub(dto.repositorioGithub())
            .notas(dto.notas())
            .numeroPresupuesto(dto.numeroPresupuesto())
            .jefe(usuarioRepository.findById(dto.jefeDni()).orElse(null))
            .cliente(dto.clienteId() != null ? clienteRepository.findById(dto.clienteId()).orElse(null) : null)
            .equipo(dto.equipoId() != null ? equipoRepository.findById(dto.equipoId()).orElse(null) : null)
            .build();
    }

    public void updateEntityFromDTO(ProyectoCreateUpdateDTO dto, Proyecto proyecto) {
        if (dto == null || proyecto == null) return;
        
        proyecto.setCodigo(dto.codigo());
        proyecto.setNombre(dto.nombre());
        proyecto.setDescripcion(dto.descripcion());
        proyecto.setEstado(com.logisteia.backend.enums.ProjectStatus.valueOf(dto.estado()));
        proyecto.setFechaInicio(dto.fechaInicio());
        proyecto.setFechaFinEstimada(dto.fechaFinEstimada());
        proyecto.setHorasEstimadas(dto.horasEstimadas());
        proyecto.setPrecioHora(dto.precioHora());
        proyecto.setPrecioTotal(dto.precioTotal());
        proyecto.setTecnologias(dto.tecnologias());
        proyecto.setRepositorioGithub(dto.repositorioGithub());
        proyecto.setNotas(dto.notas());
        proyecto.setNumeroPresupuesto(dto.numeroPresupuesto());
        proyecto.setJefe(usuarioRepository.findById(dto.jefeDni()).orElse(null));
        proyecto.setCliente(dto.clienteId() != null ? clienteRepository.findById(dto.clienteId()).orElse(null) : null);
        proyecto.setEquipo(dto.equipoId() != null ? equipoRepository.findById(dto.equipoId()).orElse(null) : null);
    }
}
