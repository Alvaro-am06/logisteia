package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.ServicioResponseDTO;
import com.logisteia.backend.dtos.ServicioCreateUpdateDTO;
import com.logisteia.backend.entities.Servicio;
import org.springframework.stereotype.Component;

@Component
public class ServicioMapper {

    public ServicioResponseDTO toResponseDTO(Servicio servicio) {
        if (servicio == null) return null;
        
        return new ServicioResponseDTO(
            servicio.getNombre(),
            servicio.getPrecioBase(),
            servicio.getDescripcion(),
            servicio.getCategoriaNombre(),
            servicio.getEstaActivo(),
            servicio.getActualizadoEn()
        );
    }

    public Servicio toEntity(ServicioCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return Servicio.builder()
            .nombre(dto.nombre())
            .precioBase(dto.precioBase())
            .descripcion(dto.descripcion())
            .categoriaNombre(dto.categoriaNombre())
            .estaActivo(dto.estaActivo())
            .build();
    }

    public void updateEntityFromDTO(ServicioCreateUpdateDTO dto, Servicio servicio) {
        if (dto == null || servicio == null) return;
        
        servicio.setNombre(dto.nombre());
        servicio.setPrecioBase(dto.precioBase());
        servicio.setDescripcion(dto.descripcion());
        servicio.setCategoriaNombre(dto.categoriaNombre());
        servicio.setEstaActivo(dto.estaActivo());
    }
}
