package com.logisteia.backend.enums;

public enum TaskStatus {
    PENDIENTE("pendiente"),
    EN_PROGRESO("en_progreso"),
    COMPLETADA("completada"),
    BLOQUEADA("bloqueada");

    private final String value;

    TaskStatus(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
