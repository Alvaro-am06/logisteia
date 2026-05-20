package com.logisteia.backend.services;

import com.logisteia.backend.dtos.AccionAdministrativaResponseDTO;
import com.logisteia.backend.dtos.AccionAdministrativaCreateUpdateDTO;
import com.logisteia.backend.entities.AccionAdministrativa;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.AccionAdministrativaMapper;
import com.logisteia.backend.repositories.AccionAdministrativaRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class AccionAdministrativaService {

    private final AccionAdministrativaRepository accionAdministrativaRepository;
    private final AccionAdministrativaMapper accionAdministrativaMapper;

    @Transactional(readOnly = true)
    public AccionAdministrativaResponseDTO obtenerPorId(Integer id) {
        AccionAdministrativa accionAdministrativa = accionAdministrativaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("AccionAdministrativa", "ID", id.toString()));
        return accionAdministrativaMapper.toResponseDTO(accionAdministrativa);
    }

    @Transactional(readOnly = true)
    public Page<AccionAdministrativaResponseDTO> obtenerTodos(Pageable pageable) {
        return accionAdministrativaRepository.findAll(pageable)
            .map(accionAdministrativaMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<AccionAdministrativaResponseDTO> obtenerPorAdministrador(String administradorDni) {
        return accionAdministrativaRepository.findByAdministradorDni(administradorDni)
            .stream()
            .map(accionAdministrativaMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<AccionAdministrativaResponseDTO> obtenerPorUsuarioAfectado(String usuarioAfectadoDni) {
        return accionAdministrativaRepository.findByUsuarioAfectadoDni(usuarioAfectadoDni)
            .stream()
            .map(accionAdministrativaMapper::toResponseDTO)
            .toList();
    }

    public AccionAdministrativaResponseDTO crear(AccionAdministrativaCreateUpdateDTO dto) {
        AccionAdministrativa accionAdministrativa = accionAdministrativaMapper.toEntity(dto);
        AccionAdministrativa guardada = accionAdministrativaRepository.save(accionAdministrativa);
        return accionAdministrativaMapper.toResponseDTO(guardada);
    }

    public AccionAdministrativaResponseDTO actualizar(Integer id, AccionAdministrativaCreateUpdateDTO dto) {
        AccionAdministrativa accionAdministrativa = accionAdministrativaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("AccionAdministrativa", "ID", id.toString()));
        
        accionAdministrativaMapper.updateEntityFromDTO(dto, accionAdministrativa);
        AccionAdministrativa actualizada = accionAdministrativaRepository.save(accionAdministrativa);
        return accionAdministrativaMapper.toResponseDTO(actualizada);
    }

    public void eliminar(Integer id) {
        AccionAdministrativa accionAdministrativa = accionAdministrativaRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("AccionAdministrativa", "ID", id.toString()));
        accionAdministrativaRepository.delete(accionAdministrativa);
    }
}
