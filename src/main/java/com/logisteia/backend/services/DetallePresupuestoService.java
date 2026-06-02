package com.logisteia.backend.services;

import com.logisteia.backend.dtos.DetallePresupuestoResponseDTO;
import com.logisteia.backend.dtos.DetallePresupuestoCreateUpdateDTO;
import com.logisteia.backend.entities.DetallePresupuesto;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.DetallePresupuestoMapper;
import com.logisteia.backend.repositories.DetallePresupuestoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
@RequiredArgsConstructor
@Transactional
public class DetallePresupuestoService {

    private final DetallePresupuestoRepository detallePresupuestoRepository;
    private final DetallePresupuestoMapper detallePresupuestoMapper;

    @Transactional(readOnly = true)
    public DetallePresupuestoResponseDTO obtenerPorId(Integer id) {
        DetallePresupuesto detallePresupuesto = detallePresupuestoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("DetallePresupuesto", "ID", id.toString()));
        return detallePresupuestoMapper.toResponseDTO(detallePresupuesto);
    }

    @Transactional(readOnly = true)
    public Page<DetallePresupuestoResponseDTO> obtenerTodos(Pageable pageable) {
        return detallePresupuestoRepository.findAll(pageable)
            .map(detallePresupuestoMapper::toResponseDTO);
    }

    @Transactional(readOnly = true)
    public List<DetallePresupuestoResponseDTO> obtenerPorPresupuesto(Integer presupuestoId) {
        return detallePresupuestoRepository.findByPresupuestoId(presupuestoId)
            .stream()
            .map(detallePresupuestoMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<DetallePresupuestoResponseDTO> obtenerPorNumeroPresupuesto(String numeroPresupuesto) {
        return detallePresupuestoRepository.findByNumeroPresupuesto(numeroPresupuesto)
            .stream()
            .map(detallePresupuestoMapper::toResponseDTO)
            .toList();
    }

    public DetallePresupuestoResponseDTO crear(DetallePresupuestoCreateUpdateDTO dto) {
        DetallePresupuesto detallePresupuesto = detallePresupuestoMapper.toEntity(dto);
        DetallePresupuesto guardado = detallePresupuestoRepository.save(detallePresupuesto);
        return detallePresupuestoMapper.toResponseDTO(guardado);
    }

    public DetallePresupuestoResponseDTO actualizar(Integer id, DetallePresupuestoCreateUpdateDTO dto) {
        DetallePresupuesto detallePresupuesto = detallePresupuestoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("DetallePresupuesto", "ID", id.toString()));
        
        detallePresupuestoMapper.updateEntityFromDTO(dto, detallePresupuesto);
        DetallePresupuesto actualizado = detallePresupuestoRepository.save(detallePresupuesto);
        return detallePresupuestoMapper.toResponseDTO(actualizado);
    }

    public void eliminar(Integer id) {
        DetallePresupuesto detallePresupuesto = detallePresupuestoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("DetallePresupuesto", "ID", id.toString()));
        detallePresupuestoRepository.delete(detallePresupuesto);
    }
}
