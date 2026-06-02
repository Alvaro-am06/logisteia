package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.UsuarioResponseDTO;
import com.logisteia.backend.dtos.UsuarioCreateUpdateDTO;
import com.logisteia.backend.services.UsuarioService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.PageRequest;

/**
 * Controlador REST para gestionar Usuarios.
 * Endpoints:
 *   GET    /api/v1/usuarios/{dni}           - Obtener usuario por DNI
 *   GET    /api/v1/usuarios/email/{email}   - Obtener usuario por email
 *   GET    /api/v1/usuarios                 - Listar todos los usuarios (paginado)
 *   POST   /api/v1/usuarios                 - Crear nuevo usuario
 *   PUT    /api/v1/usuarios/{dni}           - Actualizar usuario
 *   DELETE /api/v1/usuarios/{dni}           - Eliminar usuario
 */
@RestController
@RequestMapping("/api/v1/usuarios")
@RequiredArgsConstructor
public class UsuarioController {

    private final UsuarioService usuarioService;

    /**
     * GET /api/v1/usuarios/{dni}
     * Obtiene un usuario por su DNI.
     */
    @GetMapping("/{dni}")
    public ResponseEntity<UsuarioResponseDTO> obtenerPorDni(@PathVariable String dni) {
        UsuarioResponseDTO usuario = usuarioService.obtenerPorDni(dni);
        return ResponseEntity.ok(usuario);
    }

    /**
     * GET /api/v1/usuarios/email/{email}
     * Obtiene un usuario por su email.
     * 
     * Nota: Este endpoint debe ir antes de /{dni} en el conteo de rutas,
     * pero Spring lo maneja correctamente con path específicos.
     */
    @GetMapping("/email/{email}")
    public ResponseEntity<UsuarioResponseDTO> obtenerPorEmail(@PathVariable String email) {
        UsuarioResponseDTO usuario = usuarioService.obtenerPorEmail(email);
        return ResponseEntity.ok(usuario);
    }

    /**
     * GET /api/v1/usuarios?page=0&size=20
     * Obtiene todos los usuarios con paginación.
     * Parámetros query:
     *   - page: número de página (por defecto 0)
     *   - size: cantidad de resultados por página (por defecto 20)
     *   - sort: campo para ordenar (ej: dni,asc o nombre,desc)
     */
    @GetMapping
    public ResponseEntity<Page<UsuarioResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        
        Pageable pageable = PageRequest.of(page, size);
        Page<UsuarioResponseDTO> usuarios = usuarioService.obtenerTodos(pageable);
        return ResponseEntity.ok(usuarios);
    }

    /**
     * POST /api/v1/usuarios
     * Crea un nuevo usuario.
     * Body:
     * {
     *   "dni": "12345678A",
     *   "email": "usuario@logisteia.com",
     *   "nombre": "Juan Pérez",
     *   "contrase": "password123",
     *   "rol": "JEFE_EQUIPO",
     *   "estado": "ACTIVO",
     *   "telefono": "666123456"
     * }
     */
    @PostMapping
    public ResponseEntity<UsuarioResponseDTO> crear(@Valid @RequestBody UsuarioCreateUpdateDTO dto) {
        UsuarioResponseDTO usuario = usuarioService.crear(dto);
        return ResponseEntity.status(HttpStatus.CREATED).body(usuario);
    }

    /**
     * PUT /api/v1/usuarios/{dni}
     * Actualiza un usuario existente.
     * Body: mismo que POST
     */
    @PutMapping("/{dni}")
    public ResponseEntity<UsuarioResponseDTO> actualizar(
            @PathVariable String dni,
            @Valid @RequestBody UsuarioCreateUpdateDTO dto) {
        
        UsuarioResponseDTO usuario = usuarioService.actualizar(dni, dto);
        return ResponseEntity.ok(usuario);
    }

    /**
     * DELETE /api/v1/usuarios/{dni}
     * Elimina un usuario.
     */
    @DeleteMapping("/{dni}")
    public ResponseEntity<Void> eliminar(@PathVariable String dni) {
        usuarioService.eliminar(dni);
        return ResponseEntity.noContent().build();
    }
}
