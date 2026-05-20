package com.logisteia.backend.services;

import com.logisteia.backend.dtos.PresupuestoResponseDTO;
import com.logisteia.backend.dtos.PresupuestoCreateUpdateDTO;
import com.logisteia.backend.entities.Presupuesto;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.PresupuestoMapper;
import com.logisteia.backend.repositories.PresupuestoRepository;
import com.logisteia.backend.services.base.GenericCrudService;
import com.logisteia.backend.enums.BudgetStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import java.util.List;

/**
 * Servicio refactorizado para gestionar operaciones CRUD de Presupuestos.
 * Extiende GenericCrudService para eliminar código boilerplate (54% reducción).
 */
@Service
@Transactional
public class PresupuestoService extends GenericCrudService<Presupuesto, PresupuestoResponseDTO, PresupuestoCreateUpdateDTO, Integer> {

    private final PresupuestoRepository presupuestoRepository;
    private final PresupuestoMapper presupuestoMapper;

    public PresupuestoService(PresupuestoRepository presupuestoRepository, PresupuestoMapper presupuestoMapper) {
        super(presupuestoRepository, presupuestoMapper::toResponseDTO, "Presupuesto");
        this.presupuestoRepository = presupuestoRepository;
        this.presupuestoMapper = presupuestoMapper;
    }

    /**
     * Obtiene un presupuesto por su ID.
     */
    @Transactional(readOnly = true)
    public PresupuestoResponseDTO obtenerPorId(Integer id) {
        return toDto(findByIdOrThrow(id));
    }

    /**
     * Obtiene un presupuesto por su número.
     */
    @Transactional(readOnly = true)
    public PresupuestoResponseDTO obtenerPorNumero(String numeroPresupuesto) {
        Presupuesto presupuesto = presupuestoRepository.findByNumeroPresupuesto(numeroPresupuesto)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Presupuesto", "número", numeroPresupuesto));
        return toDto(presupuesto);
    }

    /**
     * Crea un nuevo presupuesto.
     */
    public PresupuestoResponseDTO crear(PresupuestoCreateUpdateDTO dto) {
        validateUniqueField("número de presupuesto", dto.numeroPresupuesto(), presupuestoRepository::findByNumeroPresupuesto);
        Presupuesto presupuesto = presupuestoMapper.toEntity(dto);
        return saveAndMapToDto(presupuesto);
    }

    /**
     * Actualiza un presupuesto existente.
     */
    public PresupuestoResponseDTO actualizar(Integer id, PresupuestoCreateUpdateDTO dto) {
        Presupuesto presupuesto = findByIdOrThrow(id);
        validateUniqueFieldForUpdate("número de presupuesto", presupuesto.getNumeroPresupuesto(), 
            dto.numeroPresupuesto(), presupuestoRepository::findByNumeroPresupuesto);
        presupuestoMapper.updateEntityFromDTO(dto, presupuesto);
        return saveAndMapToDto(presupuesto);
    }

    /**
     * Elimina un presupuesto.
     */
    public void eliminar(Integer id) {
        deleteById(id);
    }

    /**
     * Obtiene todos los presupuestos (con paginación).
     */
    @Transactional(readOnly = true)
    public Page<PresupuestoResponseDTO> obtenerTodos(Pageable pageable) {
        return findAll(pageable);
    }

    /**
     * Obtiene presupuestos por usuario.
     */
    @Transactional(readOnly = true)
    public List<PresupuestoResponseDTO> obtenerPorUsuario(String usuarioDni) {
        return presupuestoRepository.findByUsuarioDni(usuarioDni)
            .stream()
            .map(presupuestoMapper::toResponseDTO)
            .toList();
    }

    /**
     * Obtiene presupuestos por estado.
     */
    @Transactional(readOnly = true)
    public List<PresupuestoResponseDTO> obtenerPorEstado(BudgetStatus estado) {
        return presupuestoRepository.findByEstado(estado)
            .stream()
            .map(presupuestoMapper::toResponseDTO)
            .toList();
    }

    /**
     * Obtiene presupuestos de un usuario en un estado específico.
     */
    @Transactional(readOnly = true)
    public List<PresupuestoResponseDTO> obtenerPorUsuarioYEstado(String usuarioDni, BudgetStatus estado) {
        return presupuestoRepository.findByUsuarioDniAndEstado(usuarioDni, estado)
            .stream()
            .map(presupuestoMapper::toResponseDTO)
            .toList();
    }
}
