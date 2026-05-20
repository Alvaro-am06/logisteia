package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.ClienteResponseDTO;
import com.logisteia.backend.dtos.ClienteCreateUpdateDTO;
import com.logisteia.backend.entities.Cliente;
import com.logisteia.backend.repositories.UsuarioRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Component;

@Component
@RequiredArgsConstructor
public class ClienteMapper {

    private final UsuarioRepository usuarioRepository;

    public ClienteResponseDTO toResponseDTO(Cliente cliente) {
        if (cliente == null) return null;
        
        return new ClienteResponseDTO(
            cliente.getId(),
            cliente.getNombre(),
            cliente.getEmpresa(),
            cliente.getEmail(),
            cliente.getTelefono(),
            cliente.getDireccion(),
            cliente.getCifNif(),
            cliente.getNotas(),
            cliente.getActivo(),
            cliente.getFechaRegistro(),
            cliente.getJefe() != null ? cliente.getJefe().getDni() : null
        );
    }

    public Cliente toEntity(ClienteCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return Cliente.builder()
            .nombre(dto.nombre())
            .empresa(dto.empresa())
            .email(dto.email())
            .telefono(dto.telefono())
            .direccion(dto.direccion())
            .cifNif(dto.cifNif())
            .notas(dto.notas())
            .activo(dto.activo())
            .jefe(usuarioRepository.findById(dto.jefeDni()).orElse(null))
            .build();
    }

    public void updateEntityFromDTO(ClienteCreateUpdateDTO dto, Cliente cliente) {
        if (dto == null || cliente == null) return;
        
        cliente.setNombre(dto.nombre());
        cliente.setEmpresa(dto.empresa());
        cliente.setEmail(dto.email());
        cliente.setTelefono(dto.telefono());
        cliente.setDireccion(dto.direccion());
        cliente.setCifNif(dto.cifNif());
        cliente.setNotas(dto.notas());
        cliente.setActivo(dto.activo());
        cliente.setJefe(usuarioRepository.findById(dto.jefeDni()).orElse(null));
    }
}
