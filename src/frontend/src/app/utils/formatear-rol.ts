/**
 * Formatea el rol del usuario para mostrarlo correctamente en la interfaz
 * @param rol - El rol del usuario (jefe_equipo, trabajador, moderador, admin)
 * @returns El rol formateado (Jefe de Equipo, Trabajador, Moderador, Administrador)
 */
export function formatearRol(rol: string): string {
  const roles: { [key: string]: string } = {
    'jefe_equipo': 'Jefe de Equipo',
    'trabajador': 'Trabajador',
    'moderador': 'Moderador',
    'admin': 'Administrador'
  };

  return roles[rol] || rol;
}
