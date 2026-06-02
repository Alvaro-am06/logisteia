package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.AccionAdministrativaResponseDTO;
import com.logisteia.backend.dtos.AccionAdministrativaCreateUpdateDTO;
import com.logisteia.backend.services.AccionAdministrativaService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Acciones Administrativas.
 */
@RestController
@RequestMapping("/api/v1/acciones-administrativas")
@RequiredArgsConstructor
public class AccionAdministrativaController {

    private final AccionAdministrativaService accionAdministrativaService;

    @GetMapping("/{id}")
    public ResponseEntity<AccionAdministrativaResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(accionAdministrativaService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<AccionAdministrativaResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(accionAdministrativaService.obtenerTodos(pageable));
    }

    @GetMapping("/administrador/{administradorDni}")
    public ResponseEntity<List<AccionAdministrativaResponseDTO>> obtenerPorAdministrador(@PathVariable String administradorDni) {
        return ResponseEntity.ok(accionAdministrativaService.obtenerPorAdministrador(administradorDni));
    }

    @GetMapping("/usuario/{usuarioAfectadoDni}")
    public ResponseEntity<List<AccionAdministrativaResponseDTO>> obtenerPorUsuarioAfectado(@PathVariable String usuarioAfectadoDni) {
        return ResponseEntity.ok(accionAdministrativaService.obtenerPorUsuarioAfectado(usuarioAfectadoDni));
    }

    @PostMapping
    public ResponseEntity<AccionAdministrativaResponseDTO> crear(@Valid @RequestBody AccionAdministrativaCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(accionAdministrativaService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<AccionAdministrativaResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody AccionAdministrativaCreateUpdateDTO dto) {
        return ResponseEntity.ok(accionAdministrativaService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        accionAdministrativaService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
