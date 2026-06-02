package com.logisteia.backend.services;

import com.logisteia.backend.dtos.ClienteResponseDTO;
import com.logisteia.backend.dtos.ClienteCreateUpdateDTO;
import com.logisteia.backend.entities.Cliente;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.ClienteMapper;
import com.logisteia.backend.repositories.ClienteRepository;
import com.logisteia.backend.services.base.GenericCrudService;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

/**
 * Servicio refactorizado para gestionar operaciones CRUD de Clientes.
 * Extiende GenericCrudService para eliminar código boilerplate.
 */
@Service
@Transactional
public class ClienteService extends GenericCrudService<Cliente, ClienteResponseDTO, ClienteCreateUpdateDTO, Integer> {

    private final ClienteRepository clienteRepository;
    private final ClienteMapper clienteMapper;

    public ClienteService(ClienteRepository clienteRepository, ClienteMapper clienteMapper) {
        super(clienteRepository, clienteMapper::toResponseDTO, "Cliente");
        this.clienteRepository = clienteRepository;
        this.clienteMapper = clienteMapper;
    }

    @Transactional(readOnly = true)
    public ClienteResponseDTO obtenerPorId(Integer id) {
        return toDto(findByIdOrThrow(id));
    }

    @Transactional(readOnly = true)
    public Page<ClienteResponseDTO> obtenerTodos(Pageable pageable) {
        return findAll(pageable);
    }

    @Transactional(readOnly = true)
    public ClienteResponseDTO obtenerPorEmail(String email) {
        Cliente cliente = clienteRepository.findByEmail(email)
            .stream()
            .findFirst()
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Cliente", "email", email));
        return toDto(cliente);
    }

    @Transactional(readOnly = true)
    public List<ClienteResponseDTO> obtenerPorJefe(String jefeDni) {
        return clienteRepository.findByJefeDni(jefeDni)
            .stream()
            .map(clienteMapper::toResponseDTO)
            .toList();
    }

    @Transactional(readOnly = true)
    public List<ClienteResponseDTO> obtenerActivos() {
        return clienteRepository.findByActivo(true)
            .stream()
            .map(clienteMapper::toResponseDTO)
            .toList();
    }

    public ClienteResponseDTO crear(ClienteCreateUpdateDTO dto) {
        validateUniqueField("email", dto.email(), email -> clienteRepository.findByEmail(email).stream().findFirst());
        Cliente cliente = clienteMapper.toEntity(dto);
        return saveAndMapToDto(cliente);
    }

    public ClienteResponseDTO actualizar(Integer id, ClienteCreateUpdateDTO dto) {
        Cliente cliente = findByIdOrThrow(id);
        validateUniqueFieldForUpdate("email", cliente.getEmail(), dto.email(), 
            email -> clienteRepository.findByEmail(email).stream().findFirst());
        clienteMapper.updateEntityFromDTO(dto, cliente);
        return saveAndMapToDto(cliente);
    }

    public void eliminar(Integer id) {
        deleteById(id);
    }
}
