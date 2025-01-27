import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Cliente {
  dni: string;
  nombre: string;
  email: string;
  telefono: string;
  fecha_registro: string;
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: string;
}

@Injectable({
  providedIn: 'root'
})
export class ClienteService {
  private http = inject(HttpClient);

  private apiUrl = '/api/clientes';

  // Obtener todos los clientes
  getClientes(): Observable<ApiResponse<Cliente[]>> {
    return this.http.get<ApiResponse<Cliente[]>>(this.apiUrl);
  }

  // Obtener cliente específico por DNI
  getCliente(dni: string): Observable<ApiResponse<Cliente>> {
    return this.http.get<ApiResponse<Cliente>>(`${this.apiUrl}/${dni}`);
  }
}