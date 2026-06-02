package com.logisteia.backend.exceptions;

/**
 * Excepción lanzada cuando hay una violación de integridad de datos.
 */
public class DataIntegrityException extends RuntimeException {
    
    public DataIntegrityException(String message) {
        super(message);
    }

    public DataIntegrityException(String message, Throwable cause) {
        super(message, cause);
    }

    public static DataIntegrityException duplicateEntry(String field, String value) {
        return new DataIntegrityException(
            String.format("Ya existe un registro con %s '%s'", field, value)
        );
    }
}
