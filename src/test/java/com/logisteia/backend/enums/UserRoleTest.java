package com.logisteia.backend.enums;

import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.*;

@DisplayName("UserRole Enum Tests")
class UserRoleTest {

    @Test
    @DisplayName("UserRole.JEFE_EQUIPO existe")
    void testJefeEquipoRoleExists() {
        assertNotNull(UserRole.JEFE_EQUIPO);
        assertEquals("JEFE_EQUIPO", UserRole.JEFE_EQUIPO.name());
    }

    @Test
    @DisplayName("UserRole.TRABAJADOR existe")
    void testTrabajadorRoleExists() {
        assertNotNull(UserRole.TRABAJADOR);
        assertEquals("TRABAJADOR", UserRole.TRABAJADOR.name());
    }

    @Test
    @DisplayName("UserRole.MODERADOR existe")
    void testModeradorRoleExists() {
        assertNotNull(UserRole.MODERADOR);
        assertEquals("MODERADOR", UserRole.MODERADOR.name());
    }

    @Test
    @DisplayName("Obtener UserRole por nombre")
    void testValueOf() {
        UserRole role = UserRole.valueOf("JEFE_EQUIPO");
        assertEquals(UserRole.JEFE_EQUIPO, role);
    }

    @Test
    @DisplayName("UserRole.values() contiene todos los roles")
    void testAllRolesAvailable() {
        UserRole[] roles = UserRole.values();
        assertEquals(3, roles.length, "Debe haber 3 roles");
    }

    @Test
    @DisplayName("Comparar roles con ==")
    void testCompareRoles() {
        assertEquals(UserRole.JEFE_EQUIPO, UserRole.JEFE_EQUIPO);
        assertNotEquals(UserRole.JEFE_EQUIPO, UserRole.TRABAJADOR);
    }

    @Test
    @DisplayName("UserRole.valueOf con rol inválido lanza excepción")
    void testValueOfInvalido() {
        assertThrows(IllegalArgumentException.class, () -> UserRole.valueOf("ADMIN"));
    }

    @Test
    @DisplayName("Hashcode es consistente para mismo rol")
    void testHashCode() {
        UserRole role1 = UserRole.JEFE_EQUIPO;
        UserRole role2 = UserRole.JEFE_EQUIPO;
        assertEquals(role1.hashCode(), role2.hashCode());
    }

    @Test
    @DisplayName("getValue devuelve el valor correcto")
    void testGetValue() {
        assertEquals("jefe_equipo", UserRole.JEFE_EQUIPO.getValue());
        assertEquals("trabajador", UserRole.TRABAJADOR.getValue());
        assertEquals("moderador", UserRole.MODERADOR.getValue());
    }
}
