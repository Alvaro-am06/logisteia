package com.logisteia.backend.enums;

public enum Unit {
    HORA("hora"),
    PROYECTO("proyecto"),
    MES("mes"),
    OTRO("otro");

    private final String value;

    Unit(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
