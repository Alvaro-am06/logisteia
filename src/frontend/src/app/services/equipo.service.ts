import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface MiembroEquipo {
  id: number;
  dni: string;
  nombre: string;
  email: string;
  telefono: string | null;
  rol_proyecto: string;
  fecha_ingreso: string;
  estado_invitacion: 'pendiente' | 'aceptada' | 'rechazada';
  activo: boolean;
  estado_usuario: string;
}

export interface Equipo {
  id: number;
  nombre: string;
}

export interface MiembrosEquipoResponse {
  equipo: Equipo;
  miembros: MiembroEquipo[];
}

export interface AgregarMiembroRequest {
  email_trabajador: string;
}

export interface AgregarMiembroResponse {
  message: string;
  miembro: {
    email: string;
    nombre: string;
    rol_proyecto: string;
    estado_invitacion: string;
  };
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
}

@Injectable({
  providedIn: 'root'
})
export class EquipoService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);
  private apiUrl = `${environment.apiUrl}/api/equipo.php`;

  /**
   * Obtener los miembros del equipo del jefe de equipo autenticado
   */
  getMiembrosEquipo(): Observable<ApiResponse<MiembrosEquipoResponse>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<MiembrosEquipoResponse>>(this.apiUrl, { headers });
  }

  /**
   * Obtener equipos del jefe de equipo autenticado
   */
  getEquiposJefe(dniJefe: string): Observable<{success: boolean, equipos: Equipo[]}> {
    const headers = this.getAuthHeaders();
    return this.http.get<{success: boolean, equipos: Equipo[]}>(`${environment.apiUrl}/api/equipo.php?jefe=${dniJefe}`, { headers });
  }

  /**
   * Agregar un nuevo miembro al equipo
   */
  agregarMiembroEquipo(request: AgregarMiembroRequest): Observable<ApiResponse<AgregarMiembroResponse>> {
    const headers = this.getAuthHeaders();
    return this.http.post<ApiResponse<AgregarMiembroResponse>>(this.apiUrl, request, { headers });
  }

  /**
   * Obtener headers de autenticación desde localStorage
   */
  private getAuthHeaders(): { [key: string]: string } {
    if (!isPlatformBrowser(this.platformId)) {
      return {};
    }
    
    const usuario = localStorage.getItem('usuario');
    if (usuario) {
      const userData = JSON.parse(usuario);
      const headers = {
        'X-User-DNI': userData.dni || '',
        'X-User-Rol': userData.rol || '',
        'X-User-Nombre': userData.nombre || '',
        'X-User-Email': userData.email || ''
      };
      return headers;
    }
    return {};
  }
}