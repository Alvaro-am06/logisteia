package com.logisteia.backend.enums;

public enum UserRole {
    JEFE_EQUIPO("jefe_equipo"),
    TRABAJADOR("trabajador"),
    MODERADOR("moderador");

    private final String value;

    UserRole(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
