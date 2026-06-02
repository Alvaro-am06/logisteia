package com.logisteia.backend.services.base;

import com.logisteia.backend.exceptions.ResourceNotFoundException;
import com.logisteia.backend.exceptions.DataIntegrityException;
import org.springframework.data.domain.Page;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;
import java.util.Optional;
import java.util.function.Function;

/**
 * Clase base genérica para servicios CRUD.
 * Elimina redundancia al centralizar operaciones comunes.
 * 
 * @param <E> Tipo de entidad
 * @param <D> Tipo de DTO de respuesta
 * @param <C> Tipo de DTO de creación/actualización
 * @param <ID> Tipo de ID
 */
public abstract class GenericCrudService<E, D, C, ID> {

    protected final JpaRepository<E, ID> repository;
    protected final Function<E, D> toDtoMapper;
    protected final String entityName;

    protected GenericCrudService(JpaRepository<E, ID> repository, 
                                Function<E, D> toDtoMapper, 
                                String entityName) {
        this.repository = repository;
        this.toDtoMapper = toDtoMapper;
        this.entityName = entityName;
    }

    /**
     * Encuentra una entidad por ID o lanza excepción
     */
    protected E findByIdOrThrow(ID id) {
        return repository.findById(id)
            .orElseThrow(() -> ResourceNotFoundException.entityNotFound(entityName, "ID", id.toString()));
    }

    /**
     * Obtiene todas las entidades paginadas
     */
    public Page<D> findAll(Pageable pageable) {
        return repository.findAll(pageable).map(toDtoMapper);
    }

    /**
     * Mapea una entidad a DTO
     */
    public D toDto(E entity) {
        return entity != null ? toDtoMapper.apply(entity) : null;
    }

    /**
     * Valida que no exista un registro con el mismo valor (para campos únicos)
     */
    protected void validateUniqueField(String fieldName, String value, 
                                       Function<String, Optional<E>> finder) {
        if (finder.apply(value).isPresent()) {
            throw DataIntegrityException.duplicateEntry(fieldName, value);
        }
    }

    /**
     * Valida que no exista un registro diferente al actual con el mismo valor
     */
    protected void validateUniqueFieldForUpdate(String fieldName, String currentValue, 
                                               String newValue,
                                               Function<String, Optional<E>> finder) {
        if (!currentValue.equals(newValue) && finder.apply(newValue).isPresent()) {
            throw DataIntegrityException.duplicateEntry(fieldName, newValue);
        }
    }

    /**
     * Guarda una entidad y retorna su DTO
     */
    protected D saveAndMapToDto(E entity) {
        E saved = repository.save(entity);
        return toDtoMapper.apply(saved);
    }

    /**
     * Elimina una entidad por ID
     */
    public void deleteById(ID id) {
        E entity = findByIdOrThrow(id);
        repository.delete(entity);
    }
}
