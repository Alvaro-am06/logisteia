package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.DetallePresupuestoResponseDTO;
import com.logisteia.backend.dtos.DetallePresupuestoCreateUpdateDTO;
import com.logisteia.backend.entities.DetallePresupuesto;
import com.logisteia.backend.repositories.PresupuestoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class DetallePresupuestoMapper {

    private final PresupuestoRepository presupuestoRepository;

    public DetallePresupuestoResponseDTO toResponseDTO(DetallePresupuesto detallePresupuesto) {
        if (detallePresupuesto == null) return null;
        
        return new DetallePresupuestoResponseDTO(
            detallePresupuesto.getIdLinea(),
            detallePresupuesto.getNumeroPresupuesto(),
            detallePresupuesto.getServicioNombre(),
            detallePresupuesto.getCantidad(),
            detallePresupuesto.getPrecio(),
            detallePresupuesto.getComentario(),
            detallePresupuesto.getPresupuesto() != null ? detallePresupuesto.getPresupuesto().getIdPresupuesto() : null
        );
    }

    public DetallePresupuesto toEntity(DetallePresupuestoCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return DetallePresupuesto.builder()
            .numeroPresupuesto(dto.numeroPresupuesto())
            .servicioNombre(dto.servicioNombre())
            .cantidad(dto.cantidad())
            .precio(dto.precio())
            .comentario(dto.comentario())
            .presupuesto(presupuestoRepository.findById(dto.presupuestoId()).orElse(null))
            .build();
    }

    public void updateEntityFromDTO(DetallePresupuestoCreateUpdateDTO dto, DetallePresupuesto detallePresupuesto) {
        if (dto == null || detallePresupuesto == null) return;
        
        detallePresupuesto.setNumeroPresupuesto(dto.numeroPresupuesto());
        detallePresupuesto.setServicioNombre(dto.servicioNombre());
        detallePresupuesto.setCantidad(dto.cantidad());
        detallePresupuesto.setPrecio(dto.precio());
        detallePresupuesto.setComentario(dto.comentario());
        detallePresupuesto.setPresupuesto(presupuestoRepository.findById(dto.presupuestoId()).orElse(null));
    }
}
