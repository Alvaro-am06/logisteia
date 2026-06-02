package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.ServicioInformaticaResponseDTO;
import com.logisteia.backend.dtos.ServicioInformaticaCreateUpdateDTO;
import com.logisteia.backend.enums.ServiceCategory;
import com.logisteia.backend.services.ServicioInformaticaService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Servicios Informáticos.
 */
@RestController
@RequestMapping("/api/v1/servicios-informatica")
@RequiredArgsConstructor
public class ServicioInformaticaController {

    private final ServicioInformaticaService servicioInformaticaService;

    @GetMapping("/{id}")
    public ResponseEntity<ServicioInformaticaResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(servicioInformaticaService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<ServicioInformaticaResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(servicioInformaticaService.obtenerTodos(pageable));
    }

    @GetMapping("/categoria/{categoria}")
    public ResponseEntity<List<ServicioInformaticaResponseDTO>> obtenerPorCategoria(@PathVariable String categoria) {
        return ResponseEntity.ok(servicioInformaticaService.obtenerPorCategoria(ServiceCategory.valueOf(categoria)));
    }

    @GetMapping("/activos/lista")
    public ResponseEntity<List<ServicioInformaticaResponseDTO>> obtenerActivos() {
        return ResponseEntity.ok(servicioInformaticaService.obtenerActivos());
    }

    @PostMapping
    public ResponseEntity<ServicioInformaticaResponseDTO> crear(@Valid @RequestBody ServicioInformaticaCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(servicioInformaticaService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<ServicioInformaticaResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody ServicioInformaticaCreateUpdateDTO dto) {
        return ResponseEntity.ok(servicioInformaticaService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        servicioInformaticaService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
