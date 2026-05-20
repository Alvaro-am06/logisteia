package com.logisteia.backend.repositories;

import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.stereotype.Repository;
import com.logisteia.backend.entities.Usuario;
import com.logisteia.backend.enums.UserRole;
import com.logisteia.backend.enums.UserStatus;
import java.util.List;
import java.util.Optional;

@Repository
public interface UsuarioRepository extends JpaRepository<Usuario, String> {
    Optional<Usuario> findByEmail(String email);
    List<Usuario> findByRol(UserRole rol);
    List<Usuario> findByEstado(UserStatus estado);
    List<Usuario> findByRolAndEstado(UserRole rol, UserStatus estado);
}
