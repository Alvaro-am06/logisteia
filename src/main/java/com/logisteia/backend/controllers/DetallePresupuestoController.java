package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.DetallePresupuestoResponseDTO;
import com.logisteia.backend.dtos.DetallePresupuestoCreateUpdateDTO;
import com.logisteia.backend.services.DetallePresupuestoService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Detalles de Presupuesto.
 */
@RestController
@RequestMapping("/api/v1/detalles-presupuesto")
@RequiredArgsConstructor
public class DetallePresupuestoController {

    private final DetallePresupuestoService detallePresupuestoService;

    @GetMapping("/{id}")
    public ResponseEntity<DetallePresupuestoResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(detallePresupuestoService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<DetallePresupuestoResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(detallePresupuestoService.obtenerTodos(pageable));
    }

    @GetMapping("/presupuesto/{presupuestoId}")
    public ResponseEntity<List<DetallePresupuestoResponseDTO>> obtenerPorPresupuesto(@PathVariable Integer presupuestoId) {
        return ResponseEntity.ok(detallePresupuestoService.obtenerPorPresupuesto(presupuestoId));
    }

    @GetMapping("/numero/{numeroPresupuesto}")
    public ResponseEntity<List<DetallePresupuestoResponseDTO>> obtenerPorNumeroPresupuesto(@PathVariable String numeroPresupuesto) {
        return ResponseEntity.ok(detallePresupuestoService.obtenerPorNumeroPresupuesto(numeroPresupuesto));
    }

    @PostMapping
    public ResponseEntity<DetallePresupuestoResponseDTO> crear(@Valid @RequestBody DetallePresupuestoCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(detallePresupuestoService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<DetallePresupuestoResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody DetallePresupuestoCreateUpdateDTO dto) {
        return ResponseEntity.ok(detallePresupuestoService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        detallePresupuestoService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
