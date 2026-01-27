import { HttpInterceptorFn } from '@angular/common/http';

export const authInterceptor: HttpInterceptorFn = (req, next) => {
  // Solo añadir headers a las peticiones que van a la API
  if (req.url.includes('/api/')) {
    // Obtener datos del usuario del localStorage
    const usuarioData = localStorage.getItem('usuario');
    if (usuarioData) {
      try {
        const usuario = JSON.parse(usuarioData);

        // Clonar la petición y añadir los headers de autenticación
        req = req.clone({
          setHeaders: {
            'X-User-DNI': usuario.dni || '',
            'X-User-Rol': usuario.rol || '',
            'X-User-Nombre': usuario.nombre || '',
            'X-User-Email': usuario.email || ''
          }
        });
      } catch (error) {
      }
    }
  }

  return next(req);
};