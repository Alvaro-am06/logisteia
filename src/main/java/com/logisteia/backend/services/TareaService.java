package com.logisteia.backend.services;

import com.logisteia.backend.dtos.TareaResponseDTO;
import com.logisteia.backend.dtos.TareaCreateUpdateDTO;
import com.logisteia.backend.entities.Tarea;
import com.logisteia.backend.enums.TaskStatus;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.TareaMapper;
import com.logisteia.backend.repositories.TareaRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class TareaService {

    private final TareaRepository tareaRepository;
    private final TareaMapper tareaMapper;

    @Transactional(readOnly = true)
    public TareaResponseDTO obtenerPorId(Integer id) {
        Tarea tarea = tareaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Tarea", "ID", id.toString()));
        return tareaMapper.toResponseDTO(tarea);
    }

    @Transactional(readOnly = true)
    public Page<TareaResponseDTO> obtenerTodos(Pageable pageable) {
        return tareaRepository.findAll(pageable)
            .map(tareaMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<TareaResponseDTO> obtenerPorProyecto(Integer proyectoId) {
        return tareaRepository.findByProyectoId(proyectoId)
            .stream()
            .map(tareaMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<TareaResponseDTO> obtenerPorTrabajador(String trabajadorDni) {
        return tareaRepository.findByTrabajadorDni(trabajadorDni)
            .stream()
            .map(tareaMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<TareaResponseDTO> obtenerPorEstado(TaskStatus estado) {
        return tareaRepository.findByEstado(estado)
            .stream()
            .map(tareaMapper::toResponseDTO)
            .toList();
    }

    public TareaResponseDTO crear(TareaCreateUpdateDTO dto) {
        Tarea tarea = tareaMapper.toEntity(dto);
        Tarea guardada = tareaRepository.save(tarea);
        return tareaMapper.toResponseDTO(guardada);
    }

    public TareaResponseDTO actualizar(Integer id, TareaCreateUpdateDTO dto) {
        Tarea tarea = tareaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Tarea", "ID", id.toString()));
        
        tareaMapper.updateEntityFromDTO(dto, tarea);
        Tarea actualizada = tareaRepository.save(tarea);
        return tareaMapper.toResponseDTO(actualizada);
    }

    public void eliminar(Integer id) {
        Tarea tarea = tareaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Tarea", "ID", id.toString()));
        tareaRepository.delete(tarea);
    }
}
