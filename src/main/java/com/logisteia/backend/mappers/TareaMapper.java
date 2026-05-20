package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.TareaResponseDTO;
import com.logisteia.backend.dtos.TareaCreateUpdateDTO;
import com.logisteia.backend.entities.Tarea;
import com.logisteia.backend.repositories.ProyectoRepository;
import com.logisteia.backend.repositories.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class TareaMapper {

    private final ProyectoRepository proyectoRepository;
    private final UsuarioRepository usuarioRepository;

    public TareaResponseDTO toResponseDTO(Tarea tarea) {
        if (tarea == null) return null;
        
        return new TareaResponseDTO(
            tarea.getId(),
            tarea.getNombre(),
            tarea.getDescripcion(),
            tarea.getEstado().toString(),
            tarea.getPrioridad().toString(),
            tarea.getRolRequerido() != null ? tarea.getRolRequerido().toString() : null,
            tarea.getHorasEstimadas(),
            tarea.getHorasTrabajadas(),
            tarea.getFechaInicio(),
            tarea.getFechaFin(),
            tarea.getFechaCreacion(),
            tarea.getProyecto() != null ? tarea.getProyecto().getId() : null,
            tarea.getTrabajador() != null ? tarea.getTrabajador().getDni() : null
        );
    }

    public Tarea toEntity(TareaCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return Tarea.builder()
            .nombre(dto.nombre())
            .descripcion(dto.descripcion())
            .estado(com.logisteia.backend.enums.TaskStatus.valueOf(dto.estado()))
            .prioridad(com.logisteia.backend.enums.TaskPriority.valueOf(dto.prioridad()))
            .rolRequerido(dto.rolRequerido() != null ? com.logisteia.backend.enums.TaskRole.valueOf(dto.rolRequerido()) : null)
            .horasEstimadas(dto.horasEstimadas())
            .horasTrabajadas(dto.horasTrabajadas())
            .fechaInicio(dto.fechaInicio())
            .fechaFin(dto.fechaFin())
            .proyecto(proyectoRepository.findById(dto.proyectoId()).orElse(null))
            .trabajador(dto.trabajadorDni() != null ? usuarioRepository.findById(dto.trabajadorDni()).orElse(null) : null)
            .build();
    }

    public void updateEntityFromDTO(TareaCreateUpdateDTO dto, Tarea tarea) {
        if (dto == null || tarea == null) return;
        
        tarea.setNombre(dto.nombre());
        tarea.setDescripcion(dto.descripcion());
        tarea.setEstado(com.logisteia.backend.enums.TaskStatus.valueOf(dto.estado()));
        tarea.setPrioridad(com.logisteia.backend.enums.TaskPriority.valueOf(dto.prioridad()));
        tarea.setRolRequerido(dto.rolRequerido() != null ? com.logisteia.backend.enums.TaskRole.valueOf(dto.rolRequerido()) : null);
        tarea.setHorasEstimadas(dto.horasEstimadas());
        tarea.setHorasTrabajadas(dto.horasTrabajadas());
        tarea.setFechaInicio(dto.fechaInicio());
        tarea.setFechaFin(dto.fechaFin());
        tarea.setProyecto(proyectoRepository.findById(dto.proyectoId()).orElse(null));
        tarea.setTrabajador(dto.trabajadorDni() != null ? usuarioRepository.findById(dto.trabajadorDni()).orElse(null) : null);
    }
}
