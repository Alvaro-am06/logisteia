package com.logisteia.backend.exceptions;

/**
 * Excepción lanzada cuando una entidad no es encontrada en la BD.
 */
public class ResourceNotFoundException extends RuntimeException {
    
    public ResourceNotFoundException(String message) {
        super(message);
    }

    public ResourceNotFoundException(String message, Throwable cause) {
        super(message, cause);
    }

    public static ResourceNotFoundException entityNotFound(String entityName, String id) {
        return new ResourceNotFoundException(
            String.format("%s con ID '%s' no encontrado", entityName, id)
        );
    }

    public static ResourceNotFoundException entityNotFound(String entityName, String field, String value) {
        return new ResourceNotFoundException(
            String.format("%s con %s '%s' no encontrado", entityName, field, value)
        );
    }
}
