package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.ServicioResponseDTO;
import com.logisteia.backend.dtos.ServicioCreateUpdateDTO;
import com.logisteia.backend.services.ServicioService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Servicios.
 */
@RestController
@RequestMapping("/api/v1/servicios")
@RequiredArgsConstructor
public class ServicioController {

    private final ServicioService servicioService;

    @GetMapping("/{nombre}")
    public ResponseEntity<ServicioResponseDTO> obtenerPorNombre(@PathVariable String nombre) {
        return ResponseEntity.ok(servicioService.obtenerPorNombre(nombre));
    }

    @GetMapping
    public ResponseEntity<Page<ServicioResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(servicioService.obtenerTodos(pageable));
    }

    @GetMapping("/activos/lista")
    public ResponseEntity<List<ServicioResponseDTO>> obtenerActivos() {
        return ResponseEntity.ok(servicioService.obtenerActivos());
    }

    @PostMapping
    public ResponseEntity<ServicioResponseDTO> crear(@Valid @RequestBody ServicioCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(servicioService.crear(dto));
    }

    @PutMapping("/{nombre}")
    public ResponseEntity<ServicioResponseDTO> actualizar(
            @PathVariable String nombre,
            @Valid @RequestBody ServicioCreateUpdateDTO dto) {
        return ResponseEntity.ok(servicioService.actualizar(nombre, dto));
    }

    @DeleteMapping("/{nombre}")
    public ResponseEntity<Void> eliminar(@PathVariable String nombre) {
        servicioService.eliminar(nombre);
        return ResponseEntity.noContent().build();
    }
}
