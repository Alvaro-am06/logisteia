package com.logisteia.backend.enums;

public enum TaskPriority {
    BAJA("baja"),
    MEDIA("media"),
    ALTA("alta"),
    CRITICA("critica");

    private final String value;

    TaskPriority(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
