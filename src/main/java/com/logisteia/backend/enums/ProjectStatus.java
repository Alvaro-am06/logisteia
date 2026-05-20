package com.logisteia.backend.enums;

public enum ProjectStatus {
    CREADO("creado"),
    EN_PROCESO("en_proceso"),
    FINALIZADO("finalizado"),
    PAUSADO("pausado"),
    CANCELADO("cancelado");

    private final String value;

    ProjectStatus(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
