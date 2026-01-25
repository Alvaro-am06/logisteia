import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  success: boolean;
  data?: {
    id: string;
    nombre: string;
    email: string;
  };
  error?: string;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);

  private apiUrl = '/api/auth';

  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/login`, credentials);
  }

  logout(): Observable<{success: boolean}> {
    return this.http.post<{success: boolean}>(`${this.apiUrl}/logout`, {});
  }

  // Método para verificar si hay sesión activa (puedes implementar lógica adicional)
  isLoggedIn(): boolean {
    return !!localStorage.getItem('admin_token');
  }

  // Guardar token de sesión
  setSession(token: string): void {
    localStorage.setItem('admin_token', token);
  }

  // Limpiar sesión
  clearSession(): void {
    localStorage.removeItem('admin_token');
  }
}