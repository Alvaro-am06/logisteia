package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.AsignacionProyecto;
import java.util.List;
import java.util.Optional;

@Repository
public interface AsignacionProyectoRepository extends JpaRepository<AsignacionProyecto, Integer> {
    List<AsignacionProyecto> findByProyectoId(Integer proyectoId);
    List<AsignacionProyecto> findByTrabajadorDni(String trabajadorDni);
    Optional<AsignacionProyecto> findByProyectoIdAndTrabajadorDni(Integer proyectoId, String trabajadorDni);
}
