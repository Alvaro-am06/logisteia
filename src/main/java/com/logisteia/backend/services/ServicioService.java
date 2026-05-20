package com.logisteia.backend.services;

import com.logisteia.backend.dtos.ServicioResponseDTO;
import com.logisteia.backend.dtos.ServicioCreateUpdateDTO;
import com.logisteia.backend.entities.Servicio;
import com.logisteia.backend.mappers.ServicioMapper;
import com.logisteia.backend.repositories.ServicioRepository;
import com.logisteia.backend.services.base.GenericCrudService;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

/**
 * Servicio refactorizado para gestionar operaciones CRUD de Servicios.
 * Extiende GenericCrudService para eliminar código boilerplate.
 */
@Service
@Transactional
public class ServicioService extends GenericCrudService<Servicio, ServicioResponseDTO, ServicioCreateUpdateDTO, String> {

    private final ServicioRepository servicioRepository;
    private final ServicioMapper servicioMapper;

    public ServicioService(ServicioRepository servicioRepository, ServicioMapper servicioMapper) {
        super(servicioRepository, servicioMapper::toResponseDTO, "Servicio");
        this.servicioRepository = servicioRepository;
        this.servicioMapper = servicioMapper;
    }

    @Transactional(readOnly = true)
    public ServicioResponseDTO obtenerPorNombre(String nombre) {
        return toDto(findByIdOrThrow(nombre));
    }

    @Transactional(readOnly = true)
    public Page<ServicioResponseDTO> obtenerTodos(Pageable pageable) {
        return findAll(pageable);
    }

    @Transactional(readOnly = true)
    public List<ServicioResponseDTO> obtenerActivos() {
        return servicioRepository.findByEstaActivo(true)
            .stream()
            .map(servicioMapper::toResponseDTO)
            .toList();
    }

    public ServicioResponseDTO crear(ServicioCreateUpdateDTO dto) {
        validateUniqueField("nombre", dto.nombre(), servicioRepository::findById);
        Servicio servicio = servicioMapper.toEntity(dto);
        return saveAndMapToDto(servicio);
    }

    public ServicioResponseDTO actualizar(String nombre, ServicioCreateUpdateDTO dto) {
        Servicio servicio = findByIdOrThrow(nombre);
        servicioMapper.updateEntityFromDTO(dto, servicio);
        return saveAndMapToDto(servicio);
    }

    public void eliminar(String nombre) {
        deleteById(nombre);
    }
}
