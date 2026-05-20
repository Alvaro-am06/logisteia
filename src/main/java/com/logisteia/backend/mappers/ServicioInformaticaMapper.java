package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.ServicioInformaticaResponseDTO;
import com.logisteia.backend.dtos.ServicioInformaticaCreateUpdateDTO;
import com.logisteia.backend.entities.ServicioInformatica;
import org.springframework.stereotype.Component;

@Component
public class ServicioInformaticaMapper {

    public ServicioInformaticaResponseDTO toResponseDTO(ServicioInformatica servicioInformatica) {
        if (servicioInformatica == null) return null;
        
        return new ServicioInformaticaResponseDTO(
            servicioInformatica.getId(),
            servicioInformatica.getNombre(),
            servicioInformatica.getCategoria().toString(),
            servicioInformatica.getDescripcion(),
            servicioInformatica.getPrecioBase(),
            servicioInformatica.getUnidad().toString(),
            servicioInformatica.getTecnologias(),
            servicioInformatica.getActivo(),
            servicioInformatica.getFechaCreacion()
        );
    }

    public ServicioInformatica toEntity(ServicioInformaticaCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return ServicioInformatica.builder()
            .nombre(dto.nombre())
            .categoria(com.logisteia.backend.enums.ServiceCategory.valueOf(dto.categoria()))
            .descripcion(dto.descripcion())
            .precioBase(dto.precioBase())
            .unidad(com.logisteia.backend.enums.Unit.valueOf(dto.unidad()))
            .tecnologias(dto.tecnologias())
            .activo(dto.activo())
            .build();
    }

    public void updateEntityFromDTO(ServicioInformaticaCreateUpdateDTO dto, ServicioInformatica servicioInformatica) {
        if (dto == null || servicioInformatica == null) return;
        
        servicioInformatica.setNombre(dto.nombre());
        servicioInformatica.setCategoria(com.logisteia.backend.enums.ServiceCategory.valueOf(dto.categoria()));
        servicioInformatica.setDescripcion(dto.descripcion());
        servicioInformatica.setPrecioBase(dto.precioBase());
        servicioInformatica.setUnidad(com.logisteia.backend.enums.Unit.valueOf(dto.unidad()));
        servicioInformatica.setTecnologias(dto.tecnologias());
        servicioInformatica.setActivo(dto.activo());
    }
}
