package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.EquipoResponseDTO;
import com.logisteia.backend.dtos.EquipoCreateUpdateDTO;
import com.logisteia.backend.services.EquipoService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Equipos.
 */
@RestController
@RequestMapping("/api/v1/equipos")
@RequiredArgsConstructor
public class EquipoController {

    private final EquipoService equipoService;

    @GetMapping("/{id}")
    public ResponseEntity<EquipoResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(equipoService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<EquipoResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(equipoService.obtenerTodos(pageable));
    }

    @GetMapping("/jefe/{jefeDni}")
    public ResponseEntity<List<EquipoResponseDTO>> obtenerPorJefe(@PathVariable String jefeDni) {
        return ResponseEntity.ok(equipoService.obtenerPorJefe(jefeDni));
    }

    @GetMapping("/activos/lista")
    public ResponseEntity<List<EquipoResponseDTO>> obtenerActivos() {
        return ResponseEntity.ok(equipoService.obtenerActivos());
    }

    @PostMapping
    public ResponseEntity<EquipoResponseDTO> crear(@Valid @RequestBody EquipoCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(equipoService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<EquipoResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody EquipoCreateUpdateDTO dto) {
        return ResponseEntity.ok(equipoService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        equipoService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
