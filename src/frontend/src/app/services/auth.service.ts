import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable, tap, catchError } from 'rxjs';
import { throwError } from 'rxjs';
import { environment } from '../../environments/environment';

/**
 * Solicitud de login - debe coincidir con LoginRequestDTO del backend
 */
export interface LoginRequest {
  email: string;
  senha: string;  // Contraseña en portugués, como espera el backend
}

/**
 * Respuesta de login del backend - LoginResponseDTO
 */
export interface LoginResponse {
  success: boolean;
  message: string;
  token: string;  // JWT token
  usuario: {
    id: string;
    nome: string;
    email: string;
    rol: string;
  };
}

/**
 * Servicio de autenticación con JWT.
 * Maneja login, logout y almacenamiento del token.
 */
@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private http = inject(HttpClient);
  private platformId = inject(PLATFORM_ID);

  private apiUrl = `${environment.apiUrl}/api/v1/auth`;

  /**
   * Login con email y contraseña
   */
  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/login`, credentials)
      .pipe(
        tap(response => {
          if (response.success && response.token) {
            // Guardar token y datos del usuario
            this.setSession(response.token, response.usuario);
          }
        }),
        catchError(error => {
          console.error('Error en login:', error);
          return throwError(() => error);
        })
      );
  }

  /**
   * Logout - limpiar sesión local
   */
  logout(): void {
    this.clearSession();
  }

  /**
   * Verificar si hay sesión activa (compatible con SSR)
   */
  isLoggedIn(): boolean {
    if (!isPlatformBrowser(this.platformId)) {
      return false;
    }
    return !!this.getToken();
  }

  /**
   * Obtener token JWT del almacenamiento
   */
  getToken(): string | null {
    if (!isPlatformBrowser(this.platformId)) {
      return null;
    }
    return localStorage.getItem('access_token');
  }

  /**
   * Obtener datos del usuario almacenados
   */
  getCurrentUser() {
    if (!isPlatformBrowser(this.platformId)) {
      return null;
    }
    const userJson = localStorage.getItem('usuario');
    return userJson ? JSON.parse(userJson) : null;
  }

  /**
   * Guardar token de sesión y datos del usuario (compatible con SSR)
   */
  private setSession(token: string, usuario: any): void {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.setItem('access_token', token);
      localStorage.setItem('usuario', JSON.stringify(usuario));
    }
  }

  /**
   * Limpiar sesión (compatible con SSR)
   */
  private clearSession(): void {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('access_token');
      localStorage.removeItem('usuario');
    }
  }
}