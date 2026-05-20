package com.logisteia.backend.controllers;

import com.logisteia.backend.dtos.PresupuestoResponseDTO;
import com.logisteia.backend.dtos.PresupuestoCreateUpdateDTO;
import com.logisteia.backend.services.PresupuestoService;
import com.logisteia.backend.enums.BudgetStatus;
import jakarta.validation.Valid;
import lombok.RequiredArgsConstructor;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.domain.PageRequest;
import java.util.List;

/**
 * Controlador REST para gestionar Presupuestos.
 * Endpoints:
 *   GET    /api/v1/presupuestos/{id}                 - Obtener presupuesto por ID
 *   GET    /api/v1/presupuestos/numero/{numero}      - Obtener presupuesto por número
 *   GET    /api/v1/presupuestos                       - Listar todos (paginado)
 *   GET    /api/v1/presupuestos/usuario/{dni}        - Listar por usuario
 *   GET    /api/v1/presupuestos/estado/{estado}      - Listar por estado
 *   POST   /api/v1/presupuestos                       - Crear nuevo presupuesto
 *   PUT    /api/v1/presupuestos/{id}                 - Actualizar presupuesto
 *   DELETE /api/v1/presupuestos/{id}                 - Eliminar presupuesto
 */
@RestController
@RequestMapping("/api/v1/presupuestos")
@RequiredArgsConstructor
public class PresupuestoController {

    private final PresupuestoService presupuestoService;

    /**
     * GET /api/v1/presupuestos/{id}
     * Obtiene un presupuesto por su ID.
     */
    @GetMapping("/{id}")
    public ResponseEntity<PresupuestoResponseDTO> obtenerPorId(@PathVariable Integer id) {
        PresupuestoResponseDTO presupuesto = presupuestoService.obtenerPorId(id);
        return ResponseEntity.ok(presupuesto);
    }

    /**
     * GET /api/v1/presupuestos/numero/{numeroPresupuesto}
     * Obtiene un presupuesto por su número.
     */
    @GetMapping("/numero/{numeroPresupuesto}")
    public ResponseEntity<PresupuestoResponseDTO> obtenerPorNumero(
            @PathVariable String numeroPresupuesto) {
        PresupuestoResponseDTO presupuesto = presupuestoService.obtenerPorNumero(numeroPresupuesto);
        return ResponseEntity.ok(presupuesto);
    }

    /**
     * GET /api/v1/presupuestos?page=0&size=20
     * Obtiene todos los presupuestos con paginación.
     */
    @GetMapping
    public ResponseEntity<Page<PresupuestoResponseDTO>> obtenerTodos(
            @RequestParam(defaultValue = "0") int page,
            @RequestParam(defaultValue = "20") int size) {
        
        Pageable pageable = PageRequest.of(page, size);
        Page<PresupuestoResponseDTO> presupuestos = presupuestoService.obtenerTodos(pageable);
        return ResponseEntity.ok(presupuestos);
    }

    /**
     * GET /api/v1/presupuestos/usuario/{usuarioDni}
     * Obtiene todos los presupuestos de un usuario.
     */
    @GetMapping("/usuario/{usuarioDni}")
    public ResponseEntity<List<PresupuestoResponseDTO>> obtenerPorUsuario(
            @PathVariable String usuarioDni) {
        List<PresupuestoResponseDTO> presupuestos = presupuestoService.obtenerPorUsuario(usuarioDni);
        return ResponseEntity.ok(presupuestos);
    }

    /**
     * GET /api/v1/presupuestos/estado/{estado}
     * Obtiene presupuestos por estado.
     * Estados válidos: BORRADOR, ENVIADO, APROBADO, RECHAZADO
     */
    @GetMapping("/estado/{estado}")
    public ResponseEntity<List<PresupuestoResponseDTO>> obtenerPorEstado(
            @PathVariable BudgetStatus estado) {
        List<PresupuestoResponseDTO> presupuestos = presupuestoService.obtenerPorEstado(estado);
        return ResponseEntity.ok(presupuestos);
    }

    /**
     * POST /api/v1/presupuestos
     * Crea un nuevo presupuesto.
     * Body:
     * {
     *   "numeroPresupuesto": "PRE-2024-001",
     *   "usuarioDni": "12345678A",
     *   "proyectoId": 1,
     *   "clienteId": 2,
     *   "estado": "BORRADOR",
     *   "validezDias": 30,
     *   "total": "1500.00",
     *   "notas": "Presupuesto para desarrollo web"
     * }
     */
    @PostMapping
    public ResponseEntity<PresupuestoResponseDTO> crear(
            @Valid @RequestBody PresupuestoCreateUpdateDTO dto) {
        PresupuestoResponseDTO presupuesto = presupuestoService.crear(dto);
        return ResponseEntity.status(HttpStatus.CREATED).body(presupuesto);
    }

    /**
     * PUT /api/v1/presupuestos/{id}
     * Actualiza un presupuesto existente.
     * Body: mismo que POST
     */
    @PutMapping("/{id}")
    public ResponseEntity<PresupuestoResponseDTO> actualizar(
            @PathVariable Integer id,
            @Valid @RequestBody PresupuestoCreateUpdateDTO dto) {
        
        PresupuestoResponseDTO presupuesto = presupuestoService.actualizar(id, dto);
        return ResponseEntity.ok(presupuesto);
    }

    /**
     * DELETE /api/v1/presupuestos/{id}
     * Elimina un presupuesto.
     */
    @DeleteMapping("/{id}")
    public ResponseEntity<Void> eliminar(@PathVariable Integer id) {
        presupuestoService.eliminar(id);
        return ResponseEntity.noContent().build();
    }
}
