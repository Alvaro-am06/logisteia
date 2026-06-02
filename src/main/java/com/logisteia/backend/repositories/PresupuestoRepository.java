package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Presupuesto;
import com.logisteia.backend.enums.BudgetStatus;
import java.util.List;
import java.util.Optional;

@Repository
public interface PresupuestoRepository extends JpaRepository<Presupuesto, Integer> {
    Optional<Presupuesto> findByNumeroPresupuesto(String numeroPresupuesto);
    List<Presupuesto> findByUsuarioDni(String usuarioDni);
    List<Presupuesto> findByProyectoId(Integer proyectoId);
    List<Presupuesto> findByClienteId(Integer clienteId);
    List<Presupuesto> findByEstado(BudgetStatus estado);
    List<Presupuesto> findByUsuarioDniAndEstado(String usuarioDni, BudgetStatus estado);
}
