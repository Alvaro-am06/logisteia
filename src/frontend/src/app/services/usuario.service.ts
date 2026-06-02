import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

/**
 * DTOs del backend - deben coincidir exactamente
 */
export interface Usuario {
  dni: string;
  nome: string;
  email: string;
  rol: string;
  estado: string;
  criadoEm: string;
}

export interface UsuarioDetalle {
  usuario: Usuario;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class UsuarioService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);

  private apiUrl = `${environment.apiUrl}/api/v1/usuarios`;

  /**
   * Obtener todos los usuarios
   */
  getUsuarios(): Observable<ApiResponse<Usuario[]>> {
    return this.http.get<ApiResponse<Usuario[]>>(this.apiUrl);
  }

  /**
   * Obtener usuario específico por DNI
   */
  getUsuario(dni: string): Observable<ApiResponse<UsuarioDetalle>> {
    return this.http.get<ApiResponse<UsuarioDetalle>>(`${this.apiUrl}/${dni}`);
  }

  /**
   * Crear nuevo usuario
   */
  criarUsuario(usuario: any): Observable<ApiResponse<Usuario>> {
    return this.http.post<ApiResponse<Usuario>>(this.apiUrl, usuario);
  }

  /**
   * Actualizar usuario
   */
  atualizarUsuario(dni: string, usuario: any): Observable<ApiResponse<Usuario>> {
    return this.http.put<ApiResponse<Usuario>>(`${this.apiUrl}/${dni}`, usuario);
  }

  /**
   * Eliminar usuario
   */
  eliminarUsuario(dni: string): Observable<ApiResponse<{message: string}>> {
    return this.http.delete<ApiResponse<{message: string}>>(`${this.apiUrl}/${dni}`);
  }
}
