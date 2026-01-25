import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Cliente {
  id?: number;
  dni: string;
  nombre: string;
  email: string;
  telefono?: string;
  empresa?: string;
  direccion?: string;
  cif_nif?: string;
  notas?: string;
  fecha_registro?: string;
  jefe_dni?: string;
  activo?: number;
}

export interface ClienteRegistro extends Cliente {
  password: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  clientes?: T;
  cliente?: any;
  error?: string;
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class ClienteService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);

  private apiUrl = `${environment.apiUrl}/api/clientes.php`;

  // Obtener todos los clientes
  getClientes(): Observable<ApiResponse<Cliente[]>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<Cliente[]>>(this.apiUrl, { headers });
  }

  // Obtener cliente específico por DNI
  getCliente(dni: string): Observable<ApiResponse<Cliente>> {
    const headers = this.getAuthHeaders();
    return this.http.get<ApiResponse<Cliente>>(`${this.apiUrl}?dni=${dni}`, { headers });
  }

  // Crear nuevo cliente
  crearCliente(cliente: ClienteRegistro): Observable<ApiResponse<Cliente>> {
    const headers = this.getAuthHeaders();
    return this.http.post<ApiResponse<Cliente>>(this.apiUrl, cliente, { headers });
  }

  // Actualizar cliente existente
  actualizarCliente(dni: string, cliente: Partial<ClienteRegistro>): Observable<ApiResponse<Cliente>> {
    const headers = this.getAuthHeaders();
    return this.http.put<ApiResponse<Cliente>>(this.apiUrl, { dni, ...cliente }, { headers });
  }

  // Eliminar cliente
  eliminarCliente(cif_nif: string): Observable<ApiResponse<any>> {
    const headers = this.getAuthHeaders();
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}?cif_nif=${cif_nif}`, { headers });
  }

  /**
   * Obtener headers de autenticación desde localStorage
   */
  private getAuthHeaders(): { [key: string]: string } {
    // Solo acceder a localStorage en el navegador
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