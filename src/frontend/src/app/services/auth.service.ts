import { Injectable, inject, PLATFORM_ID } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { isPlatformBrowser } from '@angular/common';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';

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
  private platformId = inject(PLATFORM_ID);

  private apiUrl = `${environment.apiUrl}/api`;

  login(credentials: LoginRequest): Observable<LoginResponse> {
    return this.http.post<LoginResponse>(`${this.apiUrl}/login.php`, credentials);
  }

  logout(): Observable<{success: boolean}> {
    // Logout es solo local, no hay endpoint de logout en el backend
    this.clearSession();
    return new Observable(observer => {
      observer.next({ success: true });
      observer.complete();
    });
  }

  // Método para verificar si hay sesión activa (compatible con SSR)
  isLoggedIn(): boolean {
    if (!isPlatformBrowser(this.platformId)) {
      return false;
    }
    return !!localStorage.getItem('admin_token') || !!localStorage.getItem('usuario');
  }

  // Guardar token de sesión (compatible con SSR)
  setSession(token: string): void {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.setItem('admin_token', token);
    }
  }

  // Limpiar sesión (compatible con SSR)
  clearSession(): void {
    if (isPlatformBrowser(this.platformId)) {
      localStorage.removeItem('admin_token');
      localStorage.removeItem('usuario');
    }
  }
}