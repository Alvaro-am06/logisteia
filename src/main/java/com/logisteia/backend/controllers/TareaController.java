package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.TareaResponseDTO;
import com.logisteia.backend.dtos.TareaCreateUpdateDTO;
import com.logisteia.backend.enums.TaskStatus;
import com.logisteia.backend.services.TareaService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Tareas.
 */
@RestController
@RequestMapping("/api/v1/tareas")
@RequiredArgsConstructor
public class TareaController {

    private final TareaService tareaService;

    @GetMapping("/{id}")
    public ResponseEntity<TareaResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(tareaService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<TareaResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(tareaService.obtenerTodos(pageable));
    }

    @GetMapping("/proyecto/{proyectoId}")
    public ResponseEntity<List<TareaResponseDTO>> obtenerPorProyecto(@PathVariable Integer proyectoId) {
        return ResponseEntity.ok(tareaService.obtenerPorProyecto(proyectoId));
    }

    @GetMapping("/trabajador/{trabajadorDni}")
    public ResponseEntity<List<TareaResponseDTO>> obtenerPorTrabajador(@PathVariable String trabajadorDni) {
        return ResponseEntity.ok(tareaService.obtenerPorTrabajador(trabajadorDni));
    }

    @GetMapping("/estado/{estado}")
    public ResponseEntity<List<TareaResponseDTO>> obtenerPorEstado(@PathVariable String estado) {
        return ResponseEntity.ok(tareaService.obtenerPorEstado(TaskStatus.valueOf(estado)));
    }

    @PostMapping
    public ResponseEntity<TareaResponseDTO> crear(@Valid @RequestBody TareaCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(tareaService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<TareaResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody TareaCreateUpdateDTO dto) {
        return ResponseEntity.ok(tareaService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        tareaService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
