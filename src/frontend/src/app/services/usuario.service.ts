import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Usuario {
  dni: string;
  nombre: string;
  email: string;
  telefono: string;
  rol: string;
  estado: string;
  fecha_registro: string;
}

export interface UsuarioDetalle {
  usuario: Usuario;
  historial: HistorialAccion[];
}

export interface HistorialAccion {
  creado_en: string;
  administrador_dni: string;
  accion: string;
  usuario_dni: string;
  motivo: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
}

@Injectable({
  providedIn: 'root'
})
export class UsuarioService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);

  private apiUrl = `${environment.apiUrl}/api/usuarios.php`;
  private historialUrl = `${environment.apiUrl}/api/historial.php`;

  // Obtener todos los usuarios
  getUsuarios(): Observable<ApiResponse<Usuario[]>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<Usuario[]>>(this.apiUrl, { headers });
  }

  // Obtener usuario específico por DNI
  getUsuario(dni: string): Observable<ApiResponse<UsuarioDetalle>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<UsuarioDetalle>>(`${this.apiUrl}/${dni}`, { headers });
  }

  // Cambiar rol de usuario (activar/suspender/eliminar)
  cambiarRol(dni: string, operacion: string, motivo?: string): Observable<ApiResponse<{message: string}>> {
    const headers = this.getAuthHeaders();
    return this.http.post<ApiResponse<{message: string}>>(`${this.apiUrl}/${dni}`, {
      operacion,
      motivo
    }, { headers });
  }

  // Obtener historial completo de acciones administrativas
  getHistorial(): Observable<ApiResponse<HistorialAccion[]>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<HistorialAccion[]>>(this.historialUrl, { headers });
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
      return {
        'X-User-DNI': userData.dni || '',
        'X-User-Rol': userData.rol || '',
        'X-User-Nombre': userData.nombre || '',
        'X-User-Email': userData.email || ''
      };
    }
    return {};
  }
}
