import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

/**
 * DTOs del backend - ClienteResponseDTO
 */
export interface Cliente {
  id?: string;
  nome: string;
  email: string;
  telefone?: string;
  empresa?: string;
  endereco?: string;
  estado?: string;
  criadoEm?: string;
}

export interface ClienteRegistro extends Partial<Cliente> {
  senha: string;  // Contraseña en portugués
}

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
}

@Injectable({
  providedIn: 'root'
})
export class ClienteService {
  private http = inject(HttpClient);

  private apiUrl = `${environment.apiUrl}/api/v1/clientes`;

  /**
   * Obtener todos los clientes
   */
  getClientes(): Observable<ApiResponse<Cliente[]>> {
    return this.http.get<ApiResponse<Cliente[]>>(this.apiUrl);
  }

  /**
   * Obtener cliente específico por ID
   */
  getCliente(id: string): Observable<ApiResponse<Cliente>> {
    return this.http.get<ApiResponse<Cliente>>(`${this.apiUrl}/${id}`);
  }

  /**
   * Crear nuevo cliente
   */
  criarCliente(cliente: ClienteRegistro): Observable<ApiResponse<Cliente>> {
    return this.http.post<ApiResponse<Cliente>>(this.apiUrl, cliente);
  }

  /**
   * Actualizar cliente existente
   */
  atualizarCliente(id: string, cliente: Partial<ClienteRegistro>): Observable<ApiResponse<Cliente>> {
    return this.http.put<ApiResponse<Cliente>>(`${this.apiUrl}/${id}`, cliente);
  }

  /**
   * Eliminar cliente
   */
  eliminarCliente(id: string): Observable<ApiResponse<{message: string}>> {
    return this.http.delete<ApiResponse<{message: string}>>(`${this.apiUrl}/${id}`);
  }
}
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