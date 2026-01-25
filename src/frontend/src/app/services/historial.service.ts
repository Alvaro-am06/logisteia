import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface HistorialItem {
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
export class HistorialService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);

  private apiUrl = `${environment.apiUrl}/api/historial.php`;

  // Obtener todo el historial administrativo
  getHistorial(): Observable<ApiResponse<HistorialItem[]>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<HistorialItem[]>>(this.apiUrl, { headers });
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