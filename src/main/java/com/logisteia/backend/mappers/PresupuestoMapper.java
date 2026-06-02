package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.PresupuestoResponseDTO;
import com.logisteia.backend.dtos.PresupuestoCreateUpdateDTO;
import com.logisteia.backend.entities.Presupuesto;
import com.logisteia.backend.repositories.UsuarioRepository;
import com.logisteia.backend.repositories.ProyectoRepository;
import com.logisteia.backend.repositories.ClienteRepository;
import org.springframework.stereotype.Component;
import lombok.RequiredArgsConstructor;

/**
 * Mapper para convertir entre entidades Presupuesto y DTOs.
 */
@Component
@RequiredArgsConstructor
public class PresupuestoMapper {

    private final UsuarioRepository usuarioRepository;
    private final ProyectoRepository proyectoRepository;
    private final ClienteRepository clienteRepository;

    /**
     * Convierte una entidad Presupuesto a DTO de respuesta.
     */
    public PresupuestoResponseDTO toResponseDTO(Presupuesto presupuesto) {
        if (presupuesto == null) {
            return null;
        }
        
        return new PresupuestoResponseDTO(
            presupuesto.getIdPresupuesto(),
            presupuesto.getNumeroPresupuesto(),
            presupuesto.getEstado(),
            presupuesto.getValidezDias(),
            presupuesto.getTotal(),
            presupuesto.getNotas(),
            presupuesto.getFechaCreacion(),
            presupuesto.getUsuario() != null ? presupuesto.getUsuario().getDni() : null,
            presupuesto.getProyecto() != null ? presupuesto.getProyecto().getId() : null,
            presupuesto.getCliente() != null ? presupuesto.getCliente().getId() : null
        );
    }

    /**
     * Convierte un DTO de creación/actualización a entidad Presupuesto.
     */
    public Presupuesto toEntity(PresupuestoCreateUpdateDTO dto) {
        if (dto == null) {
            return null;
        }
        
        return Presupuesto.builder()
            .numeroPresupuesto(dto.numeroPresupuesto())
            .usuario(usuarioRepository.findById(dto.usuarioDni()).orElse(null))
            .proyecto(dto.proyectoId() != null ? 
                proyectoRepository.findById(dto.proyectoId()).orElse(null) : null)
            .cliente(dto.clienteId() != null ? 
                clienteRepository.findById(dto.clienteId()).orElse(null) : null)
            .estado(dto.estado())
            .validezDias(dto.validezDias())
            .total(dto.total())
            .notas(dto.notas())
            .build();
    }

    /**
     * Actualiza una entidad Presupuesto existente con datos del DTO.
     */
    public void updateEntityFromDTO(PresupuestoCreateUpdateDTO dto, Presupuesto presupuesto) {
        if (dto == null || presupuesto == null) {
            return;
        }
        
        presupuesto.setNumeroPresupuesto(dto.numeroPresupuesto());
        presupuesto.setUsuario(usuarioRepository.findById(dto.usuarioDni()).orElse(null));
        presupuesto.setProyecto(dto.proyectoId() != null ? 
            proyectoRepository.findById(dto.proyectoId()).orElse(null) : null);
        presupuesto.setCliente(dto.clienteId() != null ? 
            clienteRepository.findById(dto.clienteId()).orElse(null) : null);
        presupuesto.setEstado(dto.estado());
        presupuesto.setValidezDias(dto.validezDias());
        presupuesto.setTotal(dto.total());
        presupuesto.setNotas(dto.notas());
    }
}
