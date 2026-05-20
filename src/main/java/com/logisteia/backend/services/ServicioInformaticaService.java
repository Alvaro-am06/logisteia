package com.logisteia.backend.services;

import com.logisteia.backend.dtos.ServicioInformaticaResponseDTO;
import com.logisteia.backend.dtos.ServicioInformaticaCreateUpdateDTO;
import com.logisteia.backend.entities.ServicioInformatica;
import com.logisteia.backend.enums.ServiceCategory;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.ServicioInformaticaMapper;
import com.logisteia.backend.repositories.ServicioInformaticaRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class ServicioInformaticaService {

    private final ServicioInformaticaRepository servicioInformaticaRepository;
    private final ServicioInformaticaMapper servicioInformaticaMapper;

    @Transactional(readOnly = true)
    public ServicioInformaticaResponseDTO obtenerPorId(Integer id) {
        ServicioInformatica servicioInformatica = servicioInformaticaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("ServicioInformatica", "ID", id.toString()));
        return servicioInformaticaMapper.toResponseDTO(servicioInformatica);
    }

    @Transactional(readOnly = true)
    public Page<ServicioInformaticaResponseDTO> obtenerTodos(Pageable pageable) {
        return servicioInformaticaRepository.findAll(pageable)
            .map(servicioInformaticaMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<ServicioInformaticaResponseDTO> obtenerPorCategoria(ServiceCategory categoria) {
        return servicioInformaticaRepository.findByCategoria(categoria)
            .stream()
            .map(servicioInformaticaMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<ServicioInformaticaResponseDTO> obtenerActivos() {
        return servicioInformaticaRepository.findByActivo(true)
            .stream()
            .map(servicioInformaticaMapper::toResponseDTO)
            .toList();
    }

    public ServicioInformaticaResponseDTO crear(ServicioInformaticaCreateUpdateDTO dto) {
        ServicioInformatica servicioInformatica = servicioInformaticaMapper.toEntity(dto);
        ServicioInformatica guardado = servicioInformaticaRepository.save(servicioInformatica);
        return servicioInformaticaMapper.toResponseDTO(guardado);
    }

    public ServicioInformaticaResponseDTO actualizar(Integer id, ServicioInformaticaCreateUpdateDTO dto) {
        ServicioInformatica servicioInformatica = servicioInformaticaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("ServicioInformatica", "ID", id.toString()));
        
        servicioInformaticaMapper.updateEntityFromDTO(dto, servicioInformatica);
        ServicioInformatica actualizado = servicioInformaticaRepository.save(servicioInformatica);
        return servicioInformaticaMapper.toResponseDTO(actualizado);
    }

    public void eliminar(Integer id) {
        ServicioInformatica servicioInformatica = servicioInformaticaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("ServicioInformatica", "ID", id.toString()));
        servicioInformaticaRepository.delete(servicioInformatica);
    }
}
