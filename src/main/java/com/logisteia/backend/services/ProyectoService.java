package com.logisteia.backend.services;

import com.logisteia.backend.dtos.ProyectoResponseDTO;
import com.logisteia.backend.dtos.ProyectoCreateUpdateDTO;
import com.logisteia.backend.entities.Proyecto;
import com.logisteia.backend.enums.ProjectStatus;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.ProyectoMapper;
import com.logisteia.backend.repositories.ProyectoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class ProyectoService {

    private final ProyectoRepository proyectoRepository;
    private final ProyectoMapper proyectoMapper;

    @Transactional(readOnly = true)
    public ProyectoResponseDTO obtenerPorId(Integer id) {
        Proyecto proyecto = proyectoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Proyecto", "ID", id.toString()));
        return proyectoMapper.toResponseDTO(proyecto);
    }

    @Transactional(readOnly = true)
    public Page<ProyectoResponseDTO> obtenerTodos(Pageable pageable) {
        return proyectoRepository.findAll(pageable)
            .map(proyectoMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public ProyectoResponseDTO obtenerPorCodigo(String codigo) {
        Proyecto proyecto = proyectoRepository.findByCodigo(codigo)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Proyecto", "código", codigo));
        return proyectoMapper.toResponseDTO(proyecto);
    }

    @Transactional(readOnly = true)
    public List<ProyectoResponseDTO> obtenerPorEstado(ProjectStatus estado) {
        return proyectoRepository.findByEstado(estado)
            .stream()
            .map(proyectoMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<ProyectoResponseDTO> obtenerPorJefe(String jefeDni) {
        return proyectoRepository.findByJefeDni(jefeDni)
            .stream()
            .map(proyectoMapper::toResponseDTO)
            .toList();
    }

    public ProyectoResponseDTO crear(ProyectoCreateUpdateDTO dto) {
        Proyecto proyecto = proyectoMapper.toEntity(dto);
        Proyecto guardado = proyectoRepository.save(proyecto);
        return proyectoMapper.toResponseDTO(guardado);
    }

    public ProyectoResponseDTO actualizar(Integer id, ProyectoCreateUpdateDTO dto) {
        Proyecto proyecto = proyectoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Proyecto", "ID", id.toString()));
        
        proyectoMapper.updateEntityFromDTO(dto, proyecto);
        Proyecto actualizado = proyectoRepository.save(proyecto);
        return proyectoMapper.toResponseDTO(actualizado);
    }

    public void eliminar(Integer id) {
        Proyecto proyecto = proyectoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Proyecto", "ID", id.toString()));
        proyectoRepository.delete(proyecto);
    }
}
