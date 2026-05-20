package com.logisteia.backend.exceptions;

/**
 * Excepción lanzada cuando hay un error en la lógica de negocio.
 */
public class BusinessLogicException extends RuntimeException {
    
    public BusinessLogicException(String message) {
        super(message);
    }

    public BusinessLogicException(String message, Throwable cause) {
        super(message, cause);
    }
}
