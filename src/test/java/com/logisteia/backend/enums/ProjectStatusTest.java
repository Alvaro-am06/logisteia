package com.logisteia.backend.enums;

import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

import static org.junit.jupiter.api.Assertions.*;

@DisplayName("ProjectStatus Enum Tests")
class ProjectStatusTest {

    @Test
    @DisplayName("ProjectStatus.CREADO existe")
    void testCreadoStatusExists() {
        assertNotNull(ProjectStatus.CREADO);
        assertEquals("CREADO", ProjectStatus.CREADO.name());
    }

    @Test
    @DisplayName("ProjectStatus.EN_PROCESO existe")
    void testEnProcesoStatusExists() {
        assertNotNull(ProjectStatus.EN_PROCESO);
        assertEquals("EN_PROCESO", ProjectStatus.EN_PROCESO.name());
    }

    @Test
    @DisplayName("ProjectStatus.FINALIZADO existe")
    void testFinalizadoStatusExists() {
        assertNotNull(ProjectStatus.FINALIZADO);
        assertEquals("FINALIZADO", ProjectStatus.FINALIZADO.name());
    }

    @Test
    @DisplayName("ProjectStatus.PAUSADO existe")
    void testPausadoStatusExists() {
        assertNotNull(ProjectStatus.PAUSADO);
        assertEquals("PAUSADO", ProjectStatus.PAUSADO.name());
    }

    @Test
    @DisplayName("ProjectStatus.CANCELADO existe")
    void testCanceladoStatusExists() {
        assertNotNull(ProjectStatus.CANCELADO);
        assertEquals("CANCELADO", ProjectStatus.CANCELADO.name());
    }

    @Test
    @DisplayName("Obtener ProjectStatus por nombre")
    void testValueOf() {
        ProjectStatus status = ProjectStatus.valueOf("EN_PROCESO");
        assertEquals(ProjectStatus.EN_PROCESO, status);
    }

    @Test
    @DisplayName("ProjectStatus.values() devuelve todos los estados")
    void testAllStatusesAvailable() {
        ProjectStatus[] statuses = ProjectStatus.values();
        assertEquals(5, statuses.length, "Debe haber 5 estados de proyecto");
    }

    @Test
    @DisplayName("Estados de proyecto no son iguales entre sí")
    void testStatusesDiferentes() {
        assertNotEquals(ProjectStatus.CREADO, ProjectStatus.EN_PROCESO);
        assertNotEquals(ProjectStatus.FINALIZADO, ProjectStatus.CANCELADO);
    }

    @Test
    @DisplayName("ProjectStatus.valueOf con estado inválido lanza excepción")
    void testValueOfInvalido() {
        assertThrows(IllegalArgumentException.class, () -> ProjectStatus.valueOf("SUSPENDIDO"));
    }

    @Test
    @DisplayName("getValue devuelve el valor correcto")
    void testGetValue() {
        assertEquals("creado", ProjectStatus.CREADO.getValue());
        assertEquals("en_proceso", ProjectStatus.EN_PROCESO.getValue());
        assertEquals("finalizado", ProjectStatus.FINALIZADO.getValue());
        assertEquals("pausado", ProjectStatus.PAUSADO.getValue());
        assertEquals("cancelado", ProjectStatus.CANCELADO.getValue());
    }
}
