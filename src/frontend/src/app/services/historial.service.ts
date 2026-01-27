import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

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

  private apiUrl = '/api/historial';

  // Obtener todo el historial administrativo
  getHistorial(): Observable<ApiResponse<HistorialItem[]>> {
    return this.http.get<ApiResponse<HistorialItem[]>>(this.apiUrl);
  }
}