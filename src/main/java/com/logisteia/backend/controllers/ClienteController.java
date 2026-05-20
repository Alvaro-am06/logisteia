package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.ClienteResponseDTO;
import com.logisteia.backend.dtos.ClienteCreateUpdateDTO;
import com.logisteia.backend.services.ClienteService;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para Clientes.
 */
@RestController
@RequestMapping("/api/v1/clientes")
@RequiredArgsConstructor
public class ClienteController {

    private final ClienteService clienteService;

    @GetMapping("/{id}")
    public ResponseEntity<ClienteResponseDTO> obtenerPorId(@PathVariable Integer id) {
        return ResponseEntity.ok(clienteService.obtenerPorId(id));
    }

    @GetMapping
    public ResponseEntity<Page<ClienteResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        var pageable = PageRequest.of(page, size);
        return ResponseEntity.ok(clienteService.obtenerTodos(pageable));
    }

    @GetMapping("/email/{email}")
    public ResponseEntity<ClienteResponseDTO> obtenerPorEmail(@PathVariable String email) {
        return ResponseEntity.ok(clienteService.obtenerPorEmail(email));
    }

    @GetMapping("/jefe/{jefeDni}")
    public ResponseEntity<List<ClienteResponseDTO>> obtenerPorJefe(@PathVariable String jefeDni) {
        return ResponseEntity.ok(clienteService.obtenerPorJefe(jefeDni));
    }

    @GetMapping("/activos/lista")
    public ResponseEntity<List<ClienteResponseDTO>> obtenerActivos() {
        return ResponseEntity.ok(clienteService.obtenerActivos());
    }

    @PostMapping
    public ResponseEntity<ClienteResponseDTO> crear(@Valid @RequestBody ClienteCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(clienteService.crear(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<ClienteResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody ClienteCreateUpdateDTO dto) {
        return ResponseEntity.ok(clienteService.actualizar(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        clienteService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
