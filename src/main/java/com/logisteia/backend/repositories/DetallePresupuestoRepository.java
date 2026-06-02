package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.DetallePresupuesto;
import java.util.List;

@Repository
public interface DetallePresupuestoRepository extends JpaRepository<DetallePresupuesto, Integer> {
    List<DetallePresupuesto> findByPresupuestoId(Integer presupuestoId);
    List<DetallePresupuesto> findByNumeroPresupuesto(String numeroPresupuesto);
}
