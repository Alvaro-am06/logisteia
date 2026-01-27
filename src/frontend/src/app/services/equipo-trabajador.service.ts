import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface EquipoTrabajador {
  id: number;
  equipo_nombre: string;
  jefe_nombre: string;
  jefe_email: string;
  rol_proyecto: string;
  fecha_ingreso: string;
  estado_invitacion: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
}

@Injectable({
  providedIn: 'root'
})
export class EquipoTrabajadorService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);
  private apiUrl = `${environment.apiUrl}/api/mi-equipo-trabajador.php`;

  getMiEquipo(): Observable<ApiResponse<EquipoTrabajador>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<EquipoTrabajador>>(this.apiUrl, { headers });
  }

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
