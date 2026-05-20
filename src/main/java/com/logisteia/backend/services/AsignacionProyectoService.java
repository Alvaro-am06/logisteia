package com.logisteia.backend.services;

import com.logisteia.backend.dtos.AsignacionProyectoResponseDTO;
import com.logisteia.backend.dtos.AsignacionProyectoCreateUpdateDTO;
import com.logisteia.backend.entities.AsignacionProyecto;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.AsignacionProyectoMapper;
import com.logisteia.backend.repositories.AsignacionProyectoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class AsignacionProyectoService {

    private final AsignacionProyectoRepository asignacionProyectoRepository;
    private final AsignacionProyectoMapper asignacionProyectoMapper;

    @Transactional(readOnly = true)
    public AsignacionProyectoResponseDTO obtenerPorId(Integer id) {
        AsignacionProyecto asignacionProyecto = asignacionProyectoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("AsignacionProyecto", "ID", id.toString()));
        return asignacionProyectoMapper.toResponseDTO(asignacionProyecto);
    }

    @Transactional(readOnly = true)
    public Page<AsignacionProyectoResponseDTO> obtenerTodos(Pageable pageable) {
        return asignacionProyectoRepository.findAll(pageable)
            .map(asignacionProyectoMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<AsignacionProyectoResponseDTO> obtenerPorProyecto(Integer proyectoId) {
        return asignacionProyectoRepository.findByProyectoId(proyectoId)
            .stream()
            .map(asignacionProyectoMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<AsignacionProyectoResponseDTO> obtenerPorTrabajador(String trabajadorDni) {
        return asignacionProyectoRepository.findByTrabajadorDni(trabajadorDni)
            .stream()
            .map(asignacionProyectoMapper::toResponseDTO)
            .toList();
    }

    public AsignacionProyectoResponseDTO crear(AsignacionProyectoCreateUpdateDTO dto) {
        AsignacionProyecto asignacionProyecto = asignacionProyectoMapper.toEntity(dto);
        AsignacionProyecto guardada = asignacionProyectoRepository.save(asignacionProyecto);
        return asignacionProyectoMapper.toResponseDTO(guardada);
    }

    public AsignacionProyectoResponseDTO actualizar(Integer id, AsignacionProyectoCreateUpdateDTO dto) {
        AsignacionProyecto asignacionProyecto = asignacionProyectoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("AsignacionProyecto", "ID", id.toString()));
        
        asignacionProyectoMapper.updateEntityFromDTO(dto, asignacionProyecto);
        AsignacionProyecto actualizada = asignacionProyectoRepository.save(asignacionProyecto);
        return asignacionProyectoMapper.toResponseDTO(actualizada);
    }

    public void eliminar(Integer id) {
        AsignacionProyecto asignacionProyecto = asignacionProyectoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("AsignacionProyecto", "ID", id.toString()));
        asignacionProyectoRepository.delete(asignacionProyecto);
    }
}
