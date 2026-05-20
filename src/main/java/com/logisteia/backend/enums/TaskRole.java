package com.logisteia.backend.enums;

public enum TaskRole {
    FRONTEND_DEVELOPER("Frontend Developer"),
    BACKEND_DEVELOPER("Backend Developer"),
    DATABASE_ADMINISTRATOR("Database Administrator"),
    UI_UX_DESIGNER("UI/UX Designer"),
    QA_TESTER("QA Tester"),
    DEVOPS_ENGINEER("DevOps Engineer");

    private final String value;

    TaskRole(String value) {
        this.value = value;
    }

    public String getValue() {
        return value;
    }
}
