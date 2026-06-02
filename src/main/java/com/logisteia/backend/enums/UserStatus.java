package com.logisteia.backend.enums;

public enum UserStatus {
    ACTIVO("activo"),
    SUSPENDIDO("suspendido"),
    ELIMINADO("eliminado");

    private final String value;

    UserStatus(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
