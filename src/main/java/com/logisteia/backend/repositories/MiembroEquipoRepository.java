package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.MiembroEquipo;
import com.logisteia.backend.enums.InvitationStatus;
import java.util.List;
import java.util.Optional;

@Repository
public interface MiembroEquipoRepository extends JpaRepository<MiembroEquipo, Integer> {
    List<MiembroEquipo> findByEquipoId(Integer equipoId);
    List<MiembroEquipo> findByTrabajadorDni(String trabajadorDni);
    List<MiembroEquipo> findByEquipoIdAndActivo(Integer equipoId, Boolean activo);
    List<MiembroEquipo> findByEstadoInvitacion(InvitationStatus estado);
    Optional<MiembroEquipo> findByTokenInvitacion(String token);
    Optional<MiembroEquipo> findByEquipoIdAndTrabajadorDni(Integer equipoId, String trabajadorDni);
}
