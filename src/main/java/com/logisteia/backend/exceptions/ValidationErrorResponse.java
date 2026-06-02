package com.logisteia.backend.exceptions;

import java.time.LocalDateTime;
import java.util.List;

/**
 * Response para errores de validación con detalles de campos.
 */
public record ValidationErrorResponse(
    int status,
    String message,
    String error,
    LocalDateTime timestamp,
    String path,
    List<FieldError> fieldErrors
) {
    public ValidationErrorResponse(int status, String message, String error, String path, List<FieldError> fieldErrors) {
        this(status, message, error, LocalDateTime.now(), path, fieldErrors);
    }

    public record FieldError(String field, String message) {}
}
