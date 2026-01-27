import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Proyecto {
  id: number;
  codigo: string;
  nombre: string;
  descripcion?: string;
  jefe_dni: string;
  cliente_id?: number;
  equipo_id?: number;
  estado: string;
  fecha_inicio?: string;
  fecha_fin_estimada?: string;
  fecha_fin_real?: string;
  horas_estimadas?: number;
  horas_trabajadas?: number;
  precio_hora?: number;
  precio_total?: number;
  tecnologias?: string;
  repositorio_github?: string;
  notas?: string;
  fecha_creacion: string;
  fecha_actualizacion?: string;
  cliente_nombre?: string;
  equipo_nombre?: string;
  rol_asignado?: string;
  fecha_asignacion?: string;
}

export interface Trabajador {
  dni: string;
  nombre: string;
  email: string;
  rol: string;
  rol_asignado?: string;
  fecha_asignacion?: string;
}

export interface MiembroEquipo {
  dni: string;
  nombre: string;
  email: string;
  rol: string;
}

@Injectable({
  providedIn: 'root'
})
export class ProyectoService {

  constructor(private http: HttpClient) { }

  // Obtener proyectos del usuario actual
  getProyectos(): Observable<{success: boolean, proyectos: Proyecto[]}> {
    return this.http.get<{success: boolean, proyectos: Proyecto[]}>(`${environment.apiUrl}/api/proyectos.php`);
  }

  // Crear nuevo proyecto
  crearProyecto(proyecto: Partial<Proyecto> & {trabajadores?: Trabajador[]}): Observable<{success: boolean, proyecto_id?: number}> {
    return this.http.post<{success: boolean, proyecto_id?: number}>(`${environment.apiUrl}/api/proyectos.php`, proyecto);
  }

  // Obtener trabajadores asignados a un proyecto
  getTrabajadoresProyecto(proyectoId: number): Observable<{success: boolean, trabajadores: Trabajador[]}> {
    return this.http.get<{success: boolean, trabajadores: Trabajador[]}>(`${environment.apiUrl}/api/proyectos.php/${proyectoId}/trabajadores`);
  }

  // Asignar trabajadores a un proyecto
  asignarTrabajadores(proyectoId: number, trabajadores: Trabajador[]): Observable<{success: boolean, message: string}> {
    return this.http.post<{success: boolean, message: string}>(`${environment.apiUrl}/api/proyectos.php/${proyectoId}/trabajadores`, {trabajadores});
  }

  // Remover asignación de trabajador
  removerAsignacion(proyectoId: number, trabajadorDni: string): Observable<{success: boolean, message: string}> {
    return this.http.delete<{success: boolean, message: string}>(`${environment.apiUrl}/api/proyectos.php/${proyectoId}/trabajadores/${trabajadorDni}`);
  }

  // Obtener miembros disponibles de un equipo
  getMiembrosDisponibles(equipoId: number, proyectoId?: number): Observable<{success: boolean, miembros: MiembroEquipo[]}> {
    const params = proyectoId ? `?proyecto_id=${proyectoId}` : '';
    return this.http.get<{success: boolean, miembros: MiembroEquipo[]}>(`${environment.apiUrl}/api/proyectos.php/miembros-disponibles/${equipoId}${params}`);
  }

  // Eliminar proyecto
  eliminarProyecto(proyectoId: number): Observable<{success: boolean, error?: string}> {
    return this.http.request<{success: boolean, error?: string}>('DELETE', `${environment.apiUrl}/api/proyectos.php`, {
      body: { id: proyectoId }
    });
  }

  // Cambiar estado del proyecto
  cambiarEstadoProyecto(proyectoId: number, nuevoEstado: string): Observable<{success: boolean, error?: string}> {
    return this.http.request<{success: boolean, error?: string}>('PUT', `${environment.apiUrl}/api/proyectos.php`, {
      body: { id: proyectoId, estado: nuevoEstado }
    });
  }
}