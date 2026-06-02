package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Tarea;
import com.logisteia.backend.enums.TaskStatus;
import java.util.List;

@Repository
public interface TareaRepository extends JpaRepository<Tarea, Integer> {
    List<Tarea> findByProyectoId(Integer proyectoId);
    List<Tarea> findByTrabajadorDni(String trabajadorDni);
    List<Tarea> findByEstado(TaskStatus estado);
    List<Tarea> findByProyectoIdAndEstado(Integer proyectoId, TaskStatus estado);
    List<Tarea> findByTrabajadorDniAndEstado(String trabajadorDni, TaskStatus estado);
}
