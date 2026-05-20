package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.AsignacionProyectoResponseDTO;
import com.logisteia.backend.dtos.AsignacionProyectoCreateUpdateDTO;
import com.logisteia.backend.services.AsignacionProyectoService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Asignaciones de Proyecto.
 */
@RestController
@RequestMapping("/api/v1/asignaciones-proyecto")
@RequiredArgsConstructor
public class AsignacionProyectoController {

    private final AsignacionProyectoService asignacionProyectoService;

    @GetMapping("/{id}")
    public ResponseEntity<AsignacionProyectoResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(asignacionProyectoService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<AsignacionProyectoResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(asignacionProyectoService.obtenerTodos(pageable));
    }

    @GetMapping("/proyecto/{proyectoId}")
    public ResponseEntity<List<AsignacionProyectoResponseDTO>> obtenerPorProyecto(@PathVariable Integer proyectoId) {
        return ResponseEntity.ok(asignacionProyectoService.obtenerPorProyecto(proyectoId));
    }

    @GetMapping("/trabajador/{trabajadorDni}")
    public ResponseEntity<List<AsignacionProyectoResponseDTO>> obtenerPorTrabajador(@PathVariable String trabajadorDni) {
        return ResponseEntity.ok(asignacionProyectoService.obtenerPorTrabajador(trabajadorDni));
    }

    @PostMapping
    public ResponseEntity<AsignacionProyectoResponseDTO> crear(@Valid @RequestBody AsignacionProyectoCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(asignacionProyectoService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<AsignacionProyectoResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody AsignacionProyectoCreateUpdateDTO dto) {
        return ResponseEntity.ok(asignacionProyectoService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        asignacionProyectoService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
