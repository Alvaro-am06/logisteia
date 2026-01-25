/**
 * Utilidades para manejar estados de presupuestos
 */

/**
 * Obtiene las clases CSS para el badge de estado
 */
export function getEstadoClass(estado: string): string {
  const clases: { [key: string]: string } = {
    'borrador': 'bg-gray-100 text-gray-700',
    'enviado': 'bg-blue-100 text-blue-700',
    'aprobado': 'bg-green-100 text-green-700',
    'rechazado': 'bg-red-100 text-red-700',
    'eliminado': 'bg-gray-300 text-gray-600',
    'finalizado': 'bg-green-100 text-green-700',
    'en_proceso': 'bg-yellow-100 text-yellow-700',
    'planificacion': 'bg-purple-100 text-purple-700',
    'cancelado': 'bg-red-100 text-red-700'
  };

  return clases[estado] || 'bg-gray-100 text-gray-700';
}

/**
 * Obtiene el texto formateado para mostrar el estado
 */
export function getEstadoTexto(estado: string): string {
  const textos: { [key: string]: string } = {
    'borrador': 'Borrador',
    'enviado': 'Enviado',
    'aprobado': 'Aprobado',
    'rechazado': 'Rechazado',
    'eliminado': 'Eliminado',
    'finalizado': 'Finalizado',
    'en_proceso': 'En Proceso',
    'planificacion': 'Planificación',
    'cancelado': 'Cancelado'
  };

  return textos[estado] || estado;
}
