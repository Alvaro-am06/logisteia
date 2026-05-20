package com.logisteia.backend.services;

import com.logisteia.backend.dtos.MiembroEquipoResponseDTO;
import com.logisteia.backend.dtos.MiembroEquipoCreateUpdateDTO;
import com.logisteia.backend.entities.MiembroEquipo;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.MiembroEquipoMapper;
import com.logisteia.backend.repositories.MiembroEquipoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class MiembroEquipoService {

    private final MiembroEquipoRepository miembroEquipoRepository;
    private final MiembroEquipoMapper miembroEquipoMapper;

    @Transactional(readOnly = true)
    public MiembroEquipoResponseDTO obtenerPorId(Integer id) {
        MiembroEquipo miembroEquipo = miembroEquipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("MiembroEquipo", "ID", id.toString()));
        return miembroEquipoMapper.toResponseDTO(miembroEquipo);
    }

    @Transactional(readOnly = true)
    public Page<MiembroEquipoResponseDTO> obtenerTodos(Pageable pageable) {
        return miembroEquipoRepository.findAll(pageable)
            .map(miembroEquipoMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<MiembroEquipoResponseDTO> obtenerPorEquipo(Integer equipoId) {
        return miembroEquipoRepository.findByEquipoId(equipoId)
            .stream()
            .map(miembroEquipoMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<MiembroEquipoResponseDTO> obtenerPorTrabajador(String trabajadorDni) {
        return miembroEquipoRepository.findByTrabajadorDni(trabajadorDni)
            .stream()
            .map(miembroEquipoMapper::toResponseDTO)
            .toList();
    }

    public MiembroEquipoResponseDTO crear(MiembroEquipoCreateUpdateDTO dto) {
        MiembroEquipo miembroEquipo = miembroEquipoMapper.toEntity(dto);
        MiembroEquipo guardado = miembroEquipoRepository.save(miembroEquipo);
        return miembroEquipoMapper.toResponseDTO(guardado);
    }

    public MiembroEquipoResponseDTO actualizar(Integer id, MiembroEquipoCreateUpdateDTO dto) {
        MiembroEquipo miembroEquipo = miembroEquipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("MiembroEquipo", "ID", id.toString()));
        
        miembroEquipoMapper.updateEntityFromDTO(dto, miembroEquipo);
        MiembroEquipo actualizado = miembroEquipoRepository.save(miembroEquipo);
        return miembroEquipoMapper.toResponseDTO(actualizado);
    }

    public void eliminar(Integer id) {
        MiembroEquipo miembroEquipo = miembroEquipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("MiembroEquipo", "ID", id.toString()));
        miembroEquipoRepository.delete(miembroEquipo);
    }
}
