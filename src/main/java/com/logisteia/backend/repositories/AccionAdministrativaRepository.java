package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.AccionAdministrativa;
import java.util.List;

@Repository
public interface AccionAdministrativaRepository extends JpaRepository<AccionAdministrativa, Integer> {
    List<AccionAdministrativa> findByAdministradorDni(String administradorDni);
    List<AccionAdministrativa> findByUsuarioAfectadoDni(String usuarioDni);
    List<AccionAdministrativa> findByProyectoId(Integer proyectoId);
    List<AccionAdministrativa> findByEquipoId(Integer equipoId);
    List<AccionAdministrativa> findByAccion(String accion);
}
