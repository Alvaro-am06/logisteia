package com.logisteia.backend.enums;

public enum ServiceCategory {
    DESARROLLO_WEB("Desarrollo Web"),
    DESARROLLO_MOVIL("Desarrollo Móvil"),
    BASE_DE_DATOS("Base de Datos"),
    UI_UX_DESIGN("UI/UX Design"),
    TESTING("Testing"),
    DEVOPS("DevOps"),
    INFRAESTRUCTURA("Infraestructura"),
    CONSULTORIA("Consultoría"),
    MANTENIMIENTO("Mantenimiento"),
    OTROS("Otros");

    private final String value;

    ServiceCategory(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
