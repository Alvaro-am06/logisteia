package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.ProyectoResponseDTO;
import com.logisteia.backend.dtos.ProyectoCreateUpdateDTO;
import com.logisteia.backend.enums.ProjectStatus;
import com.logisteia.backend.services.ProyectoService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Proyectos.
 */
@RestController
@RequestMapping("/api/v1/proyectos")
@RequiredArgsConstructor
public class ProyectoController {

    private final ProyectoService proyectoService;

    @GetMapping("/{id}")
    public ResponseEntity<ProyectoResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(proyectoService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<ProyectoResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(proyectoService.obtenerTodos(pageable));
    }

    @GetMapping("/codigo/{codigo}")
    public ResponseEntity<ProyectoResponseDTO> obtenerPorCodigo(@PathVariable String codigo) {
        return ResponseEntity.ok(proyectoService.obtenerPorCodigo(codigo));
    }

    @GetMapping("/estado/{estado}")
    public ResponseEntity<List<ProyectoResponseDTO>> obtenerPorEstado(@PathVariable String estado) {
        return ResponseEntity.ok(proyectoService.obtenerPorEstado(ProjectStatus.valueOf(estado)));
    }

    @GetMapping("/jefe/{jefeDni}")
    public ResponseEntity<List<ProyectoResponseDTO>> obtenerPorJefe(@PathVariable String jefeDni) {
        return ResponseEntity.ok(proyectoService.obtenerPorJefe(jefeDni));
    }

    @PostMapping
    public ResponseEntity<ProyectoResponseDTO> crear(@Valid @RequestBody ProyectoCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(proyectoService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<ProyectoResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody ProyectoCreateUpdateDTO dto) {
        return ResponseEntity.ok(proyectoService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        proyectoService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
