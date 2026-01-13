import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Cliente {
  dni: string;
  nombre: string;
  email: string;
  telefono: string;
  fecha_registro?: string;
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

  private apiUrl = 'http://localhost/logisteia/src/www/api/clientes.php';

  // Obtener todos los clientes
  getClientes(): Observable<ApiResponse<Cliente[]>> {
    return this.http.get<ApiResponse<Cliente[]>>(this.apiUrl);
  }

  // Obtener cliente específico por DNI
  getCliente(dni: string): Observable<ApiResponse<Cliente>> {
    return this.http.get<ApiResponse<Cliente>>(`${this.apiUrl}?dni=${dni}`);
  }

  // Crear nuevo cliente
  crearCliente(cliente: ClienteRegistro): Observable<ApiResponse<Cliente>> {
    return this.http.post<ApiResponse<Cliente>>(this.apiUrl, cliente);
  }

  // Actualizar cliente existente
  actualizarCliente(dni: string, cliente: Partial<ClienteRegistro>): Observable<ApiResponse<Cliente>> {
    return this.http.put<ApiResponse<Cliente>>(this.apiUrl, { dni, ...cliente });
  }

  // Eliminar cliente
  eliminarCliente(dni: string): Observable<ApiResponse<any>> {
    return this.http.request<ApiResponse<any>>('DELETE', this.apiUrl, {
      body: { dni }
    });
  }
}