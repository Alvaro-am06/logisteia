import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Usuario {
  dni: string;
  nombre: string;
  email: string;
  telefono: string;
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

  private apiUrl = '/api/usuarios';

  // Obtener todos los usuarios
  getUsuarios(): Observable<ApiResponse<Usuario[]>> {
    return this.http.get<ApiResponse<Usuario[]>>(this.apiUrl);
  }

  // Obtener usuario específico por DNI
  getUsuario(dni: string): Observable<ApiResponse<UsuarioDetalle>> {
    return this.http.get<ApiResponse<UsuarioDetalle>>(`${this.apiUrl}/${dni}`);
  }

  // Cambiar rol de usuario
  cambiarRol(dni: string, operacion: string, motivo?: string): Observable<ApiResponse<{message: string}>> {
    return this.http.post<ApiResponse<{message: string}>>(`${this.apiUrl}/${dni}`, {
      operacion,
      motivo
    });
  }
}