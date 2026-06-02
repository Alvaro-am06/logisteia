import { Injectable, inject } from '@angular/core';
import {
  HttpInterceptor,
  HttpRequest,
  HttpHandler,
  HttpEvent
} from '@angular/common/http';
import { Observable } from 'rxjs';

/**
 * Interceptor HTTP que inyecta el token JWT en todos los requests.
 * 
 * Agrega el header 'Authorization: Bearer <token>' a todas las peticiones
 * excepto para endpoints públicos como login y registro.
 */
@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  
  intercept(
    request: HttpRequest<any>,
    next: HttpHandler
  ): Observable<HttpEvent<any>> {
    // No agregar token a endpoints públicos
    if (request.url.includes('/auth/login') || request.url.includes('/auth/register')) {
      return next.handle(request);
    }

    // Obtener token del localStorage
    const token = this.getToken();

    // Si hay token, agregarlo al header
    if (token) {
      request = request.clone({
        setHeaders: {
          Authorization: `Bearer ${token}`
        }
      });
    }

    return next.handle(request);
  }

  /**
   * Obtener JWT token del localStorage
   */
  private getToken(): string | null {
    try {
      const token = localStorage.getItem('access_token');
      return token;
    } catch (error) {
      console.error('Error obteniendo token:', error);
      return null;
    }
  }
}
