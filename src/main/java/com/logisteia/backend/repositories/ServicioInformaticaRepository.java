package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.ServicioInformatica;
import com.logisteia.backend.enums.ServiceCategory;
import java.util.List;

@Repository
public interface ServicioInformaticaRepository extends JpaRepository<ServicioInformatica, Integer> {
    List<ServicioInformatica> findByCategoria(ServiceCategory categoria);
    List<ServicioInformatica> findByActivo(Boolean activo);
    List<ServicioInformatica> findByCategoriaAndActivo(ServiceCategory categoria, Boolean activo);
}
