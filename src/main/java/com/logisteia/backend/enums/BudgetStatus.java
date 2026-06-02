package com.logisteia.backend.enums;

public enum BudgetStatus {
    BORRADOR("borrador"),
    ENVIADO("enviado"),
    APROBADO("aprobado"),
    RECHAZADO("rechazado");

    private final String value;

    BudgetStatus(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
