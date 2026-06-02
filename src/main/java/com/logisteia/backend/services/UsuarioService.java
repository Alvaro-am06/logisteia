package com.logisteia.backend.services;

import com.logisteia.backend.dtos.UsuarioResponseDTO;
import com.logisteia.backend.dtos.UsuarioCreateUpdateDTO;
import com.logisteia.backend.entities.Usuario;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.UsuarioMapper;
import com.logisteia.backend.repositories.UsuarioRepository;
import com.logisteia.backend.services.base.GenericCrudService;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;

/**
 * Servicio refactorizado para gestionar operaciones CRUD de Usuarios.
 * Extiende GenericCrudService para eliminar código boilerplate (60% reducción).
 */
@Service
@Transactional
public class UsuarioService extends GenericCrudService<Usuario, UsuarioResponseDTO, UsuarioCreateUpdateDTO, String> {

    private final UsuarioRepository usuarioRepository;
    private final UsuarioMapper usuarioMapper;

    public UsuarioService(UsuarioRepository usuarioRepository, UsuarioMapper usuarioMapper) {
        super(usuarioRepository, usuarioMapper::toResponseDTO, "Usuario");
        this.usuarioRepository = usuarioRepository;
        this.usuarioMapper = usuarioMapper;
    }

    /**
     * Obtiene un usuario por su DNI.
     */
    @Transactional(readOnly = true)
    public UsuarioResponseDTO obtenerPorDni(String dni) {
        return toDto(findByIdOrThrow(dni));
    }

    /**
     * Obtiene un usuario por su email.
     */
    @Transactional(readOnly = true)
    public UsuarioResponseDTO obtenerPorEmail(String email) {
        Usuario usuario = usuarioRepository.findByEmail(email)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Usuario", "email", email));
        return toDto(usuario);
    }

    /**
     * Crea un nuevo usuario.
     */
    public UsuarioResponseDTO crear(UsuarioCreateUpdateDTO dto) {
        validateUniqueField("email", dto.email(), usuarioRepository::findByEmail);
        Usuario usuario = usuarioMapper.toEntity(dto);
        return saveAndMapToDto(usuario);
    }

    /**
     * Actualiza un usuario existente.
     */
    public UsuarioResponseDTO actualizar(String dni, UsuarioCreateUpdateDTO dto) {
        Usuario usuario = findByIdOrThrow(dni);
        validateUniqueFieldForUpdate("email", usuario.getEmail(), dto.email(), usuarioRepository::findByEmail);
        usuarioMapper.updateEntityFromDTO(dto, usuario);
        return saveAndMapToDto(usuario);
    }

    /**
     * Elimina un usuario.
     */
    public void eliminar(String dni) {
        deleteById(dni);
    }

    /**
     * Obtiene todos los usuarios (con paginación).
     */
    @Transactional(readOnly = true)
    public Page<UsuarioResponseDTO> obtenerTodos(Pageable pageable) {
        return findAll(pageable);
    }
}
