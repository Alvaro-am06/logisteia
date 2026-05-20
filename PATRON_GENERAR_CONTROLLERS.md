# 🏭 PATRÓN: Cómo Generar Controllers para Todas las Entidades

## Introducción

El patrón usado para Usuarios y Presupuestos se puede replicar para las 12 entidades. Este documento te muestra exactamente cómo.

---

## 📋 Paso a Paso: Generar Controller para Nueva Entidad

### Ejemplo: Crear Controllers para **Equipo**

#### **1. Crear DTOs (Response + Create/Update)**

**EquipoResponseDTO.java** - Records inmutables para lectura:
```java
package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDateTime;

public record EquipoResponseDTO(
    @NotNull Integer id,
    @NotBlank String nombre,
    String descripcion,
    @NotNull Boolean activo,
    LocalDateTime fechaCreacion,
    String jefeDni  // Solo el ID, no la entidad
) {}
```

**EquipoCreateUpdateDTO.java** - Para POST/PUT:
```java
package com.logisteia.backend.dtos;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;

public record EquipoCreateUpdateDTO(
    @NotBlank(message = "El nombre del equipo no puede estar vacío")
    @Size(min = 3, max = 255)
    String nombre,

    String descripcion,

    @NotNull(message = "El DNI del jefe no puede estar vacío")
    String jefeDni,

    @NotNull(message = "Debe indicar si el equipo está activo")
    Boolean activo
) {}
```

---

#### **2. Crear Mapper**

**EquipoMapper.java** - Convertidor de Entidad ↔ DTOs:
```java
package com.logisteia.backend.mappers;

import com.logisteia.backend.dtos.EquipoResponseDTO;
import com.logisteia.backend.dtos.EquipoCreateUpdateDTO;
import com.logisteia.backend.entities.Equipo;
import com.logisteia.backend.repositories.UsuarioRepository;
import org.springframework.stereotype.Component;
import lombok.RequiredArgsConstructor;

@Component
@RequiredArgsConstructor
public class EquipoMapper {

    private final UsuarioRepository usuarioRepository;

    // toResponseDTO: Entidad → DTO
    public EquipoResponseDTO toResponseDTO(Equipo equipo) {
        if (equipo == null) return null;
        
        return new EquipoResponseDTO(
            equipo.getId(),
            equipo.getNombre(),
            equipo.getDescripcion(),
            equipo.getActivo(),
            equipo.getFechaCreacion(),
            equipo.getJefe() != null ? equipo.getJefe().getDni() : null
        );
    }

    // toEntity: DTO → Entidad (para POST)
    public Equipo toEntity(EquipoCreateUpdateDTO dto) {
        if (dto == null) return null;
        
        return Equipo.builder()
            .nombre(dto.nombre())
            .descripcion(dto.descripcion())
            .jefe(usuarioRepository.findById(dto.jefeDni()).orElse(null))
            .activo(dto.activo())
            .build();
    }

    // updateEntityFromDTO: Actualizar entidad existente
    public void updateEntityFromDTO(EquipoCreateUpdateDTO dto, Equipo equipo) {
        if (dto == null || equipo == null) return;
        
        equipo.setNombre(dto.nombre());
        equipo.setDescripcion(dto.descripcion());
        equipo.setJefe(usuarioRepository.findById(dto.jefeDni()).orElse(null));
        equipo.setActivo(dto.activo());
    }
}
```

**Patrón de Mapper:**
```
3 métodos siempre:
├─ toResponseDTO(Entidad) → DTO
├─ toEntity(DTO) → Entidad (nueva)
└─ updateEntityFromDTO(DTO, Entidad) → void (actualizar existente)
```

---

#### **3. Crear Servicio CRUD**

**EquipoService.java** - Lógica de negocio:
```java
package com.logisteia.backend.services;

import com.logisteia.backend.dtos.EquipoResponseDTO;
import com.logisteia.backend.dtos.EquipoCreateUpdateDTO;
import com.logisteia.backend.entities.Equipo;
import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.mappers.EquipoMapper;
import com.logisteia.backend.repositories.EquipoRepository;
import lombok.RequiredArgsConstructor;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;

@Service
@RequiredArgsConstructor
@Transactional
public class EquipoService {

    private final EquipoRepository equipoRepository;
    private final EquipoMapper equipoMapper;

    // GET BY ID
    @Transactional(readOnly = true)
    public EquipoResponseDTO obtenerPorId(Integer id) {
        Equipo equipo = equipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Equipo", "ID", id.toString()));
        return equipoMapper.toResponseDTO(equipo);
    }

    // GET ALL (paginado)
    @Transactional(readOnly = true)
    public Page<EquipoResponseDTO> obtenerTodos(Pageable pageable) {
        return equipoRepository.findAll(pageable)
            .map(equipoMapper::toResponseDTO);
    }

    // GET BY JEFE
    @Transactional(readOnly = true)
    public List<EquipoResponseDTO> obtenerPorJefe(String jefeDni) {
        return equipoRepository.findByJefeDni(jefeDni)
            .stream()
            .map(equipoMapper::toResponseDTO)
            .toList();
    }

    // POST (CREATE)
    public EquipoResponseDTO crear(EquipoCreateUpdateDTO dto) {
        Equipo equipo = equipoMapper.toEntity(dto);
        Equipo guardado = equipoRepository.save(equipo);
        return equipoMapper.toResponseDTO(guardado);
    }

    // PUT (UPDATE)
    public EquipoResponseDTO actualizar(Integer id, EquipoCreateUpdateDTO dto) {
        Equipo equipo = equipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Equipo", "ID", id.toString()));
        
        equipoMapper.updateEntityFromDTO(dto, equipo);
        Equipo actualizado = equipoRepository.save(equipo);
        return equipoMapper.toResponseDTO(actualizado);
    }

    // DELETE
    public void eliminar(Integer id) {
        Equipo equipo = equipoRepository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound("Equipo", "ID", id.toString()));
        equipoRepository.delete(equipo);
    }
}
```

**Patrón de Servicio:**
```
Siempre incluir:
├─ @Service @RequiredArgsConstructor
├─ Inyectar Repository y Mapper
├─ @Transactional en nivel clase
├─ Métodos CRUD:
│  ├─ obtenerPorId() - readOnly
│  ├─ obtenerTodos(Pageable) - readOnly
│  ├─ obtenerPor[Campo]() - readOnly (búsquedas custom)
│  ├─ crear() - transactional
│  ├─ actualizar() - transactional
│  └─ eliminar() - transactional
└─ Usar mapper para conversión
```

---

#### **4. Crear Controller REST**

**EquipoController.java** - Endpoints HTTP:
```java
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
    public ResponseEntity<List<EquipoResponseDTO>> obtenerPorJefe(
            @PathVariable String jefeDni) {
        return ResponseEntity.ok(equipoService.obtenerPorJefe(jefeDni));
    }

    @PostMapping
    public ResponseEntity<EquipoResponseDTO> crear(@Valid @RequestBody EquipoCreateUpdateDTO dto) {
        return ResponseEntity.status(HttpStatus.CREATED)
            .body(equipoService.crear(dto));
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
```

**Patrón de Controller:**
```
Siempre incluir:
├─ @RestController @RequestMapping("/api/v1/{recurso}")
├─ @RequiredArgsConstructor
├─ Inyectar Service (NO Repository)
├─ Métodos HTTP:
│  ├─ GET /api/v1/{recurso}/{id}
│  ├─ GET /api/v1/{recurso}?page=0&size=20
│  ├─ GET /api/v1/{recurso}/{criterio}/{valor} (búsquedas)
│  ├─ POST /api/v1/{recurso} (@Valid @RequestBody DTO)
│  ├─ PUT /api/v1/{recurso}/{id}
│  └─ DELETE /api/v1/{recurso}/{id}
├─ Códigos HTTP correctos:
│  ├─ 200 OK para GET, PUT
│  ├─ 201 CREATED para POST
│  └─ 204 NO CONTENT para DELETE
└─ Documentación Javadoc de endpoints
```

---

## 🎯 Checklist para Generar Controllers

Para cada una de las 12 entidades, sigue este checklist:

```
Entidad: _______________

DTOs:
  [ ] ___ResponseDTO.java (Record)
  [ ] ___CreateUpdateDTO.java (Record)

Mapper:
  [ ] ___Mapper.java
  [ ] Implementar toResponseDTO()
  [ ] Implementar toEntity()
  [ ] Implementar updateEntityFromDTO()

Servicio:
  [ ] ___Service.java
  [ ] Métodos READ (findById, findAll, findBy...)
  [ ] Método CREATE
  [ ] Método UPDATE
  [ ] Método DELETE
  [ ] @Transactional correctamente aplicado

Controller:
  [ ] ___Controller.java
  [ ] GET /{id}
  [ ] GET (paginado)
  [ ] GET /criterio/{valor} (búsquedas)
  [ ] POST
  [ ] PUT
  [ ] DELETE
  [ ] Códigos HTTP correctos
  [ ] @Valid en @RequestBody
  [ ] Documentación Javadoc
```

---

## 📊 Lista de 12 Entidades a Generar

```
1. Usuario
   ├─ UsuarioResponseDTO ✓
   ├─ UsuarioCreateUpdateDTO ✓
   ├─ UsuarioMapper ✓
   ├─ UsuarioService ✓
   └─ UsuarioController ✓

2. Equipo
   ├─ EquipoResponseDTO
   ├─ EquipoCreateUpdateDTO
   ├─ EquipoMapper
   ├─ EquipoService
   └─ EquipoController

3. MiembroEquipo
   ├─ MiembroEquipoResponseDTO
   ├─ MiembroEquipoCreateUpdateDTO
   ├─ MiembroEquipoMapper
   ├─ MiembroEquipoService
   └─ MiembroEquipoController

4. Cliente
   └─ (5 archivos)

5. Proyecto
   └─ (5 archivos)

6. Tarea
   └─ (5 archivos)

7. Presupuesto
   ├─ PresupuestoResponseDTO ✓
   ├─ PresupuestoCreateUpdateDTO ✓
   ├─ PresupuestoMapper ✓
   ├─ PresupuestoService ✓
   └─ PresupuestoController ✓

8. DetallePresupuesto
   └─ (5 archivos)

9. Servicio (legacy)
   └─ (5 archivos)

10. ServicioInformatica
    └─ (5 archivos)

11. AccionAdministrativa
    └─ (5 archivos)

12. AsignacionProyecto
    └─ (5 archivos)

Total: 12 × 5 = 60 archivos nuevos
       (2 entidades ya hechas = 58 pendientes)
```

---

## 🚀 Orden Recomendado de Generación

Basado en dependencias (FK):

```
1. Usuario            (sin dependencias)
2. Equipo             (FK: jefe_dni → Usuario)
3. Cliente            (FK: jefe_dni → Usuario)
4. MiembroEquipo      (FK: equipo_id, trabajador_dni)
5. Proyecto           (FK: jefe_dni, cliente_id, equipo_id)
6. Tarea              (FK: proyecto_id, trabajador_dni)
7. Presupuesto        (FK: usuario_dni, proyecto_id, cliente_id)
8. DetallePresupuesto (FK: presupuesto_id)
9. Servicio           (sin FK)
10. ServicioInformatica (sin FK)
11. AccionAdministrativa (FK: admin_dni, usuario_dni, proyecto_id, equipo_id)
12. AsignacionProyecto (FK: proyecto_id, trabajador_dni)
```

---

## 💡 Tips de Generación Rápida

### Con IDE (IntelliJ/VS Code):
1. Usa templates/Live Templates para generar boilerplate
2. Genera constructores, getters, setters automáticamente
3. Usa Refactor → Extract Method para reutilizar código

### Script de Generación:
```bash
#!/bin/bash
# Generar DTOs para Equipo
cat > EquipoResponseDTO.java << 'EOF'
package com.logisteia.backend.dtos;
...
EOF

cat > EquipoCreateUpdateDTO.java << 'EOF'
...
EOF
```

### Copiar y Pegar + Find-Replace:
1. Copia UsuarioController → EquipoController
2. Reemplaza:
   - `Usuario` → `Equipo`
   - `UsuarioService` → `EquipoService`
   - `/usuarios` → `/equipos`
   - etc.

---

## ✅ Validación Post-Generación

Para cada Controller, verifica:

- [ ] **Rutas** - Todas las URLs siguen `/api/v1/{recurso}`
- [ ] **Validación** - @Valid @RequestBody está en POST/PUT
- [ ] **Códigos HTTP** - 201 para CREATE, 204 para DELETE
- [ ] **DTOs** - No incluyen entidades, solo IDs
- [ ] **Servicios** - Usan Mappers correctamente
- [ ] **Excepciones** - Lanzan ResourceNotFoundException cuando falta recurso
- [ ] **Documentación** - Cada método tiene Javadoc

---

## 🎓 Ejemplo Completo (simplificado)

**Estructura para Cliente:**

```
DTO:
public record ClienteResponseDTO(
    Integer id,
    String nombre,
    String empresa,
    String email,
    String jefeDni
) {}

Mapper:
public EquipoResponseDTO toResponseDTO(Cliente cliente) {
    return new ClienteResponseDTO(
        cliente.getId(),
        cliente.getNombre(),
        cliente.getEmpresa(),
        cliente.getEmail(),
        cliente.getJefe().getDni()
    );
}

Service:
public class ClienteService {
    public ClienteResponseDTO obtenerPorId(Integer id) { ... }
    public List<ClienteResponseDTO> obtenerPorJefe(String jefeDni) { ... }
    public ClienteResponseDTO crear(ClienteCreateUpdateDTO dto) { ... }
}

Controller:
@RestController
@RequestMapping("/api/v1/clientes")
public class ClienteController {
    @GetMapping("/{id}")
    public ResponseEntity<ClienteResponseDTO> obtenerPorId(...) { ... }
    
    @GetMapping("/jefe/{jefeDni}")
    public ResponseEntity<List<ClienteResponseDTO>> obtenerPorJefe(...) { ... }
}
```

---

## 🔗 Referencias

- Fase 1: Entidades JPA (COMPLETADA)
- Fase 2: Controllers REST (COMPLETADA para Usuario + Presupuesto)
- Próximo: Generar 10 Controllers más (o 12 si deseas hacerlo todo)
- Fase 3: Seguridad

---

**Este patrón es **100% replicable** para todas las entidades.
Una vez apruebes el código de Usuario y Presupuesto, podemos automatizar la generación de los 10 controllers restantes.**
