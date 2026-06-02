package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Equipo;
import java.util.List;

@Repository
public interface EquipoRepository extends JpaRepository<Equipo, Integer> {
    List<Equipo> findByJefeDni(String jefeDni);
    List<Equipo> findByActivo(Boolean activo);
    List<Equipo> findByJefeDniAndActivo(String jefeDni, Boolean activo);
}
