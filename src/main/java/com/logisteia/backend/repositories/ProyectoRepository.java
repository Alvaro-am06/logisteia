package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Proyecto;
import com.logisteia.backend.enums.ProjectStatus;
import java.util.List;
import java.util.Optional;

@Repository
public interface ProyectoRepository extends JpaRepository<Proyecto, Integer> {
    Optional<Proyecto> findByCodigo(String codigo);
    List<Proyecto> findByJefeDni(String jefeDni);
    List<Proyecto> findByClienteId(Integer clienteId);
    List<Proyecto> findByEquipoId(Integer equipoId);
    List<Proyecto> findByEstado(ProjectStatus estado);
    List<Proyecto> findByJefeDniAndEstado(String jefeDni, ProjectStatus estado);
    List<Proyecto> findByClienteIdAndEstado(Integer clienteId, ProjectStatus estado);
}
