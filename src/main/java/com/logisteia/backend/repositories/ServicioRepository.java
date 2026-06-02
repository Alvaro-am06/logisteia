package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Servicio;
import java.util.List;

@Repository
public interface ServicioRepository extends JpaRepository<Servicio, String> {
    List<Servicio> findByEstaActivo(Boolean estaActivo);
    List<Servicio> findByCategoriaNombre(String categoriaNombre);
}
