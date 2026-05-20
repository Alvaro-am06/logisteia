package com.logisteia.backend.exceptions;

import java.time.LocalDateTime;

/**
 * Response estándar para errores API.
 */
public record ErrorResponse(
    int status,
    String message,
    String error,
    LocalDateTime timestamp,
    String path
) {
    public ErrorResponse(int status, String message, String error, String path) {
        this(status, message, error, LocalDateTime.now(), path);
    }
}
