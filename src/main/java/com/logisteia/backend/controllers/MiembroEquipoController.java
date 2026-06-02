package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.MiembroEquipoResponseDTO;
import com.logisteia.backend.dtos.MiembroEquipoCreateUpdateDTO;
import com.logisteia.backend.services.MiembroEquipoService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Miembros de Equipo.
 */
@RestController
@RequestMapping("/api/v1/miembros-equipo")
@RequiredArgsConstructor
public class MiembroEquipoController {

    private final MiembroEquipoService miembroEquipoService;

    @GetMapping("/{id}")
    public ResponseEntity<MiembroEquipoResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(miembroEquipoService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<MiembroEquipoResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(miembroEquipoService.obtenerTodos(pageable));
    }

    @GetMapping("/equipo/{equipoId}")
    public ResponseEntity<List<MiembroEquipoResponseDTO>> obtenerPorEquipo(@PathVariable Integer equipoId) {
        return ResponseEntity.ok(miembroEquipoService.obtenerPorEquipo(equipoId));
    }

    @GetMapping("/trabajador/{trabajadorDni}")
    public ResponseEntity<List<MiembroEquipoResponseDTO>> obtenerPorTrabajador(@PathVariable String trabajadorDni) {
        return ResponseEntity.ok(miembroEquipoService.obtenerPorTrabajador(trabajadorDni));
    }

    @PostMapping
    public ResponseEntity<MiembroEquipoResponseDTO> crear(@Valid @RequestBody MiembroEquipoCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(miembroEquipoService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<MiembroEquipoResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody MiembroEquipoCreateUpdateDTO dto) {
        return ResponseEntity.ok(miembroEquipoService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        miembroEquipoService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
