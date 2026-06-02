package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Cliente;
import java.util.List;

@Repository
public interface ClienteRepository extends JpaRepository<Cliente, Integer> {
    List<Cliente> findByJefeDni(String jefeDni);
    List<Cliente> findByEmail(String email);
    List<Cliente> findByActivo(Boolean activo);
    List<Cliente> findByJefeDniAndActivo(String jefeDni, Boolean activo);
}
