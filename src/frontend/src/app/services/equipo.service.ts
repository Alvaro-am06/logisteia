import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

export interface Equipo {
  id: string;
  nome: string;
  descricao?: string;
  criadoEm?: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class EquipoService {
  private http = inject(HttpClient);
  private apiUrl = `${environment.apiUrl}/api/v1/equipos`;

  /**
   * Obtener todos los equipos
   */
  getEquipos(): Observable<ApiResponse<Equipo[]>> {
    return this.http.get<ApiResponse<Equipo[]>>(this.apiUrl);
  }

  /**
   * Obtener equipo específico por ID
   */
  getEquipo(id: string): Observable<ApiResponse<Equipo>> {
    return this.http.get<ApiResponse<Equipo>>(`${this.apiUrl}/${id}`);
  }

  /**
   * Criar nuevo equipo
   */
  criarEquipo(equipo: Partial<Equipo>): Observable<ApiResponse<Equipo>> {
    return this.http.post<ApiResponse<Equipo>>(this.apiUrl, equipo);
  }

  /**
   * Actualizar equipo
   */
  atualizarEquipo(id: string, equipo: Partial<Equipo>): Observable<ApiResponse<Equipo>> {
    return this.http.put<ApiResponse<Equipo>>(`${this.apiUrl}/${id}`, equipo);
  }

  /**
   * Eliminar equipo
   */
  eliminarEquipo(id: string): Observable<ApiResponse<{message: string}>> {
    return this.http.delete<ApiResponse<{message: string}>>(`${this.apiUrl}/${id}`);
  }
}