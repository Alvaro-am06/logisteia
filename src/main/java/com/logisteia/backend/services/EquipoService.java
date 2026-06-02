package com.logisteia.backend.services;

import com.logisteia.backend.dtos.EquipoResponseDTO;
import com.logisteia.backend.dtos.EquipoCreateUpdateDTO;
import com.logisteia.backend.entities.Equipo;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.EquipoMapper;
import com.logisteia.backend.repositories.EquipoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class EquipoService {

    private final EquipoRepository equipoRepository;
    private final EquipoMapper equipoMapper;

    @Transactional(readOnly = true)
    public EquipoResponseDTO obtenerPorId(Integer id) {
        Equipo equipo = equipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Equipo", "ID", id.toString()));
        return equipoMapper.toResponseDTO(equipo);
    }

    @Transactional(readOnly = true)
    public Page<EquipoResponseDTO> obtenerTodos(Pageable pageable) {
        return equipoRepository.findAll(pageable)
            .map(equipoMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<EquipoResponseDTO> obtenerPorJefe(String jefeDni) {
        return equipoRepository.findByJefeDni(jefeDni)
            .stream()
            .map(equipoMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<EquipoResponseDTO> obtenerActivos() {
        return equipoRepository.findByActivo(true)
            .stream()
            .map(equipoMapper::toResponseDTO)
            .toList();
    }

    public EquipoResponseDTO crear(EquipoCreateUpdateDTO dto) {
        Equipo equipo = equipoMapper.toEntity(dto);
        Equipo guardado = equipoRepository.save(equipo);
        return equipoMapper.toResponseDTO(guardado);
    }

    public EquipoResponseDTO actualizar(Integer id, EquipoCreateUpdateDTO dto) {
        Equipo equipo = equipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Equipo", "ID", id.toString()));
        
        equipoMapper.updateEntityFromDTO(dto, equipo);
        Equipo actualizado = equipoRepository.save(equipo);
        return equipoMapper.toResponseDTO(actualizado);
    }

    public void eliminar(Integer id) {
        Equipo equipo = equipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Equipo", "ID", id.toString()));
        equipoRepository.delete(equipo);
    }
}
